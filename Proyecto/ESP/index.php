<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
  <link rel="stylesheet" href="style-inicio.css">
</head>
<?php 
  //Identificar si el usuario inicio sesion 

  session_start();
  
  if (!isset($_SESSION['usu'])) {
    header('Location:../index.php');
    exit;
  }
//cerrar sesion del usuario y redirigir a ../index.php
  if (isset($_GET['cerrar'])) {
    session_destroy();
    header('Location:../index.php');
    exit;
  }

?>
 <body >

  
  <div class="container">
    <div class="inner-container">
    <h1>Bienvenido a su Pet-Penser</h1>

      <div class="button-container">
        <div>
          <a href="Camara/Video-cam.php"><input type="button" value="Camara"style= ></a>
        </div>
        <div>
          <a href="Programacion-de-horarios/horarios.php"><input type="button" value="Horarios"></a>
          
        </div>
        <div>
          <a href="?cerrar=true"><input id="logout" type="button" value="Cerrar Sesión"></a>
        </div>
      </div>
    </div>
  </div>


  
  
</body>
</html>