# Python Client Starter

This folder contains the desktop agent that captures screenshots and uploads them
to the Laravel backend.

## Included

- configuration loader
- screenshot capture wrapper
- upload client
- offline queue handling
- main loop

## Install

```bash
pip install -r requirements.txt
```

## Run

```bash
python -m easetrack_client.main
```

## First Run

If no config file exists, the app opens a setup window and asks for:

- full name
- device ID
- API token
- server URL

The tracker saves the values locally and starts automatically after setup.

To force the setup screen again while testing:

```bash
python -m easetrack_client.main --setup
```

## Build Windows EXE

Install the build dependency once:

```bash
python -m pip install -r requirements-build.txt
```

Then build the EXE:

```powershell
powershell -ExecutionPolicy Bypass -File .\build_exe.ps1
```

The output will be in `dist\SnapTrack.exe`. Employees can run that file directly and complete the first-time setup once.

## Windows Install and Uninstall

After building the EXE, you can use the helper scripts:

```powershell
.\install.bat
```

This copies the EXE into `%LOCALAPPDATA%\SnapTrack`, creates a startup shortcut, and launches setup.

To remove it:

```powershell
.\uninstall.bat
```

The tray menu also includes:

- Pause
- Exit
- Uninstall

## Portable Package Or Installer

If Inno Setup is installed, this builds a real installer:

```powershell
powershell -ExecutionPolicy Bypass -File .\build_installer.ps1
```

If Inno Setup is not installed, the same script automatically creates a portable ZIP instead:

- `dist\portable\SnapTrack-portable.zip`

That ZIP contains:

- `SnapTrack.exe`
- `install.bat`
- `uninstall.bat`
- `README.md`
