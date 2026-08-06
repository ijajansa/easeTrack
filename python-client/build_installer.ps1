Param(
    [string]$InnoSetupCompiler = "C:\Program Files (x86)\Inno Setup 6\ISCC.exe"
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $Root

$ExePath = Join-Path $Root "dist\SnapTrack.exe"
$InstallerScript = Join-Path $Root "installer\SnapTrack.iss"
$PortableDir = Join-Path $Root "dist\portable"
$PortableZip = Join-Path $PortableDir "SnapTrack-portable.zip"

if (-not (Test-Path $ExePath)) {
    throw "SnapTrack.exe not found in dist. Build the EXE first."
}

if (-not (Test-Path $InnoSetupCompiler)) {
    New-Item -ItemType Directory -Force $PortableDir | Out-Null

    $tempBundle = Join-Path $PortableDir "SnapTrack"
    if (Test-Path $tempBundle) {
        Remove-Item -Recurse -Force $tempBundle
    }

    New-Item -ItemType Directory -Force $tempBundle | Out-Null
    Copy-Item $ExePath (Join-Path $tempBundle "SnapTrack.exe") -Force
    Copy-Item (Join-Path $Root "install.bat") $tempBundle -Force
    Copy-Item (Join-Path $Root "uninstall.bat") $tempBundle -Force
    Copy-Item (Join-Path $Root "README.md") $tempBundle -Force

    if (Test-Path $PortableZip) {
        Remove-Item $PortableZip -Force
    }

    Compress-Archive -Path (Join-Path $tempBundle "*") -DestinationPath $PortableZip -Force
    Write-Host "Inno Setup compiler not found, so a portable package was created instead:"
    Write-Host $PortableZip
    return
}

New-Item -ItemType Directory -Force (Join-Path $Root "dist\installer") | Out-Null

& $InnoSetupCompiler $InstallerScript

Write-Host "Installer build complete. Check dist\installer for SnapTrack-Setup.exe"
