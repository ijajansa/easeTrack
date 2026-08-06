@echo off
setlocal

set "ROOT=%~dp0"
set "SOURCE=%ROOT%dist\SnapTrack.exe"
if not exist "%SOURCE%" set "SOURCE=%ROOT%SnapTrack.exe"
set "INSTALL_DIR=%LOCALAPPDATA%\SnapTrack"
set "STARTUP_LINK=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\SnapTrack.lnk"

if not exist "%SOURCE%" (
    echo SnapTrack.exe was not found. Build the EXE first.
    exit /b 1
)

taskkill /IM SnapTrack.exe /F /T >nul 2>&1

if not exist "%INSTALL_DIR%" mkdir "%INSTALL_DIR%"
copy /Y "%SOURCE%" "%INSTALL_DIR%\SnapTrack.exe" >nul

powershell -NoProfile -ExecutionPolicy Bypass -Command "$ws = New-Object -ComObject WScript.Shell; $s = $ws.CreateShortcut('%STARTUP_LINK%'); $s.TargetPath = '%INSTALL_DIR%\SnapTrack.exe'; $s.WorkingDirectory = '%INSTALL_DIR%'; $s.WindowStyle = 7; $s.Save()"

start "" "%INSTALL_DIR%\SnapTrack.exe" --setup

echo SnapTrack installed to %INSTALL_DIR%.
endlocal
