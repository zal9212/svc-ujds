// Custom JavaScript for SVC-UJDS

// Confirmation dialog for delete actions
function confirmDelete(message = 'Êtes-vous sûr de vouloir supprimer cet élément ?') {
    return confirm(message);
}

// AJAX helper function
async function ajaxRequest(url, method = 'POST', data = {}) {
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: method !== 'GET' ? JSON.stringify(data) : null
        });

        return await response.json();
    } catch (error) {
        console.error('AJAX Error:', error);
        return { success: false, message: 'Erreur de connexion' };
    }
}

// Format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount) + ' FCFA';
}

// Main logic on DOM load
document.addEventListener('DOMContentLoaded', function () {
    console.log('DOM Content Loaded - Initializing scripts');

    // 1. Auto-hide flash messages
    const flashMessages = document.querySelectorAll('[role="alert"]');
    flashMessages.forEach(function (message) {
        setTimeout(function () {
            message.style.opacity = '0';
            setTimeout(function () {
                message.remove();
            }, 300);
        }, 5000);
    });

    // 2. Theme Toggle Logic (Handled inline in main.php)
    // Code removed to prevent conflict

    // 3. User Dropdowns (Tablet & Desktop)
    function setupDropdown(btnId, menuId) {
        const btn = document.getElementById(btnId);
        const menu = document.getElementById(menuId);

        if (btn && menu) {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                menu.classList.toggle('hidden');
            });

            document.addEventListener('click', function (e) {
                if (!menu.contains(e.target) && !btn.contains(e.target)) {
                    menu.classList.add('hidden');
                }
            });
        }
    }

    // Initialize tablet dropdown
    setupDropdown('user-menu-button', 'user-dropdown');

    // Initialize desktop dropdown
    setupDropdown('desktop-user-menu-button', 'desktop-user-dropdown');

    // 4. Mobile Menu Toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuIcon = document.getElementById('mobile-menu-icon');

    if (mobileMenuBtn && mobileMenu && mobileMenuIcon) {
        console.log('Mobile menu elements found, attaching listener');
        mobileMenuBtn.addEventListener('click', function () {
            const isOpen = !mobileMenu.classList.contains('hidden');
            console.log('Mobile menu clicked. Is open?', isOpen);

            if (!isOpen) {
                // Opening
                mobileMenu.classList.remove('hidden');
                mobileMenu.style.maxHeight = '0px';
                setTimeout(() => {
                    mobileMenu.style.maxHeight = '800px';
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
    } else {
        if (window.innerWidth < 768) {
            console.warn('Mobile menu elements not found on small screen');
        }
    }
});

console.log('SVC-UJDS Application Script Loaded');
