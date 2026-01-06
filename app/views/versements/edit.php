<?php
$title = 'Modifier Versement';
?>

<div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors"><?= $title ?></h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">
        Modification du versement pour <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($membre['designation']) ?></span>
    </p>
</div>

<div class="max-w-2xl bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 transition-colors">
    <form method="POST" action="<?= BASE_URL ?>/versements/update">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
        <input type="hidden" name="id" value="<?= $versement['id'] ?>">

        <div class="grid grid-cols-2 gap-6 mb-6">
            <!-- Mois -->
            <div>
                <label for="mois" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">Mois</label>
                <select name="mois" id="mois" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors">
                    <?php
                    $moisList = [
                        'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
                        'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
                    ];
                    foreach ($moisList as $ms): 
                    ?>
                        <option value="<?= $ms ?>" <?= $versement['mois'] === $ms ? 'selected' : '' ?>>
                            <?= ucfirst($ms) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Année -->
            <div>
                <label for="annee" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">Année</label>
                <input type="number" name="annee" id="annee" value="<?= $versement['annee'] ?>" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors">
            </div>
        </div>

        <div class="space-y-6">
            <!-- Montant -->
            <div>
                <label for="montant" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">Montant (FCFA)</label>
                <input 
                    type="number" 
                    id="montant" 
                    name="montant" 
                    step="0.01" 
                    value="<?= $versement['montant'] ?>" 
                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors"
                >
            </div>

            <!-- Statut -->
            <div>
                <label for="statut" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">Statut</label>
                <select name="statut" id="statut" class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors">
                    <option value="EN_ATTENTE" <?= $versement['statut'] === 'EN_ATTENTE' ? 'selected' : '' ?>>EN_ATTENTE</option>
                    <option value="PAYE" <?= $versement['statut'] === 'PAYE' ? 'selected' : '' ?>>PAYE</option>
                    <option value="PARTIEL" <?= $versement['statut'] === 'PARTIEL' ? 'selected' : '' ?>>PARTIEL</option>
                    <option value="AMENDE" <?= $versement['statut'] === 'AMENDE' ? 'selected' : '' ?>>AMENDE</option>
                    <option value="ANNULE" <?= $versement['statut'] === 'ANNULE' ? 'selected' : '' ?>>ANNULE</option>
                </select>
            </div>

            <!-- Has Amende Checkbox -->
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700">
                <input type="checkbox" name="has_amende" id="has_amende" value="1" <?= $versement['has_amende'] ? 'checked' : '' ?> class="h-5 w-5 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                <label for="has_amende" class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none cursor-pointer">
                    Appliquer une amende sur ce mois
                </label>
            </div>
        </div>

        <div class="flex gap-4 mt-8">
            <button type="submit" class="flex-1 bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-8 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium shadow-sm">
                Mettre à jour
            </button>
            <a href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>" class="flex-1 text-center bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-8 py-3 rounded-2xl hover:bg-gray-300 dark:hover:bg-gray-700 transition font-medium">
                Annuler
            </a>
        </div>
    </form>
</div>
