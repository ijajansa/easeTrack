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

start /wait "" "%INSTALL_DIR%\SnapTrack.exe" --setup
set "SETUP_EXIT=%ERRORLEVEL%"

if "%SETUP_EXIT%"=="2" (
    if exist "%STARTUP_LINK%" del /F /Q "%STARTUP_LINK%" >nul 2>&1
    if exist "%INSTALL_DIR%" rmdir /S /Q "%INSTALL_DIR%" >nul 2>&1
    echo SnapTrack setup was cancelled. Cleanup completed.
    exit /b 2
)

if not "%SETUP_EXIT%"=="0" (
    echo SnapTrack setup failed with exit code %SETUP_EXIT%.
    exit /b %SETUP_EXIT%
)

powershell -NoProfile -ExecutionPolicy Bypass -Command "Add-Type -AssemblyName System.Windows.Forms; [System.Windows.Forms.MessageBox]::Show('SnapTrack installed successfully.','SnapTrack',[System.Windows.Forms.MessageBoxButtons]::OK,[System.Windows.Forms.MessageBoxIcon]::Information) | Out-Null"

echo SnapTrack installed to %INSTALL_DIR%.
endlocal
