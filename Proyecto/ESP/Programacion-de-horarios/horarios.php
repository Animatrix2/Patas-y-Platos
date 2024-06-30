<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comida</title>
    <link rel="stylesheet" href="Estilo-horario.css">
</head>
<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los horarios de la tabla horarios
$sql = "SELECT IdHorarios, Hora, Minuto FROM horarios";
$resultado = $conexion->query($sql);

// Variable para almacenar la tabla HTML
$tablaHTML = "<table border='0' cellspacing='0' cellpadding='0'>";
$tablaHTML .= "<tr><th colspan='4'>Horarios</th></tr>";
$tablaHTML .= "<tr><td>ID</td><td>Hora</td><td></td><td>Minuto</td></tr>";

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $tablaHTML .= "<tr><td>" . $fila["IdHorarios"] . "</td><td>" . $fila["Hora"] . "</td><td>:</td><td>" . $fila["Minuto"] . "</td></tr>";
    }
} else {
    $tablaHTML .= "<tr><td colspan='4'>No se encontraron horarios en la base de datos.</td></tr>";
}

$tablaHTML .= "</table>";

// Cerrar la conexión a la base de datos

// Mostrar la tabla en la ubicación deseada de la página
?>

<body>
    <div class="ConenedorDeTodo">
        <div id="Titulo">   <h1>Horarios</h1>  </div>
        <div class="Tabla-Horarios"> 
            <div class="tabla">
                <?php echo $tablaHTML?>
            </div>
        </div>
        <div class="boton-accionar">
            <button onclick="Accionar()" id="boton-3"> <img src="https://cdn-icons-png.flaticon.com/512/1004/1004166.png" alt=""   class="Botttton" align=center> </button>
        </div>

    </div>
    <div>
        
    </div>


    <script>
        function refreshFrame() {
            const frame = document.getElementById('video-frame');
            const timestamp = new Date().getTime(); // Timestamp to prevent caching
            frame.src = 'uploads/current_frame.jpg?' + timestamp;
        }

        setInterval(refreshFrame, 33); // Refresh at approximately 30 fps (1000ms / 30 ≈ 33ms)

        function sendRequest(url) {
            fetch(url)
                .then(response => response.text())
                .then(data => {
                    console.log(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        function startCamera() {
            const esp32Url = 'http://192.168.0.10/start'; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function stopCamera() {
            const esp32Url = 'http://192.168.0.10/stop'; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function servoLeft() {
            const esp32Url = 'http://192.168.0.10/servoLeft'; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function servoRight() {
            const esp32Url = 'http://192.168.0.10/servoRight'; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function Accionar() {
            const input = document.getElementById("delay");
            const inputValue = input.value;
            servoAction(inputValue);
        }

        function Programar() {
            const hora = document.getElementById("hora");
            const minuto = document.getElementById("minuto");
            const delay = document.getElementById("delay");
            const horaValue = hora.value;
            const minutoValue = minuto.value;
            const delayValue = delay.value;

            setServoSchedule(horaValue, minutoValue, delayValue);
        }

        
        function servoAction(inputValue) {
            const esp32Url = `http://192.168.0.10/servoAction?delay=${inputValue}`; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function setServoSchedule(horaValue, minutoValue, delayValue) {
            const esp32Url = `http://192.168.0.10/setServoSchedule?hora=${horaValue}&minuto=${minutoValue}&duracion=${delayValue}`; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }
    </script>
</body>
</html>