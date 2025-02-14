#include <WiFi.h>          // สำหรับ ESP32
// #include <ESP8266WiFi.h> // สำหรับ ESP8266
#include <HTTPClient.h>
#include <ArduinoJson.h>
#include <DHT.h>

// กำหนดค่าการเชื่อมต่อ WiFi
const char* ssid = "Beam";
const char* password = "16022003";

// กำหนดค่า API
const String API_URL = "https://botxai.com/ECP4N/07_iot/smart-home/api";
const String API_KEY = "y1xneVWfBv";

// กำหนดขาพินเซนเซอร์
#define DHTPIN 4
#define DHTTYPE DHT11
#define LDR_PIN 34
#define PIR_VR_PIN 35
#define SOUND_VR_PIN 32

// กำหนดขาพิน LED
#define LED1 25   // ไฟนอกบ้าน (เขียว)
#define LED2 26   // ไฟในบ้าน (เขียว)
#define LED3 27   // พัดลม (แดง)

DHT dht(DHTPIN, DHTTYPE);

void setup() {
  Serial.begin(115200);
  pinMode(LED1, OUTPUT);
  pinMode(LED2, OUTPUT);
  pinMode(LED3, OUTPUT);
  dht.begin();

  // เชื่อมต่อ WiFi
  connectWiFi();
}

void loop() {
  // อ่านค่าจากเซนเซอร์
  float temp = dht.readTemperature();
  float humidity = dht.readHumidity();
  int ldrValue = analogRead(LDR_PIN);
  int pirValue = analogRead(PIR_VR_PIN);
  int soundValue = analogRead(SOUND_VR_PIN);

  // ตรวจสอบว่าเซนเซอร์สามารถอ่านค่าได้หรือไม่
  if (isnan(temp) || isnan(humidity)) {
    Serial.println("Error reading from DHT sensor!");
    return;
  }

  // แสดงค่าจากเซนเซอร์
  Serial.print("Temperature: ");
  Serial.print(temp);
  Serial.println(" C");
  Serial.print("Humidity: ");
  Serial.print(humidity);
  Serial.println(" %");
  Serial.print("LDR Value: ");
  Serial.println(ldrValue);
  Serial.print("PIR Value: ");
  Serial.println(pirValue);
  Serial.print("Sound Value: ");
  Serial.println(soundValue);

  // ส่งข้อมูลเซนเซอร์ไปยัง API
  sendSensorData(temp, humidity, ldrValue, pirValue, soundValue);

  // ดึงสถานะอุปกรณ์จาก API
  updateDeviceStatus();

  delay(10000); // อัพเดททุก 10 วินาที
}

// ฟังก์ชันเชื่อมต่อ WiFi
void connectWiFi() {
  Serial.print("Connecting to WiFi...");
  WiFi.begin(ssid, password);
  
  unsigned long startAttemptTime = millis();
  while (WiFi.status() != WL_CONNECTED) {
    if (millis() - startAttemptTime >= 10000) {
      Serial.println("Failed to connect to WiFi. Retrying...");
      WiFi.begin(ssid, password);  // Retry connecting
      startAttemptTime = millis();
    }
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi");
}

// ฟังก์ชันส่งข้อมูลเซนเซอร์ไปยัง API
void sendSensorData(float temp, float humidity, int ldr, int pir, int sound) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = API_URL + "/sensors.php?api_key=" + API_KEY;
    
    http.begin(url);
    http.addHeader("Content-Type", "application/json");

    // สร้าง JSON payload
    StaticJsonDocument<200> doc;
    doc["temperature"] = temp;
    doc["humidity"] = humidity;
    doc["light_level"] = ldr;
    doc["motion_detected"] = pir;
    doc["sound_level"] = sound;

    String payload;
    serializeJson(doc, payload);

    int httpCode = http.POST(payload);
    
    if (httpCode == HTTP_CODE_OK) {
      Serial.println("Sensor data sent successfully");
    } else {
      Serial.print("Error sending sensor data, HTTP Code: ");
      Serial.println(httpCode);
    }
    http.end();
  } else {
    Serial.println("WiFi not connected, unable to send data.");
  }
}

// ฟังก์ชันดึงสถานะอุปกรณ์จาก API
void updateDeviceStatus() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    String url = API_URL + "/devices.php?api_key=" + API_KEY;
    
    http.begin(url);
    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
      String payload = http.getString();
      StaticJsonDocument<200> doc;
      deserializeJson(doc, payload);

      // แสดงข้อมูลสถานะที่ได้รับจาก API
      Serial.println("Device status update:");
      for (JsonObject device : doc["data"].as<JsonArray>()) {
        int id = device["id"];
        int status = device["status"];
        
        Serial.print("Device ID: ");
        Serial.print(id);
        Serial.print(" Status: ");
        Serial.println(status);
        
        // อัพเดทสถานะ LED
        switch(id) {
          case 1: digitalWrite(LED1, status); break;
          case 2: digitalWrite(LED2, status); break;
          case 3: digitalWrite(LED3, status); break;
        }
      }
    } else {
      Serial.print("Error fetching device status, HTTP Code: ");
      Serial.println(httpCode);
    }
    http.end();
  } else {
    Serial.println("WiFi not connected, unable to update device status.");
  }
}
