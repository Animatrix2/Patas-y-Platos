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
        $countSql = "SELECT COUNT(*) AS total FROM horarios";
        $countResult = $conexion->query($countSql);
        $countRow = $countResult->fetch_assoc();
        
        if ($countRow['total'] >= 5) {
            $mensaje = "Se ha alcanzado el límite máximo de horarios.";
            echo "<script>alert('Se ha alcanzado el límite máximo de horarios.');</script>";
        } else {
            $checksql = "SELECT * FROM horarios WHERE Hora = $Hora AND Minuto = $Minuto;";
            $result = mysqli_query($conexion, $checksql);
            if (mysqli_num_rows($result) > 0) {
                $mensaje = "Hora ya existente.";
                echo "<script>alert('Hora ya existente.');</script>";
            } else {
                $textsql = "INSERT INTO horarios (Hora, Minuto) VALUES ('$Hora', '$Minuto');";
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

if (isset($_REQUEST["duracion_php"]) && $_REQUEST["duracion_php"] == "duracion") {
    $porcionValor = $_REQUEST["delay"];
    $checksql = "UPDATE `porciones` SET 
    IdPorcion= '$idPorcion',
    porcion= '$porcionValor'
    WHERE `IdPorcion` = $idPorcion;";
    $consulta = mysqli_query($conexion, $checksql);
    if ($consulta) {
        $mensaje = "Duración editada.";
    } else {
        $mensaje = "Error al editar la duración.";
    }
}

// Consulta para obtener los horarios de la tabla horarios
$sql = "SELECT IdHorarios, Hora, Minuto FROM horarios ORDER BY Hora ASC, Minuto ASC LIMIT 5";
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
<a href="../Pagina-inicio.html"><button id="atras"></button></a>
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
        <button onclick="servoAction()" id="boton-3"><img src="../img/activar-desactivar.png" alt="" class="Botttton" align=center></button>
    </div>
    <div class="Boton-Abrir">
        <button id="open">Configurar PetPenser</button>
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
                            <button onclick="Programar()" type="button" class="boton" name="programar">Confirmar</button>
                            <input type="hidden" class="boton" name="programar_php">
                        </form>
                    </td>
                    <td>
                        <form name="duracionForm" action="" method="POST">
                            <h2>Duración</h2>
                            <input type="number" id="delay" name="delay" min="1" value="<?php echo $porcionValor; ?>" required>
                            <br>
                            <button onclick="Duracion()" type="button" class="boton" name="duracion">Confirmar</button>
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
        const hora = document.getElementById("hora").value;
        const minuto = document.getElementById("minuto").value;
        if (hora === "" || minuto === "") {
            alert("Por favor complete todos los campos de hora y minuto.");
            return;
        }else {
            console.log("Programar llamado con valores:", hora, minuto);
        setServoSchedule(hora, minuto);

        document.horarioForm.programar_php.value = "programar";
        document.horarioForm.submit();

        }
       
    }

    function Duracion() {
        const duracion = document.getElementById("delay").value;
        console.log("Duracion llamado con valor:", duracion);
        if (duracion == "" || duracion<=0 ) {
        alert("Por favor complete el campo de duración. o pongo mas de 0");
        return; // Detener la ejecución si faltan campos
    }
    else{
        setMoveDuration(duracion);

        document.duracionForm.duracion_php.value="duracion";
        document.duracionForm.submit() 
    } 
    }



    
    function Eliminar(hora, minuto, idHorarios) {
        console.log("Eliminar llamado con valores:", hora, minuto);
        removeServoSchedule(hora, minuto);

        document.forms["eliminarForm_" + idHorarios].submit();
    }

    function servoAction() {
        const esp32Url = `http://192.168.0.10/servoAction`;
        console.log("servoAction llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function setServoSchedule(horaValue, minutoValue) {
        const esp32Url = `http://192.168.0.10/setServoSchedule?hora=${horaValue}&minuto=${minutoValue}`;
        console.log("setServoSchedule llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function removeServoSchedule(horaValue, minutoValue) {
        const esp32Url = `http://192.168.0.10/removeServoSchedule?hora=${horaValue}&minuto=${minutoValue}`;
        console.log("removeServoSchedule llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }

    function setMoveDuration(duracionValue) {
        const esp32Url = `http://192.168.0.10/setMoveDuration?delay=${duracionValue}`;
        console.log("setMoveDuration llamado con URL:", esp32Url);
        sendRequest(esp32Url);
    }
</script>
</body>
</html>

