<?php
/**
 * Vue : Changement de mot de passe
 */
?>

<div class="max-w-md mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white transition-colors">Mot de passe</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 transition-colors">Modifier vos informations de connexion</p>
    </div>

    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 p-8 shadow-sm transition-colors">
        <form action="<?= BASE_URL ?>/auth/updatePasswordPost" method="POST" class="space-y-6">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

            <div>
                <label for="current_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 transition-colors">Mot de passe actuel</label>
                <input type="password" name="current_password" id="current_password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white focus:border-transparent outline-none transition">
            </div>

            <div>
                <label for="new_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 transition-colors">Nouveau mot de passe</label>
                <input type="password" name="new_password" id="new_password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white focus:border-transparent outline-none transition">
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-1 transition-colors">Au moins 6 caractères.</p>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1 transition-colors">Confirmer le nouveau mot de passe</label>
                <input type="password" name="confirm_password" id="confirm_password" required
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-2 focus:ring-gray-900 dark:focus:ring-white focus:border-transparent outline-none transition">
            </div>

            <div class="flex items-center justify-between pt-4">
                <a href="<?= BASE_URL ?>/dashboard" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white font-medium transition-colors">
                    Annuler
                </a>
                <button type="submit" 
                    class="bg-gray-900 dark:bg-white dark:text-gray-900 text-white px-8 py-3 rounded-xl hover:bg-black dark:hover:bg-gray-200 transition font-medium">
                    Mettre à jour
                </button>
            </div>
        </form>
    </div>
</div>
