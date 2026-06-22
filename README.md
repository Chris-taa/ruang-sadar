# 🌿 RuangSadar Backend API

RuangSadar adalah aplikasi _mindfulness journal_ digital yang dirancang untuk membantu pengguna melacak _mood_ dan mengelola kesehatan mental melalui pencatatan jurnal harian. Repositori ini berisi sistem _backend_ API yang dibangun menggunakan framework Laravel untuk melayani pertukaran data dengan aplikasi _mobile_ RuangSadar (Android).

## 📚 API Documentation

Seluruh _endpoint_ API RuangSadar telah didokumentasikan secara interaktif menggunakan OpenAPI (Swagger). Dokumentasi ini memuat detail lengkap _request_, parameter, _body_, dan format _response_ yang dibutuhkan oleh tim _frontend/mobile_.

Kamu bisa melihat dan melakukan _testing_ API secara langsung melalui tautan berikut:

👉 **[RuangSadar API Documentation (Swagger)](https://ruang-sadar-production.up.railway.app/api/documentation)**

## ✨ Fitur Utama

- **Otentikasi Aman:** Sistem _login_ dan registrasi menggunakan token akses (Laravel Sanctum), serta dukungan **Login with Google**.
- **Mindfulness Journaling:** Sistem CRUD (Create, Read, Update, Delete) untuk catatan jurnal harian, lengkap dengan parameter _mood_, penyebab (_cause_), dan isi jurnal (_contain_).
- **Calendar Integration:** _Endpoint_ khusus (`/dates/entries`) untuk mendeteksi rekam jejak jurnal pengguna, yang difungsikan sebagai _marker dot_ pada UI kalender di aplikasi _mobile_.
- **Keamanan Data:** Proteksi _Mass Assignment_ dan relasi _database_ yang ketat antar pengguna.

## 🚀 Tech Stack

- **Framework Backend:** Laravel (PHP)
- **API Documentation:** L5-Swagger (PHP Attributes)
- **Authentication:** Laravel Sanctum & Google API Client
- **Deployment & Hosting:** Railway App

---

_Dikembangkan untuk menunjang project aplikasi mobile RuangSadar._
