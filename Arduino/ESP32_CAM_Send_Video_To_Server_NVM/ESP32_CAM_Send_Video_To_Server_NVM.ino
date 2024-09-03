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
String serverPath = "/Proyecto/ESP/Camara/upload_img.php";
const int serverPort = 80;

WiFiClient client;
WebServer server(80);

bool isCameraActive = false;
Servo myServo;
int servoPin = 12;
int servoPos = 160;

#define LED_RED_PIN 13
#define LED_GREEN_PIN 15
#define LED_BLUE_PIN 14

Ticker servoTicker;
Ticker midnightTicker;
Ticker pingTicker;
float moveDuration = 0.0;
int setHour = -1;
int setMinute = -1;
bool moveServo = false;
Ticker servoReturnTicker;

//Determina la zona horaria
WiFiUDP ntpUDP;
NTPClient timeClient(ntpUDP, "pool.ntp.org", -3 * 3600, 60000);  // GMT-3 for Buenos Aires, 1-minute update interval

// Definir estructura para los horarios programados
struct Schedule {
  int hour;
  int minute;
  bool activated;
};

// Arreglo para almacenar múltiples horarios programados
const int MAX_SCHEDULES = 5;  // Ajustar según la cantidad deseada
Schedule schedules[MAX_SCHEDULES];

//Función para controlar los leds RGB
void setRGBColor(int red, int green, int blue) {
  analogWrite(LED_RED_PIN, red);
  analogWrite(LED_GREEN_PIN, green);
  analogWrite(LED_BLUE_PIN, blue);
}


//Envía los frames al servidor

void sendFrameToServer(camera_fb_t *fb) {
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
  setRGBColor(255, 0, 0);  // LED rojo
}

void handleStopCamera() {
  isCameraActive = false;
  server.send(200, "text/plain", "Camera stopped");
  Serial.println("Camera stopped");
  setRGBColor(0, 255, 0);  // LED verde
}


void handleServoLeft() {
  if (myServo.attached()) {
    servoPos -= 10;
    myServo.write(servoPos);
    server.send(200, "text/plain", "Servo moved left");
    Serial.println("Servo moved left");
    Serial.println(servoPos);
  } else {
    Serial.println("Servo not attached!");
  }
}

void handleServoRight() {
  if (myServo.attached()) {
    servoPos += 10;
    myServo.write(servoPos);
    server.send(200, "text/plain", "Servo moved right");
    Serial.println("Servo moved right");
    Serial.println(servoPos);
  } else {
    Serial.println("Servo not attached!");
  }
}

//Se activa únicamente cuando el usuario toca el botón de activar. Mueve el servo

void handleServoAction() {
  if (myServo.attached()) {
    int delayTime = moveDuration;

    if (delayTime > 0) {
      servoPos -= 60;
      //if (servoPos > 180) servoPos = 180;
      myServo.write(servoPos);
      server.send(200, "text/plain", "Servo moved right with delay");
      Serial.println("Servo moved right");
      Serial.println(servoPos);

      delay(delayTime);

      servoPos += 60;
      //if (servoPos < 0) servoPos = 0;
      myServo.write(servoPos);
      server.send(200, "text/plain", "Servo moved left after delay");
      Serial.println("Servo moved left");
      Serial.println(servoPos);
    }
  } else {
    Serial.println("Servo not attached or missing delay parameter!");
  }
}

//Mueve el servo la cantidad de tiempo querida cuando la variable moveServo es true

void moveServoAutomatically() {
  if (myServo.attached() && moveServo) {
    servoPos -= 60;
    myServo.write(servoPos);
    Serial.println("Servo moved automatically");
    Serial.println(servoPos);

    delay(moveDuration);

    servoPos += 60;
    myServo.write(servoPos);
    Serial.println("Servo returned automatically");
    Serial.println(servoPos);

    moveServo = false;
  }
}


//Revisa si los horarios coinciden con la hora real y modifica la variable moveServo

void checkServoSchedule() {
  if (myServo.attached()) {
    timeClient.update();  // Actualizar la hora

    int currentHour = timeClient.getHours();
    int currentMinute = timeClient.getMinutes();

    Serial.printf("Current Time: %02d:%02d\n", currentHour, currentMinute);

    for (int i = 0; i < MAX_SCHEDULES; i++) {
      Serial.printf("Checking Schedule %d: %02d:%02d, activated: %d\n", i, schedules[i].hour, schedules[i].minute, schedules[i].activated);

      if (schedules[i].hour == currentHour && schedules[i].minute == currentMinute && !schedules[i].activated) {
        moveServo = true;
        schedules[i].activated = true;  // Marcar como activado para este horario
        preferences.begin("schedules", false);

        //Modifica el estado de los horarios de la MNV (memoria no volátil)
        String keyActivated = "activated" + String(i);
        preferences.putBool(keyActivated.c_str(), true);

        preferences.end();
        Serial.println("Schedule Time");
        break;  // Solo activamos una vez por horario
      }
    }
  } else {
    Serial.println("Servo not attached!");
  }
}


//Agrega horarios recibidos mediante peticiones

void handleSetServoSchedule() {
  if (server.hasArg("hora") && server.hasArg("minuto")) {
    setHour = server.arg("hora").toInt();
    setMinute = server.arg("minuto").toInt();

    // Buscar un espacio libre en el arreglo de horarios para almacenar el nuevo horario
    for (int i = 0; i < MAX_SCHEDULES; i++) {
      if (schedules[i].hour == -1 && schedules[i].minute == -1) {
        schedules[i].hour = setHour;
        schedules[i].minute = setMinute;
        schedules[i].activated = false;  // Inicializar como no activado
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

// Buscar y eliminar el horario especificado
void handleRemoveServoSchedule() {
  if (server.hasArg("hora") && server.hasArg("minuto")) {
    int removeHour = server.arg("hora").toInt();
    int removeMinute = server.arg("minuto").toInt();

    bool scheduleFound = false;

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


//Guarda los horarios en la memoria no volátil
void saveSchedules() {
  preferences.begin("schedules", false);
  for (int i = 0; i < MAX_SCHEDULES; i++) {
    String keyHour = "hour" + String(i);
    String keyMinute = "minute" + String(i);
    String keyActivated = "activated" + String(i);
    preferences.putInt(keyHour.c_str(), schedules[i].hour);
    preferences.putInt(keyMinute.c_str(), schedules[i].minute);
    preferences.putBool(keyActivated.c_str(), schedules[i].activated);
    Serial.printf("Saved Schedule %d: %02d:%02d, activated: %d\n", i, schedules[i].hour, schedules[i].minute, schedules[i].activated);
  }
  preferences.end();
}

//Carga los hoararios almacenados en la memoria no volátil
void loadSchedules() {
  preferences.begin("schedules", true);
  for (int i = 0; i < MAX_SCHEDULES; i++) {
    String keyHour = "hour" + String(i);
    String keyMinute = "minute" + String(i);
    String keyActivated = "activated" + String(i);
    schedules[i].hour = preferences.getInt(keyHour.c_str(), -1);
    schedules[i].minute = preferences.getInt(keyMinute.c_str(), -1);
    schedules[i].activated = preferences.getBool(keyActivated.c_str(), false);
    Serial.printf("Loaded Schedule %d: %02d:%02d, activated: %d\n", i, schedules[i].hour, schedules[i].minute, schedules[i].activated);
  }
  preferences.end();
}


//Almacena en una variable la duración recibida en la petición
void handleSetMoveDuration() {
  if (myServo.attached() && server.hasArg("delay")) {
    moveDuration = server.arg("delay").toFloat() * 1000.0;
    Serial.printf("Servo move duration set to %.3f seconds\n", moveDuration / 1000.0);
    saveDuration();
  }
}

//Guardala duración en la memoria no volátil
void saveDuration() {
  preferences.begin("duration", false);
  String keyDuration = "moveDuration";
  preferences.putFloat(keyDuration.c_str(), moveDuration);
  preferences.end();
}

//Carga la duración almacenada en la memoria no volátil
void loadDuration() {
  preferences.begin("duration", true);
  String keyDuration = "moveDuration";
  moveDuration = preferences.getFloat(keyDuration.c_str(), 0.0);
  preferences.end();
}

//URL Predeterminada
void handleRoot() {
  String message = "Use /start to start the camera, /stop to stop the camera, /servoLeft to move servo left, /servoRight to move servo right, /setServoSchedule to schedule servo movement, /removeServoSchedule to remove a scheduled servo movement";
  server.send(200, "text/plain", message);
}

//Si ya llegó la medianoche, reinicia los horarios para que vuelvan a activarse
void checkMidnight() {
  timeClient.update();
  int currentHour = timeClient.getHours();
  if (currentHour == 0) {  // Es medianoche
    for (int i = 0; i < MAX_SCHEDULES; i++) {
      schedules[i].activated = false;
    }
    Serial.println("All schedules reset at midnight");
  }
}


//En desuso
void sendPingToServer() {
  if (client.connect(serverName.c_str(), serverPort)) {
    client.println("GET /Proyecto/ESP/ping/ping.php HTTP/1.1");
    client.println("Host: " + serverName);
    client.println("Connection: close");
    client.println();
  } else {
    Serial.println("Failed to send ping");
  }
}


void setup() {
  WRITE_PERI_REG(RTC_CNTL_BROWN_OUT_REG, 0);

  Serial.begin(115200);
  Serial.println();

  //Configuración inicial del LED RGB
  pinMode(LED_RED_PIN, OUTPUT);
  pinMode(LED_GREEN_PIN, OUTPUT);
  pinMode(LED_BLUE_PIN, OUTPUT);

  setRGBColor(0, 0, 255);  // LED azul durante la configuración


  pinMode(FLASH_LED_PIN, OUTPUT);


  //Configuración de WiFiManager para que se conecte automáticamente a las redes guardadas, de lo contrario crea un AP desde el cual el usuario ingresa las credenciales
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


  //Configura pines para el modelo de cámara AI Thinker


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


  //Si queda espacio de caché disponible, aumenta la calidad del video (en desuso)


  if (psramFound()) {
    config.frame_size = FRAMESIZE_HQVGA;
    config.jpeg_quality = 20;
    config.fb_count = 1;
  } else {
    config.frame_size = FRAMESIZE_HQVGA;
    config.jpeg_quality = 20;
    config.fb_count = 1;
  }


  //Tira mensajes de error


  esp_err_t err = esp_camera_init(&config);
  if (err != ESP_OK) {
    Serial.printf("Camera init failed with error 0x%x", err);
    Serial.println();
    Serial.println("Restarting the ESP32 CAM.");
    delay(1000);
    ESP.restart();
  }

  sensor_t *s = esp_camera_sensor_get();
  s->set_framesize(s, FRAMESIZE_HQVGA);

  Serial.println();
  Serial.println("Set camera ESP32 CAM successfully.");

  //Configura el servo y lo mueve a su posición inicial

  myServo.setPeriodHertz(50);
  myServo.attach(servoPin, 500, 2400);
  myServo.write(servoPos);

  //Lista de instrucciones que puede recibir

  server.on("/", handleRoot);
  server.on("/start", handleStartCamera);
  server.on("/stop", handleStopCamera);

  //----------------EN DESUSO----------------------
  server.on("/servoLeft", handleServoLeft);
  server.on("/servoRight", handleServoRight);
  //-----------------------------------------------

  server.on("/servoAction", handleServoAction);
  server.on("/setServoSchedule", handleSetServoSchedule);
  server.on("/removeServoSchedule", handleRemoveServoSchedule);
  server.on("/setMoveDuration", handleSetMoveDuration);

  //Crea su propio servidor web

  server.begin();
  Serial.println("HTTP server started");

 

  // Inicializa los horarios
  for (int i = 0; i < MAX_SCHEDULES; i++) {
    schedules[i].hour = -1;
    schedules[i].minute = -1;
    schedules[i].activated = false;
  }

  //Carga horarios y duraciones ya guardados

  loadSchedules();
  loadDuration();

  //Revisa si ya es hora de dispensar
  checkServoSchedule();

  //Revisa cada 30 segundos o una hora respectivamente si los horarios deben ser revisados o reiniciados
  servoTicker.attach(30, checkServoSchedule);
  midnightTicker.attach(3600, checkMidnight);  // Chequear medianoche cada hora
  //pingTicker.attach(5, sendPingToServer);

  // Encender LED verde después de la configuración
  setRGBColor(0, 255, 0);

}

void loop() {
  server.handleClient();

  if (isCameraActive) {
    camera_fb_t *fb = esp_camera_fb_get();
    if (!fb) {
      Serial.println("Camera capture failed: esp_camera_fb_get returned NULL");
    } else {
      sendFrameToServer(fb);
    }
    delay(33);
  }

  moveServoAutomatically();
}