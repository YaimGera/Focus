<?php
session_start();
include 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/perfil.css">
    <link rel="shortcut icon" href="images/FOCUS_LOGO.png" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">FOCUS</div>
        <nav id="main-nav">
            <ul>
                <li>
                    <a href="index.php" class="nav-link" aria-label="Inicio">
                        <i class="fas fa-home"></i> <span class="nav-text">Inicio</span>
                    </a>
                </li>
                <li>
                    <a href="perfil.php" class="nav-link active-link" aria-label="Perfil">
                        <i class="fas fa-user-circle"></i> <span class="nav-text">Perfil</span>
                    </a>
                </li>
                <li>
                    <a href="cerrarSesion.php" class="nav-link" aria-label="Cerrar Sesión">
                        <i class="fas fa-sign-out-alt"></i> <span class="nav-text">Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>
    </header>

    <div class="profile-wrapper">
        <div class="profile-container">
            <h1>Bienvenid@, <span><?php echo htmlspecialchars($user['username']); ?></span>!</h1>
            <p class="profile-info"><i class="fas fa-envelope"></i> Correo: <?php echo htmlspecialchars($user['email']); ?></p>

            <div class="profile-actions">
                <a href="testimonios.php">
                    <i class="fas fa-comment-dots"></i>
                    Crear Testimonio
                </a>
                <a href="cursos.php">
                    <i class="fas fa-book-open"></i>
                    Ver Cursos
                </a>
                <a href="index.php">
                    <i class="fas fa-home"></i>
                    Página Principal
                </a>
                <a href="cerrarSesion.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Cerrar Sesión
                </a>
                </div>
        </div>
    </div>

    <footer>
        <div class="container">
            &copy; <?= date('Y') ?> FOCUS - Todos los derechos reservados.
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const mobileMenu = document.getElementById('mobile-menu'); // This element doesn't exist in your HTML, consider adding it if you want a mobile hamburger menu
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
    </script>
</body>
</html>