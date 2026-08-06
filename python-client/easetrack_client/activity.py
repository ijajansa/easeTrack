from __future__ import annotations

import ctypes
import time
from dataclasses import dataclass, field
from ctypes import wintypes
from threading import Lock
from typing import Any

IMPORT_ERROR: str | None = None

try:
    from pynput import keyboard, mouse
except Exception as exc:  # pragma: no cover - optional fallback for non-Windows/test environments
    keyboard = None
    mouse = None
    IMPORT_ERROR = f"{exc.__class__.__name__}: {exc}"


def get_idle_seconds() -> float:
    try:
        class LASTINPUTINFO(ctypes.Structure):
            _fields_ = [
                ("cbSize", wintypes.UINT),
                ("dwTime", wintypes.DWORD),
            ]

        last_input = LASTINPUTINFO()
        last_input.cbSize = ctypes.sizeof(LASTINPUTINFO)

        if not ctypes.windll.user32.GetLastInputInfo(ctypes.byref(last_input)):
            return 0.0

        kernel32 = ctypes.windll.kernel32
        get_tick_count_64 = getattr(kernel32, "GetTickCount64", None)
        tick_count = get_tick_count_64() if get_tick_count_64 else kernel32.GetTickCount()
        return max(0.0, (tick_count - last_input.dwTime) / 1000.0)
    except Exception:
        return 0.0


def get_activity_status(idle_threshold_seconds: int) -> tuple[str, float]:
    idle_seconds = get_idle_seconds()
    return ("idle" if idle_seconds >= idle_threshold_seconds else "active", idle_seconds)


@dataclass
class ActivityTracker:
    idle_threshold_seconds: int
    sleep_gap_seconds: int = 45
    working_seconds: float = 0.0
    idle_seconds: float = 0.0
    last_tick: float = field(default_factory=time.monotonic)
    last_state: str = "active"
    last_input_at: float = field(default_factory=time.monotonic)
    monitor_enabled: bool = False
    _listeners: list[Any] = field(default_factory=list, init=False, repr=False)
    _lock: Lock = field(default_factory=Lock, init=False, repr=False)

    def start(self) -> None:
        if keyboard is None or mouse is None:
            return

        try:
            self._listeners = [
                mouse.Listener(on_move=self._mark_input, on_click=self._mark_input, on_scroll=self._mark_input),
                keyboard.Listener(on_press=self._mark_input, on_release=self._mark_input),
            ]

            for listener in self._listeners:
                listener.start()

            self.monitor_enabled = True
        except Exception:
            self._listeners = []
            self.monitor_enabled = False

    def _mark_input(self, *_args: Any) -> None:
        with self._lock:
            self.last_input_at = time.monotonic()

    def _current_idle_seconds(self) -> float:
        if not self.monitor_enabled:
            return get_idle_seconds()

        with self._lock:
            elapsed = time.monotonic() - self.last_input_at
        return max(0.0, elapsed)

    def sample(self) -> tuple[str, bool, bool]:
        now = time.monotonic()
        elapsed = max(0.0, now - self.last_tick)

        resumed_from_sleep = elapsed >= self.sleep_gap_seconds
        if resumed_from_sleep:
            # Do not charge suspended laptop time to either working or idle.
            with self._lock:
                self.last_input_at = now
            self.last_tick = now

        idle_seconds = self._current_idle_seconds()
        state = "idle" if idle_seconds >= self.idle_threshold_seconds else "active"
        changed = state != self.last_state

        if elapsed:
            if resumed_from_sleep:
                self.last_tick = now
                self.last_state = state
                return state, changed, True

            if state == "idle":
                self.idle_seconds += elapsed
            else:
                self.working_seconds += elapsed

        self.last_tick = now
        self.last_state = state
        return state, changed, False

    def drain(self) -> tuple[int, int]:
        working_seconds = int(round(self.working_seconds))
        idle_seconds = int(round(self.idle_seconds))
        self.working_seconds = 0.0
        self.idle_seconds = 0.0
        return working_seconds, idle_seconds

    def reset(self) -> None:
        self.working_seconds = 0.0
        self.idle_seconds = 0.0
        self.last_tick = time.monotonic()
        self.last_state = "active"
        with self._lock:
            self.last_input_at = time.monotonic()
