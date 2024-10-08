<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comida</title>
    <link rel="stylesheet" href="Estilo-horario.css">
</head>
<?php
session_start();

include '../esp-ip.php'; 
  
if (!isset($_SESSION['usu'])) {
    header('Location:../../Pagina-inicio.php');
    exit;
}
$ID_Usuario = $_SESSION["id"];
$mensaje = "";

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "usuarios");

// Verificar conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Manejar la solicitud de eliminación
if (isset($_REQUEST["eliminar_php"]) && $_REQUEST["eliminar_php"] == "eliminar") {
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
if (isset($_REQUEST["programar_php"]) && $_REQUEST["programar_php"] == "programar") {
    $Hora = isset($_REQUEST["horas"]) ? $_REQUEST["horas"] : null;
    $Minuto = isset($_REQUEST["minutos"]) ? $_REQUEST["minutos"] : null;

    if ($Hora !== null && $Minuto !== null) {
        // Verificar el número de horarios existentes
        $countSql = "SELECT COUNT(*) AS total FROM horarios WHERE ID_Usuario = $ID_Usuario;";
        $countResult = $conexion->query($countSql);
        $countRow = $countResult->fetch_assoc();
        
        if ($countRow['total'] >= 5) {
            $mensaje = "Se ha alcanzado el límite máximo de horarios.";
            echo "<script>alert('Se ha alcanzado el límite máximo de horarios.');</script>";
        } else {
            $checksql = "SELECT * FROM horarios WHERE Hora = $Hora AND Minuto = $Minuto AND ID_Usuario = $ID_Usuario;";
            $result = mysqli_query($conexion, $checksql);
            if (mysqli_num_rows($result) > 0) {
                $mensaje = "Hora ya existente.";
                echo "<script>alert('Hora ya existente.');</script>";
            } else {
                $textsql = "INSERT INTO horarios (Hora, Minuto, ID_Usuario) VALUES ('$Hora', '$Minuto', '$ID_Usuario');";
                $consulta = mysqli_query($conexion, $textsql);
                if ($consulta) {
                    $mensaje = "Horario agregado.";
                } else {
                    $mensaje = "Error al agregar el horario.";
                }
            }
        }
    } else {
        $mensaje = "Por favor, complete todos los campos.";
    }
}

$porcionSql = "SELECT Porcion FROM porciones WHERE ID_Usuario = $ID_Usuario";
$porcionResultado = $conexion->query($porcionSql);
$porcionValor = "";

if ($porcionResultado && $porcionResultado->num_rows > 0) {
    $fila = $porcionResultado->fetch_assoc();
    $porcionValor = $fila['Porcion'];
} else {
    $porcionValor = "No se encontró el valor";
}

if (isset($_REQUEST["duracion_php"]) && $_REQUEST["duracion_php"] == "duracion") {
    $porcionValor = $_REQUEST["delay"];

    if($porcionResultado->num_rows > 0){
        $checksql = "UPDATE `porciones` SET 
        Porcion= '$porcionValor'
        WHERE `ID_Usuario` = $ID_Usuario;";

        
    }
    else{
        $checksql = "INSERT INTO porciones (Porcion, ID_Usuario) VALUES ('$porcionValor', '$ID_Usuario');";

    }


    
    $consulta = mysqli_query($conexion, $checksql);
    if ($consulta) {
        $mensaje = "Duración editada.";
    } else {
        $mensaje = "Error al editar la duración.";
    }
}

// Consulta para obtener los horarios de la tabla horarios
$sql = "SELECT IdHorarios, Hora, Minuto FROM horarios WHERE ID_Usuario = $ID_Usuario ORDER BY Hora ASC, Minuto ASC LIMIT 5";
$resultado = $conexion->query($sql);

// Variable para almacenar la tabla HTML
$tablaHTML = "<table border='0' cellspacing='0' cellpadding='0' id='tabla-horarios'>";
$tablaHTML .= "<tr><td>Hora</td><td></td><td>Minuto</td></tr>";

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $tablaHTML .= "<tr><td>" . $fila["Hora"] . "</td><td>:</td><td>" . str_pad($fila["Minuto"], 2, "0", STR_PAD_LEFT) . "</td>";
        $tablaHTML .= "<td>
        <button onclick=\"Eliminar('" . $fila["Hora"] . "', '" . $fila["Minuto"] . "', '" . $fila["IdHorarios"] . "')\"><img src='../img/basurero.png' alt='' align=center   width='20px' height='20px'></button>
        <form name='eliminarForm_" . $fila["IdHorarios"] . "' action='' method='POST' style='display:none;'>
        <input type='hidden' name='idHorarios' value='" . $fila["IdHorarios"] . "'>
        <input type='hidden' name='eliminar_php' value='eliminar'>
        </form>
        </td></tr>";
    }
} else {
    $tablaHTML .= "<tr><td colspan='4'>No se encontraron horarios en la base de datos.</td></tr>";
}

$tablaHTML .= "</table>";

?>
<body >
<a href="../../index.php"><button id="atras"></button></a>
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
    <div class="tooltip tooltip-arriba">
        <span class="tooltiptext">Dispensa comida en el momento.</span>
        <button onclick="servoAction()" id="boton-3" class="button-Extra"></button>
        </div>
    </div>
    <div class="Boton-Abrir">
        <button id="open" class="button-Extra">Configurar PetPenser</button>
    </div>
</div>

<div id="modal-delay" class="modal-container">
    <div class="modal-content">
        <button type="button" id="close-delay">Cerrar</button>
    </div>
</div>

<div id="modal_container" class="modal-container">
    <div class="modal">
        <h1>Configurar PetPenser</h1>
        <div id="Hora">
            <table id="agregar-horas">
                <tr>                                
                    <td>
                        <form name="horarioForm" action="" method="POST">
                            <h2>Horarios</h2>
                            <input type="number" id="hora" max="23" min="0" name="horas" required>
                            <label for="minuto" style="font-size: 50px;">:</label>
                            <input type="number" id="minuto" min="0" max="59" name="minutos" required>
                            <br>
                            <div class="tooltip tooltip-abajo">
                                <span class="tooltiptext">Agrega un horario.</span>
                                <button onclick="Programar()" type="button" class="boton" name="programar"></button>
                            </div>
                            <input type="hidden" class="boton" name="programar_php">
                        </form>
                    </td>
                    <td>
                        <form name="duracionForm" action="" method="POST">
                            <h2>Duración</h2>
                            <input type="number" id="delay" name="delay" min="1" value="<?php echo $porcionValor; ?>" required>
                            <br>
                            <div class="tooltip tooltip-abajo">
                                <span class="tooltiptext">Guarda la duración en segundos para dispensar el alimento.</span>
                            <button onclick="Duracion()" type="button" class="boton" name="duracion"></button>
                            </div>
                            <input type="hidden" class="boton" name="duracion_php">
                        </form>
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

    function Programar() {
    const hora = parseInt(document.getElementById("hora").value);
    const minuto = parseInt(document.getElementById("minuto").value);

    if (isNaN(hora) || isNaN(minuto)) {
        alert("Por favor complete todos los campos de hora y minuto.");
        return;
    } else if (hora < 0 || hora > 23 || minuto < 0 || minuto > 59) {
        alert("Por favor use números válidos.");
        return;
    } else {
        console.log("Programar llamado con valores:", hora, minuto);
        setServoSchedule(hora, minuto);
        setTimeout(function() {
            document.horarioForm.programar_php.value = "programar";
            document.horarioForm.submit();
        }, 500);
    }
}


    function Duracion() {
        const duracion = document.getElementById("delay").value;
        console.log("Duracion llamado con valor:", duracion);
        if (duracion == "" || duracion<=0 ) {
        alert("Por favor complete el campo de duración. o ponga más de 0");
        return; // Detener la ejecución si faltan campos
    }
    else{
        setMoveDuration(duracion);
        setTimeout(function() {
            //your code to be executed after 1 second
            document.duracionForm.duracion_php.value="duracion";
            document.duracionForm.submit() 
}, 500);

    } 
    }
    
    function Eliminar(hora, minuto, idHorarios) {
        console.log("Eliminar llamado con valores:", hora, minuto);
        removeServoSchedule(hora, minuto);

        setTimeout(function() {
        document.forms["eliminarForm_" + idHorarios].submit();
        }, 500);
    }

    function servoAction() {
        const esp32Url = `http://<?php echo $esp_ip;?>/servoAction`;
        console.log("servoAction llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function setServoSchedule(horaValue, minutoValue) {
        const esp32Url = `http://<?php echo $esp_ip;?>/setServoSchedule?hora=${horaValue}&minuto=${minutoValue}`;
        console.log("setServoSchedule llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function removeServoSchedule(horaValue, minutoValue) {
        const esp32Url = `http://<?php echo $esp_ip;?>/removeServoSchedule?hora=${horaValue}&minuto=${minutoValue}`;
        console.log("removeServoSchedule llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function setMoveDuration(duracionValue) {
        const esp32Url = `http://<?php echo $esp_ip;?>/setMoveDuration?delay=${duracionValue}`;
        console.log("setMoveDuration llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }
</script>
</body>
</html>

