<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comida</title>
    <link rel="stylesheet" href="estilo-horario.css">
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
if (isset($_REQUEST["editar"])){
    $textsql= "UPDATE `Porcion` SET 
    IdPorcion= '$idPorcion',
    porcionValor = '$porcionValor'
    WHERE `IdPorcion` = $idPorcion;";
    $consulta = mysqli_query($conn,$textsql);
    if ($consulta){
        $mensaje="Editado";
    }
    else{
        $mensaje="No :|";
    }
}









// Consulta para obtener los horarios de la tabla horarios
$sql = "SELECT IdHorarios, Hora, Minuto FROM horarios";
$resultado = $conexion->query($sql);

// Variable para almacenar la tabla HTML
$tablaHTML = "<table border='0' cellspacing='0' cellpadding='0' id='tabla-horarios'>";
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

<a href="../Pagina-inicio.html"><button><-- atras</button></a>
    <div class="ConenedorDeTodo">
        <div id="Titulo"><h1>Horarios</h1></div>
        <div class="Tabla-Horarios">
            <div class="tabla">
                <?php echo $tablaHTML; ?>
            </div>
        </div>
        <div class="boton-accionar">
            <button onclick="Accionar()" id="boton-3"><img src="https://cdn-icons-png.flaticon.com/512/1004/1004166.png" alt="" class="Botttton" align=center></button>
        </div>
        <div class="Boton-Abrir">
            <button id="open">Agregar</button>
        </div>
    </div>

    <div id="modal-delay" class="modal-container">
        <div class="modal-content">
            <form action="" method="POST">
               
                <button type="button" id="close-delay">Cerrar</button>
            </form>
        </div>
    </div>

    <div id="modal_container" class="modal-container">
        <div class="modal">
            <h1>Agregar Horarios y Modificar Porciones</h1>
            <h2>Horarios    -     Porciones</h2>
            <form action="" method="POST">
                <div id="Hora">
                    <table id="agregar-horas">
                        <tr>
                            <td>
                                <input type="number" id="hora" max="23" min="0" name="horas" >
                                <label for="minuto" style="font-size: 50px;">:</label>
                                <input type="number" id="minuto" min="0" max="59" name="minutos" >
                                <br>
                                <button type="submit" class="boton" name="listo">Agregar</button>
                            </td>
                            <td>
                                
                                <input type="number" id="delay" name="delay" value="<?php echo $porcionValor; ?>">
                                <br>
                                <button onclick="Programar()" type="submit" name="programar" id="porcioness">Programar</button>
                            </td>
                        </tr>
                    </table>
                    <br><br>
                    <div>
                        
                    </div>
                    <button type="button" id="close">Cerrar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
<script>
    

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