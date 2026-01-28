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
                            <h1 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-gray-700 to-gray-900 dark:from-white dark:via-gray-400 dark:to-white">
                                SVC-UJDS
                            </h1>
                        </div>
                        <div class="hidden md:ml-8 md:flex md:space-x-4">
                            <!-- Dashboard Link -->
                            <a href="<?= BASE_URL ?>/dashboard" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center">
                                <?= ($_SESSION['user_role'] === 'membre') ? 'Mon Espace' : 'Dashboard' ?>
                            </a>
                            
                            <!-- Admin/Comptable Links Only -->
                            <?php if (in_array($_SESSION['user_role'], ['admin', 'comptable'])): ?>
                                <a href="<?= BASE_URL ?>/membres" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center">
                                    Membres
                                </a>
                                <a href="<?= BASE_URL ?>/versements" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center">
                                    Versements
                                </a>
                                <a href="<?= BASE_URL ?>/declarations/admin" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center">
                                    Déclarations
                                </a>
                                <a href="<?= BASE_URL ?>/support/admin" class="hidden lg:flex border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 items-center">
                                    Support
                                </a>
                            <?php else: ?>
                                <a href="<?= BASE_URL ?>/declarations" class="border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 flex items-center">
                                    Mes Déclarations
                                </a>
                                <a href="<?= BASE_URL ?>/support" class="hidden lg:flex border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white px-3 py-2 rounded-xl text-sm font-semibold transition-all hover:bg-gray-50 dark:hover:bg-gray-800 items-center">
                                    Support
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <!-- Desktop: Full layout (lg and above) -->
                        <div class="hidden lg:flex lg:items-center">
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

                        <!-- Tablet: Dropdown menu (md to lg) -->
                        <div class="hidden md:flex lg:hidden items-center relative">
                            <button id="user-menu-button" class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                <div class="w-8 h-8 rounded-xl bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-700 dark:text-gray-300 font-bold text-sm">
                                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                                </div>
                                <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            
                            <!-- Dropdown Menu -->
                            <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-56 bg-white dark:bg-gray-900 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-800 py-2 z-50">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                                    <p class="text-sm font-bold text-gray-900 dark:text-white"><?= htmlspecialchars($_SESSION['username']) ?></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5"><?= htmlspecialchars($_SESSION['user_role']) ?></p>
                                </div>
                                <button id="theme-toggle-dropdown" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                                    Mode sombre
                                </button>
                                <a href="<?= BASE_URL ?>/auth/changePassword" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                    Mot de passe
                                </a>
                                <a href="<?= (in_array($_SESSION['user_role'], ['admin', 'comptable'])) ? BASE_URL . '/support/admin' : BASE_URL . '/support' ?>" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                    Support
                                </a>
                                <a href="<?= BASE_URL ?>/logout" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    Déconnexion
                                </a>
                            </div>
                        </div>

                        <!-- Mobile menu button -->
                        <div class="flex items-center md:hidden">
                            <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-xl text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                                <span class="sr-only">Ouvrir le menu</span>
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path id="mobile-menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile menu, show/hide based on menu state. -->
            <div id="mobile-menu" class="hidden md:hidden bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl border-b border-gray-200 dark:border-gray-800 overflow-hidden transition-all duration-300 ease-in-out max-h-0">
                <div class="px-4 pt-2 pb-6 space-y-2">
                    <a href="<?= BASE_URL ?>/dashboard" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                        Dashboard
                    </a>
                    <?php if (in_array($_SESSION['user_role'], ['admin', 'comptable'])): ?>
                        <a href="<?= BASE_URL ?>/membres" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                            Membres
                        </a>
                        <a href="<?= BASE_URL ?>/versements" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                            Versements
                        </a>
                        <a href="<?= BASE_URL ?>/declarations/admin" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                            Déclarations
                        </a>
                        <a href="<?= BASE_URL ?>/support/admin" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                            Support
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/declarations" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                            Mes Déclarations
                        </a>
                        <a href="<?= BASE_URL ?>/support" class="flex items-center px-4 py-3 text-base font-bold text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                            Support
                        </a>
                    <?php endif; ?>
                    
                    <div class="pt-4 mt-4 border-t border-gray-100 dark:border-gray-800">
                        <div class="flex items-center px-4 mb-4">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-800 dark:to-gray-700 flex items-center justify-center text-gray-900 dark:text-white font-black shadow-sm">
                                    <?= strtoupper(substr($_SESSION['username'], 0, 1)) ?>
                                </div>
                            </div>
                            <div class="ml-3">
                                <div class="text-base font-black text-gray-900 dark:text-white"><?= htmlspecialchars($_SESSION['username']) ?></div>
                                <div class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest"><?= htmlspecialchars($_SESSION['user_role']) ?></div>
                            </div>
                        </div>
                        <div class="space-y-1">
                            <button id="theme-toggle-mobile" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                                Mode Sombre / Clair
                            </button>
                            <a href="<?= BASE_URL ?>/auth/changePassword" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800/50 rounded-2xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                                Changer mot de passe
                            </a>
                            <a href="<?= BASE_URL ?>/logout" class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                Déconnexion
                            </a>
                        </div>
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

        // Tablet user dropdown toggle
        var userMenuBtn = document.getElementById('user-menu-button');
        var userDropdown = document.getElementById('user-dropdown');
        var themeToggleDropdown = document.getElementById('theme-toggle-dropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!userDropdown.contains(e.target) && !userMenuBtn.contains(e.target)) {
                    userDropdown.classList.add('hidden');
                }
            });

            // Theme toggle in dropdown
            if (themeToggleDropdown) {
                themeToggleDropdown.addEventListener('click', function() {
                    toggleTheme();
                    userDropdown.classList.add('hidden');
                });
            }
        }

        // Mobile menu toggle
        var mobileMenuBtn = document.getElementById('mobile-menu-button');
        var mobileMenu = document.getElementById('mobile-menu');
        var mobileMenuIcon = document.getElementById('mobile-menu-icon');

        mobileMenuBtn.addEventListener('click', function() {
            const isOpen = !mobileMenu.classList.contains('hidden');
            
            if (!isOpen) {
                // Opening
                mobileMenu.classList.remove('hidden');
                mobileMenu.style.maxHeight = '0px';
                setTimeout(() => {
                    mobileMenu.style.maxHeight = '500px';
                }, 10);
                mobileMenuIcon.setAttribute('d', 'M6 18L18 6M6 6l12 12');
            } else {
                // Closing
                mobileMenu.style.maxHeight = '0px';
                mobileMenuIcon.setAttribute('d', 'M4 6h16M4 12h16M4 18h16');
                setTimeout(() => {
                    mobileMenu.classList.add('hidden');
                }, 300);
            }
        });
    </script>

    <!-- Custom JS -->
    <script src="<?= BASE_URL ?>/js/app.js"></script>
</body>
</html>
