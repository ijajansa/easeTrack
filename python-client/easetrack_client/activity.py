from __future__ import annotations

import ctypes
from ctypes import wintypes


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

        tick_count = ctypes.windll.kernel32.GetTickCount()
        return max(0.0, (tick_count - last_input.dwTime) / 1000.0)
    except Exception:
        return 0.0


def get_activity_status(idle_threshold_seconds: int) -> tuple[str, float]:
    idle_seconds = get_idle_seconds()
    return ("idle" if idle_seconds >= idle_threshold_seconds else "active", idle_seconds)

