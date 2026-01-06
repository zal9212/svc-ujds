# 🔧 Guide de Dépannage - Problèmes Courants

## ❌ Problème: "Nom d'utilisateur ou mot de passe incorrect"

### Solution Rapide

**Exécutez le script de configuration:**

```bash
cd c:\xampp\htdocs\svc-ujds
php setup_users.php
```

Ce script va:
- ✅ Vérifier la connexion à la base de données
- ✅ Créer/mettre à jour les utilisateurs par défaut
- ✅ Hasher correctement les mots de passe
- ✅ Tester la vérification des mots de passe

**Identifiants par défaut:**
- Username: `admin`
- Password: `password123`

---

## ❌ Problème: "404 - Page non trouvée"

### Causes Possibles

1. **mod_rewrite non activé**
   ```
   Ouvrir: c:\xampp\apache\conf\httpd.conf
   Chercher: #LoadModule rewrite_module modules/mod_rewrite.so
   Retirer le #
   Redémarrer Apache
   ```

2. **Mauvaise URL**
   ```
   ✓ Correct: http://localhost/svc-ujds/public/
   ✗ Incorrect: http://localhost/svc-ujds/
   ```

3. **Test direct**
   ```
   http://localhost/svc-ujds/public/index.php
   ```

---

## ❌ Problème: Base de données n'existe pas

### Solution

1. **Ouvrir phpMyAdmin**
   ```
   http://localhost/phpmyadmin
   ```

2. **Créer la base de données**
   - Cliquer sur "Nouvelle base de données"
   - Nom: `svc_ujds`
   - Interclassement: `utf8mb4_unicode_ci`
   - Cliquer "Créer"

3. **Importer le schéma**
   - Sélectionner la base `svc_ujds`
   - Onglet "Importer"
   - Choisir: `database/schema.sql`
   - Cliquer "Exécuter"

4. **Importer les données de test (optionnel)**
   - Onglet "Importer"
   - Choisir: `database/seed.sql`
   - Cliquer "Exécuter"

5. **Créer les utilisateurs**
   ```bash
   php setup_users.php
   ```

---

## ❌ Problème: Erreur de connexion MySQL

### Vérifications

1. **MySQL est démarré?**
   - Ouvrir XAMPP Control Panel
   - Vérifier que MySQL est "Running"
   - Sinon, cliquer "Start"

2. **Identifiants corrects?**
   - Ouvrir: `config/config.php`
   - Vérifier:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_NAME', 'svc_ujds');
     define('DB_USER', 'root');
     define('DB_PASS', ''); // Vide par défaut XAMPP
     ```

---

## ❌ Problème: Page blanche / Erreur 500

### Diagnostics

1. **Activer l'affichage des erreurs**
   ```php
   // Dans config/config.php
   error_reporting(E_ALL);
   ini_set('display_errors', 1);
   ```

2. **Vérifier les logs**
   ```
   c:\xampp\apache\logs\error.log
   c:\xampp\php\logs\php_error_log
   ```

3. **Vérifier les permissions**
   ```
   Le dossier public/uploads doit être accessible en écriture
   ```

---

## ❌ Problème: Les calculs ne sont pas corrects

### Vérifications

1. **Statut du membre**
   - Si statut = `VG`, tous les calculs sont à 0
   - C'est normal!

2. **Statut des versements**
   - Seuls les versements `EN_ATTENTE` comptent dans les retards
   - `ANNULE` ne compte pas

3. **Exécuter les tests**
   ```bash
   php tests/CalculationTests.php
   ```

---

## ❌ Problème: Import Excel ne fonctionne pas

### Solution

1. **Installer les dépendances**
   ```bash
   composer install
   ```

2. **Vérifier PhpSpreadsheet**
   ```bash
   composer show phpoffice/phpspreadsheet
   ```

3. **Décommenter le code**
   - Ouvrir: `app/controllers/ImportController.php`
   - Décommenter les lignes TODO

---

## ✅ Checklist de Vérification Rapide

Avant de demander de l'aide, vérifiez:

- [ ] Apache est démarré (XAMPP Control Panel)
- [ ] MySQL est démarré (XAMPP Control Panel)
- [ ] Base de données `svc_ujds` existe (phpMyAdmin)
- [ ] Tables créées (schema.sql importé)
- [ ] Utilisateurs créés (`php setup_users.php`)
- [ ] URL correcte: `http://localhost/svc-ujds/public/`
- [ ] mod_rewrite activé dans Apache
- [ ] Pas d'erreurs dans les logs

---

## 🆘 Commandes Utiles

### Réinitialiser les utilisateurs
```bash
php setup_users.php
```

### Tester les calculs
```bash
php tests/CalculationTests.php
```

### Vérifier la configuration
```bash
php -i | findstr "PDO"
php -v
```

### Sauvegarder la base de données
```bash
# Via phpMyAdmin: Export > SQL
# Ou PowerShell:
.\backup.ps1
```

---

## 📞 Support

Si le problème persiste:

1. **Vérifier les logs**
   - `c:\xampp\apache\logs\error.log`
   - `c:\xampp\php\logs\php_error_log`

2. **Copier le message d'erreur exact**

3. **Noter les étapes pour reproduire**

4. **Vérifier la version**
   - PHP: `php -v`
   - MySQL: Dans phpMyAdmin

---

## 🔄 Réinitialisation Complète

En dernier recours:

```bash
# 1. Supprimer la base de données (phpMyAdmin)
# 2. Recréer la base de données
# 3. Importer schema.sql
# 4. Exécuter setup_users.php
# 5. Tester la connexion
```

---

**La plupart des problèmes sont résolus en exécutant `php setup_users.php`!**
