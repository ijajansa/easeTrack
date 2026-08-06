from __future__ import annotations

import json
import sys
import shutil
from dataclasses import dataclass
from pathlib import Path


@dataclass(frozen=True)
class ClientConfig:
    full_name: str
    device_id: str
    api_token: str
    server_url: str
    capture_folder: Path
    queue_folder: Path
    default_interval_seconds: int = 300
    activity_report_interval_seconds: int = 60
    idle_threshold_seconds: int = 10
    timeout_seconds: int = 20


def default_config_path() -> Path:
    return Path.home() / ".snaptrack" / "config.json"


def default_data_dir() -> Path:
    return default_config_path().parent


def resolve_config_path(path: str | Path | None = None) -> Path:
    if path is not None:
        return Path(path)

    return default_config_path()


def is_packaged() -> bool:
    return bool(getattr(sys, "frozen", False))


def save_config(data: dict, path: str | Path | None = None) -> Path:
    config_path = resolve_config_path(path)
    config_path.parent.mkdir(parents=True, exist_ok=True)
    config_path.write_text(json.dumps(data, indent=2), encoding="utf-8")
    return config_path


def load_config(path: str | Path | None = None) -> ClientConfig:
    config_path = resolve_config_path(path)
    data = json.loads(config_path.read_text(encoding="utf-8"))
    base_dir = config_path.parent

    return ClientConfig(
        full_name=str(data.get("full_name", data.get("employee_name", ""))).strip(),
        device_id=data["device_id"],
        api_token=data["api_token"],
        server_url=data["server_url"].rstrip("/"),
        capture_folder=(base_dir / data.get("capture_folder", "captures")).resolve(),
        queue_folder=(base_dir / data.get("queue_folder", "queue")).resolve(),
        default_interval_seconds=int(data.get("default_interval_seconds", 300)),
        activity_report_interval_seconds=int(data.get("activity_report_interval_seconds", 60)),
        idle_threshold_seconds=int(data.get("idle_threshold_seconds", 10)),
        timeout_seconds=int(data.get("timeout_seconds", 20)),
    )


def remove_local_data(path: str | Path | None = None) -> None:
    data_dir = resolve_config_path(path).parent if path is not None else default_data_dir()
    if data_dir.exists():
        shutil.rmtree(data_dir, ignore_errors=True)
