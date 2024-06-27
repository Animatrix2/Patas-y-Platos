<?php 
// Conexión 
    $conn = mysqli_connect('localhost', 'root', '', 'usuarios');

    // Recuperar los datos del formulario de inicio de sesión
    $username = $_POST['usu'];
    $password = $_POST['password'];

    // Consulta para verificar las credenciales del usuario
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    // Verificar si se encontraron coincidencias en la base de datos
    if (mysqli_num_rows($result) == 1) {
        // Credenciales válidas, iniciar sesión
        session_start();
        $_SESSION['usu'] = $username;
        // Redirigir al usuario a la página de inicio
        header('Location: bienvenido.php');
    } else {
        // Credenciales inválidas, mostrar mensaje de error
        $mensaje="Nombre de usuario o contraseña incorrectos.";

        //---GET--
        header('Location: iniciar.php?data='.$mensaje);

        //---Variable de Sesión---
        //session_start();
        //$_SESSION['data']=$mensaje;
        //header("Location: index.php");
    }

    // Cerrar la conexión a la base de datos
    mysqli_close($conn);



 ?>