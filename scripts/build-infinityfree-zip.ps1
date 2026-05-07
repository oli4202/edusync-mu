$ErrorActionPreference = "Stop"

$root = Split-Path -Parent $PSScriptRoot
$releaseDir = Join-Path $root "release"
$stagingDir = Join-Path $releaseDir "staging"
$zipPath = Join-Path $releaseDir "edusync-infinityfree-upload.zip"

if (Test-Path $stagingDir) {
    Remove-Item -Recurse -Force $stagingDir
}

if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}

New-Item -ItemType Directory -Path $stagingDir -Force | Out-Null

$excludeNames = @(
    ".git",
    ".agent",
    "node_modules",
    "release",
    "scratch",
    "tmp",
    "php-server.err.log",
    "php-server.out.log"
)

Get-ChildItem -Path $root -Force | ForEach-Object {
    if ($excludeNames -contains $_.Name) {
        return
    }

    if ($_.Name -eq ".env") {
        return
    }

    Copy-Item -Path $_.FullName -Destination (Join-Path $stagingDir $_.Name) -Recurse -Force
}

if (-not (Test-Path $releaseDir)) {
    New-Item -ItemType Directory -Path $releaseDir -Force | Out-Null
}

Compress-Archive -Path (Join-Path $stagingDir "*") -DestinationPath $zipPath -Force

Write-Host "Created deployment zip:"
Write-Host $zipPath
Write-Host "NOTE: .env is intentionally excluded for safety."
Write-Host "After upload, create htdocs/.env from .env.infinityfree.example and fill real values."
