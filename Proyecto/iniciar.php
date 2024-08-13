<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Inicio de Sesión</title>
    <link rel="stylesheet" href="Styles.css">
</head>
<?
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["usu"])) {
        $_SESSION['usuario'] = $_POST["usu"];
        // Realiza otras operaciones si es necesario
        header("Location: ESP/Programacion-de-horarios/horarios.php");
        exit();
    } else {
        echo "No se recibió el nombre de usuario.";
    }
}
?>
<body>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form action="login.php" method="POST">
            <form action="/ESP/Programacion-de-horarios/horarios.php" method="POST">
            <label for="usu">Nombre Usuario</label>
            <input type="text" id="usu" name="usu" required>
            
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
            </a> <button type="submit" class="accion">Iniciar Sesión</button><p> 
           
             
            <?php 
            //---GET---//
            //Revisa si se recibió una consulta GET y si existe una variable "data" en ella
            if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["data"])) {
            $mensaje=$_GET['data'];

            //---Variable de Sesión---
            //session_start();
            //$mensaje=$_SESSION['data'];

            echo $mensaje;
            }
            ?>
            
            </form>
        </form>
        </p><a href="index.php"><button class="volver">Ir atras</button>
    </div>
  
</body>
</html>
