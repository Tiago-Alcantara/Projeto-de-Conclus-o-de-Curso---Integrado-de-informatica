/bibliotecas
#include<Wire.h>
#include <Ethernet.h>
#include <MySQL_Connection.h>
#include <MySQL_Cursor.h>
#include <SPI.h>
#include <ArduinoUniqueID.h>

String idar;
char sentenca[128];
//BANCO DE DADOS
byte mac_addr[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED };
IPAddress server_addr(52, 67, 231, 97);
char user[] = "segredo";
char password[] = "segredo";

char INSERIR_TEMP[] = "INSERT INTO recebe (Id, Arduinoid, Tempo) VALUES (NULL,'%d', CURRENT_TIMESTAMP())";
char BANCODEDADOS[] = "USE sql10358106";
EthernetClient client;
MySQL_Connection conn((Client *)&client);
//declaraçao de pinos
const int MPU = 0x68;
const int botao = 6;
const int Buzzer = 8;
//Variaveis para armazenar e medir a sensibilidade
int AcX, temp1, sensi, statu;
int contador = 0;
void setup()
{
  Serial.begin(9600);
  Wire.begin();
  Wire.beginTransmission(MPU);
  Wire.write(0x6B);

  //Inicializa o MPU-6050
  Wire.write(0);
  Wire.endTransmission(true);
  pinMode(botao, INPUT);
  pinMode(Buzzer, OUTPUT);
  int statu = 0;

  for (size_t i = 0; i < 8; i++)
  {
    if (UniqueID8[i] < 0x10)
      idar += UniqueID8[i], HEX;
    idar += " ";
  }
  Ethernet.begin(mac_addr);
  if (conn.connect(server_addr, 3306, user, password))
  {
    delay(1000);
    MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
    cur_mem->execute(BANCODEDADOS);
    delete cur_mem;
  }
  else
  {
    Serial.println("A conexão falhou");
    conn.close();
  }
}
void loop()
{
  int temp1 = AcX;
  Wire.beginTransmission(MPU);
  Wire.write(0x3B);
  Wire.endTransmission(false);

  //Solicita os dados do sensor
  Wire.requestFrom(MPU, 14, true);

  //armazena o valor do sensor
  AcX = Wire.read() << 8 | Wire.read(); //0x3B (ACCEL_XOUT_H) & 0x3C (ACCEL_XOUT_L)

  delay(1000);
  //começa a parte do sensor de queda
  int sensi = AcX - temp1;
  digitalWrite(Buzzer, LOW);
  if (statu == 1 && contador < 5) {
    digitalWrite(botao, LOW);
    Serial.println("\tacidente em andamento");
    if (digitalRead(botao) == HIGH) {
      delay (500);
      statu = 0;
      contador = 0;
    } else {
      digitalWrite(Buzzer, HIGH);
      contador++;
    }
  } else if (sensi > 1000 || sensi < -1000) {
    Serial.println(" \tpossivel acidente ");
    statu = 1;
  } else if (contador == 5) {
    Serial.println("Executando sentença");
    sprintf(sentenca, INSERIR_TEMP, idar);

    MySQL_Cursor *cur_mem = new MySQL_Cursor(&conn);
    cur_mem->execute(sentenca);
    delete cur_mem;
    statu = 2;
    contador = 0; 
  } else if (statu == 2) {
    Serial.println("\tacidente");
  } else {
    Serial.println("\tta tudo tranquilo");
  }
  delay(1000);
}