<!-- Versements List -->
<div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="max-w-full">
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white transition-colors break-words">
            <?= $membre ? 'Historique des Versements' : 'Versements par Membre' ?>
        </h1>
        <p class="text-sm md:text-base text-gray-600 dark:text-gray-400 mt-1 transition-colors break-words">
            <?= $membre ? 'Détails des paiements pour ' . htmlspecialchars($membre['designation']) : 'Sélectionnez un membre pour voir son historique' ?>
        </p>
    </div>
    <?php if ($membre): ?>
        <a href="<?= BASE_URL ?>/versements" class="inline-flex items-center justify-center gap-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-700 transition font-bold text-sm min-w-fit self-start md:self-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Retour à la liste
        </a>
    <?php endif; ?>
</div>

<!-- Filters (Only show if not viewing a specific member or if using status filter) -->
<?php if (!$membre): ?>
<div class="bg-white dark:bg-gray-950 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 mb-8 shadow-sm transition-all hover:shadow-md">
    <form method="GET" action="<?= BASE_URL ?>/versements" class="flex flex-wrap items-center gap-6">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 ml-1">Rechercher ou Filtrer</label>
            <div class="flex gap-4">
                <select name="statut" class="flex-1 bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-sm font-semibold text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition-all px-4 py-3">
                    <option value="" class="bg-white dark:bg-gray-900">Tous les statuts</option>
                    <option value="EN_ATTENTE" <?= $currentStatut === 'EN_ATTENTE' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">En Attente</option>
                    <option value="PAYE" <?= $currentStatut === 'PAYE' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">Payé</option>
                    <option value="PARTIEL" <?= $currentStatut === 'PARTIEL' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">Partiel</option>
                    <option value="ANNULE" <?= $currentStatut === 'ANNULE' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">Annulé</option>
                </select>
            </div>
        </div>
        <div class="flex gap-3 w-full sm:w-auto pt-6">
            <button type="submit" class="flex-1 sm:flex-none bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-8 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-100 transition shadow-sm font-bold text-sm">
                Filtrer
            </button>
            <?php if ($currentStatut): ?>
                <a href="<?= BASE_URL ?>/versements" class="flex-1 sm:flex-none text-center bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-8 py-3 rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-700 transition font-bold text-sm">
                    Réinitialiser
                </a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- Member Info (if filtered by member) -->
<?php if ($membre): ?>
    <div class="bg-blue-50 dark:bg-blue-900/10 border border-blue-200 dark:border-blue-800/50 rounded-3xl p-6 mb-8 transition-all hover:bg-blue-100 dark:hover:bg-blue-900/20 group">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-xl">
                    <?= strtoupper(substr($membre['designation'], 0, 1)) ?>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white transition-colors truncate"><?= htmlspecialchars($membre['designation']) ?></h2>
                        <span class="px-2 py-0.5 bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300 text-[10px] font-extrabold rounded-md uppercase tracking-wider shrink-0"><?= htmlspecialchars($membre['code']) ?></span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total mensuel: <span class="font-bold text-gray-900 dark:text-gray-200"><?= number_format($membre['montant_mensuel'], 0, ',', ' ') ?> FCFA</span></p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="<?= BASE_URL ?>/versements/create?membre_id=<?= $membre['id'] ?>" class="flex items-center justify-center gap-2 bg-emerald-600 text-white px-5 py-3 rounded-2xl hover:bg-emerald-700 transition-all font-bold text-sm shadow-lg shadow-emerald-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Gérer les Paiements
                </a>
                <a href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>" class="flex items-center justify-center gap-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white px-5 py-3 rounded-2xl border border-gray-200 dark:border-gray-700 hover:shadow-xl transition-all font-bold text-sm">
                    Voir la fiche
                </a>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Main Content Table -->
<div class="bg-white dark:bg-gray-950 rounded-3xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm transition-all hover:shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-900/50">
                    <?php if (!$membre): ?>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Membre</th>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Code</th>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Téléphone</th>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest text-center">Statut</th>
                        <th class="px-8 py-5 text-right text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Actions</th>
                    <?php else: ?>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Période</th>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Montant</th>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest text-center">Statut</th>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest whitespace-nowrap">Date Paye</th>
                        <th class="px-8 py-5 text-right text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-900">
                <?php if (!$membre && empty($membres)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center justify-center opacity-50">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <p class="text-gray-500 font-medium">Aucun membre trouvé</p>
                            </div>
                        </td>
                    </tr>
                <?php elseif ($membre && empty($versements)): ?>
                    <tr>
                        <td colspan="5" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center justify-center opacity-50">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <p class="text-gray-500 font-medium">Aucun versement trouvé pour ce membre</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php if (!$membre): ?>
                        <?php foreach ($membres as $m): ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold">
                                            <?= strtoupper(substr($m['designation'], 0, 1)) ?>
                                        </div>
                                        <div>
                                            <a href="<?= BASE_URL ?>/versements?membre_id=<?= $m['id'] ?>" class="font-bold text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                                <?= htmlspecialchars($m['designation']) ?>
                                            </a>
                                            <p class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"><?= htmlspecialchars($m['titre'] ?? 'Membre') ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-sm font-bold text-gray-600 dark:text-gray-400">
                                    <?= htmlspecialchars($m['code']) ?>
                                </td>
                                <td class="px-8 py-5 text-sm font-medium text-gray-500 dark:text-gray-500">
                                    <?= htmlspecialchars($m['telephone'] ?? '—') ?>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-black <?= $m['statut'] === 'ACTIF' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400' ?> rounded-lg border border-transparent uppercase tracking-wider">
                                        <?= htmlspecialchars($m['statut']) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="<?= BASE_URL ?>/versements?membre_id=<?= $m['id'] ?>" class="inline-flex items-center gap-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 px-4 py-2 rounded-xl text-xs font-bold hover:shadow-md transition-all">
                                        Voir l'historique
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($versements as $versement): ?>
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors group">
                                <td class="px-8 py-5 text-sm font-semibold text-gray-900 dark:text-gray-300">
                                    <span class="capitalize"><?= htmlspecialchars($versement['mois']) ?></span> <?= (int)$versement['annee'] ?>
                                </td>
                                <td class="px-8 py-5 text-sm font-bold text-gray-900 dark:text-gray-200 whitespace-nowrap">
                                    <?= number_format($versement['montant'], 0, ',', ' ') ?> <span class="text-[10px] text-gray-450 uppercase ml-0.5">FCFA</span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <?php
                                    $badgeStyles = match($versement['statut']) {
                                        'PAYE' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300 border-emerald-200/50',
                                        'PARTIEL' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300 border-amber-200/50',
                                        'EN_ATTENTE' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300 border-rose-200/50',
                                        'ANNULE' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400 border-slate-200/50',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
                                    };
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1.5 text-[10px] font-black <?= $badgeStyles ?> rounded-lg border uppercase tracking-tighter">
                                        <?= htmlspecialchars($versement['statut']) ?>
                                    </span>
                                </td>
                                <td class="px-8 py-5 text-sm font-medium text-gray-500 dark:text-gray-500">
                                    <?= $versement['date_paiement'] ? date('d/m/Y', strtotime($versement['date_paiement'])) : '<span class="opacity-30">—</span>' ?>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <?php if (in_array($currentUser['role'], ['admin', 'comptable']) && $versement['statut'] === 'EN_ATTENTE'): ?>
                                        <button 
                                            onclick="markAsPaid(<?= $versement['id'] ?>, <?= $membre['montant_mensuel'] ?>)"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold transition-all shadow-sm"
                                        >
                                            Payer
                                        </button>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$membre && !empty($membres)): ?>
    <div class="mt-6 flex items-center justify-between px-2">
        <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            Total affiché: <?= count($membres) ?> membre(s)
        </div>
    </div>
<?php elseif ($membre && !empty($versements)): ?>
    <div class="mt-6 flex items-center justify-between px-2">
        <div class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
            Total affiché: <?= count($versements) ?> versement(s)
        </div>
    </div>
<?php endif; ?>

<?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
<script>
function markAsPaid(versementId, defaultMontant) {
    if (!confirm('Voulez-vous marquer ce versement de ' + defaultMontant.toLocaleString() + ' FCFA comme PAYÉ ?')) {
        return;
    }
    
    fetch('<?= BASE_URL ?>/versements/mark-paid', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${versementId}&montant=${defaultMontant}&<?= CSRF_TOKEN_NAME ?>=<?= Security::generateCsrfToken() ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Erreur: ' + data.message);
        }
    })
    .catch(error => {
        alert('Erreur de connexion');
        console.error(error);
    });
}
</script>
<?php endif; ?>
