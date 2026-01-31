$date = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupDir = "C:\xampp\backups\svc-ujds"
$mysqlPath = "C:\xampp\mysql\bin"
$projectDir = "C:\xampp\htdocs\svc-ujds"
$envFile = "$projectDir\.env"

if (-not (Test-Path $envFile)) {
    Write-Host "Error: .env file not found."
    exit 1
}

# Parse .env to get DB pass
$dbPass = ""
Get-Content $envFile | ForEach-Object {
    if ($_ -match "^DB_PASS=(.*)") {
        $dbPass = $matches[1]
    }
}

if (-not (Test-Path $backupDir)) {
    New-Item -ItemType Directory -Force -Path $backupDir
}

# Database Backup
$dbBackupFile = "$backupDir\db_backup_$date.sql"
Write-Host "Backing up database to $dbBackupFile..."
& "$mysqlPath\mysqldump.exe" -u svc_prod_user -p"$dbPass" svc_ujds_prod > $dbBackupFile

# Compress
Compress-Archive -Path $dbBackupFile -DestinationPath "$dbBackupFile.zip"
Remove-Item $dbBackupFile

# Uploads Backup
$uploadsDir = "$projectDir\public\uploads"
if (Test-Path $uploadsDir) {
    $uploadsBackupFile = "$backupDir\uploads_backup_$date.zip"
    Write-Host "Backing up uploads to $uploadsBackupFile..."
    Compress-Archive -Path $uploadsDir -DestinationPath $uploadsBackupFile
}

# Cleanup old backups (keep last 30 days)
Get-ChildItem $backupDir | Where-Object { $_.CreationTime -lt (Get-Date).AddDays(-30) } | Remove-Item

Write-Host "Backup completed."
