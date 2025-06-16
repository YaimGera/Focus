<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// --- Handle New Testimonial Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';

    if (!$content) {
        $error = "Por favor escribe tu testimonio antes de publicarlo.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO testimonials (user_id, content, created_at) VALUES (:user_id, :content, NOW())");
            $stmt->execute([
                ':user_id' => $user_id,
                ':content' => $content,
            ]);
            $success = "¡Testimonio publicado con éxito! Gracias por compartir tu experiencia.";
            // Clear the textarea value for the form after successful submission
            $content = ''; 

        } catch (PDOException $e) {
            // Log the error for debugging (e.g., error_log($e->getMessage());)
            $error = "Hubo un error al publicar tu testimonio. Por favor, inténtalo de nuevo más tarde.";
        }
    }
}

// --- Fetch All Testimonials for Display ---
// We'll join with the users table to get the username
// Order by created_at DESC to show latest first
$testimonialsStmt = $pdo->query("
    SELECT t.content, t.created_at, u.username
    FROM testimonials t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$all_testimonials = $testimonialsStmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>FOCUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/testimonio.css">
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

    <div class="main-content-wrapper">
        <div class="testimonials-display">
            <h1 class="section-title">Lo que dicen los usuarios</h1>

            <?php if (empty($all_testimonials)): ?>
                <p class="no-testimonials">Aún no hay testimonios. ¡Sé el primero en compartir tu experiencia!</p>
            <?php else: ?>
                <div class="testimonials-grid">
                    <?php foreach ($all_testimonials as $testimonial): ?>
                        <div class="testimonial-card">
                            <p class="testimonial-content"><?= nl2br(htmlspecialchars($testimonial['content'])) ?></p>
                            <div class="testimonial-meta">
                                <span><i class="fas fa-user"></i> <strong><?= htmlspecialchars($testimonial['username']) ?></strong></span>
                                <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($testimonial['created_at'])) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <hr style="width: 80%; border: none; border-top: 1px dashed var(--border-light); margin: 60px auto;">

        <div class="testimonial-form-section">
            <h1 class="section-title">Comparte tu Propia Experiencia</h1>
            <?php if ($error): ?>
                <p class="message error-message"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error) ?></p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="message success-message"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></p>
            <?php endif; ?>
            <form method="POST" action="" class="testimonial-form">
                <div class="form-group">
                    <label for="content">Tu testimonio:</label>
                    <textarea id="content" name="content" rows="5" required></textarea>
                </div>
                <button type="submit">Publicar Testimonio</button>
            </form>
            <a href="perfil.php" class="back-link"></i> Ir a mi Perfil</a>
        </div>
    </div>

    <footer>
        <div class="container">
            &copy; <?= date('Y') ?> FOCUS - Todos los derechos reservados.
        </div>
    </footer>

    </body>
</html>