<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32-CAM Video Stream</title>
    <link rel="stylesheet" href="Style-esp.css">
    <style>
        body{
            background-color: green;
        }
  
</style>
</head>
<body>

    <h1 style="text-align: center;">Camara y progamacion del Pet-Penser</h1>
    <div class="contenedor-botones">
        <button onclick="startCamera()" id="botone-1">Start Camera</button>
        <button onclick="stopCamera()" id="botone-2">Stop Camera</button>
        <button onclick="servoLeft()">Servo abrir</button>
        <button onclick="servoRight()" >Servo cerrar</button>
    </div>

        <img id="video-frame" src="uploads/current_frame.jpg" alt="Video Frame">


   
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
