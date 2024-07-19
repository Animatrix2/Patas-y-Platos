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
        $checksql = "SELECT * FROM horarios WHERE Hora = $Hora AND Minuto = $Minuto;";
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
$idPorcion = 1;
$porcionSql = "SELECT Porcion FROM porciones WHERE IdPorcion = $idPorcion";
$porcionResultado = $conexion->query($porcionSql);
$porcionValor = "";


if ($porcionResultado && $porcionResultado->num_rows > 0) {
    $fila = $porcionResultado->fetch_assoc();
    $porcionValor = $fila['Porcion'];
} else {
    $porcionValor = "No se encontró el valor";
}

if (isset($_REQUEST["programar"])) {
    $porcionValor = $_REQUEST["delay"];
    $checksql = "UPDATE `Porciones` SET 
    IdPorcion= '$idPorcion',
    porcion= '$porcionValor'
    WHERE `IdPorcion` = $idPorcion;";
    $consulta = mysqli_query($conexion, $checksql);
    if ($consulta) {
        $mensaje = "Editado";
    } else {
        $mensaje = "No :|";
    }
}









// Consulta para obtener los horarios de la tabla horarios
$sql = "SELECT IdHorarios, Hora, Minuto FROM horarios";
$resultado = $conexion->query($sql);

// Variable para almacenar la tabla HTML
$tablaHTML = "<table border='0' cellspacing='0' cellpadding='0' id='tabla-horarios'>";
$tablaHTML .= "<tr><td>Hora</td><td></td><td>Minuto</td></tr>";

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $tablaHTML .= "<tr><td>" . $fila["Hora"] . "</td><td>:</td><td>" . $fila["Minuto"] . "</td>";
        $tablaHTML .= "<td>
                        
                        <input type='submit' name='eliminar' value='Eliminar' onclick=\"Quitar('" . $fila["Hora"] . "', '" . $fila["Minuto"] . "')\">
                        
                        <form action='' method='POST' style='display:inline-block;'>
                        <input type='hidden' name='idHorarios' value='" . $fila["IdHorarios"] . "'>
                        <input type='submit' name='eliminar' value='Confirmar'>
                        </form>
                        </td></tr>";
    }
} else {
    $tablaHTML .= "<tr><td colspan='4'>No se encontraron horarios en la base de datos.</td></tr>";
}

$tablaHTML .= "</table>";
?>
<body>
    

<a href="../Pagina-inicio.html"><button><-- atras</button></a>
<div class="ConenedorDeTodo">
    <div id="Titulo">
        <h1>Horarios</h1>
    </div>
    <div class="Tabla-Horarios">
        <div class="tabla">
            <?php echo $tablaHTML; ?>
        </div>
    </div>
    <div class="boton-accionar">
        <button onclick="Accionar()" id="boton-3"><img src="https://cdn-icons-png.flaticon.com/512/1004/1004166.png"
                alt="" class="Botttton" align=center></button>
    </div>
    <div class="Boton-Abrir">
        <button id="open">Agregar-Modificar Porciones</button>
    </div>
</div>

<div id="modal-delay" class="modal-container">
    <div class="modal-content">


        <button type="button" id="close-delay">Cerrar</button>

    </div>
</div>

<div id="modal_container" class="modal-container">
    <div class="modal">
 <h1> Horarios   Porciones</h1> 
        
        <div id="Hora">
            <table id="agregar-horas">
                <tr>                                
                    <td>
                            <form id="horarioForm" action="" method="POST">

                           <h2>Horarios</h2>
                            <input type="number" id="hora" max="23" min="0" name="horas" required>
                            <label for="minuto" style="font-size: 50px;">:</label>
                            <input  type="number" id="minuto" min="0" max="59" name="minutos" required>
                            <br>
                            <button onclick="Programar()" type="button" class="boton" name="listo">Aceptar</button>
                            <button type="submit" class="boton" name="listo" id="porcioness">Confirmar</button>

                            </form>
                    </td>
                    <td>
                  
                            <form id="porcionForm" action="" method="POST">

                           <h2>Porciones</h2>
                            <input type="number" id="delay" name="delay" value="<?php echo $porcionValor; ?>" required>
                            <br>
                            <button onclick="Duracion()" type="button" class="boton" name="programar">Aceptar</button>
                            <button type="submit" class="boton" name="programar" id="porcioness">Confirmar</button>

                            </form>
                        </td>
                        <br>
                    </td>
                </tr>
            </table>
            <br><br>
            <div>

            </div>
            <button type="button" id="close">Cerrar</button>
        </div>

    </div>
</div>

<script src="script.js"></script>
<script>

    //Los dos EventListeners siguientes sirven para prevenir que el formulario se envíe. En teoría debería enviarlo 
    //luego de procesarse el envío de solicitud, pero actualmente no funciona

    //Comentar para hacer que se envíe el form en lugar de la solicitud

    function sendRequest(url) {
        console.log("Enviando solicitud a:", url);
        fetch(url)
            .then(response => response.text())
            .then(data => {
                console.log("Respuesta recibida:", data);
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

    function Accionar() {
        servoAction();
    }

    function Programar() {
        const hora = document.getElementById("hora").value;
        const minuto = document.getElementById("minuto").value;
        if (hora === "" || minuto === "") {
        console.log("Por favor complete todos los campos de hora y minuto.");
        return; // Detener la ejecución si faltan campos
    }
        console.log("Programar llamado con valores:", hora, minuto);
        setServoSchedule(hora, minuto);
    }

    function Quitar(hora, minuto) {
        console.log("Quitar llamado con valores:", hora, minuto);
        removeServoSchedule(hora, minuto);
    }

    function Duracion() {
        const duracion = document.getElementById("delay").value;
        console.log("Duracion llamado con valor:", duracion);
        setMoveDuration(duracion);
    }

    function servoAction(inputValue) {
        const esp32Url = `http://192.168.0.10/servoAction`; // Replace with your ESP32-CAM IP address
        console.log("servoAction llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function setServoSchedule(horaValue, minutoValue) {
        const esp32Url = `http://192.168.0.10/setServoSchedule?hora=${horaValue}&minuto=${minutoValue}`; // Replace with your ESP32-CAM IP address
        console.log("setServoSchedule llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function removeServoSchedule(horaValue, minutoValue) {
        const esp32Url = `http://192.168.0.10/removeServoSchedule?hora=${horaValue}&minuto=${minutoValue}`; // Replace with your ESP32-CAM IP address
        console.log("removeServoSchedule llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function setMoveDuration(duracionValue) {
        const esp32Url = `http://192.168.0.10/setMoveDuration?delay=${duracionValue}`; // Replace with your ESP32-CAM IP address
        console.log("setMoveDuration llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }


</script>
</body>

</html>