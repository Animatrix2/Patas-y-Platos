<?php
// Conexión 
$conn = mysqli_connect('localhost', 'root', '', 'usuarios');

// Recuperar los datos del formulario de inicio de sesión
$username = $_POST['usu'];
$password = $_POST['password'];

// Consulta para obtener el hash de la contraseña
$query = "SELECT * FROM users WHERE username='$username'";
$result = mysqli_query($conn, $query);

// Verificar si se encontró el usuario
if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);
    $hashed_password = $user['password'];

    // Verificar la contraseña introducida con la almacenada en la base de datos
    if (password_verify($password, $hashed_password)) {
        // Credenciales válidas, iniciar sesión
        session_start();
        $_SESSION['usu'] = $username;
        // Redirigir al usuario a la página de inicio
        header('Location: bienvenido.php');
    } else {
        $mensaje = "Nombre de usuario o contraseña incorrectos.";
        header('Location: iniciar.php?data='.$mensaje);
    }
} else {
    $mensaje = "Nombre de usuario o contraseña incorrectos.";
    header('Location: iniciar.php?data='.$mensaje);
}

// Cerrar la conexión a la base de datos
mysqli_close($conn);
?>
