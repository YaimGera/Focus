<?php
session_start();
include 'includes/db.php'; // Incluir la conexión a la base de datos

// Redirige al usuario si no ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id']; // Obtiene el ID del usuario actual

// --- Obtener los cursos desde la base de datos ---
$courses = [];
try {
    // Consulta para obtener todos los cursos gratuitos y sus instructores
    $stmt = $pdo->prepare("
        SELECT
            c.course_id,
            c.title,
            c.description,
            c.cost,
            c.start_date,
            c.end_date,
            c.is_free,
            i.name AS instructor_name,
            i.bio AS instructor_bio -- Incluir biografía del instructor
        FROM courses c
        LEFT JOIN instructors i ON c.instructor_id = i.instructor_id
        WHERE c.is_free = 1
        ORDER BY c.title ASC
    ");
    $stmt->execute();
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En un entorno de producción, loggea el error en lugar de mostrarlo
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al cargar los cursos: ' . $e->getMessage()];
}

// --- Obtener los IDs de los cursos en los que el usuario actual ya está inscrito ---
$enrolledCourses = [];
try {
    $stmt = $pdo->prepare("SELECT course_id FROM user_courses WHERE user_id = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $enrolledCourses = $stmt->fetchAll(PDO::FETCH_COLUMN); // Devuelve un array simple de course_id
} catch (PDOException $e) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al verificar tus inscripciones: ' . $e->getMessage()];
}

// --- Manejar mensajes de notificación (éxito, error, info) desde otras páginas (ej. inscripcion.php) ---
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Limpia el mensaje después de mostrarlo para que no aparezca de nuevo
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FOCUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/cursos.css"> <link rel="shortcut icon" href="images/FOCUS_LOGO.png" type="image/x-icon">
</head>
<body>
    <header>
        <div class="logo">FOCUS</div>
        <div class="menu-toggle" id="mobile-menu">
            <i class="fas fa-bars"></i>
        </div>
        <nav id="main-nav">
            <ul>
                <li>
                    <a href="index.php" class="nav-link" aria-label="Inicio">
                        <i class="fas fa-home"></i> <span class="nav-text">Inicio</span>
                    </a>
                </li>
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
            </ul>
        </nav>
    </header>

    <div class="courses-wrapper">
        <h1>Cursos de Finanzas Disponibles</h1>

        <?php if ($message): ?>
            <div class="message-box <?= htmlspecialchars($message['type']) ?>">
                <?= htmlspecialchars($message['text']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($courses)): ?>
            <p class="no-courses-message">No hay cursos de finanzas gratuitos disponibles actualmente. ¡Vuelve pronto para nuevas oportunidades de aprendizaje!</p>
        <?php else: ?>
            <div class="courses-grid">
                <?php foreach ($courses as $course):
                    $isUserEnrolled = in_array($course['course_id'], $enrolledCourses);
                ?>
                    <div class="course-card">
                        <div>
                            <h2>
                                <?= htmlspecialchars($course['title']) ?>
                                <?php if($course['is_free']): ?>
                                    <span class="free-tag">Gratis</span>
                                <?php endif; ?>
                            </h2>
                            <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                            <p>
                                <i class="fas fa-chalkboard-teacher"></i>
                                <strong>Instructor(es):</strong> <?= htmlspecialchars($course['instructor_name'] ?: 'Sin instructor asignado') ?>
                            </p>
                            <p>
                                <i class="fas fa-calendar-alt"></i>
                                <strong>Fechas:</strong> <?= htmlspecialchars($course['start_date']) ?> - <?= htmlspecialchars($course['end_date']) ?>
                            </p>
                            <p class="course-cost <?= $course['is_free'] ? 'free' : '' ?>">
                                <?php if($course['is_free']): ?>
                                    <i class="fas fa-tag"></i> Gratis
                                <?php else: ?>
                                    <i class="fas fa-dollar-sign"></i> <?= number_format($course['cost'], 2, '.', ',') ?> MXN
                                <?php endif; ?>
                            </p>
                        </div>
                        <?php if ($isUserEnrolled): ?>
                            <a href="mi_curso.php?course_id=<?= $course['course_id'] ?>" class="action-button enrolled view-course">
                                <i class="fas fa-book-open"></i> Ver Curso
                            </a>
                        <?php else: ?>
                            <form action="inscripcion.php" method="POST">
                                <input type="hidden" name="course_id" value="<?= $course['course_id'] ?>">
                                <button type="submit" class="action-button">
                                    <i class="fas fa-graduation-cap"></i> Inscribirme
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <a href="perfil.php" class="back-link">Ir a mi Perfil</a>
    </div>

    <footer>
        <div class="container">
            &copy; <?= date('Y') ?> FOCUS - Todos los derechos reservados.
        </div>
    </footer>
    <script src="scripts/cursos.js"></script>
</body>
</html>