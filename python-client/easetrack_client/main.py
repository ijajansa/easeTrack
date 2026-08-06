from __future__ import annotations

import argparse
import logging
import subprocess
import sys
import threading
import time
from dataclasses import replace
from pathlib import Path

from easetrack_client.activity import ActivityTracker, IMPORT_ERROR
from easetrack_client.capture import capture_screenshot
from easetrack_client.config import default_data_dir, is_packaged, remove_local_data
from easetrack_client.onboarding import ensure_config
from easetrack_client.uploader import fetch_settings, flush_queue, push_activity, queue_failed_file, upload_screenshot
from easetrack_client.tray import TrayController, IMPORT_ERROR as TRAY_IMPORT_ERROR


def main() -> None:
    parser = argparse.ArgumentParser(add_help=True)
    parser.add_argument("--setup", action="store_true", help="Force the first-run setup window")
    parser.add_argument("--uninstall", action="store_true", help="Remove local SnapTrack data and exit")
    parser.add_argument("--reset", action="store_true", help="Alias for --uninstall")
    args = parser.parse_args()

    if args.uninstall or args.reset:
        uninstall_client()
        return

    config = ensure_config(force_setup=args.setup)
    capture_dir = Path(config.capture_folder)
    queue_dir = Path(config.queue_folder)
    log_dir = Path("logs")
    capture_dir.mkdir(parents=True, exist_ok=True)
    queue_dir.mkdir(parents=True, exist_ok=True)
    log_dir.mkdir(parents=True, exist_ok=True)

    logging.basicConfig(
        level=logging.INFO,
        format="%(asctime)s [%(levelname)s] %(name)s: %(message)s",
        handlers=[
            logging.FileHandler(log_dir / "easetrack-client.log", encoding="utf-8"),
            logging.StreamHandler(),
        ],
    )
    logger = logging.getLogger(__name__)
    logger.info("Capture folder: %s", capture_dir.resolve())
    logger.info("Queue folder: %s", queue_dir.resolve())
    logger.info("Tray menu: %s", "enabled" if is_packaged() and TRAY_IMPORT_ERROR is None else "disabled")

    settings = {}
    interval_seconds = config.default_interval_seconds
    activity_interval_seconds = config.activity_report_interval_seconds
    idle_threshold_seconds = config.idle_threshold_seconds
    timeout_seconds = config.timeout_seconds
    runtime_config = config
    enabled = True
    paused = False
    next_settings_refresh = 0.0
    next_screenshot_at = time.monotonic()
    next_activity_push_at = time.monotonic() + activity_interval_seconds
    tracker = ActivityTracker(idle_threshold_seconds)
    tracker.start()
    exit_event = threading.Event()
    pause_event = threading.Event()
    pause_event.set()
    uninstall_event = threading.Event()

    tray = TrayController(
        on_pause=lambda is_paused: pause_event.clear() if is_paused else pause_event.set(),
        on_exit=exit_event.set,
        on_uninstall=lambda: (uninstall_event.set(), exit_event.set()),
    )
    if is_packaged():
        tray.start()

    logger.info("Client initialized for %s (%s).", config.full_name or "Unknown", config.device_id)
    if tracker.monitor_enabled:
        logger.info("Using event-based mouse and keyboard monitoring for idle tracking.")
    else:
        if IMPORT_ERROR:
            logger.warning("Event-based monitoring unavailable; pynput import failed: %s", IMPORT_ERROR)
        else:
            logger.warning("Event-based monitoring unavailable; listener startup failed or was blocked. Falling back to Windows idle API.")

    while not exit_event.is_set():
        loop_start = time.monotonic()
        now = loop_start

        current_paused = not pause_event.is_set()
        if current_paused != paused:
            paused = current_paused
            if paused:
                tracker.reset()
                logger.info("Tracking paused from tray menu.")
            else:
                tracker.reset()
                next_activity_push_at = now + activity_interval_seconds
                next_screenshot_at = now + interval_seconds
                logger.info("Tracking resumed from tray menu.")

        if now >= next_settings_refresh:
            try:
                settings = fetch_settings(runtime_config)
                interval_seconds = int(settings.get("interval", interval_seconds))
                enabled = bool(settings.get("enabled", True))
                activity_interval_seconds = int(settings.get("activity_interval_seconds", activity_interval_seconds))
                idle_threshold_seconds = int(settings.get("idle_threshold_seconds", idle_threshold_seconds))
                timeout_seconds = int(settings.get("timeout_seconds", timeout_seconds))
                runtime_config = replace(config, timeout_seconds=timeout_seconds)
            except Exception:
                pass
            next_settings_refresh = now + 60

        if paused:
            tracker.reset()
            next_activity_push_at = now + activity_interval_seconds
            next_screenshot_at = now + interval_seconds
            time.sleep(1)
            continue

        if not enabled:
            tracker.reset()
            next_activity_push_at = now + activity_interval_seconds
            next_screenshot_at = now + interval_seconds
            logger.info("Tracking disabled by server settings; activity counters reset.")
            time.sleep(1)
            continue

        status, changed, resumed_from_sleep = tracker.sample()

        if resumed_from_sleep:
            logger.info("System sleep detected; resetting activity timing from wake-up time.")

        def push_bucket() -> bool:
            working_seconds = int(round(tracker.working_seconds))
            idle_seconds = int(round(tracker.idle_seconds))
            raw_idle_seconds = int(round(tracker._current_idle_seconds()))

            if working_seconds == 0 and idle_seconds == 0:
                return True

            logger.info(
                "Pushing activity bucket: status=%s working_seconds=%s idle_seconds=%s raw_idle_seconds=%s threshold=%ss",
                status,
                working_seconds,
                idle_seconds,
                raw_idle_seconds,
                idle_threshold_seconds,
            )
            try:
                push_activity(runtime_config, working_seconds, idle_seconds, status)
            except Exception:
                logger.exception("Failed to push activity bucket.")
                return False

            tracker.drain()
            return True

        if changed:
            if push_bucket():
                next_activity_push_at = now + activity_interval_seconds
            else:
                next_activity_push_at = now + 5

        if now >= next_activity_push_at:
            if push_bucket():
                next_activity_push_at = now + activity_interval_seconds
            else:
                next_activity_push_at = now + 5

        if now >= next_screenshot_at:
            try:
                screenshot_path = capture_screenshot(capture_dir)
            except Exception:
                logger.exception("Screenshot capture failed.")
                next_screenshot_at = now + max(5, interval_seconds)
                time.sleep(1)
                continue

            try:
                upload_screenshot(runtime_config, screenshot_path)
            except Exception:
                queue_failed_file(config, screenshot_path)
                logger.warning("Screenshot upload failed; queued locally at %s.", screenshot_path)
            else:
                logger.info("Screenshot uploaded: %s", screenshot_path)
            next_screenshot_at = now + interval_seconds

            try:
                flush_queue(runtime_config)
            except Exception:
                pass

        elapsed = time.monotonic() - loop_start
        time.sleep(max(0.0, 1.0 - elapsed))

    if uninstall_event.is_set():
        uninstall_client()


def uninstall_client() -> None:
    logger = logging.getLogger(__name__)
    logger.info("Uninstall requested.")

    data_dir = default_data_dir()
    remove_local_data()
    logger.info("Removed local SnapTrack data: %s", data_dir)

    if is_packaged():
        exe_path = Path(sys.executable)
        command = (
            f"timeout /t 2 /nobreak >nul "
            f"& del /f /q \"{exe_path}\" "
            f"& exit /b 0"
        )
        subprocess.Popen(["cmd", "/c", command], creationflags=subprocess.CREATE_NO_WINDOW)
        logger.info("Scheduled EXE removal: %s", exe_path)
    else:
        logger.info("Source-mode uninstall finished. Delete the project folder if desired.")


if __name__ == "__main__":
    main()
