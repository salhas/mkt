# 📱 Dokumentasi RESTful API - Yayasan MKT (Mitra Kemanusiaan Terpadu)

Dokumentasi ini berisi panduan integrasi RESTful API backend **Laravel 13** dengan aplikasi mobile **Flutter** untuk ekosistem penanggulangan bencana, relawan donor darah, tim rescue, dan transparansi donasi kemanusiaan MKT.

---

## 🌐 Konfigurasi Dasar (Base Specification)

- **Base URL**: `http://localhost:8000/api/v1/` atau `http://YOUR_SERVER_IP/api/v1/`
- **Format Data**: JSON (`application/json`)
- **Autentikasi**: Laravel Sanctum Bearer Token (`Authorization: Bearer <access_token>`)

---

## 🔑 Header Standar HTTP Request

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <access_token>
```

---

## 📋 Ringkasan Daftar Endpoints

| Modul | Method | Endpoint | Fungsi | Akses |
|---|---|---|---|---|
| **Auth** | `POST` | `/api/v1/auth/login` | Login akun pengguna / relawan | Publik |
| **Auth** | `POST` | `/api/v1/auth/register` | Pendaftaran akun pengguna baru | Publik |
| **Auth** | `GET` | `/api/v1/auth/me` | Mengambil profil user aktif | `Bearer Token` |
| **Auth** | `POST` | `/api/v1/auth/logout` | Logout & cabut token Sanctum | `Bearer Token` |
| **Profil MKT** | `GET` | `/api/v1/mkt-profile` | Profil resmi, visi-misi, hotline, & bank | Publik |
| **Mitra Lembaga** | `GET` | `/api/v1/partners` | Daftar mitra (PMI, Basarnas, BPBD, RS, Rescue) | Publik |
| **Mitra Lembaga** | `GET` | `/api/v1/partners/{id}` | Rincian detail instansi mitra & personel | Publik |
| **Relawan** | `GET` | `/api/v1/volunteers` | Daftar anggota & relawan personel | Publik |
| **Relawan** | `GET` | `/api/v1/volunteers/{id}` | Rincian profil & KTA digital relawan | Publik |
| **Relawan** | `POST` | `/api/v1/volunteers/register` | Pendaftaran relawan dari Flutter | Publik |
| **Peta Bencana** | `GET` | `/api/v1/disaster-events` | Data lokasi & koordinat bencana real-time | Publik |
| **Peta Bencana** | `GET` | `/api/v1/disaster-events/{id}`| Detail insiden bencana & tim rescue | Publik |
| **Peta Bencana** | `POST` | `/api/v1/disaster-events` | Melaporkan kejadian bencana dari lapangan | `Bearer Token` |
| **Peringatan & Siaga**| `GET` | `/api/v1/alerts/live` | Feed notifikasi agregat darurat real-time (SAR, Bencana, Logistik, Donasi) | Publik |
| **Operasi & Siaga SAR** | `GET` | `/api/v1/sar-operations` | List Operasi & Siaga SAR (Respon Musibah / Kesiapsiagaan) | Publik |
| **Operasi & Siaga SAR** | `GET` | `/api/v1/sar-operations/{id}`| Detail giat Operasi SAR, Komandan, & Alat | Publik |
| **Operasi & Siaga SAR** | `POST` | `/api/v1/sar-operations` | Tambah / Lapor Operasi SAR dari Lapangan | `Bearer Token` |
| **Donasi** | `GET` | `/api/v1/donations` | Feed riwayat penerimaan donasi publik | Publik |
| **Donasi** | `POST` | `/api/v1/donations` | Kirim donasi baru dari aplikasi mobile | Publik |
| **Logistik** | `GET` | `/api/v1/logistics` | Stok bantuan logistik darurat bencana | Publik |
| **Struktur MKT** | `GET` | `/api/v1/management` | Data Struktur Bagan Organisasi MKT | Publik |

---

## 📑 Detail Payload & Contoh Response JSON

### 1. Autentikasi (`POST /api/v1/auth/login`)

#### Request Body:
```json
{
  "email": "relawan@mkt.or.id",
  "password": "password123",
  "device_name": "Flutter_Android_Device"
}
```

#### Response (200 OK):
```json
{
  "success": true,
  "message": "Login berhasil.",
  "access_token": "1|sanctum_token_string_here...",
  "token_type": "Bearer",
  "user": {
    "id": 4,
    "name": "Tim Rescue & Relawan",
    "email": "relawan@mkt.or.id",
    "role": "relawan"
  }
}
```

---

### 2. Profil Lembaga MKT (`GET /api/v1/mkt-profile`)

#### Response (200 OK):
```json
{
  "success": true,
  "data": {
    "name": "Yayasan MKT Indonesia (Mitra Kemanusiaan Terpadu)",
    "description": "Yayasan amal sosial kemanusiaan yang berfokus pada penghimpunan donasi publik...",
    "address": "Perumahan Insignia Oasis Blok B1-11 No 7, Kota Makassar, Sulawesi Selatan",
    "phone": "+62 812-3456-7890",
    "email": "info@mkt.or.id",
    "vision": "Menjadi lembaga kemanusiaan terdepan...",
    "mission": "1. Menyelenggarakan donasi publik transparan...",
    "bank_accounts": [
      {
        "bank": "Bank Syariah Indonesia (BSI)",
        "account_number": "777-888-999-1",
        "account_name": "Yayasan MKT - Donasi Kemanusiaan"
      }
    ]
  }
}
```

---

### 3. Instansi Mitra Lembaga (`GET /api/v1/partners`)

#### Query Parameters (Opsional):
- `category`: `PMI` | `Basarnas` | `BPBD` | `Rumah Sakit` | `Tim Rescue`
- `search`: Kata kunci nama mitra / PIC

#### Response (200 OK):
```json
{
  "success": true,
  "count": 5,
  "data": [
    {
      "id": 1,
      "code": "MTR-PMI-001",
      "name": "Palang Merah Indonesia (PMI) Kota Makassar",
      "category": "PMI",
      "pic_name": "Dr. H. Syamsul Rizal, S.E., M.Si.",
      "pic_phone": "08114455667",
      "phone": "0411-855123",
      "status": "Aktif",
      "personnel_count": 120,
      "volunteers_count": 4
    }
  ]
}
```

---

### 4. Peta Operasi Bencana (`GET /api/v1/disaster-events`)

#### Response (200 OK):
```json
{
  "success": true,
  "count": 2,
  "data": [
    {
      "id": 1,
      "title": "Banjir Luapan Sungai Ciliwung",
      "category": "Banjir",
      "location": "Kampung Melayu, Jakarta Timur",
      "latitude": -6.2244,
      "longitude": 106.8622,
      "severity": "Tinggi",
      "status": "Evakuasi",
      "victim_count": 150,
      "rescue_team_leader": "Ahmad Roni",
      "date_occurred": "2026-07-10"
    }
  ]
}
```

---

### 5. Melaporkan Bencana Darurat (`POST /api/v1/disaster-events`)
*(Memerlukan Header `Authorization: Bearer <token>`)*

#### Request Body:
```json
{
  "title": "Banjir Bandang & Tanah Longsor",
  "category": "Banjir",
  "location": "Kecamatan Bantaeng, Sulsel",
  "latitude": -5.5492,
  "longitude": 119.9542,
  "severity": "Kritis",
  "status": "Tanggap Darurat",
  "victim_count": 80,
  "rescue_team_leader": "Kapten Hendra Suwandi",
  "date_occurred": "2026-07-30",
  "description": "Butuh bantuan perahu karet & tim medis lapangan segera."
}
```

---

## 💻 Contoh Pengkodean Integrasi di Flutter (Dart)

### Auth Service (`auth_service.dart`)

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

class MktApiService {
  static const String baseUrl = 'http://YOUR_SERVER_IP/api/v1';

  // Login Request
  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
        'device_name': 'Flutter_App_Device',
      }),
    );

    return jsonDecode(response.body);
  }

  // Fetch Disaster Map Events
  static Future<List<dynamic>> fetchDisasterEvents() async {
    final response = await http.get(
      Uri.parse('$baseUrl/disaster-events'),
      headers: {'Accept': 'application/json'},
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return data['data'];
    }
    return [];
  }

  // Report Disaster Incident (Protected Route)
  static Future<bool> reportIncident(String token, Map<String, dynamic> payload) async {
    final response = await http.post(
      Uri.parse('$baseUrl/disaster-events'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode(payload),
    );

    return response.statusCode == 201;
  }
}
```

---

*Dokumentasi ini diterbitkan oleh Tim Pengembangan Sistem Informasi Yayasan MKT.*  
*Hak Cipta © 2026 Yayasan Mitra Kemanusiaan Terpadu (MKT).*
