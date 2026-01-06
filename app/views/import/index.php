<!-- Import/Export Interface -->
<div class="mb-8">
    <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Import / Export</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Importer et exporter les données</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Import Excel -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 transition-colors">
        <div class="flex items-center mb-6">
            <div class="bg-green-100 dark:bg-green-900/20 rounded-full p-3 mr-4 transition-colors">
                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors">Import Excel</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors">Importer des données depuis Excel</p>
            </div>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/import/upload" enctype="multipart/form-data" id="importForm">
            <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                    Fichier Excel <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 rounded-2xl p-8 text-center hover:border-gray-400 dark:hover:border-gray-500 transition-colors">
                    <input 
                        type="file" 
                        name="excel_file" 
                        id="excel_file"
                        accept=".xlsx,.xls"
                        required
                        class="hidden"
                        onchange="updateFileName(this)"
                    >
                    <label for="excel_file" class="cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400 transition-colors">
                            <span class="font-medium text-gray-900 dark:text-white">Cliquez pour sélectionner</span> ou glissez-déposez
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 transition-colors">Excel (.xlsx, .xls) jusqu'à 5MB</p>
                        <p id="file-name" class="text-sm text-green-600 dark:text-green-400 mt-2 font-medium hidden transition-colors"></p>
                    </label>
                </div>
            </div>

            <!-- Format attendu -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-2xl p-4 mb-6 transition-colors">
                <p class="text-sm font-medium text-blue-900 dark:text-blue-300 mb-2 transition-colors">📋 Format attendu:</p>
                <ul class="text-xs text-blue-800 dark:text-blue-400 space-y-1 transition-colors">
                    <li>• Numéro, Code membre, Téléphone, Titre, Désignation</li>
                    <li>• Missidé, Montant mensuel</li>
                    <li>• Colonnes mensuelles (février → décembre)</li>
                    <li>• Nombre de mois en retard, Amende, Avance</li>
                    <li>• Montant versé, Montant dû, Statut membre</li>
                </ul>
            </div>

            <button 
                type="submit"
                class="w-full bg-green-600 text-white px-6 py-3 rounded-2xl hover:bg-green-700 transition font-medium"
            >
                📥 Importer les données
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="<?= BASE_URL ?>/import/template" class="text-sm text-green-600 dark:text-green-400 hover:text-green-900 dark:hover:text-green-200 font-medium transition-colors">
                Télécharger le modèle Excel
            </a>
        </div>
    </div>

    <!-- Export Excel -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 transition-colors">
        <div class="flex items-center mb-6">
            <div class="bg-blue-100 dark:bg-blue-900/20 rounded-full p-3 mr-4 transition-colors">
                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors">Export Excel</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors">Exporter les données vers Excel</p>
            </div>
        </div>

        <div class="space-y-4 mb-6">
            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 transition-colors">
                <h3 class="font-medium text-gray-900 dark:text-white mb-2 transition-colors">📊 Export complet</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 transition-colors">
                    Tous les membres avec calculs, versements et avances
                </p>
                <a 
                    href="<?= BASE_URL ?>/export/excel?type=complet"
                    class="block w-full bg-blue-600 dark:bg-blue-500 text-white px-6 py-3 rounded-2xl hover:bg-blue-700 dark:hover:bg-blue-600 transition font-medium text-center shadow-sm"
                >
                    📥 Exporter tout
                </a>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 transition-colors">
                <h3 class="font-medium text-gray-900 dark:text-white mb-2 transition-colors">⚠️ Membres en retard</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 transition-colors">
                    Uniquement les membres avec des retards
                </p>
                <a 
                    href="<?= BASE_URL ?>/export/excel?type=retards"
                    class="block w-full bg-orange-600 dark:bg-orange-500 text-white px-6 py-3 rounded-2xl hover:bg-orange-700 dark:hover:bg-orange-600 transition font-medium text-center shadow-sm"
                >
                    📥 Exporter retards
                </a>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-4 transition-colors">
                <h3 class="font-medium text-gray-900 dark:text-white mb-2 transition-colors">✅ Membres actifs</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 transition-colors">
                    Uniquement les membres avec statut ACTIF
                </p>
                <a 
                    href="<?= BASE_URL ?>/export/excel?type=actifs"
                    class="block w-full bg-green-600 dark:bg-green-500 text-white px-6 py-3 rounded-2xl hover:bg-green-700 dark:hover:bg-green-600 transition font-medium text-center shadow-sm"
                >
                    📥 Exporter actifs
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Export PDF -->
<div class="mt-6">
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 transition-colors">
        <div class="flex items-center mb-6">
            <div class="bg-red-100 dark:bg-red-900/20 rounded-full p-3 mr-4 transition-colors">
                <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white transition-colors">Export PDF</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors">Générer des rapports PDF</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a 
                href="<?= BASE_URL ?>/export/pdf?type=rapport-general"
                class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-center group"
            >
                <svg class="w-12 h-12 text-gray-600 dark:text-gray-400 mx-auto mb-3 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="font-medium text-gray-900 dark:text-white mb-1 transition-colors">Rapport Général</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors">Vue d'ensemble complète</p>
            </a>

            <a 
                href="<?= BASE_URL ?>/export/pdf?type=liste-membres"
                class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-center group"
            >
                <svg class="w-12 h-12 text-gray-600 dark:text-gray-400 mx-auto mb-3 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="font-medium text-gray-900 dark:text-white mb-1 transition-colors">Liste Membres</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors">Tous les membres</p>
            </a>

            <a 
                href="<?= BASE_URL ?>/export/pdf?type=etat-paiements"
                class="bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-800 rounded-2xl p-6 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-center group"
            >
                <svg class="w-12 h-12 text-gray-600 dark:text-gray-400 mx-auto mb-3 group-hover:text-red-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="font-medium text-gray-900 dark:text-white mb-1 transition-colors">État Paiements</h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 transition-colors">Versements et retards</p>
            </a>
        </div>
    </div>
</div>

<script>
function updateFileName(input) {
    const fileName = input.files[0]?.name;
    const fileNameDisplay = document.getElementById('file-name');
    if (fileName) {
        fileNameDisplay.textContent = '✓ ' + fileName;
        fileNameDisplay.classList.remove('hidden');
    }
}
</script>
