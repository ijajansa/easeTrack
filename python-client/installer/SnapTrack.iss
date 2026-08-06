#define MyAppName "SnapTrack"
#define MyAppVersion "1.0.0"
#define MyAppPublisher "SnapTrack"
#define MyAppExeName "SnapTrack.exe"

[Setup]
AppId={{C1D3F21A-3ED2-4A55-95B0-7E5E0D2D9D72}}
AppName={#MyAppName}
AppVersion={#MyAppVersion}
AppPublisher={#MyAppPublisher}
DefaultDirName={localappdata}\Programs\{#MyAppName}
DefaultGroupName={#MyAppName}
DisableProgramGroupPage=yes
OutputBaseFilename=SnapTrack-Setup
OutputDir=..\dist\installer
Compression=lzma
SolidCompression=yes
WizardStyle=modern
PrivilegesRequired=lowest
ArchitecturesAllowed=x64compatible
ArchitecturesInstallIn64BitMode=x64compatible
SetupIconFile=..\..\backend\public\favicon.ico
UninstallDisplayIcon={app}\{#MyAppExeName}
UsePreviousAppDir=no
UsePreviousGroup=no

[Tasks]
Name: "desktopicon"; Description: "Create a &desktop shortcut"; GroupDescription: "Additional shortcuts:"; Flags: unchecked
Name: "startup"; Description: "Start SnapTrack when Windows starts"; GroupDescription: "Startup options:"; Flags: unchecked

[Files]
Source: "..\dist\SnapTrack.exe"; DestDir: "{app}"; DestName: "{#MyAppExeName}"; Flags: ignoreversion
Source: "..\install.bat"; DestDir: "{app}"; Flags: ignoreversion
Source: "..\uninstall.bat"; DestDir: "{app}"; Flags: ignoreversion

[Icons]
Name: "{group}\SnapTrack"; Filename: "{app}\{#MyAppExeName}"
Name: "{group}\Uninstall SnapTrack"; Filename: "{uninstallexe}"
Name: "{commondesktop}\SnapTrack"; Filename: "{app}\{#MyAppExeName}"; Tasks: desktopicon
Name: "{userstartup}\SnapTrack"; Filename: "{app}\{#MyAppExeName}"; Tasks: startup

[Run]
Filename: "{app}\{#MyAppExeName}"; Description: "Launch SnapTrack"; Flags: nowait postinstall skipifsilent
