from __future__ import annotations

import logging
import sys
import threading
from pathlib import Path
from typing import Callable

from PIL import Image

logger = logging.getLogger(__name__)

try:
    import pystray
except Exception as exc:  # pragma: no cover - optional dependency
    pystray = None
    IMPORT_ERROR = f"{exc.__class__.__name__}: {exc}"
else:
    IMPORT_ERROR = None


def _icon_source_path() -> Path | None:
    bundle_root = Path(getattr(sys, "_MEIPASS", ""))
    for packaged_name in ("icon.png", "snaptrack-icon.png"):
        packaged_icon = bundle_root / "assets" / packaged_name
        if packaged_icon.exists():
            return packaged_icon

    repo_icon = (Path(__file__).resolve().parents[1] / ".." / "backend" / "public" / "assets" / "img" / "icon.png").resolve()
    return repo_icon if repo_icon.exists() else None


def _load_icon_image():
    icon_path = _icon_source_path()
    if icon_path is None:
        return None

    try:
        return Image.open(icon_path)
    except Exception:
        logger.exception("Unable to load tray icon from %s", icon_path)
        return None


class TrayController:
    def __init__(
        self,
        on_pause: Callable[[bool], None],
        on_exit: Callable[[], None],
        on_uninstall: Callable[[], None],
    ) -> None:
        self._on_pause = on_pause
        self._on_exit = on_exit
        self._on_uninstall = on_uninstall
        self._paused = False
        self._icon = None
        self._thread: threading.Thread | None = None

    @property
    def available(self) -> bool:
        return pystray is not None and IMPORT_ERROR is None

    def start(self) -> None:
        if not self.available:
            logger.warning("Tray menu unavailable: %s", IMPORT_ERROR or "pystray not installed")
            return

        icon_image = _load_icon_image()
        if icon_image is None:
            logger.warning("Tray menu unavailable: brand icon could not be loaded.")
            return

        self._icon = pystray.Icon(
            "SnapTrack",
            icon_image,
            "SnapTrack",
            menu=pystray.Menu(
                pystray.MenuItem(self._pause_label, self._toggle_pause, default=True),
                pystray.MenuItem("Exit", self._exit),
                pystray.MenuItem("Uninstall", self._uninstall),
            ),
        )

        self._thread = threading.Thread(target=self._icon.run, name="SnapTrackTray", daemon=True)
        self._thread.start()
        logger.info("Tray menu started.")

    @property
    def _pause_label(self) -> str:
        return "Resume" if self._paused else "Pause"

    def _toggle_pause(self, _icon=None, _item=None) -> None:
        self._paused = not self._paused
        self._on_pause(self._paused)
        if self._icon is not None:
            try:
                self._icon.menu = pystray.Menu(
                    pystray.MenuItem(self._pause_label, self._toggle_pause, default=True),
                    pystray.MenuItem("Exit", self._exit),
                    pystray.MenuItem("Uninstall", self._uninstall),
                )
                self._icon.update_menu()
            except Exception:
                logger.exception("Unable to refresh tray menu.")

    def _exit(self, _icon=None, _item=None) -> None:
        self._on_exit()
        if self._icon is not None:
            self._icon.stop()

    def _uninstall(self, _icon=None, _item=None) -> None:
        self._on_uninstall()
        if self._icon is not None:
            self._icon.stop()
