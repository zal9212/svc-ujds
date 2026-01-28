<?php
/**
 * Vue Index des Déclarations (Membre)
 */
?>

<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Mes Déclarations</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Historique de vos déclarations de paiement</p>
    </div>
    
    <a href="<?= BASE_URL ?>/declarations/submit" class="flex items-center justify-center bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-6 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium shadow-lg transform hover:scale-[1.02]">
        + Déclarer un Paiement
    </a>
</div>

<div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm">
    <?php if (empty($declarations)): ?>
        <div class="p-12 text-center text-gray-500 dark:text-gray-400">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <p class="text-lg font-medium">Aucune déclaration trouvée</p>
            <p class="mt-1">Vous n'avez pas encore soumis de déclaration de paiement.</p>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-950 transition-colors">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase tracking-widest">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <?php foreach ($declarations as $d): ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900 dark:text-white"><?= date('d/m/Y H:i', strtotime($d['created_at'])) ?></span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">
                                    <?php
                                    echo match($d['type_paiement']) {
                                        'mois_en_cours' => 'Mois en cours',
                                        'dette_anterieure' => 'Dette antérieure',
                                        'avance_mois' => 'Avance mois futurs',
                                        'avance_annee' => 'Avance année',
                                        default => $d['type_paiement']
                                    };
                                    ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-bold text-gray-900 dark:text-white"><?= number_format($d['montant'], 0, ',', ' ') ?> FCFA</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php
                                $statusClass = match($d['statut']) {
                                    'EN_ATTENTE' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                    'VALIDE' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                    'REJETE' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                                ?>
                                <span class="px-2.5 py-1 text-[10px] font-black <?= $statusClass ?> rounded-full uppercase tracking-tighter">
                                    <?= str_replace('_', ' ', $d['statut']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="<?= BASE_URL ?>/declarations/show?id=<?= $d['id'] ?>" class="inline-flex items-center gap-2 text-sm font-bold text-gray-900 dark:text-white hover:underline">
                                    Détails & Chat
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
