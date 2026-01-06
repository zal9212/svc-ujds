-- Script de sauvegarde de la base de données
-- À exécuter régulièrement pour sauvegarder les données

-- Utilisation en ligne de commande:
-- mysqldump -u root -p svc_ujds > backup_svc_ujds_YYYY-MM-DD.sql

-- Pour restaurer:
-- mysql -u root -p svc_ujds < backup_svc_ujds_YYYY-MM-DD.sql

-- Script PowerShell pour automatiser les sauvegardes
-- Créer: backup.ps1

<#
# Sauvegarde automatique de la base de données SVC-UJDS
$date = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupFile = "backup_svc_ujds_$date.sql"
$backupPath = "C:\xampp\htdocs\svc-ujds\backups\$backupFile"

# Créer le dossier backups s'il n'existe pas
New-Item -ItemType Directory -Force -Path "C:\xampp\htdocs\svc-ujds\backups" | Out-Null

# Exécuter mysqldump
& "C:\xampp\mysql\bin\mysqldump.exe" -u root svc_ujds > $backupPath

# Compresser la sauvegarde
Compress-Archive -Path $backupPath -DestinationPath "$backupPath.zip" -Force
Remove-Item $backupPath

# Supprimer les sauvegardes de plus de 30 jours
Get-ChildItem "C:\xampp\htdocs\svc-ujds\backups" -Filter "*.zip" | 
    Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) } | 
    Remove-Item

Write-Host "Sauvegarde créée: $backupFile.zip"
#>

-- Pour planifier la sauvegarde automatique:
-- 1. Créer backup.ps1 avec le script ci-dessus
-- 2. Ouvrir le Planificateur de tâches Windows
-- 3. Créer une nouvelle tâche
-- 4. Action: Démarrer un programme
-- 5. Programme: powershell.exe
-- 6. Arguments: -ExecutionPolicy Bypass -File "C:\xampp\htdocs\svc-ujds\backup.ps1"
-- 7. Planifier: Quotidien à 2h00 du matin
