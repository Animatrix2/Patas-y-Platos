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
    <h1>ESP32-CAM Video Stream</h1>
    <img id="video-frame" src="uploads/current_frame.jpg" alt="Video Frame">
    
    <script>
        function refreshFrame() {
            const frame = document.getElementById('video-frame');
            const timestamp = new Date().getTime(); // Timestamp to prevent caching
            frame.src = 'uploads/current_frame.jpg?' + timestamp;
        }

        setInterval(refreshFrame, 33); // Refresh at approximately 30 fps (1000ms / 30 ≈ 33ms)
    </script>
</body>
</html>
