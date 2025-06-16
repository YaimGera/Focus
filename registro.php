<?php
session_start();
include 'includes/db.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    /*Tipo encriptacion de la contraseña, aqui lo que se hace es que HASHEA la contraseña conviertiendola
    en varios caracteres casi imposible revertir el proceso, y esto lo hacemos para la seguridad y sobre
    todo la privacidad de los usuarios que usan de nuestro sitio web*/
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];

    // Basic validation (you might want more robust validation here)
    if (empty($username) || empty($password) || empty($email)) {
        $error = "Todos los campos son obligatorios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Formato de correo electrónico inválido.";
    } else {
        try {
            // Check if username or email already exists
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
            $checkStmt->execute(['username' => $username, 'email' => $email]);
            if ($checkStmt->fetchColumn() > 0) {
                $error = "El nombre de usuario o el correo electrónico ya están registrados.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (:username, :password, :email)");
                if ($stmt->execute(['username' => $username, 'password' => $password, 'email' => $email])) {
                    $_SESSION['user_id'] = $pdo->lastInsertId();
                    header("Location: perfil.php");
                    exit();
                } else {
                    $error = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
                }
            }
        } catch (PDOException $e) {
            $error = "Error de base de datos: " . $e->getMessage();
            // In a production environment, you might log the actual error and show a generic message
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FOCUS</title>
    <link rel="shortcut icon" href="/images/FOCUS_LOGO" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/registro.css">
    <link rel="shortcut icon" href="images/FOCUS_LOGO.png" type="image/x-icon">
   
</head>
<body>
    <div class="register-container">
        <h1>Registrarse</h1>
        <form method="POST" action="" class="register-form">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Nombre de usuario" required>
            </div>
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit">Registrar</button>
        </form>
        <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
        <div class="footer-links">
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia Sesión</a></p>
        </div>
    </div>
</body>
</html>