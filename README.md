# Système de Gestion des Versements d'Association (SVC-UJDS)

![PHP](https://img.shields.io/badge/PHP-8.0+-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)
![TailwindCSS](https://img.shields.io/badge/TailwindCSS-3.0+-06B6D4)
![License](https://img.shields.io/badge/License-MIT-green)

## 📋 Description

Application web professionnelle de gestion des versements pour une association, développée en **PHP 8 POO** avec architecture **MVC stricte**, base de données **MySQL**, interface moderne en **TailwindCSS**, avec fonctionnalités d'import/export **Excel** et génération **PDF**.

## ✨ Fonctionnalités Principales

### Gestion des Membres
- ✅ Création, modification, suppression de membres
- ✅ Gestion des statuts (ACTIF, VG, SUSPENDU)
- ✅ Recherche et filtrage avancés
- ✅ Fiche détaillée avec historique complet

### Suivi des Versements
- ✅ Gestion mensuelle des cotisations
- ✅ Statuts de paiement (EN_ATTENTE, PAYE, PARTIEL, ANNULE)
- ✅ Calcul automatique des retards
- ✅ Mise à jour rapide des statuts

### Calculs Automatiques
- ✅ **Mois en retard**: Nombre de versements EN_ATTENTE
- ✅ **Amendes**: Retard × 2 000 FCFA
- ✅ **Montant versé**: Somme des paiements PAYE et PARTIEL
- ✅ **Montant dû**: (Retard × Montant mensuel) + Amende - Avances - Versements
- ✅ **Statut VG**: Tous les calculs à zéro

### Gestion des Avances
- ✅ Enregistrement des avances sur cotisations
- ✅ Déduction automatique du montant dû
- ✅ Historique complet

### Sécurité
- ✅ Authentification sécurisée (password hashing)
- ✅ Protection CSRF
- ✅ Protection XSS
- ✅ Requêtes préparées (SQL injection prevention)
- ✅ Gestion des rôles (admin, comptable, membre)
- ✅ Audit trail complet

### Rapports et Exports
- ✅ Import Excel (PhpSpreadsheet)
- ✅ Export Excel avec calculs
- ✅ Export PDF (TCPDF)
- ✅ Dashboard avec KPIs

## 🏗️ Architecture

### Structure MVC Stricte

```
svc-ujds/
├── app/
│   ├── core/              # Classes de base
│   │   ├── Database.php   # Singleton PDO
│   │   ├── Model.php      # Modèle de base
│   │   ├── Controller.php # Contrôleur de base
│   │   └── Security.php   # Utilitaires de sécurité
│   ├── models/            # Modèles métier
│   │   ├── Membre.php     # Logique métier membres
│   │   ├── Versement.php  # Gestion versements
│   │   ├── Avance.php     # Gestion avances
│   │   ├── Amende.php     # Calcul amendes
│   │   └── Utilisateur.php # Authentification
│   ├── controllers/       # Contrôleurs
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── MembreController.php
│   │   ├── VersementController.php
│   │   └── AvanceController.php
│   ├── views/             # Vues (TailwindCSS)
│   │   ├── layout/
│   │   ├── auth/
│   │   ├── dashboard/
│   │   ├── membres/
│   │   ├── versements/
│   │   └── avances/
│   └── services/          # Services (Excel, PDF)
├── config/
│   ├── config.php         # Configuration
│   └── routes.php         # Définition des routes
├── database/
│   ├── schema.sql         # Schéma de la base
│   └── seed.sql           # Données de test
├── public/                # Point d'entrée web
│   ├── index.php          # Front controller
│   ├── .htaccess          # Réécriture d'URL
│   ├── css/
│   └── js/
├── composer.json          # Dépendances PHP
└── README.md
```

## 🚀 Installation

### Prérequis

- **PHP** >= 8.0
- **MySQL** >= 5.7
- **Apache** avec mod_rewrite
- **Composer** (pour les dépendances)
- **XAMPP** recommandé pour Windows

### Étapes d'Installation

#### 1. Cloner ou Copier le Projet

```bash
# Le projet est déjà dans c:\xampp\htdocs\svc-ujds
cd c:\xampp\htdocs\svc-ujds
```

#### 2. Installer les Dépendances

```bash
composer install
```

Cela installera:
- `phpoffice/phpspreadsheet` (Import/Export Excel)
- `tecnickcom/tcpdf` (Génération PDF)

#### 3. Créer la Base de Données

1. Ouvrir **phpMyAdmin** (http://localhost/phpmyadmin)
2. Exécuter le fichier `database/schema.sql`
3. (Optionnel) Exécuter `database/seed.sql` pour les données de test

Ou via ligne de commande:

```bash
mysql -u root -p < database/schema.sql
mysql -u root -p < database/seed.sql
```

#### 4. Configuration

Éditer `config/config.php` si nécessaire:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'svc_ujds');
define('DB_USER', 'root');
define('DB_PASS', '');
```

#### 5. Permissions

```bash
# Windows (PowerShell en admin)
icacls "public\uploads" /grant Everyone:F

# Linux/Mac
chmod -R 775 public/uploads
```

#### 6. Accéder à l'Application

URL: **http://localhost/svc-ujds/public/**

**Compte par défaut:**
- Username: `admin`
- Password: `password123`

## 📊 Règles Métier

### Statuts des Membres

| Statut | Description | Calculs |
|--------|-------------|---------|
| **ACTIF** | Membre actif | Tous les calculs normaux |
| **VG** | Voyage/Inactif | Aucun calcul (tout à 0) |
| **SUSPENDU** | Membre suspendu | Bloqué |

### Statuts des Versements

| Statut | Description |
|--------|-------------|
| **EN_ATTENTE** | Non payé (compte dans les retards) |
| **PAYE** | Payé intégralement |
| **PARTIEL** | Payé partiellement |
| **ANNULE** | Annulé (ne compte pas) |

### Formules de Calcul

```
Mois en retard = COUNT(versements WHERE statut = 'EN_ATTENTE')
                 (0 si statut = 'VG')

Amende = Mois en retard × 2 000 FCFA
         (0 si statut = 'VG')

Total versé = SUM(montant WHERE statut IN ('PAYE', 'PARTIEL'))

Total avance = SUM(montant des avances)

Montant dû = (Mois en retard × Montant mensuel) + Amende - Total versé - Total avance
             (0 si statut = 'VG')
             (Minimum 0)
```

## 🎨 Design

L'interface est inspirée du thème portfolio moderne avec:
- **Police**: Poppins (Google Fonts)
- **Couleurs**: Palette professionnelle (gris, noir, blanc)
- **Composants**: Coins arrondis (2rem), ombres subtiles
- **Badges**: Codes couleur clairs
  - 🟢 Vert: ACTIF, PAYE
  - 🟠 Orange: PARTIEL, EN_ATTENTE
  - 🔴 Rouge: SUSPENDU, retards
  - ⚪ Gris: VG, ANNULE

## 👥 Rôles et Permissions

| Rôle | Permissions |
|------|-------------|
| **admin** | Accès complet (CRUD, suppression, changement statuts) |
| **comptable** | Gestion membres, versements, avances (pas de suppression) |
| **membre** | Consultation uniquement |

## 📦 Dépendances

### PHP (composer.json)

```json
{
    "require": {
        "php": ">=8.0",
        "phpoffice/phpspreadsheet": "^1.29",
        "tecnickcom/tcpdf": "^6.6"
    }
}
```

### Frontend

- **TailwindCSS** 3.x (CDN)
- **Google Fonts** (Poppins)
- JavaScript vanilla (pas de framework)

## 🔒 Sécurité

### Mesures Implémentées

1. **Authentification**
   - Hashage bcrypt des mots de passe
   - Sessions sécurisées
   - Régénération d'ID de session

2. **Protection CSRF**
   - Tokens CSRF sur tous les formulaires
   - Validation côté serveur

3. **Protection XSS**
   - `htmlspecialchars()` sur toutes les sorties
   - Sanitization des entrées

4. **Protection SQL Injection**
   - Requêtes préparées PDO uniquement
   - Pas de concaténation SQL

5. **Contrôle d'Accès**
   - Vérification des rôles
   - Middleware d'authentification

6. **Audit Trail**
   - Historique de toutes les actions
   - Traçabilité complète

## 📱 Responsive Design

L'application est entièrement responsive:
- **Mobile**: Navigation hamburger, tables scrollables
- **Tablet**: Layout adaptatif
- **Desktop**: Expérience complète

## 🧪 Tests

### Données de Test

Le fichier `database/seed.sql` contient:
- 3 utilisateurs (admin, comptable, membre)
- 5 membres avec différents statuts
- Versements variés (payés, en attente, partiels)
- Avances de test

### Vérification des Calculs

Comparer les résultats de l'application avec le fichier Excel source pour valider:
- Mois en retard
- Amendes
- Montants dus

## 📄 Licence

MIT License - Libre d'utilisation et de modification

## 👨‍💻 Développement

### Technologies Utilisées

- **Backend**: PHP 8 POO, PDO, MVC
- **Frontend**: HTML5, TailwindCSS, JavaScript
- **Base de données**: MySQL 5.7+
- **Serveur**: Apache (mod_rewrite)

### Standards de Code

- PSR-12 (PHP)
- Commentaires en français
- Nommage explicite
- Séparation des responsabilités

## 🆘 Support

Pour toute question ou problème:
1. Vérifier les logs Apache/PHP
2. Vérifier la configuration de la base de données
3. S'assurer que mod_rewrite est activé
4. Vérifier les permissions des dossiers

## 🔄 Mises à Jour Futures

- [ ] Import Excel complet avec mapping
- [ ] Export PDF avancé avec graphiques
- [ ] API REST
- [ ] Notifications par email
- [ ] Statistiques avancées
- [ ] Multi-langue

---

**Développé avec ❤️ pour SVC-UJDS**
