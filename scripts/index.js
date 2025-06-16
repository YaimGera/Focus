document.addEventListener('DOMContentLoaded', function () {
    const mobileMenu = document.getElementById('mobile-menu');
    const mainNav = document.getElementById('main-nav');
    const menuOverlay = document.getElementById('menu-overlay');
    const body = document.body;

    function toggleMenu() {
        mainNav.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        body.classList.toggle('no-scroll');
    }

    mobileMenu.addEventListener('click', toggleMenu);
    menuOverlay.addEventListener('click', toggleMenu);

    const navLinks = document.querySelectorAll('#main-nav ul li a');
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (mainNav.classList.contains('active')) {
                toggleMenu();
            }
        });
    });
});