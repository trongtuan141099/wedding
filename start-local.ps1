# Local development only. Production deployment uses Apache/PHP on your hosting.
$ErrorActionPreference = 'Stop'
$projectRoot = $PSScriptRoot
$phpExecutable = 'D:\xampp\php\php.exe'
if (-not (Test-Path -LiteralPath $phpExecutable)) {
    $phpExecutable = (Get-Command php -ErrorAction Stop).Source
}
$uploadTemp = Join-Path $projectRoot 'data/upload-tmp'
New-Item -ItemType Directory -Force -Path $uploadTemp | Out-Null
Write-Host 'Dashboard: http://127.0.0.1:8088/admin.php'
Write-Host 'Invitation: http://127.0.0.1:8088/index.php'
Write-Host 'Press Ctrl+C to stop.'
& $phpExecutable -d "upload_tmp_dir=$uploadTemp" -d upload_max_filesize=16M -d post_max_size=20M -S 127.0.0.1:8088 -t $projectRoot
