from __future__ import annotations

import logging
from datetime import datetime
from pathlib import Path

import pyautogui
from PIL import ImageGrab

logger = logging.getLogger(__name__)


def capture_screenshot(output_dir: Path) -> Path:
    output_dir.mkdir(parents=True, exist_ok=True)
    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
    file_path = output_dir / f"screenshot_{timestamp}.jpg"
    logger.info("Capturing screenshot to %s", file_path)
    image = None
    try:
        image = pyautogui.screenshot()
        _save_optimized(image, file_path)
        logger.info("Screenshot saved using pyautogui: %s", file_path)
        return file_path
    except Exception as exc:
        logger.warning("pyautogui screenshot failed, trying ImageGrab fallback: %s", exc)

    image = ImageGrab.grab()
    _save_optimized(image, file_path)
    logger.info("Screenshot saved using ImageGrab: %s", file_path)
    return file_path


def _save_optimized(image, file_path: Path) -> None:
    rgb_image = image.convert("RGB")
    rgb_image.save(file_path, format="JPEG", quality=82, optimize=True, progressive=True)
    logger.info("Optimized screenshot size: %.2f MB", file_path.stat().st_size / (1024 * 1024))
