<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usu'])) {
    header('Location: ../../../index.php');
    exit;
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Manejar la solicitud POST para cambiar el nombre de usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nuevo_nombre = trim($_POST['nuevo_nombre']);
    $usuario_actual = $_SESSION['usu'];

    // Consulta para actualizar el nombre de usuario en la tabla `users`
    $sql = "UPDATE users SET username = ? WHERE username = ?";

    // Preparar la declaración
    $stmt = $conexion->prepare($sql);
    if ($stmt === false) {
        die("Error al preparar la declaración: " . $conexion->error);
    }

    $stmt->bind_param("ss", $nuevo_nombre, $usuario_actual);

    // Ejecutar la declaración
    if ($stmt->execute()) {
        // Si la actualización fue exitosa, actualizar la sesión y redirigir a la página deseada
        $_SESSION['usu'] = $nuevo_nombre;
        header('Location: ../pag-config.php');
        exit;
    } else {
        echo "Error al actualizar el nombre de usuario: " . $stmt->error;
    }

    // Cerrar la declaración
    $stmt->close();
}

// Cerrar la conexión
$conexion->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../cambio-contraseña/style.css">
    <title>Cambiar Nombre de Usuario</title>
   

</head>
<body>
<a href="../pag-config.php"><button id="atras"></button></a>
<div class="inner-container">
<div class="login-container">
        <h1>Cambiar Nombre de Usuario</h1>
        <br>
        <br>
        <form action="" method="POST">
            <label for="nuevo_nombre">Nuevo Nombre</label><br>
            <input type="text" name="nuevo_nombre" id="nuevo_nombre" required><br><br>
            <input type="submit" class="button" value="Cambiar Nombre" style="margin-top: 50px;">
        </form>
    </div>
</div>
</body>
</html>
