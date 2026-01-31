<!-- Member Form (Create/Edit) -->
<?php
$isEdit = isset($membre);
$isProfile = $isProfile ?? false;
$title = $isProfile ? 'Mon Profil' : ($isEdit ? 'Modifier le Membre' : 'Nouveau Membre');
$subtitle = $isProfile ? 'Gérez vos informations personnelles et votre photo' : 'Remplissez les informations du membre';
$action = $isProfile ? BASE_URL . '/membres/updateProfile' : ($isEdit ? BASE_URL . '/membres/update' : BASE_URL . '/membres/store');
?>

<div class="mb-8 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white transition-colors"><?= $title ?></h1>
            <?php if ($isProfile): ?>
                <span class="px-2.5 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 text-[10px] font-black rounded-lg uppercase tracking-wider border border-blue-200/50">
                    <?= htmlspecialchars($currentUser['role']) ?>
                </span>
            <?php endif; ?>
        </div>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors"><?= $subtitle ?></p>
    </div>
    
    <?php if ($isProfile): ?>
        <a href="<?= BASE_URL ?>/auth/changePassword" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
            Modifier le mot de passe
        </a>
    <?php endif; ?>
</div>

<div class="max-w-3xl">
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 sm:p-8 transition-colors">
        <form method="POST" action="<?= $action ?>" id="membreForm" enctype="multipart/form-data">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $membre['id'] ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <?php if (!$isProfile): ?>
                <!-- Numéro -->
                <div>
                    <label for="numero" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Numéro <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="numero" 
                        name="numero" 
                        <?php if (!$isEdit): ?>
                            disabled
                            placeholder="Généré automatiquement"
                        <?php else: ?>
                            required
                            readonly
                            value="<?= $membre['numero'] ?? '' ?>"
                        <?php endif; ?>
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-gray-50 dark:bg-gray-950 transition"
                    >
                </div>
                <?php endif; ?>

                <!-- Photo de Profil -->
                <div>
                    <label for="photo_profil" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Photo de Profil
                    </label>
                    
                    <?php if ($isEdit && !empty($membre['photo_profil'])): ?>
                        <div class="mb-3">
                            <img src="<?= BASE_URL . '/' . htmlspecialchars($membre['photo_profil']) ?>" alt="Photo actuelle" class="h-20 w-20 rounded-full object-cover border-2 border-gray-200">
                        </div>
                    <?php endif; ?>

                    <input 
                        type="file" 
                        id="photo_profil" 
                        name="photo_profil" 
                        accept="image/jpeg,image/png,image/webp"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-gray-50 dark:bg-gray-950 transition"
                    >
                    <p class="mt-1 text-xs text-gray-500">JPG, PNG ou WEBP. Max 5MB.</p>
                </div>

                <?php if (!$isProfile): ?>
                <!-- Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Code Membre <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="code" 
                        name="code" 
                        <?php if (!$isEdit): ?>
                            disabled
                            placeholder="Généré automatiquement"
                        <?php else: ?>
                            required
                            readonly
                            value="<?= htmlspecialchars($membre['code'] ?? '') ?>"
                        <?php endif; ?>
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent bg-gray-50 dark:bg-gray-950 transition"
                    >
                </div>
                <?php endif; ?>

                <!-- Titre -->
                <div>
                    <label for="titre" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Civilité
                    </label>
                    <select 
                        id="titre" 
                        name="titre"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                    >
                        <option value="">Sélectionner...</option>
                        <option value="M." <?= ($membre['titre'] ?? '') === 'M.' ? 'selected' : '' ?>>M.</option>
                        <option value="Mme" <?= ($membre['titre'] ?? '') === 'Mme' ? 'selected' : '' ?>>Mme</option>
                        <option value="Mlle" <?= ($membre['titre'] ?? '') === 'Mlle' ? 'selected' : '' ?>>Mlle</option>
                        <option value="Bappa" <?= ($membre['titre'] ?? '') === 'Bappa' ? 'selected' : '' ?>>Bappa</option>
                        <option value="Kaou" <?= ($membre['titre'] ?? '') === 'Kaou' ? 'selected' : '' ?>>Kaou</option>
                        <option value="Koto" <?= ($membre['titre'] ?? '') === 'Koto' ? 'selected' : '' ?>>Koto</option>
                        <option value="Oustaz" <?= ($membre['titre'] ?? '') === 'Oustaz' ? 'selected' : '' ?>>Oustaz</option>
                        <option value="Grand" <?= ($membre['titre'] ?? '') === 'Grand' ? 'selected' : '' ?>>Grand</option>
                        <option value="Mody" <?= ($membre['titre'] ?? '') === 'Mody' ? 'selected' : '' ?>>Mody</option>
                        <option value="Dr" <?= ($membre['titre'] ?? '') === 'Dr' ? 'selected' : '' ?>>Dr</option>
                        <option value="Pr" <?= ($membre['titre'] ?? '') === 'Pr' ? 'selected' : '' ?>>Pr</option>
                    </select>
                </div>

                <!-- Téléphone -->
                <div>
                    <label for="telephone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Téléphone
                    </label>
                    <input 
                        type="tel" 
                        id="telephone" 
                        name="telephone"
                        value="<?= htmlspecialchars($membre['telephone'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                        placeholder="+243 900 000 000"
                    >
                </div>

                <!-- Désignation (Full Width) -->
                <div class="md:col-span-2">
                    <label for="designation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Nom Complet <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="designation" 
                        name="designation" 
                        required
                        value="<?= htmlspecialchars($membre['designation'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                        placeholder="Ex: Jean Dupont"
                    >
                </div>

                <?php if (!$isProfile): ?>
                <!-- Missidé -->
                <div>
                    <label for="misside" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Missidé
                    </label>
                    <input 
                        type="text" 
                        id="misside" 
                        name="misside"
                        value="<?= htmlspecialchars($membre['misside'] ?? '') ?>"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                        placeholder="Lieu d'origine"
                    >
                </div>

                <!-- Montant Mensuel -->
                <div>
                    <label for="montant_mensuel" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Cotisation Mensuelle <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="montant_mensuel" 
                        name="montant_mensuel" 
                        required
                        step="0.01"
                        value="<?= $membre['montant_mensuel'] ?? '50000' ?>"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                    >
                </div>

                <!-- Statut -->
                <div class="md:col-span-2">
                    <label for="statut" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Statut <span class="text-red-500">*</span>
                    </label>
                    <select 
                        id="statut" 
                        name="statut" 
                        required
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                    >
                        <option value="ACTIF" <?= ($membre['statut'] ?? 'ACTIF') === 'ACTIF' ? 'selected' : '' ?>>ACTIF</option>
                        <option value="VG" <?= ($membre['statut'] ?? '') === 'VG' ? 'selected' : '' ?>>VG (Voyage)</option>
                        <option value="SUSPENDU" <?= ($membre['statut'] ?? '') === 'SUSPENDU' ? 'selected' : '' ?>>SUSPENDU</option>
                    </select>
                </div>
                <?php endif; ?>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <button 
                    type="submit"
                    class="flex-1 bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-8 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium shadow-sm"
                >
                    <?= $isEdit ? 'Mettre à jour' : 'Créer le membre' ?>
                </button>
                <a 
                    href="<?= $isProfile ? BASE_URL . '/dashboard' : ($isEdit ? BASE_URL . '/membres/show?id=' . $membre['id'] : BASE_URL . '/membres') ?>"
                    class="flex-1 text-center bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-8 py-3 rounded-2xl hover:bg-gray-300 dark:hover:bg-gray-700 transition font-medium"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
