<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagina-Inicio</title>
  <link rel="stylesheet" href="style-inicio.css">
</head>
<style>
  #config {
    display: inline-block;
    text-align: center; 
    padding: 10px 20px; /* Ajusta el padding según sea necesario */

  }
</style>
<?php 
  //Identificar si el usuario inicio sesion 

  session_start();
  
  if (!isset($_SESSION['usu'])) {
    header('Location:Pagina-inicio.php');
    exit;
  }
//cerrar sesion del usuario y redirigir a ../index.php
  if (isset($_GET['cerrar'])) {
    session_destroy();
    header('Location:Pagina-inicio.php');
    exit;
  }

?>
 <body >

  
  <div class="container">
    <div class="inner-container">
    <h1>Bienvenido a su Pet-Penser</h1>

      <div class="button-container">
        <div>
          <a href="ESP/Camara/Video-cam.php"><input type="button" value="Camara"style= ></a>
        </div>
        <div>
          <a href="ESP/Programacion-de-horarios/horarios.php"><input type="button" value="Horarios"></a>
          
        </div>
        <div>
          <a href="ESP/Cambio-Contra-usuario/pag-config.php">
            <input type="button" value="Configuración" id="config">
          </a>
        </div>
        <div>
          <a href="?cerrar=true"><input id="logout" type="button" value="Cerrar Sesión"></a>
          
        </div>
      </div>
    </div>
  </div>


  
  
</body>
</html>