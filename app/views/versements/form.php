<!-- Versement/Amende Grid Form -->
<?php
$isAmendeMode = (isset($mode) && $mode === 'amende');
$pageTitle = $isAmendeMode ? 'Gérer les Amendes' : 'Gérer les Versements';
$themeColor = $isAmendeMode ? 'red' : 'green';
$btnColor = $isAmendeMode ? 'bg-red-600 hover:bg-red-700' : 'bg-gray-900 hover:bg-black';

$moisList = [
    'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 
    'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'
];
?>

<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white transition-colors"><?= $pageTitle ?> - <?= $annee ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">
                <?= $isAmendeMode ? 'Cochez les mois avec amende' : 'Cochez les mois payés' ?> 
                pour <span class="font-semibold"><?= htmlspecialchars($membre['designation']) ?></span>
            </p>
        </div>
        <div class="flex items-center gap-2 self-center sm:self-auto">
            <a href="<?= BASE_URL ?>/versements/create?membre_id=<?= $membre['id'] ?>&annee=<?= $annee - 1 ?>&mode=<?= $mode ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                ← <?= $annee - 1 ?>
            </a>
            <span class="px-4 py-2 <?= $isAmendeMode ? 'bg-red-600' : 'bg-gray-900 dark:bg-white dark:text-gray-900' ?> text-white rounded-xl font-bold shadow-sm">
                <?= $annee ?>
            </span>
            <a href="<?= BASE_URL ?>/versements/create?membre_id=<?= $membre['id'] ?>&annee=<?= $annee + 1 ?>&mode=<?= $mode ?>" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                <?= $annee + 1 ?> →
            </a>
        </div>
    </div>
</div>

<div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-5 sm:p-8 transition-colors">
    <form method="POST" action="<?= BASE_URL ?>/versements/store">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
        <input type="hidden" name="membre_id" value="<?= $membre['id'] ?>">
        <input type="hidden" name="annee" value="<?= $annee ?>">
        <input type="hidden" name="mode" value="<?= $mode ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-8">
            <?php foreach ($moisList as $mois): 
                $payment = $paymentsMap[$mois] ?? null;
                $isPaid = $payment && in_array($payment['statut'], ['PAYE', 'PARTIEL', 'ANTICIPATION', 'ANTICIPATION (PARTIEL)', 'PAYE (AVANCE)', 'PARTIEL (AVANCE)']);
                $isPartiel = $payment && in_array($payment['statut'], ['PARTIEL', 'ANTICIPATION (PARTIEL)', 'PARTIEL (AVANCE)']);
                $isAnticipation = $payment && in_array($payment['statut'], ['ANTICIPATION', 'ANTICIPATION (PARTIEL)', 'PAYE (AVANCE)', 'PARTIEL (AVANCE)']);
                $hasAmende = $payment && !empty($payment['has_amende']);
                
                $disabled = false;
                $cardClass = 'border-gray-200 dark:border-gray-800';
                
                if ($isAmendeMode) {
                    if ($isPaid) {
                        $disabled = true;
                        $cardClass = 'bg-gray-50 dark:bg-gray-950/50 opacity-60 dark:border-gray-800';
                    } elseif ($hasAmende) {
                        $cardClass = 'border-red-500 bg-red-50 dark:bg-red-900/10 dark:border-red-500/50';
                    }
                } else {
                    if ($isAnticipation) {
                        $cardClass = 'border-purple-500 bg-purple-50 dark:bg-purple-900/10 dark:border-purple-500/50';
                    } elseif ($isPaid) {
                        $cardClass = 'border-green-500 bg-green-50 dark:bg-green-900/10 dark:border-green-500/50';
                    }
                }
            ?>
                <div class="relative flex flex-col p-4 rounded-2xl border-2 transition-all hover:border-gray-300 dark:hover:border-gray-700 <?= $cardClass ?> group">
                    <div class="flex items-center justify-between mb-4">
                        <span class="font-bold text-gray-900 dark:text-white capitalize text-lg">
                            <?= ucfirst($mois) ?>
                        </span>
                        
                        <?php if ($isAmendeMode && $isPaid): ?>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                                Libéré
                            </span>
                        <?php elseif ($isAnticipation): ?>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300">
                                Anticipé
                            </span>
                        <?php elseif (!$isAmendeMode && $hasAmende): ?>
                             <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300">
                                Amende
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="">
                        <?php if ($isAmendeMode): ?>
                            <label class="flex items-center p-3 rounded-xl border transition-colors cursor-pointer <?= $hasAmende && !$disabled ? 'bg-red-100 dark:bg-red-900/30 border-red-200 dark:border-red-700' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750' ?>">
                                <input 
                                    type="checkbox" 
                                    name="amendes[<?= $mois ?>]" 
                                    value="1" 
                                    class="w-5 h-5 text-red-600 border-gray-300 dark:border-gray-600 rounded focus:ring-red-500 disabled:opacity-50"
                                    <?= $hasAmende ? 'checked' : '' ?>
                                    <?= $disabled ? 'disabled' : '' ?>
                                    onchange="const card = this.closest('.group'); if(this.checked) card.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/10'); else { card.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/10'); card.classList.add('border-gray-200', 'dark:border-gray-800'); }"
                                >
                                <div class="ml-3 flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white <?= $disabled ? 'text-gray-400 dark:text-gray-600' : '' ?>">Appliquer l'amende</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">2 000 FCFA</span>
                                </div>
                            </label>

                        <?php else: ?>
                            <label class="flex items-center p-3 rounded-xl border transition-colors cursor-pointer <?= $isPaid ? 'bg-green-100 dark:bg-green-900/30 border-green-200 dark:border-green-700' : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750' ?>">
                                <input 
                                    type="checkbox" 
                                    name="months[<?= $mois ?>]" 
                                    value="1" 
                                    class="w-5 h-5 text-green-600 border-gray-300 dark:border-gray-600 rounded focus:ring-green-500"
                                    <?= $isPaid ? 'checked' : '' ?>
                                    onchange="const card = this.closest('.group'); if(this.checked) card.classList.add('border-green-500', 'bg-green-50', 'dark:bg-green-900/10'); else { card.classList.remove('border-green-500', 'bg-green-50', 'dark:bg-green-900/10'); card.classList.add('border-gray-200', 'dark:border-gray-800'); }"
                                >
                                <div class="ml-3 flex flex-col">
                                    <span class="font-medium text-gray-900 dark:text-white">Dû mensuel payé</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400"><?= number_format($membre['montant_mensuel'], 0, ',', ' ') ?> FCFA</span>
                                </div>
                            </label>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-100 dark:border-gray-800">
            <a 
                href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>"
                class="w-full sm:w-auto text-center px-8 py-3 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition font-medium"
            >
                Annuler
            </a>
            <button 
                type="submit"
                class="w-full sm:w-auto <?= $btnColor ?> text-white px-8 py-3 rounded-2xl transition font-medium shadow-lg"
            >
                Enregistrer les changements
            </button>
        </div>
    </form>
</div>
    </form>
</div>
