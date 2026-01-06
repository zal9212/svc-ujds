# Sauvegarde automatique de la base de données SVC-UJDS
$date = Get-Date -Format "yyyy-MM-dd_HHmmss"
$backupFile = "backup_svc_ujds_$date.sql"
$backupPath = "C:\xampp\htdocs\svc-ujds\backups\$backupFile"

# Créer le dossier backups s'il n'existe pas
New-Item -ItemType Directory -Force -Path "C:\xampp\htdocs\svc-ujds\backups" | Out-Null

Write-Host "Démarrage de la sauvegarde..." -ForegroundColor Yellow

try {
    # Exécuter mysqldump
    & "C:\xampp\mysql\bin\mysqldump.exe" -u root svc_ujds > $backupPath
    
    if (Test-Path $backupPath) {
        # Compresser la sauvegarde
        Compress-Archive -Path $backupPath -DestinationPath "$backupPath.zip" -Force
        Remove-Item $backupPath
        
        $fileSize = (Get-Item "$backupPath.zip").Length / 1KB
        Write-Host "✓ Sauvegarde créée: $backupFile.zip ($([math]::Round($fileSize, 2)) KB)" -ForegroundColor Green
        
        # Supprimer les sauvegardes de plus de 30 jours
        $deleted = Get-ChildItem "C:\xampp\htdocs\svc-ujds\backups" -Filter "*.zip" | 
            Where-Object { $_.LastWriteTime -lt (Get-Date).AddDays(-30) }
        
        if ($deleted) {
            $deleted | Remove-Item
            Write-Host "✓ Supprimé $($deleted.Count) ancienne(s) sauvegarde(s)" -ForegroundColor Cyan
        }
    } else {
        Write-Host "✗ Erreur: Fichier de sauvegarde non créé" -ForegroundColor Red
    }
} catch {
    Write-Host "✗ Erreur lors de la sauvegarde: $_" -ForegroundColor Red
}

Write-Host "Sauvegarde terminée." -ForegroundColor Yellow
