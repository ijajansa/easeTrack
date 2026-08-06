# SnapTrack Installer

This folder contains the Inno Setup script for producing a real Windows installer.

## Requirements

- Build the EXE first with `build_exe.ps1`
- Install [Inno Setup 6](https://jrsoftware.org/isinfo.php)

## Build

```powershell
powershell -ExecutionPolicy Bypass -File .\build_installer.ps1
```

The installer will be created under `dist\installer\SnapTrack-Setup.exe`.

## What it does

- installs SnapTrack into `%LOCALAPPDATA%\Programs\SnapTrack`
- creates Start Menu entries
- adds an uninstall entry in Apps & Features
- optionally creates a desktop shortcut
- optionally starts with Windows
