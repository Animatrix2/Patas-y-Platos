<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página de Bienvenida</title>
    <meta http-equiv="Refresh" content="5;ESP/Pagina-inicio.php" />
    <link rel="stylesheet" href="styles.css">
</head>
<?php
session_start();
  
if (!isset($_SESSION['usu'])) {
    header('Location:../index.php');
    exit;
}

// Leer el archivo JSON
$jsonData = file_get_contents('cuidados_mascota.json');
$mensajes = json_decode($jsonData, true)['mensajes'];

// Seleccionar un mensaje aleatorio
$mensajeAleatorio = $mensajes[array_rand($mensajes)];
?>
<body>
    <div class="hero-container">
        <div class="hero-content">
            <h1>¡Bienvenido <?php echo $_SESSION['usu']; ?>!</h1>
            <p>Iniciando PET-Penser</p>
            
            <!-- Mostrar mensaje aleatorio sobre el cuidado de la mascota -->
            <div class="mensajes-cuidados">
                <h2>Consejo para el cuidado de tu mascota:</h2>
                <p><?php echo $mensajeAleatorio; ?></p>
            </div>
        </div>
    </div>
</body>
</html>