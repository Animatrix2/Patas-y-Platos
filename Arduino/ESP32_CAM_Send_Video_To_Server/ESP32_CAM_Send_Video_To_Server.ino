#include <WiFi.h>
#include <WebServer.h>
#include <ESP32Servo.h>
#include "soc/soc.h"
#include "soc/rtc_cntl_reg.h"
#include <WiFiManager.h>
#include <Ticker.h>
#include <TimeLib.h>
#include <NTPClient.h>
#include <Preferences.h>

Preferences preferences;

#define CAMERA_MODEL_AI_THINKER

#include "esp_camera.h"
#include "camera_pins.h"

#define FLASH_LED_PIN 4

String serverName = "192.168.0.248";
String serverPath = "/ESP32CAM/upload_img.php";
const int serverPort = 80;

WiFiClient client;
WebServer server(80);

bool isCameraActive = false;
Servo myServo;
int servoPin = 13;
int servoPos = 0;

Ticker servoTicker;
Ticker midnightTicker;
int moveDuration = 0;
int setHour = -1;
int setMinute = -1;
bool moveServo = false;
Ticker servoReturnTicker;

WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", -3 * 3600, 60000); // GMT-3 for Buenos Aires, 1-minute update interval

// Definir estructura para los horarios programados
struct Schedule {
  int hour;
  int minute;
  bool activated;
};

// Arreglo para almacenar múltiples horarios programados
const int MAX_SCHEDULES = 5;  // Ajustar según la cantidad deseada
Schedule schedules[MAX_SCHEDULES];


void sendFrameToServer(camera_fb_t * fb) {
  if (!fb) {
    Serial.println("Camera capture failed: Frame buffer is null");
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
    Serial.println("Error: " + String(client.getWriteError()));
    Serial.print("Free heap: ");
    Serial.println(esp_get_free_heap_size());
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
    int delayTime = server.arg("delay").toInt() * 1000;
    
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

void moveServoAutomatically() {
  if (moveServo) {
    servoPos += 90;
    if (servoPos > 180) servoPos = 180;
    myServo.write(servoPos);
    Serial.println("Servo moved automatically");

    delay(moveDuration);

    servoPos -= 90;
    if (servoPos < 0) servoPos = 0;
    myServo.write(servoPos);
    Serial.println("Servo returned automatically");

    moveServo = false;
  }
}

void checkServoSchedule() {

  timeClient.update(); // Actualizar la hora

  int currentHour = timeClient.getHours();

  int currentMinute = timeClient.getMinutes();

  Serial.printf("Current Time: %02d:%02d\n", currentHour, currentMinute);

  for (int i = 0; i < MAX_SCHEDULES; i++) {

    Serial.printf("Checking Schedule %d: %02d:%02d, activated: %d\n", i, schedules[i].hour, schedules[i].minute, schedules[i].activated);

    if (schedules[i].hour == currentHour && schedules[i].minute == currentMinute && !schedules[i].activated) {

      moveServo = true;

      schedules[i].activated = true; // Marcar como activado para este horario

      Serial.println("Schedule Time");

      break; // Solo activamos una vez por horario

    }

  }

}

void handleSetServoSchedule() {

  if (server.hasArg("hora") && server.hasArg("minuto") && server.hasArg("duracion")) {

    setHour = server.arg("hora").toInt();

    setMinute = server.arg("minuto").toInt();

    moveDuration = server.arg("duracion").toInt() * 1000;

    // Buscar un espacio libre en el arreglo de horarios para almacenar el nuevo horario

    for (int i = 0; i < MAX_SCHEDULES; i++) {

      if (schedules[i].hour == -1 && schedules[i].minute == -1) {

        schedules[i].hour = setHour;

        schedules[i].minute = setMinute;

        schedules[i].activated = false; // Inicializar como no activado

        saveSchedules();

        break;

      }

    }

    server.send(200, "text/plain", "Servo schedule set");

    Serial.printf("Servo schedule set to %02d:%02d for %d seconds\n", setHour, setMinute, moveDuration / 1000);

  } else {

    server.send(400, "text/plain", "Missing parameters");

  }

}

void handleRemoveServoSchedule() {
  if (server.hasArg("hora") && server.hasArg("minuto")) {
    int removeHour = server.arg("hora").toInt();
    int removeMinute = server.arg("minuto").toInt();

    bool scheduleFound = false;

    // Buscar y eliminar el horario especificado
    for (int i = 0; i < MAX_SCHEDULES; i++) {
      if (schedules[i].hour == removeHour && schedules[i].minute == removeMinute) {
        schedules[i].hour = -1;
        schedules[i].minute = -1;
        schedules[i].activated = false;
        scheduleFound = true;
        saveSchedules();
        break;
      }
    }

    if (scheduleFound) {
      server.send(200, "text/plain", "Servo schedule removed");
      Serial.printf("Servo schedule removed: %02d:%02d\n", removeHour, removeMinute);
    } else {
      server.send(404, "text/plain", "Schedule not found");
      Serial.printf("Schedule not found: %02d:%02d\n", removeHour, removeMinute);
    }
  } else {
    server.send(400, "text/plain", "Missing parameters");
  }
}

void saveSchedules() {
  preferences.begin("schedules", false);
  for (int i = 0; i < MAX_SCHEDULES; i++) {
    String keyHour = "hour" + String(i);
    String keyMinute = "minute" + String(i);
    String keyActivated = "activated" + String(i);
    preferences.putInt(keyHour.c_str(), schedules[i].hour);
    preferences.putInt(keyMinute.c_str(), schedules[i].minute);
    preferences.putBool(keyActivated.c_str(), schedules[i].activated);
  }
  preferences.end();
}

void loadSchedules() {
  preferences.begin("schedules", true);
  for (int i = 0; i < MAX_SCHEDULES; i++) {
    String keyHour = "hour" + String(i);
    String keyMinute = "minute" + String(i);
    String keyActivated = "activated" + String(i);
    schedules[i].hour = preferences.getInt(keyHour.c_str(), -1);
    schedules[i].minute = preferences.getInt(keyMinute.c_str(), -1);
    schedules[i].activated = preferences.getBool(keyActivated.c_str(), false);
  }
  preferences.end();
}


void handleRoot() {
  String message = "Use /start to start the camera, /stop to stop the camera, /servoLeft to move servo left, /servoRight to move servo right, /setServoSchedule to schedule servo movement, /removeServoSchedule to remove a scheduled servo movement";
  server.send(200, "text/plain", message);
}

void checkMidnight() {
  timeClient.update();
  int currentHour = timeClient.getHours();
  int currentMinute = timeClient.getMinutes();

  if (currentHour == 0 && currentMinute == 0) {  // Es medianoche
    for (int i = 0; i < MAX_SCHEDULES; i++) {
      schedules[i].activated = false;
    }
    Serial.println("All schedules reset at midnight");
  }
}


void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  Serial.begin(115200);
  Serial.println();

  pinMode(FLASH_LED_PIN, OUTPUT);

  WiFiManager wifiManager;

  if (!wifiManager.autoConnect("ESP32-CAM-AP")) {
    Serial.println("Failed to connect and hit timeout");
    delay(3000);
    ESP.restart();
  }
  Serial.println("Connected to WiFi!");
  Serial.println(WiFi.localIP());

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
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 20;  
    config.fb_count = 1;
  } else {
    config.frame_size = FRAMESIZE_SVGA;
    config.jpeg_quality = 20; 
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

  myServo.setPeriodHertz(50); 
  myServo.attach(servoPin, 500, 2400); 
  myServo.write(servoPos); 

  server.on("/", handleRoot);
  server.on("/start", handleStartCamera);
  server.on("/stop", handleStopCamera);
  server.on("/servoLeft", handleServoLeft);
  server.on("/servoRight", handleServoRight);
  server.on("/servoAction", handleServoAction);
  server.on("/setServoSchedule", handleSetServoSchedule);
  server.on("/removeServoSchedule", handleRemoveServoSchedule);

  server.begin();
  Serial.println("HTTP server started");

  // Initialize schedules
  for (int i = 0; i < MAX_SCHEDULES; i++) {
    schedules[i].hour = -1;
    schedules[i].minute = -1;
    schedules[i].activated = false;
  }

  loadSchedules();

  servoTicker.attach(5, checkServoSchedule); 
  midnightTicker.attach(60, checkMidnight); // Chequear medianoche cada minuto
}

void loop() {
  server.handleClient();

  if (isCameraActive) {
    camera_fb_t * fb = esp_camera_fb_get();
    if (!fb) {
      Serial.println("Camera capture failed: esp_camera_fb_get returned NULL");
    } else {
      sendFrameToServer(fb);
    }
    delay(33);
  }

  moveServoAutomatically();
}

