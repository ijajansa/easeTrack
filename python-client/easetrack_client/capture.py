from __future__ import annotations

from datetime import datetime
from pathlib import Path

import pyautogui


def capture_screenshot(output_dir: Path) -> Path:
    output_dir.mkdir(parents=True, exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    file_path = output_dir / f"screenshot_{timestamp}.png"
    image = pyautogui.screenshot()
    image.save(file_path)
    return file_path

