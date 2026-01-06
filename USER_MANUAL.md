# Guide Utilisateur - Système de Gestion des Versements

## 📖 Table des Matières

1. [Connexion](#connexion)
2. [Tableau de Bord](#tableau-de-bord)
3. [Gestion des Membres](#gestion-des-membres)
4. [Gestion des Versements](#gestion-des-versements)
5. [Gestion des Avances](#gestion-des-avances)
6. [Import/Export](#importexport)
7. [Rôles et Permissions](#rôles-et-permissions)

---

## 🔐 Connexion

### Accès à l'Application
- **URL:** http://localhost/svc-ujds/public/
- **Comptes par défaut:**
  - Admin: `admin` / `password123`
  - Comptable: `comptable` / `password123`
  - Membre: `membre` / `password123`

### Première Connexion
1. Entrez votre nom d'utilisateur
2. Entrez votre mot de passe
3. Cliquez sur "Se connecter"

> ⚠️ **Important:** Changez votre mot de passe après la première connexion!

---

## 📊 Tableau de Bord

Le tableau de bord affiche une vue d'ensemble du système:

### KPIs (Indicateurs Clés)
- **Total Membres:** Nombre total de membres (actifs, VG, suspendus)
- **Total Collecté:** Somme de tous les paiements reçus
- **Total Dû:** Montant total des impayés
- **En Attente:** Nombre de versements non payés

### Sections
- **Activités Récentes:** Derniers paiements effectués
- **Membres en Retard:** Top 5 des membres avec le plus de retards

### Actions Rapides
- Créer un nouveau membre
- Voir tous les membres

---

## 👥 Gestion des Membres

### Voir la Liste des Membres

1. Cliquez sur "Membres" dans le menu
2. Utilisez les filtres:
   - **Recherche:** Par nom, code ou téléphone
   - **Statut:** ACTIF, VG, SUSPENDU

### Créer un Nouveau Membre

1. Cliquez sur "+ Nouveau Membre"
2. Remplissez le formulaire:
   - **Numéro** (obligatoire)
   - **Code Membre** (obligatoire, unique)
   - **Titre:** M., Mme, Mlle, Dr, Pr
   - **Téléphone**
   - **Désignation** (nom complet, obligatoire)
   - **Missidé** (lieu de mission)
   - **Montant Mensuel** (obligatoire)
   - **Statut:** ACTIF, VG, SUSPENDU
3. Cliquez sur "Créer le membre"

### Voir les Détails d'un Membre

1. Cliquez sur "Détails →" dans la liste
2. Vous verrez:
   - **Informations du membre**
   - **Calculs automatiques:**
     - Mois en retard
     - Amende (retard × 2000 FCFA)
     - Total versé
     - Montant dû
   - **Historique des versements**
   - **Liste des avances**

### Modifier un Membre

1. Dans la fiche membre, cliquez sur "Modifier"
2. Modifiez les informations
3. Cliquez sur "Mettre à jour"

### Statuts des Membres

| Statut | Description | Calculs |
|--------|-------------|---------|
| **ACTIF** | Membre actif | Tous les calculs normaux |
| **VG** | Voyage/Inactif | Aucun calcul (tout à 0) |
| **SUSPENDU** | Membre suspendu | Bloqué, pas de nouveaux versements |

> 💡 **Astuce:** Un membre VG ne génère aucun retard ni amende, même s'il a des versements EN_ATTENTE.

---

## 💰 Gestion des Versements

### Créer un Versement

1. Dans la fiche d'un membre, cliquez sur "+ Nouveau Versement"
2. Sélectionnez:
   - **Mois** (janvier à décembre)
   - **Année**
   - **Montant** (0 si non payé)
   - **Statut:**
     - **EN_ATTENTE:** Non payé (compte dans les retards)
     - **PAYE:** Payé intégralement
     - **PARTIEL:** Payé partiellement
     - **ANNULE:** Annulé (ne compte pas)
3. Cliquez sur "Créer le versement"

### Voir Tous les Versements

1. Cliquez sur "Versements" dans le menu
2. Filtrez par statut si nécessaire
3. Utilisez "Marquer Payé" pour mettre à jour rapidement

### Statuts des Versements

| Statut | Badge | Description |
|--------|-------|-------------|
| **EN_ATTENTE** | 🔴 Rouge | Non payé, compte dans les retards |
| **PAYE** | 🟢 Vert | Payé intégralement |
| **PARTIEL** | 🟠 Orange | Payé partiellement |
| **ANNULE** | ⚪ Gris | Annulé, ne compte pas |

---

## 💵 Gestion des Avances

### Qu'est-ce qu'une Avance?

Une avance est un paiement anticipé sur les cotisations futures. Elle est automatiquement déduite du montant dû.

### Créer une Avance

1. Dans la fiche d'un membre, cliquez sur "+ Ajouter" dans la section Avances
2. Entrez:
   - **Montant** (obligatoire, > 0)
   - **Date de l'avance**
   - **Motif** (optionnel)
3. Cliquez sur "Enregistrer l'avance"

> ⚠️ **Important:** L'avance sera immédiatement déduite du montant dû du membre.

---

## 📥📤 Import/Export

### Importer depuis Excel

1. Cliquez sur "Import" dans le menu
2. Préparez votre fichier Excel avec les colonnes:
   - Numéro, Code membre, Téléphone, Titre, Désignation
   - Missidé, Montant mensuel
   - Colonnes mensuelles (février → décembre)
   - Nombre de mois en retard, Amende, Avance
   - Montant versé, Montant dû, Statut membre
3. Cliquez sur "Choisir un fichier" ou glissez-déposez
4. Cliquez sur "Importer les données"

### Exporter vers Excel

Trois types d'export disponibles:

1. **Export Complet:** Tous les membres avec calculs
2. **Membres en Retard:** Uniquement ceux avec des retards
3. **Membres Actifs:** Uniquement les membres ACTIF

Cliquez sur le bouton correspondant pour télécharger.

### Exporter vers PDF

Trois types de rapports:

1. **Rapport Général:** Vue d'ensemble complète
2. **Liste Membres:** Tous les membres
3. **État Paiements:** Versements et retards

---

## 👤 Rôles et Permissions

### Admin
- ✅ Accès complet
- ✅ Créer, modifier, supprimer membres
- ✅ Gérer versements et avances
- ✅ Supprimer des données
- ✅ Changer les statuts
- ✅ Import/Export

### Comptable
- ✅ Créer, modifier membres
- ✅ Gérer versements et avances
- ✅ Changer les statuts
- ✅ Import/Export
- ❌ Pas de suppression

### Membre
- ✅ Consultation uniquement
- ❌ Aucune modification

---

## 📐 Règles de Calcul

### Mois en Retard
```
Nombre de versements avec statut EN_ATTENTE
(0 si membre VG)
```

### Amende
```
Mois en retard × 2 000 FCFA
(0 si membre VG)
```

### Total Versé
```
Somme des montants PAYE + PARTIEL
```

### Montant Dû
```
(Mois en retard × Montant mensuel) + Amende - Total versé - Total avances
Minimum: 0
(0 si membre VG)
```

---

## 🆘 Aide et Support

### Problèmes Courants

**Je ne peux pas me connecter**
- Vérifiez votre nom d'utilisateur et mot de passe
- Contactez un administrateur

**Les calculs ne semblent pas corrects**
- Vérifiez le statut du membre (VG = tout à 0)
- Vérifiez les statuts des versements
- Seuls EN_ATTENTE comptent dans les retards

**Je ne vois pas certains boutons**
- Vérifiez votre rôle (membre = lecture seule)
- Contactez un administrateur pour changer votre rôle

### Contact

Pour toute question ou problème, contactez l'administrateur système.

---

**Version:** 1.0.0  
**Dernière mise à jour:** Janvier 2026
