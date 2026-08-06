from __future__ import annotations

import shutil
from pathlib import Path

import requests

from .config import ClientConfig


def _headers(config: ClientConfig) -> dict[str, str]:
    return {
        "X-Device-Id": config.device_id,
        "X-Api-Token": config.api_token,
    }


def fetch_settings(config: ClientConfig) -> dict:
    response = requests.get(
        f"{config.server_url}/api/settings",
        headers=_headers(config),
        timeout=config.timeout_seconds,
    )
    response.raise_for_status()
    return response.json()


def push_activity(config: ClientConfig, working_seconds: int, idle_seconds: int, status: str) -> bool:
    response = requests.post(
        f"{config.server_url}/api/activity",
        headers={**_headers(config), "Content-Type": "application/json"},
        json={
            "working_seconds": working_seconds,
            "idle_seconds": idle_seconds,
            "status": status,
        },
        timeout=config.timeout_seconds,
    )
    response.raise_for_status()
    return True


def upload_screenshot(config: ClientConfig, file_path: Path) -> bool:
    with file_path.open("rb") as handle:
        files = {"file": (file_path.name, handle, "image/png")}
        response = requests.post(
            f"{config.server_url}/api/upload",
            headers=_headers(config),
            files=files,
            timeout=config.timeout_seconds,
        )
    response.raise_for_status()
    return True


def queue_failed_file(config: ClientConfig, file_path: Path) -> Path:
    config.queue_folder.mkdir(parents=True, exist_ok=True)
    queued_path = config.queue_folder / file_path.name
    shutil.copy2(file_path, queued_path)
    return queued_path


def flush_queue(config: ClientConfig) -> None:
    if not config.queue_folder.exists():
        return

    for queued_file in sorted(config.queue_folder.glob("*")):
        if not queued_file.is_file():
            continue
        try:
            if upload_screenshot(config, queued_file):
                queued_file.unlink(missing_ok=True)
        except Exception:
            return
