# Guide de Démarrage Rapide

## 🚀 Accès à l'Application

**URL:** http://localhost/svc-ujds/public/

**Compte par défaut:**
- Username: `admin`
- Password: `password123`

## ⚠️ Prérequis

1. **Base de données créée:**
   ```sql
   -- Ouvrir phpMyAdmin: http://localhost/phpmyadmin
   -- Importer: database/schema.sql
   -- Importer: database/seed.sql
   ```

2. **Apache en cours d'exécution** (XAMPP Control Panel)

3. **mod_rewrite activé** dans Apache

## 🔧 Si vous voyez "404 - Page non trouvée"

### Vérification 1: Base de données
```bash
# Ouvrir phpMyAdmin
# Vérifier que la base 'svc_ujds' existe
```

### Vérification 2: Apache mod_rewrite
```
1. Ouvrir: c:\xampp\apache\conf\httpd.conf
2. Chercher: #LoadModule rewrite_module modules/mod_rewrite.so
3. Retirer le # au début de la ligne
4. Sauvegarder
5. Redémarrer Apache dans XAMPP Control Panel
```

### Vérification 3: Tester directement
```
http://localhost/svc-ujds/public/index.php
```

Si cela fonctionne, le problème vient de mod_rewrite.

## 📋 Étapes d'Installation Complète

```bash
# 1. Aller dans le dossier
cd c:\xampp\htdocs\svc-ujds

# 2. Installer les dépendances
composer install

# 3. Créer la base de données
# Via phpMyAdmin ou ligne de commande:
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql

# 4. Accéder à l'application
# http://localhost/svc-ujds/public/
```

## 🎯 Pages Disponibles

- `/` ou `/login` - Page de connexion
- `/dashboard` - Tableau de bord
- `/membres` - Liste des membres
- `/versements` - Liste des versements
- `/import` - Import/Export

## 🐛 Debug

Si problème persiste, vérifier:

1. **Logs Apache:** `c:\xampp\apache\logs\error.log`
2. **Logs PHP:** `c:\xampp\php\logs\php_error_log`
3. **Afficher les erreurs:** Dans `config/config.php`, vérifier que `display_errors` est à 1

## ✅ Test Rapide

```php
// Créer: c:\xampp\htdocs\svc-ujds\public\test.php
<?php
echo "PHP fonctionne!";
phpinfo();
```

Accéder à: http://localhost/svc-ujds/public/test.php

Si cela fonctionne, PHP est OK. Le problème est dans le routing.

---

**Besoin d'aide?** Vérifiez que:
- ✅ Apache est démarré
- ✅ MySQL est démarré  
- ✅ Base de données `svc_ujds` existe
- ✅ mod_rewrite est activé
