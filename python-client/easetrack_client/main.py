from __future__ import annotations

import time
from pathlib import Path

from .activity import get_activity_status
from .capture import capture_screenshot
from .config import load_config
from .uploader import fetch_settings, flush_queue, push_activity, queue_failed_file, upload_screenshot


def main() -> None:
    config = load_config()
    capture_dir = Path(config.capture_folder)
    queue_dir = Path(config.queue_folder)
    capture_dir.mkdir(parents=True, exist_ok=True)
    queue_dir.mkdir(parents=True, exist_ok=True)

    settings = {}
    interval_seconds = config.default_interval_seconds
    activity_interval_seconds = config.activity_report_interval_seconds
    idle_threshold_seconds = config.idle_threshold_seconds
    enabled = True
    next_settings_refresh = 0.0
    next_screenshot_at = time.monotonic()
    next_activity_push_at = time.monotonic() + activity_interval_seconds
    pending_working_seconds = 0
    pending_idle_seconds = 0

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
            pending_working_seconds = 0
            pending_idle_seconds = 0
            next_activity_push_at = now + activity_interval_seconds
            next_screenshot_at = now + interval_seconds
            time.sleep(1)
            continue

        status, _idle_seconds = get_activity_status(idle_threshold_seconds)
        if status == "idle":
            pending_idle_seconds += 1
        else:
            pending_working_seconds += 1

        if now >= next_activity_push_at:
            try:
                push_activity(config, pending_working_seconds, pending_idle_seconds, status)
                pending_working_seconds = 0
                pending_idle_seconds = 0
                next_activity_push_at = now + activity_interval_seconds
            except Exception:
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
