<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Système de Gestion des Versements' ?></title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/css/app.css">
    
    <script>
        // Tailwind Config for Dark Mode
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

        // Initialize theme before page load to avoid flash
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
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- Navigation -->
        <nav class="bg-white dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-800 transition-colors sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">SVC-UJDS</h1>
                        </div>
                        <div class="hidden md:ml-6 md:flex md:space-x-8">
                            <!-- Dashboard Link -->
                            <a href="<?= BASE_URL ?>/dashboard" class="border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                                <?= ($_SESSION['user_role'] === 'membre') ? 'Mon Espace' : 'Dashboard' ?>
                            </a>
                            
                            <!-- Admin/Comptable Links Only -->
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'comptable'])): ?>
                                <a href="<?= BASE_URL ?>/membres" class="border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                                    Membres
                                </a>
                                <a href="<?= BASE_URL ?>/versements" class="border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-200 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition">
                                    Versements
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <div class="hidden md:flex md:items-center">
                            <!-- Dark Mode Toggle -->
                            <button id="theme-toggle" class="p-2 mr-4 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 focus:outline-none transition-colors" title="Changer le thème">
                                <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                                <svg id="theme-toggle-light-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                            </button>

                            <span class="text-sm text-gray-700 dark:text-gray-300 mr-4">
                                <?= htmlspecialchars($_SESSION['username']) ?> 
                            </span>
                            <div class="flex items-center space-x-4">
                                <a href="<?= BASE_URL ?>/auth/changePassword" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition font-medium">
                                    Mot de passe
                                </a>
                                <a href="<?= BASE_URL ?>/logout" class="text-sm text-red-600 hover:text-red-800 transition font-medium">
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                        
                        <!-- Mobile menu button -->
                        <div class="flex items-center md:hidden">
                            <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 focus:outline-none transition">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path id="mobile-menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 pb-4 px-4 transition-colors">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="<?= BASE_URL ?>/dashboard" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 transition">
                        Dashboard
                    </a>
                    <?php if (in_array($_SESSION['user_role'], ['admin', 'comptable'])): ?>
                        <a href="<?= BASE_URL ?>/membres" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 transition">
                            Membres
                        </a>
                        <a href="<?= BASE_URL ?>/versements" class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-300 transition">
                            Versements
                        </a>
                    <?php endif; ?>
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200 dark:border-gray-800">
                    <div class="flex items-center px-4">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 font-bold">
                                <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                            </div>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800 dark:text-white"><?= htmlspecialchars($_SESSION['username']) ?></div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400"><?= htmlspecialchars($_SESSION['user_role']) ?></div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <button id="theme-toggle-mobile" class="w-full text-left block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            Changer le thème
                        </button>
                        <a href="<?= BASE_URL ?>/auth/changePassword" class="block px-4 py-2 text-base font-medium text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            Changer mot de passe
                        </a>
                        <a href="<?= BASE_URL ?>/logout" class="block px-4 py-2 text-base font-medium text-red-600 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                            Déconnexion
                        </a>
                    </div>
                </div>
            </div>
        </nav>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <?php if (isset($_SESSION['flash']['success'])): ?>
                <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-2xl" role="alert">
                    <p><?= htmlspecialchars($_SESSION['flash']['success']) ?></p>
                </div>
                <?php unset($_SESSION['flash']['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['flash']['error'])): ?>
                <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-2xl" role="alert">
                    <p><?= htmlspecialchars($_SESSION['flash']['error']) ?></p>
                </div>
                <?php unset($_SESSION['flash']['error']); ?>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?= $content ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-800 mt-12 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-gray-500 dark:text-gray-400 text-sm">
                &copy; <?= date('Y') ?> SVC-UJDS. Tous droits réservés.
            </p>
        </div>
    </footer>

    <script>
        // Theme toggle logic
        var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        var themeToggleBtn = document.getElementById('theme-toggle');
        var themeToggleBtnMobile = document.getElementById('theme-toggle-mobile');

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

        updateIcons();
        themeToggleBtn.addEventListener('click', toggleTheme);
        if (themeToggleBtnMobile) themeToggleBtnMobile.addEventListener('click', toggleTheme);

        // Mobile menu toggle
        var mobileMenuBtn = document.getElementById('mobile-menu-button');
        var mobileMenu = document.getElementById('mobile-menu');
        var mobileMenuIcon = document.getElementById('mobile-menu-icon');

        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
            // Toggle icon between burger and X
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenuIcon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
            } else {
                mobileMenuIcon.setAttribute('d', 'M6 18L18 6M6 6l12 12');
            }
        });
    </script>

    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>
