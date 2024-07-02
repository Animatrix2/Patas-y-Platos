<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comida</title>
    <link rel="stylesheet" href="Estilo-horario.css">
</head>
<?php
$mensaje = "";
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Manejar la solicitud de eliminación
if (isset($_REQUEST["eliminar"])) {
    $idHorarios = $_REQUEST["idHorarios"];
    $deleteSql = "DELETE FROM horarios WHERE IdHorarios = $idHorarios";
    $resultadoEliminar = $conexion->query($deleteSql);
    if ($resultadoEliminar) {
        $mensaje = "Horario eliminado.";
    } else {
        $mensaje = "Error al eliminar el horario.";
    }
}

// Manejar la solicitud de agregar
if (isset($_REQUEST["listo"])) {
    $Hora = isset($_REQUEST["horas"]) ? $_REQUEST["horas"] : null;
    $Minuto = isset($_REQUEST["minutos"]) ? $_REQUEST["minutos"] : null;

    if ($Hora !== null && $Minuto !== null) {
        $checksql = "SELECT * FROM horarios WHERE Hora = $Hora;";
        $result = mysqli_query($conexion, $checksql);
        if (mysqli_num_rows($result) > 0) {
            $mensaje = "Hora Ya existente";
        } else {
            $textsql = "INSERT INTO horarios (Hora, Minuto) VALUES ('$Hora', '$Minuto');";
            $consulta = mysqli_query($conexion, $textsql);
            if ($consulta) {
                $mensaje = "Agregado";
            } else {
                $mensaje = "No :(";
            }
        }
    } else {
        $mensaje = "Por favor, complete todos los campos.";
    }
}

// Consulta para obtener los horarios de la tabla horarios
$sql = "SELECT IdHorarios, Hora, Minuto FROM horarios";
$resultado = $conexion->query($sql);

// Variable para almacenar la tabla HTML
$tablaHTML = "<table border='0' cellspacing='0' cellpadding='0'>";
$tablaHTML .= "<tr><th colspan='4'>Horarios</th></tr>";
$tablaHTML .= "<tr><td>Hora</td><td></td><td>Minuto</td><td>Acción</td></tr>";

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $tablaHTML .= "<tr><td>" . $fila["Hora"] . "</td><td>:</td><td>" . $fila["Minuto"] . "</td>";
        $tablaHTML .= "<td><form action='' method='POST' style='display:inline-block;'><input type='hidden' name='idHorarios' value='" . $fila["IdHorarios"] . "'><input type='submit' name='eliminar' value='Eliminar'></form></td></tr>";
    }
} else {
    $tablaHTML .= "<tr><td colspan='4'>No se encontraron horarios en la base de datos.</td></tr>";
}

$tablaHTML .= "</table>";
?>

<body>

    <div class="ConenedorDeTodo">
        <div id="Titulo"><h1>Horarios</h1></div>
        <div class="Tabla-Horarios">
            <div class="tabla">
                <?php echo $tablaHTML ?>
            </div>
        </div>
        <div class="boton-accionar">
            <button onclick="Accionar()" id="boton-3"><img src="https://cdn-icons-png.flaticon.com/512/1004/1004166.png" alt="" class="Botttton" align=center></button>
        </div>
        <div >
            <button id="open">Modificar-Eliminar</button>
        </div>
    </div>

    <div id="modal_container" class="modal-container">
    
        <div class="modal">
            <h1>Agregar Nuevos Horarios</h1>
            <form action="" method="POST">
            <div id="Hora">
                
                    <input type="number" id="hora" name="horas" require> 
                    <label for="minuto">:</label>
                    <input type="number" id="minuto" name="minutos" require>
                    <br>
                    <br>
                <input type="submit" class="boton" name="listo" value="agregar">
                <button id="close">Cerrar</button>
            </div>
        </div>
    </div>
</form>
</form>

<script src="script.js"></script>

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