from __future__ import annotations

import os
import sys
from pathlib import Path
from typing import Any

from easetrack_client.config import ClientConfig, default_config_path, load_config, save_config


def _repo_root() -> Path:
    return Path(__file__).resolve().parents[1]


def _icon_source_path() -> Path:
    bundle_root = Path(getattr(sys, "_MEIPASS", ""))
    for packaged_name in ("icon.png", "snaptrack-icon.png"):
        packaged_icon = bundle_root / "assets" / packaged_name
        if packaged_icon.exists():
            return packaged_icon

    candidate = _repo_root() / ".." / "backend" / "public" / "assets" / "img" / "icon.png"
    candidate = candidate.resolve()
    if candidate.exists():
        return candidate

    return candidate


def _default_server_url() -> str:
    return os.environ.get("SNAPTRACK_SERVER_URL", "http://127.0.0.1:8000").strip() or "http://127.0.0.1:8000"


def ensure_config(path: str | Path | None = None, force_setup: bool = False) -> ClientConfig:
    config_path = Path(path) if path is not None else default_config_path()
    if not force_setup and config_path.exists():
        return load_config(config_path)

    data = _prompt_with_gui()
    if data is None:
        data = _prompt_with_console()

    save_config(data, config_path)
    return load_config(config_path)


def _prompt_with_gui() -> dict[str, Any] | None:
    try:
        import tkinter as tk
        from tkinter import ttk
    except Exception:
        return None

    result: dict[str, Any] = {}

    root = tk.Tk()
    root.title("SnapTrack Setup")
    root.geometry("620x680")
    root.minsize(560, 620)
    root.resizable(False, False)
    root.configure(bg="#f8fafc")
    root.attributes("-topmost", True)

    icon_path = _icon_source_path()
    if icon_path.exists():
        try:
            icon_image = tk.PhotoImage(file=str(icon_path))
            root.iconphoto(True, icon_image)
            root._snaptrack_icon = icon_image  # Keep a reference for Tk.
        except Exception:
            try:
                root.iconbitmap(default=str(icon_path))
            except Exception:
                pass

    root.grid_rowconfigure(0, weight=1)
    root.grid_columnconfigure(0, weight=1)

    outer = ttk.Frame(root, padding=18)
    outer.grid(row=0, column=0, sticky="nsew")
    outer.grid_rowconfigure(0, weight=1)
    outer.grid_columnconfigure(0, weight=1)

    canvas = tk.Canvas(outer, bg="#f8fafc", highlightthickness=0, bd=0)
    scrollbar = ttk.Scrollbar(outer, orient="vertical", command=canvas.yview)
    scroll_frame = ttk.Frame(canvas, padding=22)

    scroll_frame.bind(
        "<Configure>",
        lambda _event: canvas.configure(scrollregion=canvas.bbox("all")),
    )

    canvas_window = canvas.create_window((0, 0), window=scroll_frame, anchor="nw")
    canvas.configure(yscrollcommand=scrollbar.set)

    def _sync_width(event) -> None:
        canvas.itemconfigure(canvas_window, width=event.width)

    canvas.bind("<Configure>", _sync_width)

    canvas.grid(row=0, column=0, sticky="nsew")
    scrollbar.grid(row=0, column=1, sticky="ns")

    frame = scroll_frame

    if icon_path.exists():
        try:
            preview = tk.PhotoImage(file=str(icon_path)).subsample(4, 4)
            ttk.Label(frame, image=preview).pack(anchor="w", pady=(0, 10))
            root._snaptrack_preview = preview  # Keep a reference for Tk.
        except Exception:
            pass

    ttk.Label(frame, text="SnapTrack First-Time Setup", font=("Segoe UI", 18, "bold")).pack(anchor="w", pady=(0, 8))
    ttk.Label(
        frame,
        text="Enter the details provided by your admin. The tracker will save them locally and run until uninstall.",
        wraplength=500,
        foreground="#475569",
    ).pack(anchor="w", pady=(0, 16))

    fields: dict[str, tk.StringVar] = {}

    def add_field(label: str, key: str, default: str = "", show: str | None = None) -> None:
        ttk.Label(frame, text=label).pack(anchor="w", pady=(10, 4))
        var = tk.StringVar(value=default)
        entry = ttk.Entry(frame, textvariable=var, show=show)
        entry.pack(fill="x")
        fields[key] = var

    add_field("Full name", "full_name")
    add_field("Device ID", "device_id")
    add_field("API token", "api_token", show="*")
    add_field("Server URL", "server_url", _default_server_url())

    message = tk.StringVar(value="")
    ttk.Label(frame, textvariable=message, foreground="#b91c1c").pack(anchor="w", pady=(10, 0))

    def submit() -> None:
        values = {key: var.get().strip() for key, var in fields.items()}

        if not values["full_name"] or not values["device_id"] or not values["api_token"] or not values["server_url"]:
            message.set("Please fill in all fields.")
            return

        result.update(
            {
                "full_name": values["full_name"],
                "device_id": values["device_id"],
                "api_token": values["api_token"],
                "server_url": values["server_url"],
                "capture_folder": "captures",
                "queue_folder": "queue",
                "default_interval_seconds": 10,
                "activity_report_interval_seconds": 60,
                "idle_threshold_seconds": 30,
                "timeout_seconds": 20,
            }
        )
        root.destroy()

    footer = ttk.Frame(outer, padding=(22, 14, 22, 22))
    footer.grid(row=1, column=0, columnspan=2, sticky="ew")
    footer.grid_columnconfigure(0, weight=1)

    ttk.Separator(footer).grid(row=0, column=0, columnspan=2, sticky="ew", pady=(0, 14))
    ttk.Button(footer, text="Save and Start", command=submit).grid(row=1, column=1, sticky="e")

    root.protocol("WM_DELETE_WINDOW", root.destroy)
    root.mainloop()
    return result or None


def _prompt_with_console() -> dict[str, Any]:
    print("SnapTrack setup")
    full_name = input("Full name: ").strip()
    device_id = input("Device ID: ").strip()
    api_token = input("API token: ").strip()
    server_url = input(f"Server URL [{_default_server_url()}]: ").strip() or _default_server_url()

    return {
        "full_name": full_name,
        "device_id": device_id,
        "api_token": api_token,
        "server_url": server_url,
        "capture_folder": "captures",
        "queue_folder": "queue",
        "default_interval_seconds": 10,
        "activity_report_interval_seconds": 60,
        "idle_threshold_seconds": 30,
        "timeout_seconds": 20,
    }
