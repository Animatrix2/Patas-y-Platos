<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {
    // Conexión
    $conn = mysqli_connect("localhost", "root", "", "usuarios") or die("Conexión fallida: " . mysqli_connect_error());

    // Recuperar los datos del formulario
    $username = mysqli_real_escape_string($conn, $_POST['usu']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Verificar si el nombre de usuario ya existe
    $sql_check = "SELECT * FROM `users` WHERE username = '$username'";
    $result_check = mysqli_query($conn, $sql_check);

    if (mysqli_num_rows($result_check) > 0) {
        echo "La cuenta ya existe";
    } else {
        // Cifrar la contraseña
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el nuevo usuario en la base de datos con la contraseña cifrada
        $sql_add = "INSERT INTO `users` (`username`, `password`) VALUES ('$username', '$hashed_password')";
        $query = mysqli_query($conn, $sql_add);

        if ($query) {
            header('Location: bienvenido.php');
        } else {
            $mensaje="Nombre de usuario ya usado";
            header('Location: crear.php?data='.$mensaje);
        }
    }

    // Cerrar la conexión a la base de datos
    mysqli_close($conn);
}
?>

