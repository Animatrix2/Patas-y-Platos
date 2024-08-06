<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Registro de cuenta</title>
    <link rel="stylesheet" href="Styles.css">
</head>
<body>
    <div class="login-container">
        <h2>Crea tu cuenta</h2>
        <form action="register.php" method="POST">
            <label for="usu">Nombre Usuario</label>
            <input type="text" id="usu" name="usu" required>
            
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit" name="submit">Registrar</button><br><br>
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
         <a href="index.php" class="back-link">Volver atrás</a>
    </div>
</body>
</html>
