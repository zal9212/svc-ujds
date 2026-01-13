<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - SVC-UJDS</title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script>
        // Tailwind Config for Dark Mode (REQUIRED for class-based toggling)
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                },
            },
        }

        // Initialize theme before page load
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 transition-colors duration-300">
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Logo/Title -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-semibold text-gray-900 dark:text-white mb-2 transition-colors">SVC-UJDS</h1>
                <p class="text-gray-600 dark:text-gray-400">Système de Gestion des Versements</p>
            </div>

            <!-- Dark Mode Toggle (Standalone in Login) -->
            <div class="flex justify-end mb-4">
                <button id="theme-toggle" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none transition-colors" title="Changer le thème">
                    <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                    <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                </button>
            </div>

            <!-- Login Card -->
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-800 p-8 transition-colors">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6 text-center transition-colors">Connexion</h2>

                <?php if (isset($_SESSION['flash']['error'])): ?>
                    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 px-4 py-3 rounded-2xl mb-6 transition-colors" role="alert">
                        <p class="text-sm"><?= htmlspecialchars($_SESSION['flash']['error']) ?></p>
                    </div>
                    <?php unset($_SESSION['flash']['error']); ?>
                <?php endif; ?>

                <form method="POST" action="<?= BASE_URL ?>/login">
                    <input type="hidden" name="<?= CSRF_TOKEN_NAME ?>" value="<?= Security::generateCsrfToken() ?>">
                    
                    <div class="mb-6">
                        <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                            Identifiant (Tél / Username)
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                            placeholder="Numéro de tél ou nom d'utilisateur"
                        >
                    </div>

                    <div class="mb-6">
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 transition-colors">
                            Mot de passe
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            class="w-full px-4 py-3 border border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-gray-900 focus:border-transparent transition"
                            placeholder="Entrez votre mot de passe"
                        >
                    </div>

                    <button 
                        type="submit"
                        class="w-full bg-gray-900 dark:bg-white dark:text-gray-900 text-white py-3 px-4 rounded-2xl hover:bg-black dark:hover:bg-gray-200 transition font-medium"
                    >
                        Se connecter
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-sm text-gray-600 dark:text-gray-400 transition-colors">
                        Compte par défaut: <strong class="dark:text-white">admin</strong> / <strong class="dark:text-white">password123</strong>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <p class="text-center text-gray-500 text-sm mt-8">
                &copy; <?= date('Y') ?> SVC-UJDS. Tous droits réservés.
            </p>
        </div>
    </div>

    <script>
        // Theme toggle logic (Consistent with Layout)
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        var themeToggleBtn = document.getElementById('theme-toggle');

        function updateIcons() {
            if (document.documentElement.classList.contains('dark')) {
                themeToggleLightIcon.classList.remove('hidden');
                themeToggleDarkIcon.classList.add('hidden');
            } else {
                themeToggleLightIcon.classList.add('hidden');
                themeToggleDarkIcon.classList.remove('hidden');
            }
        }

        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            updateIcons();
        }

        // Initialize icons based on current state (set by head script)
        updateIcons();

        // Event listener
        if (themeToggleBtn) {
            themeToggleBtn.addEventListener('click', toggleTheme);
        }
    </script>
</body>
</html>
