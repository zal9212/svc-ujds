<!-- Member Detail -->
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white transition-colors"><?= htmlspecialchars($membre['designation']) ?></h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors font-medium"><?= htmlspecialchars($membre['code']) ?></p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <?php if (in_array($currentUser['role'], ['admin'])): ?>
                <form action="<?= BASE_URL ?>/membres/delete" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce membre ? Cette action est irréversible et supprimera tout l\'historique associé.');" class="flex-1 sm:flex-none">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                    <input type="hidden" name="id" value="<?= (int)$membre['id'] ?>">
                    <button type="submit" class="w-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 px-6 py-3 rounded-2xl border border-red-100 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 transition font-medium shadow-sm">
                        Supprimer
                    </button>
                </form>
            <?php endif; ?>
            
            <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
                <a href="<?= BASE_URL ?>/membres/edit?id=<?= (int)$membre['id'] ?>" class="flex-1 sm:flex-none text-center bg-white dark:bg-gray-900 text-gray-900 dark:text-white px-6 py-3 rounded-2xl border border-gray-300 dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-600 transition font-medium shadow-sm">
                    Modifier
                </a>
            <?php endif; ?>

            <?php if ($currentUser['role'] === 'membre'): ?>
                <a href="<?= BASE_URL ?>/auth/changePassword" class="flex-1 sm:flex-none text-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 px-6 py-3 rounded-2xl border border-blue-100 dark:border-blue-800 hover:bg-blue-100 dark:hover:bg-red-900/40 transition font-medium shadow-sm">
                    Mot de passe
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/export/pdf?type=fiche-membre&id=<?= (int)$membre['id'] ?>" class="flex-1 sm:flex-none text-center bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 px-6 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 hover:bg-slate-200 dark:hover:bg-slate-700 transition font-medium shadow-sm flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Fiche PDF
            </a>

            <a href="<?= BASE_URL ?>/membres" class="flex-1 sm:flex-none text-center bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-300 px-6 py-3 rounded-2xl hover:bg-gray-300 dark:hover:bg-gray-700 transition font-medium">
                ← Retour
            </a>
        </div>
    </div>
</div>

<!-- Years Navigation (Dynamic List) -->
<div class="flex flex-wrap justify-center items-center gap-2 mb-8 bg-gray-50 dark:bg-gray-900/50 p-4 rounded-3xl border border-gray-100 dark:border-gray-800 transition-colors">
    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mr-2">Années concernées :</span>
    <?php foreach ($availableYears as $y): ?>
        <a href="<?= BASE_URL ?>/membres/show?id=<?= (int)$membre['id'] ?>&annee=<?= (int)$y ?>" 
           class="px-5 py-2 rounded-xl text-sm font-semibold transition-all duration-300 <?= (int)$y == (int)$annee ? 'bg-gray-950 text-white dark:bg-white dark:text-gray-950 shadow-md transform scale-110' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' ?>">
            <?= (int)$y ?>
        </a>
    <?php endforeach; ?>
</div>

<!-- Global Summary Section -->
<?php if (in_array($currentUser['role'], ['admin', 'comptable']) || (isset($_SESSION['user_id']) && $membre['user_id'] == $_SESSION['user_id'])): ?>
    <div class="mb-8 overflow-hidden">
        <h2 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-4">Résumé Global (Toutes Années)</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-gray-900 dark:bg-white rounded-2xl p-6 shadow-lg transform hover:scale-[1.02] transition-all duration-300">
                <p class="text-xs font-medium text-gray-400 dark:text-gray-500 mb-1 uppercase">Total Versé</p>
                <p class="text-2xl font-bold text-white dark:text-gray-900"><?= number_format($globalSituation['total_verse'], 0, ',', ' ') ?> <span class="text-xs font-normal opacity-70">FCFA</span></p>
            </div>
            
            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase">Total Anticipations</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400"><?= number_format($globalSituation['total_avance'], 0, ',', ' ') ?> <span class="text-xs font-normal opacity-70">FCFA</span></p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase">Dette Cumulée</p>
                <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?= number_format($globalSituation['montant_du'], 0, ',', ' ') ?> <span class="text-xs font-normal opacity-70">FCFA</span></p>
            </div>

            <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 shadow-sm hover:shadow-md transition-all group relative">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1 uppercase">Mois en Retard</p>
                <div class="flex items-end justify-between">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white"><?= $globalSituation['mois_retard'] ?> <span class="text-xs font-normal text-gray-500 uppercase">Mois</span></p>
                    
                    <?php if (in_array($currentUser['role'], ['admin'])): ?>
                        <div class="flex gap-2">
                            <!-- Modify Button (Trigger Modal) -->
                            <button onclick="openRetardModal()" 
                               class="p-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                               title="Modifier / Ajuster">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </button>
                            
                            <!-- Delete All Button -->
                            <?php if ($globalSituation['mois_retard'] > 0): ?>
                                <button onclick="deleteAllRetards(<?= (int)$membre['id'] ?>)" 
                                        class="p-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition" 
                                        title="Supprimer tous les retards">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Year Navigation (Traditional) -->
<div class="flex justify-center mb-6">
    <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1 transition-colors">
        <a href="<?= BASE_URL ?>/membres/show?id=<?= (int)$membre['id'] ?>&annee=<?= (int)$annee - 1 ?>" class="px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-900 hover:shadow-sm transition">
            ← <?= (int)$annee - 1 ?>
        </a>
        <span class="px-4 py-2 rounded-md text-sm font-bold text-gray-900 dark:text-white bg-white dark:bg-gray-900 shadow-sm transition-colors">
            <?= (int)$annee ?>
        </span>
        <a href="<?= BASE_URL ?>/membres/show?id=<?= (int)$membre['id'] ?>&annee=<?= (int)$annee + 1 ?>" class="px-4 py-2 rounded-md text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-900 hover:shadow-sm transition">
            <?= (int)$annee + 1 ?> →
        </a>
    </div>
</div>


<!-- Status Badge -->
<div class="mb-6">
    <?php
    $badgeClass = match($membre['statut']) {
        'ACTIF' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'VG' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        'SUSPENDU' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300'
    };
    ?>
    <span class="inline-block px-4 py-2 text-sm font-medium <?= $badgeClass ?> rounded-full">
        <?= htmlspecialchars($membre['statut']) ?>
    </span>
</div>

<!-- Summary Cards (Year Specific) -->
<?php if (in_array($currentUser['role'], ['admin', 'comptable']) || $membre['user_id'] == $currentUser['id']): ?>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2 transition-colors">Mois en Retard (<?= $annee ?>)</p>
            <p class="text-3xl font-semibold text-orange-600 dark:text-orange-400 transition-colors"><?= $membre['mois_retard_annee'] ?></p>
        </div>
        
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2 transition-colors">Amende (<?= $annee ?>)</p>
            <p class="text-2xl font-semibold text-red-600 dark:text-red-400 transition-colors"><?= number_format($membre['amende_annee'], 0, ',', ' ') ?> FCFA</p>
        </div>
        
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2 transition-colors">Total Versé (<?= $annee ?>)</p>
            <p class="text-2xl font-semibold text-green-600 dark:text-green-400 transition-colors"><?= number_format($membre['total_verse_annee'], 0, ',', ' ') ?> FCFA</p>
        </div>
        
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-2 transition-colors">Montant Dû (<?= $annee ?>)</p>
            <p class="text-2xl font-semibold text-red-600 dark:text-red-400 transition-colors"><?= number_format($membre['montant_du_annee'], 0, ',', ' ') ?> FCFA</p>
        </div>
    </div>
<?php endif; ?>

<!-- Two Column Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Left Column - Info -->
    <div class="lg:col-span-1">
        <!-- Member Info -->
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-6 transition-colors">
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 transition-colors">Informations</h2>
            <dl class="space-y-3">
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Numéro</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-300 mt-1 transition-colors"><?= htmlspecialchars($membre['numero'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Titre</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-300 mt-1 transition-colors"><?= htmlspecialchars($membre['titre'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Téléphone</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-300 mt-1 transition-colors"><?= htmlspecialchars($membre['telephone'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Missidé</dt>
                    <dd class="text-sm text-gray-900 dark:text-gray-300 mt-1 transition-colors"><?= htmlspecialchars($membre['misside'] ?? '-') ?></dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-600 dark:text-gray-400 transition-colors">Cotisation Mensuelle</dt>
                    <dd class="text-sm text-gray-900 dark:text-white mt-1 font-medium transition-colors"><?= number_format($membre['montant_mensuel'], 0, ',', ' ') ?> FCFA / mois</dd>
                </div>
            </dl>

            <!-- User Account Section -->
            <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <h3 class="text-sm font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-4 px-1">Compte Portail</h3>
                
                <?php if (!$membre['user_id']): ?>
                    <div class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-100 dark:border-amber-900/30">
                        <p class="text-xs text-amber-700 dark:text-amber-400 mb-3">Ce membre n'a pas encore de compte utilisateur pour accéder au portail.</p>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <form action="<?= BASE_URL ?>/membres/createAccount" method="POST">
                                <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                                <input type="hidden" name="id" value="<?= (int)$membre['id'] ?>">
                                <button type="submit" class="w-full py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors">
                                    Générer un compte
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="flex items-center justify-between bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-900/30">
                        <div>
                            <p class="text-[10px] text-blue-600 dark:text-blue-400 uppercase font-bold tracking-tight">Identifiant (Tél)</p>
                            <p class="text-sm font-semibold text-blue-900 dark:text-blue-300"><?= htmlspecialchars($membre['telephone'] ?: $membre['code']) ?></p>
                        </div>
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/40 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-600 dark:text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Avances (Pour Dettes) -->
        <?php if (in_array($currentUser['role'], ['admin', 'comptable']) || $membre['user_id'] == $currentUser['id']): ?>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors">Historique des Avances</h2>
                    <span class="text-xs text-orange-600 dark:text-orange-400 font-medium bg-orange-50 dark:bg-orange-900/20 px-2 py-1 rounded-lg">Pour dettes</span>
                </div>
                <?php if (empty($lists['avances'])): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">Aucune avance enregistrée.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($lists['avances'] as $avance): ?>
                            <div class="border-b border-gray-100 dark:border-gray-800 pb-3 last:border-0 transition-colors group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white transition-colors"><?= number_format($avance['montant'], 0, ',', ' ') ?> FCFA</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors"><?= date('d/m/Y', strtotime($avance['date_avance'])) ?></p>
                                    </div>
                                    <?php if (in_array($currentUser['role'], ['admin'])): ?>
                                        <div class="flex gap-2">
                                            <a href="<?= BASE_URL ?>/avances/edit?id=<?= (int)$avance['id'] ?>" 
                                               class="p-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <button onclick="promptDeleteAvance(<?= (int)$avance['id'] ?>)" 
                                                    class="p-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($avance['motif'])): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 transition-colors"><?= htmlspecialchars($avance['motif']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800 transition-colors">
                        <p class="text-sm font-medium text-orange-600 dark:text-orange-400 transition-colors">Total Avances: <?= number_format($lists['total_avances'], 0, ',', ' ') ?> FCFA</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Anticipations (Pour Mois Futurs) -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors">Historique des Anticipations</h2>
                    <span class="text-xs text-purple-600 dark:text-purple-400 font-medium bg-purple-50 dark:bg-purple-900/20 px-2 py-1 rounded-lg">Mois futurs</span>
                </div>
                <?php if (empty($lists['anticipations'])): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">Aucune anticipation enregistrée.</p>
                <?php else: ?>
                    <div class="space-y-3">
                        <?php foreach ($lists['anticipations'] as $avance): ?>
                            <div class="border-b border-gray-100 dark:border-gray-800 pb-3 last:border-0 transition-colors group">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white transition-colors"><?= number_format($avance['montant'], 0, ',', ' ') ?> FCFA</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors">
                                            le <?= date('d/m/Y', strtotime($avance['date_avance'])) ?>
                                            <?php if (!empty($avance['date_debut'])): ?>
                                                &bull; <span class="text-purple-600 dark:text-purple-400 font-medium">Débute le <?= date('m/Y', strtotime($avance['date_debut'])) ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <?php if (in_array($currentUser['role'], ['admin'])): ?>
                                        <div class="flex gap-2">
                                            <a href="<?= BASE_URL ?>/avances/edit?id=<?= (int)$avance['id'] ?>" 
                                               class="p-1.5 bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </a>
                                            <button onclick="promptDeleteAvance(<?= (int)$avance['id'] ?>)" 
                                                    class="p-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if (!empty($avance['motif'])): ?>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1 transition-colors"><?= htmlspecialchars($avance['motif']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-800 transition-colors">
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400 transition-colors">Total Anticipations: <?= number_format($lists['total_anticipations'], 0, ',', ' ') ?> FCFA</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Configuration / Ajout (Admin Only) -->
        <?php if (in_array($currentUser['role'], ['admin'])): ?>
            <div id="ajustement-retards" class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 mb-6 transition-colors mt-6 scroll-mt-20">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 transition-colors">Ajouter Avance ou Anticipation</h2>
                
                <div class="bg-blue-50 dark:bg-blue-900/10 p-4 rounded-xl text-sm text-blue-800 dark:text-blue-200 mb-4">
                    <ul class="list-disc pl-5 space-y-1">
                        <li><strong>Avance</strong> : Pour payer les dettes existantes (mois en retard)</li>
                        <li><strong>Anticipation</strong> : Pour anticiper les mois futurs (pas de dette)</li>
                        <li>Pour <strong>configurer les retards</strong>, utilisez le bouton "Modifier" (crayon) sur la carte "Mois en Retard" en haut de page.</li>
                    </ul>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Formulaire Avance -->
                    <form action="<?= BASE_URL ?>/membres/update_financial_config" method="POST" onsubmit="return confirm('Cette avance sera utilisée pour payer les dettes existantes. Continuer ?');" class="p-4 border border-orange-200 dark:border-orange-800/30 rounded-xl bg-orange-50/50 dark:bg-orange-900/10">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $membre['id'] ?>">
                        <input type="hidden" name="type" value="AVANCE">
                        
                        <label class="block text-sm font-medium text-orange-700 dark:text-orange-300 mb-2">
                            Ajouter Avance (pour dettes)
                        </label>
                        <input 
                            type="number" 
                            name="avance_initiale" 
                            min="0"
                            step="0.01"
                            placeholder="Montant FCFA"
                            class="w-full px-3 py-2 border border-orange-300 dark:border-orange-700 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-transparent transition text-sm mb-3"
                        >
                        <button 
                            type="submit"
                            class="w-full bg-orange-600 text-white px-4 py-2 rounded-xl hover:bg-orange-700 transition font-medium text-sm shadow-sm"
                        >
                            Ajouter Avance
                        </button>
                    </form>

                    <!-- Formulaire Anticipation -->
                    <form action="<?= BASE_URL ?>/membres/update_financial_config" method="POST" onsubmit="return confirm('Cette anticipation sera utilisée pour les mois futurs. Continuer ?');" class="p-4 border border-purple-200 dark:border-purple-800/30 rounded-xl bg-purple-50/50 dark:bg-purple-900/10">
                        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                        <input type="hidden" name="id" value="<?= $membre['id'] ?>">
                        <input type="hidden" name="type" value="ANTICIPATION">
                        
                        <label class="block text-sm font-medium text-purple-700 dark:text-purple-300 mb-2">
                            Ajouter Anticipation (mois futurs)
                        </label>
                        <input 
                            type="number" 
                            name="avance_initiale" 
                            min="0"
                            step="0.01"
                            placeholder="Montant FCFA"
                            class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm mb-3"
                            required
                        >
                        
                        <label class="block text-sm font-medium text-purple-700 dark:text-purple-300 mb-2">
                            Date de début (optionnel)
                        </label>
                        <input 
                            type="date" 
                            name="date_debut" 
                            class="w-full px-3 py-2 border border-purple-300 dark:border-purple-700 dark:bg-gray-800 dark:text-white rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition text-sm mb-3"
                        >
                        <button 
                            type="submit"
                            class="w-full bg-purple-600 text-white px-4 py-2 rounded-xl hover:bg-purple-700 transition font-medium text-sm shadow-sm"
                        >
                            Ajouter Anticipation
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="lg:col-span-2">
        <?php if (in_array($currentUser['role'], ['admin', 'comptable']) || $membre['user_id'] == $currentUser['id']): ?>
            <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-6 transition-colors">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white transition-colors">Historique <?= $annee ?></h2>
                    <?php if (in_array($currentUser['role'], ['admin', 'comptable'])): ?>
                        <div class="flex gap-2">
                            <a href="<?= BASE_URL ?>/versements/create?membre_id=<?= $membre['id'] ?>&annee=<?= $annee ?>&mode=amende" class="bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 px-4 py-2 rounded-2xl border border-red-100 dark:border-red-800 hover:bg-red-100 dark:hover:bg-red-900/40 transition text-sm font-medium">
                                + Gérer Amendes
                            </a>
                            <a href="<?= BASE_URL ?>/versements/create?membre_id=<?= $membre['id'] ?>&annee=<?= $annee ?>&mode=versement" class="bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-4 py-2 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition text-sm font-medium">
                                + Nouveau Versement
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (empty($membre['versements'])): ?>
                    <p class="text-sm text-gray-500 dark:text-gray-400 transition-colors">Aucun versement en <?= $annee ?></p>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50 dark:bg-gray-950 transition-colors">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Période</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Statut</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date Paiement</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800 transition-colors">
                                <?php 
                                // Préparation : Grouper les données réconciliées par mois
                                $versementsByMonth = [];
                                // Mapping mois nombre -> nom (DOIT matcher exactement la BDD/Membre.php)
                                $moisFr = [
                                    1 => 'janvier', 2 => 'février', 3 => 'mars', 4 => 'avril',
                                    5 => 'mai', 6 => 'juin', 7 => 'juillet', 8 => 'août',
                                    9 => 'septembre', 10 => 'octobre', 11 => 'novembre', 12 => 'décembre'
                                ];

                                if (!empty($globalSituation['reconciled'])) {
                                    foreach ($globalSituation['reconciled'] as $rec) {
                                        // On ne garde que ceux de l'année en cours d'affichage
                                        if ($rec['annee'] == $annee) {
                                            $versementsByMonth[$rec['mois']][] = $rec;
                                        }
                                    }
                                }

                                // Boucle sur les 12 mois
                                for ($m = 1; $m <= 12; $m++):
                                    $nomMois = $moisFr[$m];
                                    $entries = $versementsByMonth[$nomMois] ?? [];
                                    
                                    // Si aucune entrée pour ce mois, on affiche une ligne vide
                                    if (empty($entries)):
                                ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-500 transition-colors">
                                            <?= ucfirst($nomMois) ?> <?= $annee ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-400 dark:text-gray-600">-</td>
                                        <td class="px-4 py-3">
                                            <span class="inline-block px-2 py-1 text-[10px] font-bold bg-gray-50 text-gray-400 border border-gray-200 dark:bg-gray-800 dark:text-gray-600 dark:border-gray-700 rounded-lg">
                                                -
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-400 dark:text-gray-600">-</td>
                                    </tr>
                                <?php 
                                    else: 
                                        // Sinon on affiche chaque entrée trouvée pour ce mois
                                        foreach ($entries as $rec):
                                            $vStatut = $rec['display_statut'];
                                            $vMontant = $rec['display_montant'];
                                            $appliedAdvance = $rec['applied_advance'] ?? 0;
                                            // Récupérer l'ID original si dispo (pour suppression) - pour les items virtuels, pas d'ID
                                            $originalId = (strpos((string)$rec['id'], 'virt_') === 0) ? null : $rec['id'];
                                ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300 transition-colors">
                                            <?= htmlspecialchars(ucfirst($rec['mois'])) ?> <?= (int)$rec['annee'] ?>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300 transition-colors">
                                            <div class="flex flex-col">
                                                <?php 
                                                $pPaid = $rec['paid_principal'] ?? $vMontant;
                                                $aPaid = $rec['paid_amende'] ?? 0;
                                                ?>
                                                <span class="font-medium">
                                                    <?= number_format($pPaid, 0, ',', ' ') ?> 
                                                    <?php if ($aPaid > 0): ?>+ <?= number_format($aPaid, 0, ',', ' ') ?><?php endif; ?>
                                                    FCFA
                                                </span>
                                                <?php if ($appliedAdvance > 0): ?>
                                                    <span class="text-[10px] text-purple-600 dark:text-purple-400 font-bold leading-tight">
                                                        <?php 
                                                        $appP = $rec['applied_principal'] ?? 0;
                                                        $appA = $rec['applied_amende'] ?? 0;
                                                        if ($appA > 0): ?>
                                                            (+<?= number_format($appP, 0, ',', ' ') ?> + <?= number_format($appA, 0, ',', ' ') ?> amende via ant.)
                                                        <?php else: ?>
                                                            (+<?= number_format($appliedAdvance, 0, ',', ' ') ?> via anticipation)
                                                        <?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($rec['is_amende']) || ($rec['amende_due'] ?? 0) > 0): ?>
                                                    <span class="inline-flex items-center mt-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 transition-colors w-fit">
                                                        <?= ($rec['display_statut'] === 'AMENDE' || $rec['is_amende']) ? 'Amende' : '+ Amende incluse' ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 transition-colors">
                                            <?php
                                            $statusBadge = match($vStatut) {
                                                'PAYE', 'PAYE (AVANCE)', 'ANTICIPATION', 'VIRTUAL' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
                                                'PARTIEL', 'PARTIEL (AVANCE)', 'ANTICIPATION (PARTIEL)' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
                                                'EN_ATTENTE', 'RETARD' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300',
                                                'AMENDE' => 'bg-red-600 text-white', 
                                                'ANNULE' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400'
                                            };
                                            ?>
                                            <span class="inline-block px-2 py-1 text-[10px] font-bold <?= $statusBadge ?> rounded-lg transition-colors shadow-sm">
                                                <?= $vStatut === 'VIRTUAL' ? 'ANTICIPATION' : $vStatut ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400 transition-colors relative group">
                                            <span class="block"><?= (!empty($rec['date_paiement'])) ? date('d/m/Y', strtotime($rec['date_paiement'])) : '-' ?></span>
                                            
                                            <?php if (in_array($currentUser['role'], ['admin']) && $originalId): ?>
                                                <div class="absolute right-2 top-1/2 -translate-y-1/2 transition-opacity flex gap-1">
                                                    <button onclick="promptDeleteVersement(<?= $originalId ?>)" class="text-red-400 hover:text-red-600 p-1" title="Supprimer définitivement">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php 
                                        endforeach; // End foreach entries
                                    endif; // End if empty entries
                                endfor; // End for 1-12
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Message for unauthorized viewing -->
            <div class="bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 p-8 text-center transition-colors">
                <p class="text-gray-500 dark:text-gray-400 transition-colors">
                    Ces infos de versement ne sont visibles que par l'administration ou le membre concerné.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Retard Modal -->
<div id="retardModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 transition-opacity opacity-0 pointer-events-none">
    <div class="bg-white dark:bg-gray-900 rounded-2xl w-full max-w-md p-6 transform scale-95 transition-transform duration-300 shadow-2xl border border-gray-200 dark:border-gray-800">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Ajuster les Mois en Retard</h3>
        
        <form action="<?= BASE_URL ?>/membres/update_financial_config" method="POST">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
            <input type="hidden" name="id" value="<?= $membre['id'] ?>">

            <div class="space-y-4">
                <div class="bg-blue-50 dark:bg-blue-900/10 p-3 rounded-lg text-sm text-blue-800 dark:text-blue-200">
                    Définissez la durée et la date de fin. Cochez "Remise à zéro" pour remplacer le compteur actuel.
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre de mois</label>
                    <input type="number" name="mois_retard" min="1" placeholder="Ex: 5" required
                        value="<?= $globalSituation['mois_retard'] > 0 ? $globalSituation['mois_retard'] : '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-gray-900">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Date de fin</label>
                    <input type="date" name="date_finale" max="<?= date('Y-m-d') ?>" required
                        value="<?= $lastRetardDate ?? '' ?>"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-xl dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-gray-900">
                </div>

                <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/50 p-3 rounded-lg border border-gray-100 dark:border-gray-700">
                    <input type="checkbox" id="modal_reset_retards" name="reset_retards" value="1" class="h-4 w-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900">
                    <label for="modal_reset_retards" class="text-sm font-medium text-gray-700 dark:text-gray-300 select-none cursor-pointer">
                        Effacer d'abord les retards existants <br><span class="text-xs font-normal text-gray-500">(Force le compteur à la valeur ci-dessus)</span>
                    </label>
                </div>

                <div class="flex gap-3 mt-6 pt-2">
                    <button type="button" onclick="closeRetardModal()" class="flex-1 px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-200 dark:hover:bg-gray-700 transition font-medium">
                        Annuler
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded-xl hover:bg-black dark:hover:bg-gray-300 transition font-medium">
                        Appliquer
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function openRetardModal() {
    const modal = document.getElementById('retardModal');
    modal.classList.remove('hidden', 'opacity-0', 'pointer-events-none');
    modal.classList.add('flex', 'opacity-100', 'pointer-events-auto');
    setTimeout(() => {
        modal.querySelector('div').classList.remove('scale-95');
        modal.querySelector('div').classList.add('scale-100');
    }, 10);
}

function closeRetardModal() {
    const modal = document.getElementById('retardModal');
    modal.querySelector('div').classList.remove('scale-100');
    modal.querySelector('div').classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden', 'opacity-0', 'pointer-events-none');
        modal.classList.remove('flex', 'opacity-100', 'pointer-events-auto');
    }, 200);
}

function promptDeleteAvance(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette anticipation ? Cette action est irréversible.')) {
        fetch('<?= BASE_URL ?>/avances/delete', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + id + '&<?= CSRF_TOKEN_NAME ?>=<?= Security::generateCsrfToken() ?>'
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                location.reload();
            } else {
                throw new Error(data.message || 'Erreur serveur (' + response.status + ')');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Erreur: ' + error.message);
        });
    }
}

function promptDeleteVersement(id) {
    if (confirm('ATTENTION: Vous êtes sur le point de supprimer DÉFINITIVEMENT un versement ou un retard. Voulez-vous continuer ?')) {
        fetch('<?= BASE_URL ?>/versements/delete', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'id=' + id + '&<?= CSRF_TOKEN_NAME ?>=<?= Security::generateCsrfToken() ?>'
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                location.reload();
            } else {
                throw new Error(data.message || 'Erreur serveur (' + response.status + ')');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Erreur: ' + error.message);
        });
    }
}

function deleteAllRetards(membreId) {
    if (confirm('ATTENTION EXTRÊME : Vous allez supprimer TOUS les mois marqués comme "En Retard" pour ce membre.\n\nCette action est irréversible et remettra le compteur de retard à 0 mois.\n\nVoulez-vous vraiment continuer ?')) {
        fetch('<?= BASE_URL ?>/versements/delete-all-retards', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'membre_id=' + membreId + '&<?= CSRF_TOKEN_NAME ?>=<?= Security::generateCsrfToken() ?>'
        })
        .then(async response => {
            const data = await response.json();
            if (response.ok && data.success) {
                alert(data.message);
                location.reload();
            } else {
                throw new Error(data.message || 'Erreur serveur (' + response.status + ')');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Erreur: ' + error.message);
        });
    }
}
</script>
