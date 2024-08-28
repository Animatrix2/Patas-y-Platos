<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Configuracion de cuenta</title>
    <?php 
session_start();


if (!isset($_SESSION['usu'])) {
    header('Location: ../index.php');
    exit;
}


if (isset($_GET['cerrar'])) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}


$conexion = new mysqli("localhost", "root", "", "usuarios");


if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_SESSION['usu'];
    $sql = "DELETE FROM users WHERE username = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $usuario);

    if ($stmt->execute()) {
        session_destroy();
        header('Location: ../index.php');
        exit;
    } else {
        echo "Error al eliminar la cuenta: " . $conexion->error;
    }
    $stmt->close();
}

$conexion->close();
?>



</head>
<body>

<div class="container">

    <div class="inner-container">
    <h1>Configuracion de Cuenta</h1> 
      <div class="button-container">
      <div class="user-options">
        <a href="cambio-de-nombre/Nombre.php"><input type="button" value="Cambiar nombre de usuario"></a>
        <a href="cambiar_contraseña.php"><input type="button" value="Cambiar contraseña"></a>
        <input type="button" value="Eliminar cuenta" id="open" class="Eliminar">
    </div>
      </div>
      <div><a href="../../index.php"><button id="atras"></button></a></div>
    </div>
    
  </div>


  <div id="modal_container" class="modal-container">
    <div class="modal">
        <button type="button" id="close" class="Eliminar">Atras</button>
        <h1>¿Estás seguro de eliminar la cuenta?</h1>
        <p>Tus mascotas estarán tristes sin su alimentación diaria</p>
        <img src="../img/perro-triste.png" width="200px"><br>
        <form method="POST" action="">
            <button type="submit" class="Eliminar">Eliminar Cuenta</button>
        </form>
    </div>
</div>

<script src="script.js"></script>
</body>
</html>