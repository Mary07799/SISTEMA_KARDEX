<?php
require_once 'conexion.php';

if (isset($_SESSION['usuario'])) {
    header('Location: menu.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $rol = $_POST['rol'] === 'docente' ? 'docente' : 'estudiante';

    if (!$nombre || !$email || !$password) {
        $error = "Todos los campos son obligatorios.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = "Este correo ya está registrado.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $hash, $rol])) {
                $_SESSION['usuario'] = $nombre;
                $_SESSION['usuario_id'] = $pdo->lastInsertId();
                $_SESSION['rol'] = $rol;
                header('Location: menu.php');
                exit;
            } else {
                $error = "Error al registrar usuario.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Registro - C-PAC</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #000;
            color: #fff;
            display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;
        }
        form {
            background: #111;
            padding: 20px; border-radius: 8px;
            box-shadow: 0 0 20px #00e6e6;
        }
        input, select {
            width: 100%; padding: 8px; margin: 8px 0; border-radius: 4px; border: none;
        }
        button {
            width: 100%; padding: 10px; background: #00e6e6; border: none; border-radius: 4px;
            color: #000; font-weight: bold; cursor: pointer; text-transform: uppercase; font-size: 16px;
        }
        button:hover { background: #00baba; }
        .error { color: #ff5555; margin-bottom: 10px; }
        a { color: #00e6e6; text-decoration: none; display: block; margin-top: 10px; text-align: center; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <form method="post" action="">
        <h2>Registro de usuario</h2>
        <?php if ($error): ?>
            <div class="error"><?=htmlspecialchars($error)?></div>
        <?php endif; ?>
        <input type="text" name="nombre" placeholder="Nombre completo" required />
        <input type="email" name="email" placeholder="Correo electrónico" required />
        <input type="password" name="password" placeholder="Contraseña" required />
        <select name="rol" required>
            <option value="estudiante" selected>Estudiante</option>
            <option value="docente">Docente</option>
        </select>
        <button type="submit">Registrar</button>
        <a href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
    </form>
</body>
</html>
