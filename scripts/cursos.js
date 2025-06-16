document.addEventListener('DOMContentLoaded', function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const mainNav = document.getElementById('main-nav');
    if (mobileMenu && mainNav) {
        mobileMenu.addEventListener('click', function () {
            mainNav.classList.toggle('active');
        });
        const navLinks = document.querySelectorAll('#main-nav ul li a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (mainNav.classList.contains('active')) {
                    mainNav.classList.remove('active');
                }
            });
        });
    }
});