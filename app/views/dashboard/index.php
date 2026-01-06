<!-- Dashboard -->
<div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
    <div>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Tableau de bord</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Vue d'ensemble du système de gestion des versements</p>
    </div>
    
    <div class="flex items-center gap-3">
        <form action="<?= BASE_URL ?>/dashboard" method="GET" class="flex items-center gap-3 bg-white dark:bg-gray-800 p-2 rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
            <label for="annee" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest ml-2">Période :</label>
            <select name="annee" id="annee" onchange="this.form.submit()" class="bg-white dark:bg-gray-800 border-none text-sm font-bold text-gray-900 dark:text-white focus:ring-0 cursor-pointer rounded-lg px-2">
                <option value="all" <?= $selectedYear === 'all' ? 'selected' : '' ?> class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Toutes les années</option>
                <?php foreach ($availableYears as $y): ?>
                    <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?> class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white">Année <?= $y ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Membres -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Total Membres</p>
                <p class="text-3xl font-semibold text-gray-900 dark:text-white mt-2 transition-colors"><?= $totalMembres ?></p>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900/30 rounded-full p-3 transition-colors">
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>
        <div class="mt-4 text-sm text-gray-600 dark:text-gray-400 transition-colors">
            <span class="text-green-600 dark:text-green-400 font-medium"><?= $membresActifs ?></span> actifs · 
            <span class="text-gray-500 dark:text-gray-500 font-medium"><?= $membresVG ?></span> Voyage / Non Travail · 
            <span class="text-red-600 dark:text-red-400 font-medium"><?= $membresSuspendus ?></span> suspendus
        </div>
    </div>

    <!-- Total Collecté -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Total Collecté</p>
                <p class="text-3xl font-semibold text-green-600 dark:text-green-400 mt-2 transition-colors"><?= number_format($totalCollecte, 0, ',', ' ') ?></p>
            </div>
            <div class="bg-green-100 dark:bg-green-900/30 rounded-full p-3 transition-colors">
                <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <p class="mt-4 text-sm text-gray-600">FCFA</p>
    </div>

    <!-- Total Dû -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Total Dettes </p>
                <p class="text-3xl font-semibold text-red-600 dark:text-red-400 mt-2 transition-colors"><?= number_format($totalDu, 0, ',', ' ') ?></p>
            </div>
            <div class="bg-red-100 dark:bg-red-900/30 rounded-full p-3 transition-colors">
                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 transition-colors">FCFA</p>
    </div>

    <!-- Total Avances (Remplacement de "En Attente") -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Total Avances</p>
                <p class="text-3xl font-semibold text-purple-600 dark:text-purple-400 mt-2 transition-colors"><?= number_format($totalAvances, 0, ',', ' ') ?></p>
            </div>
            <div class="bg-purple-100 dark:bg-purple-900/30 rounded-full p-3 transition-colors">
                <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
        </div>
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 transition-colors">FCFA</p>
    </div>
</div>

<!-- Two Column Layout -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Membres à Jour -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors">Membres à Jour</h2>
            <span class="text-xs font-bold text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/30 px-2 py-1 rounded-lg">Top 10</span>
        </div>
        <div class="space-y-4">
            <?php if (empty($membresAJour)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Aucun membre à jour pour cette période</p>
                </div>
            <?php else: ?>
                <?php foreach ($membresAJour as $membre): ?>
                    <a href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>" class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-xl px-2 -mx-2 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 font-bold group-hover:scale-110 transition-transform">
                                <?= strtoupper(substr($membre['designation'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors"><?= htmlspecialchars($membre['designation']) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors"><?= htmlspecialchars($membre['code']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 rounded-full transition-colors">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                À JOUR
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Membres avec Retard -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors">Membres en Retard</h2>
            <span class="text-xs font-bold text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-1 rounded-lg">Top 10</span>
        </div>
        <div class="space-y-4">
            <?php if (empty($membresRetard)): ?>
                <div class="flex flex-col items-center justify-center py-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-700 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 text-sm">Tout le monde est à jour !</p>
                </div>
            <?php else: ?>
                <?php foreach ($membresRetard as $membre): ?>
                    <a href="<?= BASE_URL ?>/membres/show?id=<?= $membre['id'] ?>" class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-xl px-2 -mx-2 transition-all group">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400 font-bold group-hover:scale-110 transition-transform">
                                <?= strtoupper(substr($membre['designation'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="font-medium text-gray-900 dark:text-white transition-colors"><?= htmlspecialchars($membre['designation']) ?></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 transition-colors"><?= htmlspecialchars($membre['code']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex px-2.5 py-1 text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300 rounded-full transition-colors mb-1">
                                <?= $membre['mois_retard'] ?> MOIS
                            </span>
                            <p class="text-xs font-semibold text-gray-600 dark:text-gray-400 transition-colors"><?= number_format($membre['montant_du'], 0, ',', ' ') ?> FCFA</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Export Actions -->
<div class="mt-8">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 transition-colors">Actions d'Export PDF</h2>
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
            <a href="<?= BASE_URL ?>/export/pdf?type=a-jour" class="flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-4 rounded-2xl transition shadow-sm font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                PDF Membres à Jour
            </a>
            <a href="<?= BASE_URL ?>/export/pdf?type=en-retard" class="flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white px-6 py-4 rounded-2xl transition shadow-sm font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                PDF Membres en Retard
            </a>
            <a href="<?= BASE_URL ?>/export/pdf?type=tous" class="flex items-center justify-center gap-2 bg-slate-700 hover:bg-slate-800 text-white px-6 py-4 rounded-2xl transition shadow-sm font-semibold text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2a4 4 0 10-8 0v2r2 2h4l2-2h4l2 2h4l2-2z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11l-7 7-7-7"></path>
                </svg>
                PDF État Global
            </a>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions -->
<div class="mt-8 flex flex-col sm:flex-row gap-4">
    <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
        <a href="<?= BASE_URL ?>/membres/create" class="flex items-center justify-center bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-6 py-3 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium">
            + Nouveau Membre
        </a>
    <?php endif; ?>
    <a href="<?= BASE_URL ?>/membres" class="flex items-center justify-center bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-6 py-3 rounded-2xl border border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600 transition font-medium">
        Voir tous les membres
    </a>
</div>
