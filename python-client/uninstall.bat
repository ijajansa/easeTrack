@echo off
setlocal

set "APP_DIR=%LOCALAPPDATA%\SnapTrack"
set "INSTALL_DIR=%LOCALAPPDATA%\Programs\SnapTrack"
set "STARTUP_LINK=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\SnapTrack.lnk"
set "DATA_DIR=%USERPROFILE%\.snaptrack"

taskkill /IM SnapTrack.exe /F /T >nul 2>&1

if exist "%STARTUP_LINK%" del /F /Q "%STARTUP_LINK%"
if exist "%APP_DIR%" rmdir /S /Q "%APP_DIR%"
if exist "%INSTALL_DIR%" rmdir /S /Q "%INSTALL_DIR%"
if exist "%DATA_DIR%" rmdir /S /Q "%DATA_DIR%"

echo SnapTrack removed.
endlocal
