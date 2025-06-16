<?php
session_start();
include 'includes/db.php'; // Incluir la conexión a la base de datos

if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Debes iniciar sesión para inscribirte en un curso.'];
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
    $userId = $_SESSION['user_id'];
    $courseId = (int)$_POST['course_id'];

    try {
        // Verificar si el curso es gratuito
        $stmt = $pdo->prepare("SELECT is_free FROM courses WHERE course_id = :courseId");
        $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course || !$course['is_free']) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Este curso no está disponible o no es gratuito.'];
            header("Location: cursos.php");
            exit();
        }

        // Verificar si el usuario ya está inscrito
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_courses WHERE user_id = :userId AND course_id = :courseId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $isEnrolled = $stmt->fetchColumn();

        if ($isEnrolled > 0) {
            $_SESSION['message'] = ['type' => 'info', 'text' => 'Ya estás inscrito en este curso.'];
            header("Location: mi_curso.php?course_id=" . $courseId); // Redirige directamente al curso
            exit();
        }

        // Realizar la inscripción
        $stmt = $pdo->prepare("INSERT INTO user_courses (user_id, course_id) VALUES (:userId, :courseId)");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['message'] = ['type' => 'success', 'text' => '¡Te has inscrito en el curso con éxito!'];
        header("Location: mi_curso.php?course_id=" . $courseId); // Redirige a la página del curso
        exit();

    } catch (PDOException $e) {
        // En caso de error, establece un mensaje de error y redirige
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al intentar inscribirte: ' . $e->getMessage()];
        header("Location: cursos.php");
        exit();
    }
} else {
    // Si se accede directamente sin POST o sin course_id
    $_SESSION['message'] = ['type' => 'error', 'text' => 'Solicitud inválida.'];
    header("Location: cursos.php");
    exit();
}
?>