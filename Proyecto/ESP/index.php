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



  
  
</body>
</html>