#include <WiFi.h>
#include <WebServer.h>
#include <ESP32Servo.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include <WiFiManager.h> // Incluir la librería WiFiManager

#define CAMERA_MODEL_AI_THINKER

#include "esp_camera.h"

//======================================== CAMERA_MODEL_AI_THINKER GPIO.
#include "camera_pins.h"
//======================================== 

// LED Flash PIN (GPIO 4)
#define FLASH_LED_PIN 4             

// Server Address or Server IP.
String serverName = "192.168.0.248";  //--> Cambia esto por la dirección IP de tu servidor o tu nombre de dominio
String serverPath = "/ESP32CAM/upload_img.php";
// Server Port.
const int serverPort = 80;

// Initialize WiFiClient.
WiFiClient client;

// Initialize WebServer on port 80.
WebServer server(80);

bool isCameraActive = false;
Servo myServo; // Crear una instancia del servomotor
int servoPin = 13; // Definir el pin del servomotor
int servoPos = 10; // Posición inicial del servomotor

void sendFrameToServer(camera_fb_t * fb) {
  if (!fb) {
    Serial.println("Camera capture failed");
    return;
  }

  if (client.connect(serverName.c_str(), serverPort)) {
    String head = "--dataMarker\r\nContent-Disposition: form-data; name=\"imageFile\"; filename=\"frame.jpg\"\r\nContent-Type: image/jpeg\r\n\r\n";
    String boundary = "\r\n--dataMarker--\r\n";
    
    uint32_t imageLen = fb->len;
    uint32_t dataLen = head.length() + boundary.length();
    uint32_t totalLen = imageLen + dataLen;

    client.println("POST " + serverPath + " HTTP/1.1");
    client.println("Host: " + serverName);
    client.println("Content-Length: " + String(totalLen));
    client.println("Content-Type: multipart/form-data; boundary=dataMarker");
    client.println();
    client.print(head);
  
    uint8_t *fbBuf = fb->buf;
    size_t fbLen = fb->len;
    for (size_t n = 0; n < fbLen; n += 1024) {
      if (n + 1024 < fbLen) {
        client.write(fbBuf, 1024);
        fbBuf += 1024;
      } else if (fbLen % 1024 > 0) {
        size_t remainder = fbLen % 1024;
        client.write(fbBuf, remainder);
      }
    }   
    client.print(boundary);
    esp_camera_fb_return(fb);
  } else {
    Serial.println("Connection to server failed");
    esp_camera_fb_return(fb);
  }
}

void handleStartCamera() {
  isCameraActive = true;
  server.send(200, "text/plain", "Camera started");
  Serial.println("Camera started");
}

void handleStopCamera() {
  isCameraActive = false;
  server.send(200, "text/plain", "Camera stopped");
  Serial.println("Camera stopped");
}

void handleServoLeft() {
  servoPos -= 10;
  if (servoPos < 0) servoPos = 0;
  myServo.write(servoPos);
  server.send(200, "text/plain", "Servo moved left");
  Serial.println("Servo moved left");
}

void handleServoRight() {
  servoPos += 10;
  if (servoPos > 180) servoPos = 180;
  myServo.write(servoPos);
  server.send(200, "text/plain", "Servo moved right");
  Serial.println("Servo moved right");
}

void handleServoAction() {
  if (server.hasArg("delay")) {
    int delayTime = server.arg("delay").toInt();
    
    if (delayTime > 0) {
      servoPos += 90;
      if (servoPos > 180) servoPos = 180;
      myServo.write(servoPos);
      server.send(200, "text/plain", "Servo moved right with delay");
      Serial.println("Servo moved right");

      delay(delayTime);

      servoPos -= 90;
      if (servoPos < 0) servoPos = 0;
      myServo.write(servoPos);
      server.send(200, "text/plain", "Servo moved left after delay");
      Serial.println("Servo moved left");
      return;
    }
  }
}

void handleRoot() {
  server.send(200, "text/plain", "Use /start to start the camera, /stop to stop the camera, /servoLeft to move servo left, and /servoRight to move servo right");
}

void setup() {

  // Disable brownout detector.
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);
  
  Serial.begin(115200);
  Serial.println();

  pinMode(FLASH_LED_PIN, OUTPUT);

  // Configurar el WiFiManager para manejar la conexión WiFi
  WiFiManager wifiManager;

  wifiManager.resetSettings(); // Descomentar esta línea para borrar las credenciales guardadas
  if (!wifiManager.autoConnect("ESP32-CAM-AP")) {
    Serial.println("Failed to connect and hit timeout");
    delay(3000);
    ESP.restart();
  }
  Serial.println("Connected to WiFi!");

  Serial.println(WiFi.localIP());

  // Set up the camera
  Serial.println();
  Serial.print("Set the camera ESP32 CAM...");
  
  camera_config_t config;
  config.ledc_channel = LEDC_CHANNEL_0;
  config.ledc_timer = LEDC_TIMER_0;
  config.pin_d0 = Y2_GPIO_NUM;
  config.pin_d1 = Y3_GPIO_NUM;
  config.pin_d2 = Y4_GPIO_NUM;
  config.pin_d3 = Y5_GPIO_NUM;
  config.pin_d4 = Y6_GPIO_NUM;
  config.pin_d5 = Y7_GPIO_NUM;
  config.pin_d6 = Y8_GPIO_NUM;
  config.pin_d7 = Y9_GPIO_NUM;
  config.pin_xclk = XCLK_GPIO_NUM;
  config.pin_pclk = PCLK_GPIO_NUM;
  config.pin_vsync = VSYNC_GPIO_NUM;
  config.pin_href = HREF_GPIO_NUM;
  config.pin_sscb_sda = SIOD_GPIO_NUM;
  config.pin_sscb_scl = SIOC_GPIO_NUM;
  config.pin_pwdn = PWDN_GPIO_NUM;
  config.pin_reset = RESET_GPIO_NUM;
  config.xclk_freq_hz = 20000000;
  config.pixel_format = PIXFORMAT_JPEG;

  if(psramFound()){
    config.frame_size = FRAMESIZE_HQVGA;
    config.jpeg_quality = 10;  //--> 0-63 lower number means higher quality
    config.fb_count = 2;
  } else {
    config.frame_size = FRAMESIZE_HQVGA;
    config.jpeg_quality = 12; //--> 0-63 lower number means higher quality
    config.fb_count = 1;
  }

  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    Serial.println();
    Serial.println("Restarting the ESP32 CAM.");
    delay(1000);
    ESP.restart();
  }

  sensor_t * s = esp_camera_sensor_get();
  s->set_framesize(s, FRAMESIZE_HQVGA);

  Serial.println();
  Serial.println("Set camera ESP32 CAM successfully.");

  // Initialize the servo
  myServo.setPeriodHertz(50); // Establecer la frecuencia del PWM del servo en 50Hz
  myServo.attach(servoPin, 500, 2400); // Asignar el pin del servo y los límites del pulso
  myServo.write(servoPos); // Inicializar la posición del servo

  // Set up web server routes.
  server.on("/", handleRoot);
  server.on("/start", handleStartCamera);
  server.on("/stop", handleStopCamera);
  server.on("/servoLeft", handleServoLeft);
  server.on("/servoRight", handleServoRight);
  server.on("/servoAction", handleServoAction);

  server.begin();
  Serial.println("HTTP server started");
}

void loop() {
  // Handle client requests
  server.handleClient();

  if (isCameraActive) {
    camera_fb_t * fb = esp_camera_fb_get();
    sendFrameToServer(fb);
    delay(33); // Delay to achieve approximately 30 fps
  }
}
