from __future__ import annotations

import json
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True)
class ClientConfig:
    device_id: str
    api_token: str
    server_url: str
    capture_folder: Path
    queue_folder: Path
    default_interval_seconds: int = 300
    activity_report_interval_seconds: int = 60
    idle_threshold_seconds: int = 10
    timeout_seconds: int = 20


def load_config(path: str | Path = "config.json") -> ClientConfig:
    config_path = Path(path)
    data = json.loads(config_path.read_text(encoding="utf-8"))

    return ClientConfig(
        device_id=data["device_id"],
        api_token=data["api_token"],
        server_url=data["server_url"].rstrip("/"),
        capture_folder=Path(data.get("capture_folder", "captures")),
        queue_folder=Path(data.get("queue_folder", "queue")),
        default_interval_seconds=int(data.get("default_interval_seconds", 300)),
        activity_report_interval_seconds=int(data.get("activity_report_interval_seconds", 60)),
        idle_threshold_seconds=int(data.get("idle_threshold_seconds", 10)),
        timeout_seconds=int(data.get("timeout_seconds", 20)),
    )
