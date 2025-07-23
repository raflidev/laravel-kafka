# Laravel Kafka

Proyek ini merupakan contoh implementasi Laravel dengan Kafka queue menggunakan Docker.

---

## 🚀 Prasyarat

Sebelum menjalankan proyek ini, pastikan Anda sudah menginstal:

- [Docker](https://www.docker.com/)
- [Docker Compose](https://docs.docker.com/compose/)

---

## ⚙️ Menjalankan Aplikasi

Untuk membangun dan menjalankan seluruh container (Laravel, Kafka, dan dependensi lainnya), jalankan perintah berikut di terminal:

```bash
docker compose up -d --build
```

## 🧵 Menjalankan Kafka Queue Worker

Setelah seluruh container berjalan, jalankan worker Kafka untuk memproses queue dengan nama `messages`:

```bash
docker exec -it laravel-app php artisan queue:work kafka --queue=messages --verbose
```

> **Catatan**: Gantilah `laravel-app` jika nama service Laravel di file `docker-compose.yml` Anda berbeda.

---

## 📬 Mengirim Pesan ke Kafka

Untuk mengirim data ke Kafka, gunakan endpoint berikut:

### Endpoint

```
POST /api/messages
```

### Header

```http
Content-Type: application/json
Accept: application/json
```

### Body (Contoh Payload)

```json
{
  "title": "Test Message",
  "content": "This is a test message"
}
```

---

## 🗂️ Struktur Proyek (Singkat)

Berikut adalah gambaran singkat struktur folder dan file penting dalam proyek ini:

```
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── MessageController.php
│   ├── Jobs/
│   │   └── ProcessKafkaMessage.php
│   └── Kafka/
│       └── CustomKafkaConsumer.php
├── routes/
│   └── api.php
├── docker-compose.yml
└── README.md
```

---

## 📄 Lisensi

Proyek ini dirilis di bawah lisensi [MIT](https://opensource.org/licenses/MIT).
