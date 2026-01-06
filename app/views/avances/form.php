<!-- Anticipation Form (Create/Edit) -->
<?php
$isEdit = isset($avance);
$title = $isEdit ? 'Modifier l\'Anticipation' : 'Nouvelle Anticipation';
$action = $isEdit ? BASE_URL . '/avances/update' : BASE_URL . '/avances/store';
?>

<div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors"><?= $title ?></h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">
        <?= $isEdit ? 'Modifier l\'anticipation' : 'Enregistrer une anticipation' ?> pour 
        <span class="font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($membre['designation']) ?></span>
    </p>
</div>

<!-- Member Info -->
<div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800 rounded-2xl p-6 mb-6 transition-colors">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p class="text-sm font-medium text-gray-600 dark:text-blue-200">Membre</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-blue-100"><?= htmlspecialchars($membre['designation']) ?></p>
            <p class="text-sm text-gray-600 dark:text-blue-300"><?= htmlspecialchars($membre['code']) ?></p>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-600 dark:text-blue-200">Statut</p>
            <?php
            $badgeClass = match($membre['statut']) {
                'ACTIF' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                'VG' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
                'SUSPENDU' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
            };
            ?>
            <span class="inline-block px-3 py-1 text-xs font-medium <?= $badgeClass ?> rounded-full mt-2">
                <?= htmlspecialchars($membre['statut']) ?>
            </span>
        </div>
    </div>
</div>

<div class="max-w-2xl">
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 transition-colors">
        <form method="POST" action="<?= $action ?>" id="avanceForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
            <?php if ($isEdit): ?>
                <input type="hidden" name="id" value="<?= $avance['id'] ?>">
            <?php else: ?>
                <input type="hidden" name="membre_id" value="<?= $membre['id'] ?>">
            <?php endif; ?>

            <div class="space-y-6">
                <!-- Montant -->
                <div>
                    <label for="montant" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Montant (FCFA) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="montant" 
                        name="montant" 
                        required
                        step="0.01"
                        min="0.01"
                        value="<?= $isEdit ? $avance['montant'] : '' ?>"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors"
                        placeholder="Entrez le montant de l'anticipation"
                    >
                </div>

                <!-- Date -->
                <div>
                    <label for="date_avance" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Date de l'Anticipation <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="date_avance" 
                        name="date_avance" 
                        required
                        value="<?= $isEdit ? date('Y-m-d', strtotime($avance['date_avance'])) : date('Y-m-d') ?>"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors"
                    >
                </div>

                <!-- Motif -->
                <div>
                    <label for="motif" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                        Motif
                    </label>
                    <textarea 
                        id="motif" 
                        name="motif" 
                        rows="4"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition-colors"
                        placeholder="Raison de l'anticipation (optionnel)"
                    ><?= $isEdit ? htmlspecialchars($avance['motif']) : '' ?></textarea>
                </div>
            </div>

            <!-- Info Box -->
            <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-800/30 rounded-2xl p-4 transition-colors">
                <p class="text-sm text-yellow-800 dark:text-yellow-200 transition-colors">
                    <strong>⚠️ Important:</strong> L'anticipation sera automatiquement déduite du montant dû du membre.
                    <?= $isEdit ? 'La modification recalculera les soldes.' : 'Elle sera prise en compte dans les calculs futurs.' ?>
                </p>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row gap-4 mt-8">
                <button 
                    type="submit"
                    class="flex-1 bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-8 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium shadow-sm"
                >
                    <?= $isEdit ? 'Mettre à jour' : 'Enregistrer l\'anticipation' ?>
                </button>
                <a 
                    href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>"
                    class="flex-1 text-center bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-8 py-3 rounded-2xl hover:bg-gray-300 dark:hover:bg-gray-700 transition font-medium"
                >
                    Annuler
                </a>
            </div>
        </form>
    </div>
</div>
