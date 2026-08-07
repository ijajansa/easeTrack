from __future__ import annotations

import os
import sys
from pathlib import Path
from typing import Any

from easetrack_client.config import ClientConfig, default_config_path, load_config, save_config


_SETUP_CANCELLED = object()


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

    data = _prompt_with_gui(config_path)
    if data is _SETUP_CANCELLED:
        return None

    if data is None:
        data = _prompt_with_console()
        save_config(data, config_path)
    else:
        return load_config(config_path)

    return load_config(config_path)


def _prompt_with_gui(config_path: Path) -> dict[str, Any] | None:
    try:
        import tkinter as tk
        from tkinter import ttk
    except Exception:
        return None

    result: dict[str, Any] = {}
    cancelled = {"value": False}
    toast_window: tk.Toplevel | None = None
    submit_button: ttk.Button | None = None
    cancel_button: ttk.Button | None = None

    root = tk.Tk()
    root.title("SnapTrack Setup")
    root.geometry("760x760")
    root.minsize(720, 720)
    root.resizable(False, False)
    root.configure(bg="#eef2ff")
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

    style = ttk.Style(root)
    try:
        style.theme_use("clam")
    except Exception:
        pass

    style.configure("Setup.TFrame", background="#eef2ff")
    style.configure("Panel.TFrame", background="#ffffff")
    style.configure("Title.TLabel", font=("Segoe UI", 20, "bold"), background="#ffffff", foreground="#0f172a")
    style.configure("Body.TLabel", font=("Segoe UI", 10), background="#ffffff", foreground="#475569")
    style.configure("Field.TLabel", font=("Segoe UI", 10, "bold"), background="#ffffff", foreground="#0f172a")
    style.configure("Setup.TButton", font=("Segoe UI", 10, "bold"), padding=(14, 10))

    outer = ttk.Frame(root, padding=18, style="Setup.TFrame")
    outer.grid(row=0, column=0, sticky="nsew")
    outer.grid_rowconfigure(0, weight=1)
    outer.grid_columnconfigure(0, weight=1)

    canvas = tk.Canvas(outer, bg="#eef2ff", highlightthickness=0, bd=0)
    scrollbar = ttk.Scrollbar(outer, orient="vertical", command=canvas.yview)
    scroll_frame = ttk.Frame(canvas, padding=22, style="Setup.TFrame")

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

    panel = ttk.Frame(frame, padding=22, style="Panel.TFrame")
    panel.pack(fill="both", expand=True)

    if icon_path.exists():
        try:
            preview = tk.PhotoImage(file=str(icon_path)).subsample(3, 3)
            ttk.Label(panel, image=preview, background="#ffffff").pack(anchor="w", pady=(0, 14))
            root._snaptrack_preview = preview  # Keep a reference for Tk.
        except Exception:
            pass

    ttk.Label(panel, text="SnapTrack First-Time Setup", style="Title.TLabel").pack(anchor="w", pady=(0, 8))
    ttk.Label(
        panel,
        text="Enter the employee details once. SnapTrack will save them locally, start automatically, and keep running until uninstall.",
        wraplength=620,
        style="Body.TLabel",
    ).pack(anchor="w", pady=(0, 18))

    details = ttk.Frame(panel, style="Panel.TFrame")
    details.pack(fill="x", pady=(0, 14))
    for column in range(2):
        details.grid_columnconfigure(column, weight=1, uniform="setupcols")
    ttk.Label(details, text="Employee details", style="Field.TLabel").grid(row=0, column=0, sticky="w")
    ttk.Label(details, text="Connection settings", style="Field.TLabel").grid(row=0, column=1, sticky="w")
    ttk.Label(details, text="Full name, device ID, and API token are required.", style="Body.TLabel").grid(row=1, column=0, sticky="w", pady=(4, 0))
    ttk.Label(details, text="Server URL should point to your admin panel domain.", style="Body.TLabel").grid(row=1, column=1, sticky="w", pady=(4, 0))

    fields: dict[str, tk.StringVar] = {}

    def add_field(label: str, key: str, default: str = "", show: str | None = None) -> None:
        ttk.Label(panel, text=label, style="Field.TLabel").pack(anchor="w", pady=(12, 4))
        var = tk.StringVar(value=default)
        entry = ttk.Entry(panel, textvariable=var, show=show)
        entry.pack(fill="x")
        fields[key] = var

    add_field("Full name", "full_name")
    add_field("Device ID", "device_id")
    add_field("API token", "api_token", show="*")
    add_field("Server URL", "server_url", _default_server_url())

    message = tk.StringVar(value="")
    ttk.Label(panel, textvariable=message, background="#ffffff", foreground="#b91c1c").pack(anchor="w", pady=(10, 0))

    def show_toast(title: str, text: str, success: bool = True) -> None:
        nonlocal toast_window

        if toast_window is not None and toast_window.winfo_exists():
            try:
                toast_window.destroy()
            except Exception:
                pass

        toast_window = tk.Toplevel(root)
        toast_window.overrideredirect(True)
        toast_window.attributes("-topmost", True)
        toast_window.configure(bg="#ffffff")

        width, height = 360, 120
        screen_w = toast_window.winfo_screenwidth()
        x = screen_w - width - 24
        y = 24
        toast_window.geometry(f"{width}x{height}+{x}+{y}")

        border = tk.Frame(toast_window, bg="#22c55e" if success else "#ef4444", bd=0)
        border.pack(fill="both", expand=True)
        card = tk.Frame(border, bg="#ffffff", padx=16, pady=14)
        card.pack(fill="both", expand=True, padx=4, pady=4)

        tk.Label(
            card,
            text=title,
            font=("Segoe UI", 12, "bold"),
            bg="#ffffff",
            fg="#0f172a",
            anchor="w",
        ).pack(fill="x")
        tk.Label(
            card,
            text=text,
            font=("Segoe UI", 10),
            bg="#ffffff",
            fg="#475569",
            justify="left",
            wraplength=320,
        ).pack(fill="x", pady=(8, 0))
        toast_window.after(2200, lambda: toast_window.destroy() if toast_window and toast_window.winfo_exists() else None)

    def submit() -> None:
        nonlocal submit_button, cancel_button

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
        save_config(result, config_path)
        message.set("")
        if submit_button is not None:
            submit_button.state(["disabled"])
        if cancel_button is not None:
            cancel_button.state(["disabled"])
        show_toast("Setup complete", "SnapTrack has been installed successfully and is ready to run.")
        root.after(1800, root.destroy)

    def cancel() -> None:
        cancelled["value"] = True
        root.destroy()

    footer = ttk.Frame(outer, padding=(22, 14, 22, 22))
    footer.grid(row=1, column=0, sticky="ew")
    footer.grid_columnconfigure(0, weight=1)

    ttk.Separator(footer).grid(row=0, column=0, columnspan=2, sticky="ew", pady=(0, 14))
    cancel_button = ttk.Button(footer, text="Cancel setup", command=cancel, style="Setup.TButton")
    cancel_button.grid(row=1, column=0, sticky="w")
    submit_button = ttk.Button(footer, text="Save and Start", command=submit, style="Setup.TButton")
    submit_button.grid(row=1, column=1, sticky="e")

    root.bind("<Escape>", lambda _event: cancel())
    root.protocol("WM_DELETE_WINDOW", cancel)
    root.mainloop()
    if cancelled["value"]:
        return _SETUP_CANCELLED

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
