<?php
/**
 * Vue Liste Admin des Déclarations (Admin/Comptable)
 */
?>

<div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Déclarations en Attente</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Vérifiez et validez les paiements déclarés par les membres.</p>
</div>

<div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
    <?php if (empty($declarations)): ?>
        <div class="p-16 text-center">
            <div class="w-20 h-20 bg-green-50 dark:bg-green-900/20 rounded-full flex items-center justify-center mx-auto mb-4 transition-colors">
                <svg class="w-10 h-10 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white transition-colors">Tout est à jour !</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-1 transition-colors">Aucune déclaration en attente de validation.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-950 transition-colors">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Membre</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800 transition-colors">
                    <?php foreach ($declarations as $d): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center font-bold text-gray-900 dark:text-white transition-colors group-hover:bg-blue-100 dark:group-hover:bg-blue-900/30 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-all">
                                        <?= strtoupper(substr($d['designation'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white transition-colors"><?= htmlspecialchars($d['designation']) ?></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors"><?= htmlspecialchars($d['code']) ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-gray-600 dark:text-gray-400 transition-colors">
                                    <?php
                                    echo match($d['type_paiement']) {
                                        'mois_en_cours' => 'Mois en cours',
                                        'dette_anterieure' => 'Dette antérieure',
                                        'avance_mois' => 'Avance mois',
                                        'avance_annee' => 'Avance année',
                                        default => $d['type_paiement']
                                    };
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-black text-gray-900 dark:text-white transition-colors"><?= number_format($d['montant'], 0, ',', ' ') ?> FCFA</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs text-gray-500 dark:text-gray-400 transition-colors"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="<?= BASE_URL ?>/declarations/show?id=<?= $d['id'] ?>" class="inline-flex items-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-xl text-xs font-bold transition hover:bg-black dark:hover:bg-gray-200 shadow-md">
                                    Vérifier & Valider
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
