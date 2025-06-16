document.addEventListener('DOMContentLoaded', function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const mainNav = document.getElementById('main-nav');
    // If you implement the overlay, also get it here: const menuOverlay = document.getElementById('menu-overlay');
    // And also the body: const body = document.body;

    if (mobileMenu && mainNav) { // Check if elements exist
        mobileMenu.addEventListener('click', function () {
            mainNav.classList.toggle('active');
            // if (menuOverlay) menuOverlay.classList.toggle('active');
            // if (body) body.classList.toggle('no-scroll');
        });

        // Optional: Close menu when a navigation link is clicked
        const navLinks = document.querySelectorAll('#main-nav ul li a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (mainNav.classList.contains('active')) {
                    mainNav.classList.remove('active');
                    // if (menuOverlay) menuOverlay.classList.remove('active');
                    // if (body) body.classList.remove('no-scroll');
                }
            });
        });
    }
});