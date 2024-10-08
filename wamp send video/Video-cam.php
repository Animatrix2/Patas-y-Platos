<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESP32-CAM Video Stream</title>
    <style>
        #video-frame {
            width: 100%;
            max-width: 640px;
        }
    </style>
</head>
<body>

    <button onclick="startCamera()">Start Camera</button>
    <button onclick="stopCamera()">Stop Camera</button>
    <button onclick="servoLeft()">Servo Left</button>
    <button onclick="servoRight()">Servo Right</button>
    <br>
    <label for="hora">Hora</label>
    <input type="number" id="hora" min="0" max="23">
    <label for="minuto">Minuto</label>
    <input type="number" id="minuto" min="0" max="59">
    <label for="delay">Duración (seg)</label>
    <input type="number" id="delay" min="0" max="10" required>
    <br>
    <button onclick="Accionar()">Accionar</button>
    <button onclick="Programar()">Programar</button>
    <button onclick="Quitar()">Quitar Horario</button>
    <button onclick="Duracion()">Cambiar duración</button>

    <br>
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
            const horaValue = hora.value;
            const minutoValue = minuto.value;

            setServoSchedule(horaValue, minutoValue);
        }

        function Quitar() {
            const hora = document.getElementById("hora");
            const minuto = document.getElementById("minuto");

            const horaValue = hora.value;
            const minutoValue = minuto.value;

            removeServoSchedule(horaValue, minutoValue);
        }

        function Duracion() {
            const duracion = document.getElementById("delay");
            const duracionValue = duracion.value;
            setMoveDuration(duracionValue);
        }

        
        function servoAction(inputValue) {
            const esp32Url = `http://192.168.0.10/servoAction?delay=${inputValue}`; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function setServoSchedule(horaValue, minutoValue) {
            const esp32Url = `http://192.168.0.10/setServoSchedule?hora=${horaValue}&minuto=${minutoValue}`; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function removeServoSchedule(horaValue, minutoValue){
            const esp32Url = `http://192.168.0.10/removeServoSchedule?hora=${horaValue}&minuto=${minutoValue}`; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        function setMoveDuration(duracionValue){
            const esp32Url = `http://192.168.0.10/setMoveDuration?delay=${duracionValue}`; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
        }

        

        
    </script>
</body>
</html>
