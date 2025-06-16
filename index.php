<?php
session_start();
include 'includes/db.php';

// Check if user logged in
$user_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="es"> <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCUS</title>
    <link rel="shortcut icon" href="./images/FOCUS_LOGO" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/index.css">
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
                <?php if ($user_logged_in): ?>
                    <li>
                        <a href="perfil.php" class="nav-link" aria-label="Perfil">
                            <i class="fas fa-user-circle"></i> <span class="nav-text">Perfil</span>
                        </a>
                    </li>
                    <li>
                        <a href="cerrarSesion.php" class="nav-link" aria-label="Cerrar Sesión">
                            <i class="fas fa-sign-out-alt"></i> <span class="nav-text">Cerrar Sesión</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li>
                        <a href="login.php" class="nav-link" aria-label="Iniciar Sesión">
                            <i class="fas fa-sign-in-alt"></i> <span class="nav-text">Iniciar Sesión</span>
                        </a>
                    </li>
                    <li>
                        <a href="registro.php" class="nav-link" aria-label="Registrarse">
                            <i class="fas fa-user-plus"></i> <span class="nav-text">Registrarse</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero-section">
            <div class="container">
                <h1>Tu patrimonio no crece solo, <span class="gradient-text">Actúa, invierte, prospera...</span></h1>
                <p>Aprende a invertir en la bolsa de valores con estrategias probadas por expertos y alcanza la libertad financiera que siempre has deseado.</p>
                <a href="cursos.php" class="button">Explorar cursos</a>
                <br><br>
            </div>
        </section>
        <section class="cta-section section-padding">
            <div class="container">
                <h2>¡Comienza hoy tu camino hacia la libertad financiera!</h2>
                <p>No esperes más para tomar el control de tu futuro. Nuestros cursos están diseñados para ayudarte a aprender sobre como invertir en la bolsa de valores.</p>
            </div>
        </section>
        <section class="why-choose-us section-padding">
            <div class="container">
                <h2>¿Por qué elegir <span class="gradient-text">FOCUS</span>?</h2>
                <div class="features-grid">
                    <div class="feature-item card-hover">
                        <img src="images/finanzas.png" alt="FOCUS_IMAGEN">
                        <h3>Estrategias probadas</h3>
                        <p>Accede a métodos de inversión que han demostrado resultados en el mercado de valores.</p>
                    </div>
                    <div class="feature-item card-hover">
                        <img src="images/relacion.png" alt="FOCUS_IMAGEN">
                        <h3>Comunidad de apoyo</h3>
                        <p>Únete a una red de inversores y expertos dispuestos a compartir conocimientos y experiencias.</p>
                    </div>
                    <div class="feature-item card-hover">
                        <img src="images/pensar.png" alt="FOCUS_IMAGEN">
                        <h3>Aprendizaje flexible</h3>
                        <p>Nuestros cursos se adaptan a tu ritmo, permitiéndote aprender en cualquier momento y lugar.</p>
                    </div>
                    <div class="feature-item card-hover">
                        <img src="images/conocimiento.png" alt="FOCUS_IMAGEN">
                        <h3>Personal con conocimiento</h3>
                        <p>Recibe orientación de personal que conoce sobre el tema en el sector financiero.</p>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container">
            &copy; <?= date('Y') ?> FOCUS - Todos los derechos reservados.
        </div>
    </footer>

    </body>
</html>