<?php
/**
 * Vue Formulaire de Déclaration (Membre)
 */
?>

<div class="max-w-3xl mx-auto">
    <div class="mb-8">
        <a href="<?= BASE_URL ?>/declarations" class="text-sm font-medium text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition flex items-center gap-2">
            ← Retour aux déclarations
        </a>
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white mt-4">Déclarer un Paiement</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Remplissez les détails de votre paiement pour validation par l'administrateur.</p>
    </div>

    <form action="<?= BASE_URL ?>/declarations/store" method="POST" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">

        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Montant -->
                <div class="md:col-span-1">
                    <label for="montant" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">Montant Versé (FCFA)</label>
                    <input type="number" name="montant" id="montant" required step="0.01" min="0" placeholder="Ex: 5000"
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition">
                </div>

                <!-- Type de paiement -->
                <div class="md:col-span-1">
                    <label for="type_paiement" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">Type de Paiement</label>
                    <select name="type_paiement" id="type_paiement" required
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition cursor-pointer">
                        <?php if (!$isCurrentMonthPaid): ?>
                            <option value="mois_en_cours">Paiement du mois en cours</option>
                        <?php endif; ?>
                        <option value="dette_anterieure">Règlement dette(s) antérieure(s)</option>
                        <option value="anticipation">Anticipation (mois à venir)</option>
                    </select>
                </div>

                <!-- Preuve de paiement -->
                <div class="md:col-span-2">
                    <label for="preuve" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">Preuve de Paiement (Image/Capture)</label>
                    <div class="relative group">
                        <input type="file" name="preuve" id="preuve" accept="image/*" required
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                        <div class="w-full px-4 py-8 bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl flex flex-col items-center justify-center text-gray-500 group-hover:bg-gray-100 dark:group-hover:bg-gray-700 transition">
                            <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-sm font-medium" id="file-name">Cliquez ou glissez une image ici</span>
                            <span class="text-xs mt-1">Format JPG, PNG (Max 5Mo)</span>
                        </div>
                    </div>
                </div>

                <!-- Message explicatif -->
                <div class="md:col-span-2">
                    <label for="message" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wide">Message (Optionnel)</label>
                    <textarea name="message" id="message" rows="4" placeholder="Précisez les mois concernés ou toute autre information utile..."
                        class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-800 border-none rounded-2xl text-gray-900 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white transition"></textarea>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full bg-gray-900 dark:bg-white text-white dark:text-gray-900 py-4 rounded-2xl text-lg font-bold shadow-xl hover:shadow-2xl transition transform hover:-translate-y-1">
                    Envoyer ma Déclaration
                </button>
                <p class="text-center text-xs text-gray-500 mt-4 italic">
                    Note : Votre déclaration sera vérifiée par un administrateur avant d'être appliquée à votre solde.
                </p>
            </div>
        </div>
    </form>
</div>

<script>
    // Preview file name on selection
    document.getElementById('preuve').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : "Cliquez ou glissez une image ici";
        document.getElementById('file-name').textContent = fileName;
        document.getElementById('file-name').classList.add('text-gray-900', 'dark:text-white');
    });
</script>
