# Guide de Déploiement en Production

## 📋 Checklist Pré-Déploiement

### 1. Environnement Serveur

- [ ] **Serveur Web:** Apache 2.4+ ou Nginx
- [ ] **PHP:** Version 8.0 ou supérieure
- [ ] **MySQL:** Version 5.7 ou supérieure
- [ ] **Extensions PHP requises:**
  - [ ] PDO
  - [ ] pdo_mysql
  - [ ] mbstring
  - [ ] json
  - [ ] zip (pour Excel)
  - [ ] gd ou imagick (pour PDF)

### 2. Sécurité

- [ ] Changer tous les mots de passe par défaut
- [ ] Configurer `APP_ENV=production` dans `.env`
- [ ] Désactiver l'affichage des erreurs
- [ ] Activer HTTPS (certificat SSL)
- [ ] Configurer le pare-feu
- [ ] Restreindre l'accès à phpMyAdmin

### 3. Base de Données

- [ ] Créer la base de données de production
- [ ] Importer le schéma (`database/schema.sql`)
- [ ] **NE PAS** importer `seed.sql` en production
- [ ] Configurer les sauvegardes automatiques
- [ ] Créer un utilisateur MySQL dédié (pas root)

### 4. Fichiers

- [ ] Supprimer les fichiers de test
- [ ] Configurer les permissions correctes
- [ ] Vérifier que `.htaccess` est présent
- [ ] Configurer les logs

---

## 🚀 Étapes de Déploiement

### Étape 1: Préparer le Serveur

```bash
# Mettre à jour le système
sudo apt update && sudo apt upgrade -y

# Installer Apache, PHP, MySQL
sudo apt install apache2 php8.0 mysql-server php8.0-mysql php8.0-mbstring php8.0-zip php8.0-gd -y

# Activer mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Étape 2: Transférer les Fichiers

```bash
# Via FTP/SFTP ou Git
# Copier tous les fichiers SAUF:
# - /vendor (sera régénéré)
# - /backups
# - /tests
# - .env (créer un nouveau)
```

### Étape 3: Installer les Dépendances

```bash
cd /var/www/html/svc-ujds
composer install --no-dev --optimize-autoloader
```

### Étape 4: Configurer la Base de Données

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base et l'utilisateur
CREATE DATABASE svc_ujds CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'svc_user'@'localhost' IDENTIFIED BY 'MOT_DE_PASSE_FORT';
GRANT ALL PRIVILEGES ON svc_ujds.* TO 'svc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Importer le schéma
mysql -u svc_user -p svc_ujds < database/schema.sql
```

### Étape 5: Configuration de l'Application

Créer/Modifier `config/config.php`:

```php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'svc_ujds');
define('DB_USER', 'svc_user');
define('DB_PASS', 'MOT_DE_PASSE_FORT');

// Configuration de l'application
define('BASE_URL', 'https://votre-domaine.com');

// IMPORTANT: Mode production
putenv('APP_ENV=production');
```

### Étape 6: Permissions des Fichiers

```bash
# Propriétaire Apache
sudo chown -R www-data:www-data /var/www/html/svc-ujds

# Permissions
sudo find /var/www/html/svc-ujds -type d -exec chmod 755 {} \;
sudo find /var/www/html/svc-ujds -type f -exec chmod 644 {} \;

# Dossier uploads writable
sudo chmod 775 /var/www/html/svc-ujds/public/uploads
```

### Étape 7: Configuration Apache

Créer `/etc/apache2/sites-available/svc-ujds.conf`:

```apache
<VirtualHost *:80>
    ServerName votre-domaine.com
    ServerAdmin admin@votre-domaine.com
    DocumentRoot /var/www/html/svc-ujds/public

    <Directory /var/www/html/svc-ujds/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/svc-ujds-error.log
    CustomLog ${APACHE_LOG_DIR}/svc-ujds-access.log combined

    # Redirection HTTPS
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =votre-domaine.com
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>
```

Activer le site:

```bash
sudo a2ensite svc-ujds.conf
sudo systemctl reload apache2
```

### Étape 8: Configurer HTTPS (Let's Encrypt)

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-apache -y

# Obtenir le certificat SSL
sudo certbot --apache -d votre-domaine.com

# Le renouvellement est automatique
```

### Étape 9: Créer le Premier Utilisateur Admin

```bash
# Se connecter à MySQL
mysql -u svc_user -p svc_ujds

# Créer l'admin (remplacer le hash par un vrai mot de passe hashé)
INSERT INTO utilisateurs (username, password, role) 
VALUES ('admin', '$2y$10$...HASH...', 'admin');
```

Pour générer le hash du mot de passe:

```php
<?php
echo password_hash('VOTRE_MOT_DE_PASSE', PASSWORD_DEFAULT);
```

### Étape 10: Configurer les Sauvegardes

```bash
# Créer le script de sauvegarde
sudo nano /usr/local/bin/backup-svc-ujds.sh
```

Contenu:

```bash
#!/bin/bash
DATE=$(date +%Y-%m-%d_%H%M%S)
BACKUP_DIR="/var/backups/svc-ujds"
mkdir -p $BACKUP_DIR

# Sauvegarde base de données
mysqldump -u svc_user -pMOT_DE_PASSE svc_ujds | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Sauvegarde fichiers uploads
tar -czf $BACKUP_DIR/uploads_$DATE.tar.gz /var/www/html/svc-ujds/public/uploads

# Supprimer les sauvegardes de plus de 30 jours
find $BACKUP_DIR -type f -mtime +30 -delete

echo "Sauvegarde terminée: $DATE"
```

Rendre exécutable et planifier:

```bash
sudo chmod +x /usr/local/bin/backup-svc-ujds.sh

# Ajouter au crontab (tous les jours à 2h)
sudo crontab -e
# Ajouter: 0 2 * * * /usr/local/bin/backup-svc-ujds.sh >> /var/log/svc-ujds-backup.log 2>&1
```

---

## 🔒 Sécurité Post-Déploiement

### 1. Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
```

### 2. Fail2Ban (Protection contre brute force)

```bash
sudo apt install fail2ban -y
sudo systemctl enable fail2ban
sudo systemctl start fail2ban
```

### 3. Désactiver les Fonctions PHP Dangereuses

Dans `php.ini`:

```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen
```

### 4. Headers de Sécurité

Déjà dans `.htaccess`, mais vérifier:

```apache
Header set X-Content-Type-Options "nosniff"
Header set X-Frame-Options "SAMEORIGIN"
Header set X-XSS-Protection "1; mode=block"
Header set Strict-Transport-Security "max-age=31536000"
```

---

## 📊 Monitoring

### 1. Logs à Surveiller

- `/var/log/apache2/svc-ujds-error.log`
- `/var/log/apache2/svc-ujds-access.log`
- `/var/log/mysql/error.log`

### 2. Monitoring de la Base de Données

```sql
-- Vérifier la taille de la base
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'svc_ujds'
GROUP BY table_schema;
```

---

## 🧪 Tests Post-Déploiement

- [ ] Accéder à l'URL de production
- [ ] Tester la connexion
- [ ] Créer un membre de test
- [ ] Créer un versement de test
- [ ] Vérifier les calculs
- [ ] Tester l'export CSV
- [ ] Vérifier les logs
- [ ] Tester depuis différents navigateurs
- [ ] Tester la version mobile

---

## 🆘 Rollback en Cas de Problème

```bash
# Restaurer la base de données
gunzip < /var/backups/svc-ujds/backup_DATE.sql.gz | mysql -u svc_user -p svc_ujds

# Restaurer les fichiers
tar -xzf /var/backups/svc-ujds/uploads_DATE.tar.gz -C /
```

---

## 📞 Support

En cas de problème:
1. Vérifier les logs
2. Vérifier la configuration
3. Restaurer depuis la sauvegarde si nécessaire

---

**Déploiement réussi! 🎉**
