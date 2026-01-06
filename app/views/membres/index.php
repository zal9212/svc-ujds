<!-- Membres List -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Membres</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Gestion des membres de l'association</p>
    </div>
    <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
        <a href="<?= BASE_URL ?>/membres/create" class="bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-6 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium text-center">
            + Nouveau Membre
        </a>
    <?php endif; ?>
</div>

<!-- Filters -->
<div class="bg-white dark:bg-gray-950 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 mb-8 shadow-sm transition-all hover:shadow-md">
    <form method="GET" action="<?= BASE_URL ?>/membres" class="flex flex-wrap gap-6">
        <div class="w-full lg:flex-1 min-w-[300px]">
            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 ml-1">Recherche</label>
            <div class="relative group">
                <input 
                    type="text" 
                    name="search" 
                    value="<?= htmlspecialchars($currentSearch ?? '') ?>"
                    placeholder="Nom, matricule ou missidé..."
                    class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-sm font-semibold text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition-all pl-12 pr-4 py-3"
                >
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-gray-900 dark:group-focus-within:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
            </div>
        </div>
        
        <div class="grid grid-cols-2 sm:flex gap-4 w-full lg:w-auto">
            <div class="flex-1 min-w-[120px]">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 ml-1">Statut</label>
                <select name="statut" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-sm font-semibold text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition-all px-4 py-3">
                    <option value="" class="bg-white dark:bg-gray-900">Tous</option>
                    <option value="ACTIF" <?= $currentStatut === 'ACTIF' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">Actif</option>
                    <option value="VG" <?= $currentStatut === 'VG' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">VG</option>
                    <option value="SUSPENDU" <?= $currentStatut === 'SUSPENDU' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">Suspendu</option>
                </select>
            </div>

            <div class="flex-1 min-w-[120px]">
                <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-2 ml-1">Finance</label>
                <select name="situation" class="w-full bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800 text-sm font-semibold text-gray-900 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition-all px-4 py-3">
                    <option value="" class="bg-white dark:bg-gray-900">Tout</option>
                    <option value="a_jour" <?= $currentSituation === 'a_jour' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">À jour</option>
                    <option value="en_retard" <?= $currentSituation === 'en_retard' ? 'selected' : '' ?> class="bg-white dark:bg-gray-900">Retard</option>
                </select>
            </div>

            <div class="col-span-2 sm:flex gap-3 pt-6">
                <button type="submit" class="flex-1 sm:flex-none bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-8 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-100 transition shadow-sm font-bold text-sm">
                    Filtrer
                </button>
                <?php if ($currentSearch || $currentStatut || $currentSituation || $currentHasAmende): ?>
                    <a href="<?= BASE_URL ?>/membres" class="flex-1 sm:flex-none text-center bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-8 py-3 rounded-2xl hover:bg-gray-200 dark:hover:bg-gray-700 transition font-bold text-sm">
                        Réinitialiser
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </form>
</div>

<!-- Members Table -->
<div class="bg-white dark:bg-gray-950 rounded-3xl border border-gray-200 dark:border-gray-800 overflow-hidden shadow-sm transition-all hover:shadow-md">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
            <thead>
                <tr class="bg-gray-50/50 dark:bg-gray-900/50">
                    <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Membre</th>
                    <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Contact / Localité</th>
                    <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest text-center">Mt Mensuel</th>
                    <?php endif; ?>
                    <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest text-center">Statut</th>
                    <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
                        <th class="px-8 py-5 text-left text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest text-center">Finance</th>
                    <?php endif; ?>
                    <th class="px-8 py-5 text-right text-xs font-bold text-gray-500 dark:text-gray-500 uppercase tracking-widest">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-900">
                <?php if (empty($membres)): ?>
                    <tr>
                        <td colspan="7" class="px-8 py-16 text-center">
                            <div class="flex flex-col items-center justify-center opacity-50">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                <p class="text-gray-500 font-medium font-bold">Aucun membre trouvé</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($membres as $membre): ?>
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors group">
                            <td class="px-8 py-5">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold text-xl transition-all group-hover:bg-blue-600 group-hover:text-white shadow-sm">
                                        <?= strtoupper(substr($membre['designation'], 0, 1)) ?>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors"><?= htmlspecialchars($membre['designation']) ?></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest"><?= htmlspecialchars($membre['code']) ?></span>
                                            <?php if ($membre['titre']): ?>
                                                <span class="px-1.5 py-0.5 bg-gray-100 dark:bg-gray-800 text-[10px] font-bold text-gray-500 rounded uppercase"><?= htmlspecialchars($membre['titre']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-5">
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-300"><?= htmlspecialchars($membre['telephone'] ?? '-') ?></p>
                                <p class="text-xs font-semibold text-gray-450 dark:text-gray-500"><?= htmlspecialchars($membre['misside'] ?? '-') ?></p>
                            </td>
                            <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
                                <td class="px-8 py-5 text-center text-sm font-bold text-gray-900 dark:text-gray-300">
                                    <?= number_format($membre['montant_mensuel'], 0, ',', ' ') ?> <span class="text-[10px] opacity-50 ml-0.5">FCFA</span>
                                </td>
                            <?php endif; ?>
                            <td class="px-8 py-5 text-center">
                                <?php
                                $badgeStyles = match($membre['statut']) {
                                    'ACTIF' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border-emerald-200/50',
                                    'VG' => 'bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-400 border-slate-200/50',
                                    'SUSPENDU' => 'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-400 border-rose-200/50',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
                                };
                                ?>
                                <span class="inline-flex items-center px-3 py-1.5 text-[10px] font-black <?= $badgeStyles ?> rounded-lg border uppercase tracking-tighter">
                                    <?= htmlspecialchars($membre['statut']) ?>
                                </span>
                            </td>
                            <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
                                <td class="px-8 py-5 text-center">
                                    <?php if ($membre['montant_du'] > 0): ?>
                                        <div class="flex flex-col items-center">
                                            <span class="text-sm font-black text-rose-600 dark:text-rose-400"><?= number_format($membre['montant_du'], 0, ',', ' ') ?></span>
                                            <span class="text-[9px] font-bold bg-rose-100 dark:bg-rose-900/30 text-rose-800 dark:text-rose-400 px-1.5 py-0.5 rounded uppercase -mt-0.5"><?= $membre['mois_retard'] ?> MOIS</span>
                                        </div>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-800 dark:text-emerald-400 text-[10px] font-black rounded uppercase">À JOUR</span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="px-8 py-5 text-right flex items-center justify-end gap-2">
                                <a href="<?= BASE_URL ?>/versements?membre_id=<?= $membre['id'] ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-emerald-600 dark:hover:text-emerald-400 transition-all shadow-sm" title="Historique des paiements">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                </a>
                                <a href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>" class="inline-flex items-center justify-center w-10 h-10 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-blue-600 dark:hover:text-blue-400 transition-all shadow-sm" title="Détails du membre">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($membres)): ?>
        Total: <?= count($membres) ?> membre(s)
    </div>
<?php endif; ?>
