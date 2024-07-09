<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet-Camera</title>
    <link rel="stylesheet" href="Style-esp.css">
</head>
<body>
<a href="../Pagina-inicio.html"><button><-- atras</button></a>
    <h1>Pet-Camera</h1>
  
    <div class="contenedor-botones">
        <!-- <button onclick="startCamera()" id="botone-1">Start Camera</button>
        <button onclick="stopCamera()" id="botone-2">Stop Camera</button> -->
    </div>
    <div class="image-container">
        <img id="video-frame" src="uploads/current_frame.jpg" alt="Video Frame" class="background-standby">
    </div>
    <div class="switch-container">
        <label class="switch">
            <input class="cb" type="checkbox" id="camera-toggle" />
            <span class="toggle">
                <span class="left">off</span>
                <span class="right">on</span>
            </span>
        </label>
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
            document.getElementById('video-frame').classList.remove('background-standby');
        }

        function stopCamera() {
            const esp32Url = 'http://192.168.0.10/stop'; // Replace with your ESP32-CAM IP address
            sendRequest(esp32Url);
            document.getElementById('video-frame').classList.add('background-standby');
        }

        document.getElementById('camera-toggle').addEventListener('change', function() {
            if (this.checked) {
                startCamera();
            } else {
                stopCamera();
            }
        });

        window.addEventListener('beforeunload', function() {
            stopCamera();
        });
    </script>
</body>
</html>
