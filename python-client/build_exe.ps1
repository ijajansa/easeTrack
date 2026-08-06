Param(
    [string]$Name = "SnapTrack"
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
$BackendIcon = Join-Path $Root "..\backend\public\assets\img\icon.png"
$ExeIcon = Join-Path $Root "..\backend\public\assets\img\icon.png"
Set-Location $Root

if (-not (Get-Command python -ErrorAction SilentlyContinue)) {
    throw "Python is required to build the EXE."
}

python -m PyInstaller --noconfirm --clean --onefile --windowed `
    --name $Name `
    --icon $ExeIcon `
    --paths $Root `
    --collect-submodules easetrack_client `
    --add-data "$BackendIcon;assets" `
    --collect-all pyautogui `
    --collect-all pynput `
    --collect-all pystray `
    --collect-all PIL `
    easetrack_client\__main__.py

Write-Host "Build complete. Check the dist folder for $Name.exe"
