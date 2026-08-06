from __future__ import annotations

import logging
import time
from pathlib import Path

from .activity import ActivityTracker, IMPORT_ERROR
from .capture import capture_screenshot
from .config import load_config
from .uploader import fetch_settings, flush_queue, push_activity, queue_failed_file, upload_screenshot


def main() -> None:
    config = load_config()
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

    settings = {}
    interval_seconds = config.default_interval_seconds
    activity_interval_seconds = config.activity_report_interval_seconds
    idle_threshold_seconds = config.idle_threshold_seconds
    enabled = True
    next_settings_refresh = 0.0
    next_screenshot_at = time.monotonic()
    next_activity_push_at = time.monotonic() + activity_interval_seconds
    tracker = ActivityTracker(idle_threshold_seconds)
    tracker.start()
    if tracker.monitor_enabled:
        logger.info("Using event-based mouse and keyboard monitoring for idle tracking.")
    else:
        if IMPORT_ERROR:
            logger.warning("Event-based monitoring unavailable; pynput import failed: %s", IMPORT_ERROR)
        else:
            logger.warning("Event-based monitoring unavailable; listener startup failed or was blocked. Falling back to Windows idle API.")

    while True:
        loop_start = time.monotonic()
        now = loop_start

        if now >= next_settings_refresh:
            try:
                settings = fetch_settings(config)
                interval_seconds = int(settings.get("interval", interval_seconds))
                enabled = bool(settings.get("enabled", True))
                activity_interval_seconds = int(settings.get("activity_interval_seconds", activity_interval_seconds))
                idle_threshold_seconds = int(settings.get("idle_threshold_seconds", idle_threshold_seconds))
            except Exception:
                pass
            next_settings_refresh = now + 60

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
                push_activity(config, working_seconds, idle_seconds, status)
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
            screenshot_path = capture_screenshot(capture_dir)
            try:
                upload_screenshot(config, screenshot_path)
            except Exception:
                queue_failed_file(config, screenshot_path)
            next_screenshot_at = now + interval_seconds

            try:
                flush_queue(config)
            except Exception:
                pass

        elapsed = time.monotonic() - loop_start
        time.sleep(max(0.0, 1.0 - elapsed))


if __name__ == "__main__":
    main()
