<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$course = null;
$instructor = null;
$message = null;

if (isset($_GET['course_id'])) {
    $courseId = (int)$_GET['course_id'];

    try {
        // Verificar si el usuario está inscrito en este curso
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_courses WHERE user_id = :userId AND course_id = :courseId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $isEnrolled = $stmt->fetchColumn();

        if ($isEnrolled == 0) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'No estás inscrito en este curso o el curso no existe.'];
            header("Location: cursos.php");
            exit();
        }

        // Obtener detalles del curso y del instructor
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
                i.bio AS instructor_bio,
                i.photo_url AS instructor_photo,
                i.number AS instructor_phone_number
            FROM courses c
            LEFT JOIN instructors i ON c.instructor_id = i.instructor_id
            WHERE c.course_id = :courseId
        ");
        $stmt->bindParam(':courseId', $courseId, PDO::PARAM_INT);
        $stmt->execute();
        $course = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$course) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'El curso solicitado no fue encontrado.'];
            header("Location: cursos.php");
            exit();
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al cargar los detalles del curso: ' . $e->getMessage()];
        header("Location: cursos.php");
        exit();
    }
} else {
    $_SESSION['message'] = ['type' => 'error', 'text' => 'ID de curso no especificado.'];
    header("Location: cursos.php");
    exit();
}

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($course['title']) ?> | FOCUS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/cursos.css"><link rel="stylesheet" href="css/mi_curso.css"> <link rel="shortcut icon" href="images/FOCUS_LOGO.png" type="image/x-icon">
    <link rel="stylesheet" href="css/mi_curso2.css">
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

    <div class="course-detail-wrapper">
        <?php if ($message): ?>
            <div class="message-box <?= htmlspecialchars($message['type']) ?>">
                <?= htmlspecialchars($message['text']) ?>
            </div>
        <?php endif; ?>

        <?php if ($course): ?>
            <div class="course-detail-card">
                <h1><?= htmlspecialchars($course['title']) ?></h1>
                <?php if ($course['is_free']): ?>
                    <span class="free-tag-large">Curso Gratuito</span>
                <?php endif; ?>

                <div class="course-meta">
                    <p><i class="fas fa-calendar-alt"></i> <strong>Fechas:</strong> <?= htmlspecialchars($course['start_date']) ?> - <?= htmlspecialchars($course['end_date']) ?></p>
                    <p><i class="fas fa-dollar-sign"></i> <strong>Costo:</strong> <?= $course['is_free'] ? 'Gratis' : htmlspecialchars($course['cost']) ?></p>
                </div>

                <div class="course-description">
                    <h2>Descripción del Curso</h2>
                    <p><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                </div>

                <div class="instructor-section">
                    <h2>Tu Instructor Asignado</h2>
                    <div class="instructor-info">
                        <?php if ($course['instructor_photo']): ?>
                            <img src="<?= htmlspecialchars($course['instructor_photo']) ?>" alt="Foto de <?= htmlspecialchars($course['instructor_name']) ?>" class="instructor-photo">
                        <?php else: ?>
                            <img src="images/default_instructor.png" alt="Instructor" class="instructor-photo">
                        <?php endif; ?>
                        <div class="instructor-details">
                            <h3><?= htmlspecialchars($course['instructor_name'] ?: 'Instructor Desconocido') ?></h3>
                            <p class="instructor-bio"><?= nl2br(htmlspecialchars($course['instructor_bio'] ?: 'Sin biografía disponible. Este instructor es un experto en finanzas personales con amplia experiencia en ahorro y crédito, dedicado a guiarte hacia la libertad financiera.')) ?></p>
                            <?php if ($course['instructor_phone_number']): // Agregamos este bloque para mostrar el número ?>
                                <hr>
                                <p>Numero telefonico para contactar al instructor asignado y tomar el curso elegido...</p>
                                <p>Contactar via WhatsApp</p>
                                <p class="instructor-contact"><i class="fas fa-phone"></i> <strong>Teléfono:</strong> <a href="tel:<?= htmlspecialchars($course['instructor_phone_number']) ?>"><?= htmlspecialchars($course['instructor_phone_number']) ?></a></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!---------------------- - - - -- - - - CONTENIDO DEL CURSO 1 ---------------------------------------------------------->
                
                <?php if ($courseId == 3): // Información específica y atractiva para "Ahorro y Crédito Inteligente" ?>
                    <div class="info-section">
                        <h2>El Ahorro y Crédito Inteligente: Tu Guía Hacia la Libertad Financiera</h2>
                        <p>En el corazón de la estabilidad económica, el <span class="concepto-destacado">Ahorro y Crédito Inteligente</span> se refiere a la habilidad de gestionar tu dinero de manera eficiente, optimizando gastos, ahorros, inversiones y pagos de deudas para lograr estabilidad económica y alcanzar tus metas financieras. Implica una comprensión profunda de cómo funciona el dinero y cómo tomar decisiones estratégicas para tu bienestar financiero a largo plazo.</p>

                        <p>Aquí te presentamos los pilares y conceptos clave que exploraremos:</p>

                        <h3>Ahorro Inteligente: Construyendo tu Futuro</h3>
                        <p>El ahorro inteligente no se trata solo de guardar dinero, sino de hacerlo con un propósito y de forma estratégica.</p>

                        <h4>Estrategias para un Ahorro Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-money-check-alt"></i> <strong>Elabora un presupuesto mensual:</strong> El primer paso es saber a dónde va tu dinero. Registra todos tus ingresos y gastos para identificar patrones y áreas donde puedes reducir.</li>
                            <li><i class="fas fa-bullseye"></i> <strong>Define objetivos claros y realistas:</strong> ¿Para qué estás ahorrando? Un fondo de emergencia, la compra de una casa, un viaje, la educación, la jubilación.</li>
                            <li><i class="fas fa-robot"></i> <strong>Automatiza tu ahorro:</strong> Configura transferencias automáticas para ahorrar de forma disciplinada y evitar la tentación de gastar ese dinero.</li>
                            <li><i class="fas fa-shopping-bag"></i> <strong>Evita compras impulsivas:</strong> Antes de adquirir algo, piensa dos veces si realmente lo necesitas. Tómate un tiempo para evaluar la necesidad.</li>
                            <li><i class="fas fa-hand-holding-usd"></i> <strong>Reduce gastos fijos:</strong> Revisa tus contratos de servicios y compara opciones para optimizar los costos. Considera reducir el consumo de energía en tu hogar.</li>
                            <li><i class="fas fa-tag"></i> <strong>Aprovecha descuentos y promociones:</strong> Busca ofertas, pero no compres algo solo porque está rebajado si no lo necesitas.</li>
                            <li><i class="fas fa-gem"></i> <strong>Invierte en calidad:</strong> A veces, optar por lo más barato sale más caro a largo plazo. Piensa en la durabilidad y eficiencia.</li>
                            <li><i class="fas fa-wallet"></i> <strong>Genera un fondo de emergencia:</strong> Es crucial tener un colchón financiero para imprevistos (enfermedades, accidentes, desempleo).</li>
                        </ul>
                        <div class="centro">
                            <img src="images/rico.png" alt="curso 1">
                        </div>
                        <h4>Beneficios del Ahorro Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-cogs"></i> <strong>Disciplina financiera:</strong> Desarrollas hábitos que te dan mayor control sobre tu dinero.</li>
                            <li><i class="fas fa-trophy"></i> <strong>Alcanzar metas:</strong> Te permite lograr grandes proyectos de vida, desde comprar un auto hasta invertir en un negocio o dar el enganche para una casa.</li>
                            <li><i class="fas fa-handshake"></i> <strong>Reducción y control de deudas:</strong> Tener ahorros te ayuda a evitar que las deudas te saturen.</li>
                            <li><i class="fas fa-chart-pie"></i> <strong>Generación de ganancias:</strong> Al invertir tus ahorros en productos financieros adecuados, tu dinero puede crecer.</li>
                            <li><i class="fas fa-heart"></i> <strong>Tranquilidad y seguridad:</strong> Estar preparado para imprevistos reduce el estrés financiero.</li>
                        </ul>

                        <h3>Crédito Inteligente: Una Herramienta para Crecer</h3>
                        <p>El crédito, si se usa de forma inteligente, puede ser una herramienta poderosa para construir patrimonio y alcanzar metas. Sin embargo, su mal uso puede llevar a problemas financieros graves.</p>

                        <h4>Uso Inteligente del Crédito:</h4>
                        <ul>
                            <li><i class="fas fa-balance-scale"></i> <strong>Entiende la diferencia entre "deuda buena" y "deuda mala":</strong> Deuda buena genera valor (educación, hipoteca); deuda mala es para consumo que pierde valor.</li>
                            <li><i class="fas fa-calculator"></i> <strong>Calcula tu capacidad de pago:</strong> Antes de solicitar un préstamo, evalúa cuánto puedes pagar cómodamente cada mes.</li>
                            <li><i class="fas fa-percent"></i> <strong>Elige un plazo y tasa de interés adecuados:</strong> Compara opciones que se adapten a tus ingresos y objetivos.</li>
                            <li><i class="fas fa-calendar-check"></i> <strong>Paga puntualmente:</strong> Crucial para evitar intereses adicionales, cargos por mora y construir un buen historial.</li>
                            <li><i class="fas fa-file-contract"></i> <strong>Revisa las condiciones del contrato:</strong> Asegúrate de entender todas las tasas, comisiones y costos asociados.</li>
                            <li><i class="fas fa-award"></i> <strong>Desarrolla un historial crediticio sólido:</strong> Usa el crédito de manera responsable, pagando a tiempo y manteniendo tus cuentas abiertas.</li>
                            <li><i class="fas fa-times-circle"></i> <strong>Evita el sobreendeudamiento:</strong> No tomes varios créditos al mismo tiempo si no tienes un plan claro para pagarlos.</li>
                            <li><i class="fas fa-lightbulb"></i> <strong>Considera un crédito para inversiones:</strong> Un crédito bien administrado puede financiar estudios, mejoras en el hogar o consolidar deudas.</li>
                        </ul>
                        <div class="centro">
                            <img src="images/dinero.png" alt="curso 1">
                        </div>
                        <h4>Riesgos del Mal Uso del Crédito:</h4>
                        <ul>
                            <li><i class="fas fa-cart-arrow-down"></i> <strong>Gastos innecesarios y compras impulsivas:</strong> La tarjeta de crédito no es dinero adicional.</li>
                            <li><i class="fas fa-exclamation-triangle"></i> <strong>Sobreendeudamiento:</strong> Acumular demasiadas deudas que superan tu capacidad de pago.</li>
                            <li><i class="fas fa-dollar-sign"></i> <strong>Altas tasas de interés:</strong> Los intereses se acumulan rápidamente si no pagas el total.</li>
                            <li><i class="fas fa-ban"></i> <strong>Pérdida de capacidad de endeudamiento:</strong> Afecta futuras solicitudes de préstamos importantes.</li>
                            <li><i class="fas fa-frown"></i> <strong>Impacto negativo en el historial crediticio:</strong> Daña tu puntaje, dificultando la obtención de créditos o resultando en tasas más altas.</li>
                        </ul>
                    </div>
                <?php else: // Mensaje genérico para otros cursos ?>
                    <div class="course-content-section">
                        <h2>Contenido del Curso (Próximamente)</h2>
                        <p>¡Felicidades por tu inscripción! El contenido detallado del curso y las lecciones estarán disponibles pronto. Mantente atento a las actualizaciones en tu perfil.</p>
                    </div>
                <?php endif; ?>

                
                <!------------------------------CONTENIDO DEL CURSO 2---------------------------------------------------------->


                <?php if ($courseId == 2): // Información específica y atractiva para "Inversiones para Principiantes: Lo Básico" ?>
                    <div class="info-section">
                        <h2>El Ahorro, Crédito e Inversión Inteligente: Tu Guía Hacia la Libertad Financiera</h2>
                        <p>En el corazón de la estabilidad económica, el <span class="concepto-destacado">Ahorro, Crédito e Inversión Inteligente</span> se refiere a la habilidad de gestionar tu dinero de manera eficiente, optimizando gastos, ahorros, inversiones y pagos de deudas para lograr estabilidad económica y alcanzar tus metas financieras. Implica una comprensión profunda de cómo funciona el dinero y cómo tomar decisiones estratégicas para tu bienestar financiero a largo plazo.</p>

                        <p>Aquí te presentamos los pilares y conceptos clave que exploraremos:</p>

                        <h3>Ahorro Inteligente: Construyendo tu Futuro</h3>
                        <p>El ahorro inteligente no se trata solo de guardar dinero, sino de hacerlo con un propósito y de forma estratégica.</p>

                        <h4>Estrategias para un Ahorro Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-money-check-alt"></i> <strong>Elabora un presupuesto mensual:</strong> El primer paso es saber a dónde va tu dinero. Registra todos tus ingresos y gastos para identificar patrones y áreas donde puedes reducir.</li>
                            <li><i class="fas fa-bullseye"></i> <strong>Define objetivos claros y realistas:</strong> ¿Para qué estás ahorrando? Un fondo de emergencia, la compra de una casa, un viaje, la educación, la jubilación.</li>
                            <li><i class="fas fa-robot"></i> <strong>Automatiza tu ahorro:</strong> Configura transferencias automáticas para ahorrar de forma disciplinada y evitar la tentación de gastar ese dinero.</li>
                            <li><i class="fas fa-shopping-bag"></i> <strong>Evita compras impulsivas:</strong> Antes de adquirir algo, piensa dos veces si realmente lo necesitas. Tómate un tiempo para evaluar la necesidad.</li>
                            <li><i class="fas fa-hand-holding-usd"></i> <strong>Reduce gastos fijos:</strong> Revisa tus contratos de servicios y compara opciones para optimizar los costos. Considera reducir el consumo de energía en tu hogar.</li>
                            <li><i class="fas fa-tag"></i> <strong>Aprovecha descuentos y promociones:</strong> Busca ofertas, pero no compres algo solo porque está rebajado si no lo necesitas.</li>
                            <li><i class="fas fa-gem"></i> <strong>Invierte en calidad:</strong> A veces, optar por lo más barato sale más caro a largo plazo. Piensa en la durabilidad y eficiencia.</li>
                            <li><i class="fas fa-wallet"></i> <strong>Genera un fondo de emergencia:</strong> Es crucial tener un colchón financiero para imprevistos (enfermedades, accidentes, desempleo).</li>
                        </ul>
                        <div class="centro">
                            <img src="images/ganador.png" alt="curso 1">
                        </div>
                        <h4>Beneficios del Ahorro Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-cogs"></i> <strong>Disciplina financiera:</strong> Desarrollas hábitos que te dan mayor control sobre tu dinero.</li>
                            <li><i class="fas fa-trophy"></i> <strong>Alcanzar metas:</strong> Te permite lograr grandes proyectos de vida, desde comprar un auto hasta invertir en un negocio o dar el enganche para una casa.</li>
                            <li><i class="fas fa-handshake"></i> <strong>Reducción y control de deudas:</strong> Tener ahorros te ayuda a evitar que las deudas te saturen.</li>
                            <li><i class="fas fa-chart-pie"></i> <strong>Generación de ganancias:</strong> Al invertir tus ahorros en productos financieros adecuados, tu dinero puede crecer.</li>
                            <li><i class="fas fa-heart"></i> <strong>Tranquilidad y seguridad:</strong> Estar preparado para imprevistos reduce el estrés financiero.</li>
                        </ul>

                        <h3>Crédito Inteligente: Una Herramienta para Crecer</h3>
                        <p>El crédito, si se usa de forma inteligente, puede ser una herramienta poderosa para construir patrimonio y alcanzar metas. Sin embargo, su mal uso puede llevar a problemas financieros graves.</p>

                        <h4>Uso Inteligente del Crédito:</h4>
                        <ul>
                            <li><i class="fas fa-balance-scale"></i> <strong>Entiende la diferencia entre "deuda buena" y "deuda mala":</strong> Deuda buena genera valor (educación, hipoteca); deuda mala es para consumo que pierde valor.</li>
                            <li><i class="fas fa-calculator"></i> <strong>Calcula tu capacidad de pago:</strong> Antes de solicitar un préstamo, evalúa cuánto puedes pagar cómodamente cada mes.</li>
                            <li><i class="fas fa-percent"></i> <strong>Elige un plazo y tasa de interés adecuados:</strong> Compara opciones que se adapten a tus ingresos y objetivos.</li>
                            <li><i class="fas fa-calendar-check"></i> <strong>Paga puntualmente:</strong> Crucial para evitar intereses adicionales, cargos por mora y construir un buen historial.</li>
                            <li><i class="fas fa-file-contract"></i> <strong>Revisa las condiciones del contrato:</strong> Asegúrate de entender todas las tasas, comisiones y costos asociados.</li>
                            <li><i class="fas fa-award"></i> <strong>Desarrolla un historial crediticio sólido:</strong> Usa el crédito de manera responsable, pagando a tiempo y manteniendo tus cuentas abiertas.</li>
                            <li><i class="fas fa-times-circle"></i> <strong>Evita el sobreendeudamiento:</strong> No tomes varios créditos al mismo tiempo si no tienes un plan claro para pagarlos.</li>
                            <li><i class="fas fa-lightbulb"></i> <strong>Considera un crédito para inversiones:</strong> Un crédito bien administrado puede financiar estudios, mejoras en el hogar o consolidar deudas.</li>
                        </ul>
                        <div class="centro">
                            <img src="images/inversion.png" alt="curso 1">
                        </div>
                        <h4>Riesgos del Mal Uso del Crédito:</h4>
                        <ul>
                            <li><i class="fas fa-cart-arrow-down"></i> <strong>Gastos innecesarios y compras impulsivas:</strong> La tarjeta de crédito no es dinero adicional.</li>
                            <li><i class="fas fa-exclamation-triangle"></i> <strong>Sobreendeudamiento:</strong> Acumular demasiadas deudas que superan tu capacidad de pago.</li>
                            <li><i class="fas fa-dollar-sign"></i> <strong>Altas tasas de interés:</strong> Los intereses se acumulan rápidamente si no pagas el total.</li>
                            <li><i class="fas fa-ban"></i> <strong>Pérdida de capacidad de endeudamiento:</strong> Afecta futuras solicitudes de préstamos importantes.</li>
                            <li><i class="fas fa-frown"></i> <strong>Impacto negativo en el historial crediticio:</strong> Daña tu puntaje, dificultando la obtención de créditos o resultando en tasas más altas.</li>
                        </ul>

                        <h3>Inversiones Inteligentes: Haz que tu Dinero Trabaje para Ti</h3>
                        <p>Una vez que domines el ahorro y el crédito, el siguiente paso para la libertad financiera es hacer que tu dinero crezca a través de las inversiones. Invertir es poner tu dinero a trabajar con la expectativa de obtener un rendimiento en el futuro.</p>

                        <h4>¿Por qué invertir?</h4>
                        <ul>
                            <li><i class="fas fa-chart-line"></i> <strong>Superar la inflación:</strong> El costo de vida sube con el tiempo. Invertir te ayuda a que tu dinero no pierda valor.</li>
                            <li><i class="fas fa-rocket"></i> <strong>Alcanzar metas financieras grandes:</strong> Comprar una casa, planificar tu jubilación, la educación de tus hijos, o iniciar un negocio.</li>
                            <li><i class="fas fa-money-bill-wave"></i> <strong>Generar ingresos pasivos:</strong> Obtener ganancias sin tener que trabajar activamente por ellas.</li>
                        </ul>

                        <h4>Tipos de Inversiones para Principiantes:</h4>
                        <p>Existen diversas opciones de inversión, cada una con su nivel de riesgo y potencial de retorno. Es crucial entender que <strong>a mayor potencial de ganancia, generalmente mayor es el riesgo.</strong></p>

                        <ul>
                            <li><i class="fas fa-building"></i> <strong>Bienes Raíces:</strong> Comprar propiedades (casas, apartamentos, terrenos) para alquilar, revender o desarrollar. Es una inversión tangible que puede generar ingresos y aumentar de valor. Requiere una inversión inicial significativa y suele ser menos líquida (difícil de convertir rápidamente en efectivo).</li>
                            <li><i class="fas fa-chart-area"></i> <strong>Acciones:</strong> Cuando compras una acción, estás adquiriendo una pequeña parte de una empresa. Si la empresa crece y sus ganancias aumentan, el valor de sus acciones puede subir, y tú puedes venderlas a un precio mayor. Algunas empresas también pagan dividendos (una parte de sus ganancias) a sus accionistas.
                                <ul>
                                    <li><strong>Ventajas:</strong> Potencial de altos rendimientos. Fácil de comprar y vender (alta liquidez).</li>
                                    <li><strong>Riesgos:</strong> El valor puede fluctuar mucho. Puedes perder parte o todo tu capital.</li>
                                </ul>
                            </li>
                            <li><i class="fas fa-coins"></i> <strong>Criptomonedas (Ej. Bitcoin, Ethereum):</strong> Son monedas digitales que operan en una tecnología llamada blockchain. Son descentralizadas, lo que significa que no están controladas por ningún gobierno o banco central. Su valor está determinado por la oferta y la demanda.
                                <ul>
                                    <li><strong>Ventajas:</strong> Potencial de crecimiento explosivo. Innovación tecnológica.</li>
                                    <li><strong>Riesgos:</strong> Extremadamente volátiles (el precio puede subir o bajar drásticamente en poco tiempo). No reguladas en muchos países. Requiere investigación profunda.</li>
                                </ul>
                            </li>
                            <li><i class="fas fa-piggy-bank"></i> <strong>Fondos de Inversión:</strong> Son como una "cesta" de diferentes inversiones (acciones, bonos, etc.) gestionada por profesionales. Al invertir en un fondo, compras una pequeña parte de esa cesta diversificada. Esto reduce el riesgo porque tu dinero no está en una sola cosa.
                                <ul>
                                    <li><strong>Ventajas:</strong> Diversificación instantánea. Gestión profesional. Accesible con montos bajos.</li>
                                    <li><strong>Riesgos:</strong> Aunque diversificado, aún hay riesgo de pérdida. Se cobran comisiones de gestión.</li>
                                </ul>
                            </li>
                            <li><i class="fas fa-handshake-alt-slash"></i> <strong>Bonos:</strong> Son como un "préstamo" que le haces a un gobierno o a una empresa. A cambio, ellos te pagan intereses regularmente durante un período determinado y te devuelven el capital al final. Suelen ser menos riesgosos que las acciones.
                                <ul>
                                    <li><strong>Ventajas:</strong> Menor riesgo que las acciones. Pagos de intereses regulares.</li>
                                    <li><strong>Riesgos:</strong> Rendimientos más bajos que las acciones. Riesgo de inflación.</li>
                                </ul>
                            </li>
                        </ul>
                        <div class="centro">
                            <img src="images/inv.png" alt="curso 1">
                        </div>
                        <h4>Consejos Clave para Invertir de Forma Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-book-reader"></i> <strong>Educación Continua:</strong> Antes de invertir, aprende lo más que puedas. Investiga y comprende lo que estás comprando.</li>
                            <li><i class="fas fa-shield-alt"></i> <strong>Comienza con Pequeñas Cantidades:</strong> No necesitas una fortuna para empezar. Puedes iniciar con montos pequeños para familiarizarte.</li>
                            <li><i class="fas fa-sitemap"></i> <strong>Diversifica:</strong> No pongas todos tus huevos en la misma canasta. Distribuye tus inversiones en diferentes tipos de activos para reducir el riesgo.</li>
                            <li><i class="fas fa-hourglass-half"></i> <strong>Piensa a Largo Plazo:</strong> Las inversiones, especialmente en acciones y bienes raíces, rinden mejor con el tiempo. No busques ganancias rápidas.</li>
                            <li><i class="fas fa-chart-line"></i> <strong>Asesórate:</strong> Si no estás seguro, busca la ayuda de un asesor financiero certificado.</li>
                            <li><i class="fas fa-heartbeat"></i> <strong>Invierte solo lo que estés dispuesto a perder:</strong> Especialmente en activos de alto riesgo como las criptomonedas. Nunca inviertas dinero que necesites para tus necesidades básicas o tu fondo de emergencia.</li>
                            <li><i class="fas fa-eye"></i> <strong>Mantente informado, pero no reacciones impulsivamente:</strong> El mercado tiene altibajos. Evita tomar decisiones basadas en el pánico o la euforia.</li>
                        </ul>
                    </div>
                <?php endif; ?>

                <!------------------------------CONTENIDO DEL CURSO 3 (en el orden de la pagina obvio)---------------------------------------------------------->
                
                <?php if ($courseId == 1): // Información específica y atractiva para "Inversiones para Principiantes: Lo Básico" ?>
                    <div class="info-section">
                        <h2>Introducción a las Finanzas Personales: Tu Camino Hacia la Libertad Financiera</h2>
                        <p>Las finanzas personales son la clave para tener el control de tu vida económica. Se refieren a cómo gestionas tu dinero: desde cuánto ganas hasta cuánto gastas, ahorras, inviertes y cómo manejas tus deudas. Entender y aplicar principios básicos de finanzas personales te permitirá tomar decisiones inteligentes para tu bienestar y alcanzar tus metas a corto, mediano y largo plazo.</p>

                        <h3>¿Por qué son importantes las Finanzas Personales?</h3>
                        <ul>
                            <li><i class="fas fa-hand-holding-usd"></i> <strong>Control y Tranquilidad:</strong> Te permiten saber dónde está tu dinero y planificar su uso, reduciendo el estrés financiero.</li>
                            <li><i class="fas fa-bullseye"></i> <strong>Logro de Metas:</strong> Hacen posible alcanzar sueños como comprar una casa, viajar, estudiar, o jubilarte cómodamente.</li>
                            <li><i class="fas fa-shield-alt"></i> <strong>Seguridad ante Imprevistos:</strong> Te preparan para emergencias económicas, evitando que una situación inesperada desestabilice tu vida.</li>
                            <li><i class="fas fa-chart-line"></i> <strong>Crecimiento Patrimonial:</strong> Te enseñan a hacer que tu dinero trabaje para ti, incrementando tus activos a lo largo del tiempo.</li>
                        </ul>

                        <p>Aquí te presentamos los pilares y conceptos clave que exploraremos para dominar tus finanzas personales:</p>

                        <h3>El Ahorro Inteligente: Construyendo tu Futuro</h3>
                        <p>El ahorro inteligente no se trata solo de guardar dinero, sino de hacerlo con un propósito y de forma estratégica.</p>

                        <h4>Estrategias para un Ahorro Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-money-check-alt"></i> <strong>Elabora un presupuesto mensual:</strong> El primer paso es saber a dónde va tu dinero. Registra todos tus ingresos y gastos para identificar patrones y áreas donde puedes reducir.</li>
                            <li><i class="fas fa-bullseye"></i> <strong>Define objetivos claros y realistas:</strong> ¿Para qué estás ahorrando? Un fondo de emergencia, la compra de una casa, un viaje, la educación, la jubilación.</li>
                            <li><i class="fas fa-robot"></i> <strong>Automatiza tu ahorro:</strong> Configura transferencias automáticas para ahorrar de forma disciplinada y evitar la tentación de gastar ese dinero.</li>
                            <li><i class="fas fa-shopping-bag"></i> <strong>Evita compras impulsivas:</strong> Antes de adquirir algo, piensa dos veces si realmente lo necesitas. Tómate un tiempo para evaluar la necesidad.</li>
                            <li><i class="fas fa-hand-holding-usd"></i> <strong>Reduce gastos fijos:</strong> Revisa tus contratos de servicios y compara opciones para optimizar los costos. Considera reducir el consumo de energía en tu hogar.</li>
                            <li><i class="fas fa-tag"></i> <strong>Aprovecha descuentos y promociones:</strong> Busca ofertas, pero no compres algo solo porque está rebajado si no lo necesitas.</li>
                            <li><i class="fas fa-gem"></i> <strong>Invierte en calidad:</strong> A veces, optar por lo más barato sale más caro a largo plazo. Piensa en la durabilidad y eficiencia.</li>
                            <li><i class="fas fa-wallet"></i> <strong>Genera un fondo de emergencia:</strong> Es crucial tener un colchón financiero para imprevistos (enfermedades, accidentes, desempleo).</li>
                        </ul>
                        <div class="centro">
                            <img src="images/superacion-personal.png" alt="Persona feliz con dinero">
                        </div>
                        <h4>Beneficios del Ahorro Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-cogs"></i> <strong>Disciplina financiera:</strong> Desarrollas hábitos que te dan mayor control sobre tu dinero.</li>
                            <li><i class="fas fa-trophy"></i> <strong>Alcanzar metas:</strong> Te permite lograr grandes proyectos de vida, desde comprar un auto hasta invertir en un negocio o dar el enganche para una casa.</li>
                            <li><i class="fas fa-handshake"></i> <strong>Reducción y control de deudas:</strong> Tener ahorros te ayuda a evitar que las deudas te saturen.</li>
                            <li><i class="fas fa-chart-pie"></i> <strong>Generación de ganancias:</strong> Al invertir tus ahorros en productos financieros adecuados, tu dinero puede crecer.</li>
                            <li><i class="fas fa-heart"></i> <strong>Tranquilidad y seguridad:</strong> Estar preparado para imprevistos reduce el estrés financiero.</li>
                        </ul>

                        <h3>Crédito Inteligente: Una Herramienta para Crecer</h3>
                        <p>El crédito, si se usa de forma inteligente, puede ser una herramienta poderosa para construir patrimonio y alcanzar metas. Sin embargo, su mal uso puede llevar a problemas financieros graves.</p>

                        <h4>Uso Inteligente del Crédito:</h4>
                        <ul>
                            <li><i class="fas fa-balance-scale"></i> <strong>Entiende la diferencia entre "deuda buena" y "deuda mala":</strong> Deuda buena genera valor (educación, hipoteca); deuda mala es para consumo que pierde valor.</li>
                            <li><i class="fas fa-calculator"></i> <strong>Calcula tu capacidad de pago:</strong> Antes de solicitar un préstamo, evalúa cuánto puedes pagar cómodamente cada mes.</li>
                            <li><i class="fas fa-percent"></i> <strong>Elige un plazo y tasa de interés adecuados:</strong> Compara opciones que se adapten a tus ingresos y objetivos.</li>
                            <li><i class="fas fa-calendar-check"></i> <strong>Paga puntualmente:</strong> Crucial para evitar intereses adicionales, cargos por mora y construir un buen historial.</li>
                            <li><i class="fas fa-file-contract"></i> <strong>Revisa las condiciones del contrato:</strong> Asegúrate de entender todas las tasas, comisiones y costos asociados.</li>
                            <li><i class="fas fa-award"></i> <strong>Desarrolla un historial crediticio sólido:</strong> Usa el crédito de manera responsable, pagando a tiempo y manteniendo tus cuentas abiertas.</li>
                            <li><i class="fas fa-times-circle"></i> <strong>Evita el sobreendeudamiento:</strong> No tomes varios créditos al mismo tiempo si no tienes un plan claro para pagarlos.</li>
                            <li><i class="fas fa-lightbulb"></i> <strong>Considera un crédito para inversiones:</strong> Un crédito bien administrado puede financiar estudios, mejoras en el hogar o consolidar deudas.</li>
                        </ul>
                        <div class="centro">
                            <img src="images/negocios-y-finanzas.png"  alt="Dinero en crecimiento">
                        </div>
                        <h4>Riesgos del Mal Uso del Crédito:</h4>
                        <ul>
                            <li><i class="fas fa-cart-arrow-down"></i> <strong>Gastos innecesarios y compras impulsivas:</strong> La tarjeta de crédito no es dinero adicional.</li>
                            <li><i class="fas fa-exclamation-triangle"></i> <strong>Sobreendeudamiento:</strong> Acumular demasiadas deudas que superan tu capacidad de pago.</li>
                            <li><i class="fas fa-dollar-sign"></i> <strong>Altas tasas de interés:</strong> Los intereses se acumulan rápidamente si no pagas el total.</li>
                            <li><i class="fas fa-ban"></i> <strong>Pérdida de capacidad de endeudamiento:</strong> Afecta futuras solicitudes de préstamos importantes.</li>
                            <li><i class="fas fa-frown"></i> <strong>Impacto negativo en el historial crediticio:</strong> Daña tu puntaje, dificultando la obtención de créditos o resultando en tasas más altas.</li>
                        </ul>

                        <h3>Inversiones Inteligentes: Haz que tu Dinero Trabaje para Ti</h3>
                        <p>Una vez que domines el ahorro y el crédito, el siguiente paso para la libertad financiera es hacer que tu dinero crezca a través de las inversiones. Invertir es poner tu dinero a trabajar con la expectativa de obtener un rendimiento en el futuro.</p>

                        <h4>¿Por qué invertir?</h4>
                        <ul>
                            <li><i class="fas fa-chart-line"></i> <strong>Superar la inflación:</strong> El costo de vida sube con el tiempo. Invertir te ayuda a que tu dinero no pierda valor.</li>
                            <li><i class="fas fa-rocket"></i> <strong>Alcanzar metas financieras grandes:</strong> Comprar una casa, planificar tu jubilación, la educación de tus hijos, o iniciar un negocio.</li>
                            <li><i class="fas fa-money-bill-wave"></i> <strong>Generar ingresos pasivos:</strong> Obtener ganancias sin tener que trabajar activamente por ellas.</li>
                        </ul>

                        <h4>Tipos de Inversiones para Principiantes:</h4>
                        <p>Existen diversas opciones de inversión, cada una con su nivel de riesgo y potencial de retorno. Es crucial entender que <strong>a mayor potencial de ganancia, generalmente mayor es el riesgo.</strong></p>

                        <ul>
                            <li><i class="fas fa-building"></i> <strong>Bienes Raíces:</strong> Comprar propiedades (casas, apartamentos, terrenos) para alquilar, revender o desarrollar. Es una inversión tangible que puede generar ingresos y aumentar de valor. Requiere una inversión inicial significativa y suele ser menos líquida (difícil de convertir rápidamente en efectivo).</li>
                            <li><i class="fas fa-chart-area"></i> <strong>Acciones:</strong> Cuando compras una acción, estás adquiriendo una pequeña parte de una empresa. Si la empresa crece y sus ganancias aumentan, el valor de sus acciones puede subir, y tú puedes venderlas a un precio mayor. Algunas empresas también pagan dividendos (una parte de sus ganancias) a sus accionistas.
                                <ul>
                                    <li><strong>Ventajas:</strong> Potencial de altos rendimientos. Fácil de comprar y vender (alta liquidez).</li>
                                    <li><strong>Riesgos:</strong> El valor puede fluctuar mucho. Puedes perder parte o todo tu capital.</li>
                                </ul>
                            </li>
                            <li><i class="fas fa-coins"></i> <strong>Criptomonedas (Ej. Bitcoin, Ethereum):</strong> Son monedas digitales que operan en una tecnología llamada blockchain. Son descentralizadas, lo que significa que no están controladas por ningún gobierno o banco central. Su valor está determinado por la oferta y la demanda.
                                <ul>
                                    <li><strong>Ventajas:</strong> Potencial de crecimiento explosivo. Innovación tecnológica.</li>
                                    <li><strong>Riesgos:</strong> Extremadamente volátiles (el precio puede subir o bajar drásticamente en poco tiempo). No reguladas en muchos países. Requiere investigación profunda.</li>
                                </ul>
                            </li>
                            <li><i class="fas fa-piggy-bank"></i> <strong>Fondos de Inversión:</strong> Son como una "cesta" de diferentes inversiones (acciones, bonos, etc.) gestionada por profesionales. Al invertir en un fondo, compras una pequeña parte de esa cesta diversificada. Esto reduce el riesgo porque tu dinero no está en una sola cosa.
                                <ul>
                                    <li><strong>Ventajas:</strong> Diversificación instantánea. Gestión profesional. Accesible con montos bajos.</li>
                                    <li><strong>Riesgos:</strong> Aunque diversificado, aún hay riesgo de pérdida. Se cobran comisiones de gestión.</li>
                                </ul>
                            </li>
                            <li><i class="fas fa-handshake-alt-slash"></i> <strong>Bonos:</strong> Son como un "préstamo" que le haces a un gobierno o a una empresa. A cambio, ellos te pagan intereses regularmente durante un período determinado y te devuelven el capital al final. Suelen ser menos riesgosos que las acciones.
                                <ul>
                                    <li><strong>Ventajas:</strong> Menor riesgo que las acciones. Pagos de intereses regulares.</li>
                                    <li><strong>Riesgos:</strong> Rendimientos más bajos que las acciones. Riesgo de inflación.</li>
                                </ul>
                            </li>
                        </ul>
                        <div class="centro">
                            <img src="images/simbolo-de-dolar.png" alt="Dinero en crecimiento">
                        </div>
                        <h4>Consejos Clave para Invertir de Forma Inteligente:</h4>
                        <ul>
                            <li><i class="fas fa-book-reader"></i> <strong>Educación Continua:</strong> Antes de invertir, aprende lo más que puedas. Investiga y comprende lo que estás comprando.</li>
                            <li><i class="fas fa-shield-alt"></i> <strong>Comienza con Pequeñas Cantidades:</strong> No necesitas una fortuna para empezar. Puedes iniciar con montos pequeños para familiarizarte.</li>
                            <li><i class="fas fa-sitemap"></i> <strong>Diversifica:</strong> No pongas todos tus huevos en la misma canasta. Distribuye tus inversiones en diferentes tipos de activos para reducir el riesgo.</li>
                            <li><i class="fas fa-hourglass-half"></i> <strong>Piensa a Largo Plazo:</strong> Las inversiones, especialmente en acciones y bienes raíces, rinden mejor con el tiempo. No busques ganancias rápidas.</li>
                            <li><i class="fas fa-chart-line"></i> <strong>Asesórate:</strong> Si no estás seguro, busca la ayuda de un asesor financiero certificado.</li>
                            <li><i class="fas fa-heartbeat"></i> <strong>Invierte solo lo que estés dispuesto a perder:</strong> Especialmente en activos de alto riesgo como las criptomonedas. Nunca inviertas dinero que necesites para tus necesidades básicas o tu fondo de emergencia.</li>
                            <li><i class="fas fa-eye"></i> <strong>Mantente informado, pero no reacciones impulsivamente:</strong> El mercado tiene altibajos. Evita tomar decisiones basadas en el pánico o la euforia.</li>
                        </ul>
                    </div>
                <?php endif; ?>
                

            </div> <?php else: ?>
            <p class="no-course-found-message">No se pudo cargar la información del curso. Por favor, intenta de nuevo o regresa a la página de cursos.</p>
        <?php endif; ?>
        <a href="cursos.php" class="back-link">Volver a Cursos Gratuitos</a>
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