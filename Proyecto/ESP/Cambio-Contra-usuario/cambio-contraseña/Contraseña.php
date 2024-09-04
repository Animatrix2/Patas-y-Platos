<?php
session_start();

if (!isset($_SESSION['usu'])) {
    header('Location:../../../Pagina-inicio.php');
    exit;
}
$ID_Usuario = $_SESSION["id"];
$mensaje = "";

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password_actual = $_POST["password_actual"];
    $password_nueva = $_POST["password_nueva"];
    $password_nueva_confirmacion = $_POST["password_nueva_confirmacion"];
    
    // Obtener la contraseña actual del usuario en la base de datos
    $sql = "SELECT password FROM users WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $ID_Usuario);
    $stmt->execute();
    $stmt->bind_result($password_hash);
    $stmt->fetch();
    $stmt->close();

    // Verificar la contraseña actual
    if (password_verify($password_actual, $password_hash)) {
        // Verificar que las nuevas contraseñas coincidan
        if ($password_nueva === $password_nueva_confirmacion) {
            // Hashear la nueva contraseña
            $password_nueva_hash = password_hash($password_nueva, PASSWORD_DEFAULT);
            
            // Actualizar la contraseña en la base de datos
            $sql = "UPDATE users SET password = ? WHERE id = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("si", $password_nueva_hash, $ID_Usuario);
            
            if ($stmt->execute()) {
                $mensaje = "Contraseña actualizada correctamente.";
            } else {
                $mensaje = "Error al actualizar la contraseña.";
            }
            $stmt->close();
        } else {
            $mensaje = "Las nuevas contraseñas no coinciden.";
        }
    } else {
        $mensaje = "La contraseña actual es incorrecta.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="../pag-config.php"><button id="atras"></button></a>
<div class="inner-container">
<div class="login-container">
    <h2>Cambiar Contraseña</h2>
    <form method="POST" action="">
        <label for="password_actual">Contraseña Actual:</label>
        <input type="password" id="password_actual" name="password_actual" required><br>

        <label for="password_nueva">Nueva Contraseña:</label>
        <input type="password" id="password_nueva" name="password_nueva" required><br>

        <label for="password_nueva_confirmacion">Confirmar Nueva Contraseña:</label>
        <input type="password" id="password_nueva_confirmacion" name="password_nueva_confirmacion" required><br>

        <input type="submit" class="button" value="Cambiar Contraseña">
    </form>
</div>

</div>
<script>
    if(<?php $mensaje?>){
        alert('<?php echo $mensaje?>');
        window.location.href = 'Contraseña.php';
    }
</script>

</body>
</html>
