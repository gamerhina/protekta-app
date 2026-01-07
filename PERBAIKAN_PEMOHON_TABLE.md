# Perbaikan Field Pemohon pada Multi-Row Table

## Masalah
Nama pemohon pada field tabel multi-baris tidak bisa disimpan karena:
1. Frontend hanya me-render input text biasa untuk semua kolom, termasuk kolom pemohon
2. Backend tidak memiliki logika untuk memproses data pemohon yang ada di dalam tabel

## Solusi yang Diterapkan

### 1. Frontend (create.blade.php)

#### A. Render Kolom Pemohon sebagai Select Dropdown
- Menambahkan fungsi helper `renderTableCell()` yang mendeteksi tipe kolom
- Jika kolom bertipe 'pemohon', render sebagai:
  - Select dropdown dengan opsi mahasiswa/dosen
  - 2 hidden inputs untuk menyimpan `type` dan `id`
- Jika kolom bertipe lain, render sebagai input text biasa

#### B. Wire Event Listeners untuk Pemohon Select
- Menambahkan event listener pada setiap pemohon select
- Ketika select berubah, otomatis update hidden inputs (type dan id)
- Diterapkan pada:
  - Baris pertama (saat tabel di-render)
  - Baris baru (saat tombol "Tambah Baris" diklik)

#### C. Update Fungsi addTableRow
- Menggunakan data kolom asli (dengan informasi tipe)
- Membuat cell berdasarkan tipe kolom
- Wire pemohon selects pada baris baru

#### D. Update Fungsi reindexTableRows
- Meng-update name attribute untuk input DAN select
- Memastikan indexing tetap konsisten setelah hapus baris

### 2. Backend (AdminSuratController.php)

#### A. Validasi Kolom Pemohon di Tabel
- Mendeteksi kolom dengan tipe 'pemohon'
- Menambahkan validation rules untuk struktur nested:
  ```php
  "form_data.$key.*.$colKey" => 'nullable|array'
  "form_data.$key.*.$colKey.type" => 'nullable|in:mahasiswa,dosen'
  "form_data.$key.*.$colKey.id" => 'nullable|integer|min:1'
  ```

## Struktur Data yang Dikirim

### Contoh Form Data:
```javascript
form_data[anggota_tim][0][nama_pemohon][type] = "mahasiswa"
form_data[anggota_tim][0][nama_pemohon][id] = "123"
form_data[anggota_tim][0][peran] = "Ketua"

form_data[anggota_tim][1][nama_pemohon][type] = "dosen"
form_data[anggota_tim][1][nama_pemohon][id] = "456"
form_data[anggota_tim][1][peran] = "Pembimbing"
```

### Struktur PHP Array:
```php
$data['anggota_tim'] = [
    0 => [
        'nama_pemohon' => [
            'type' => 'mahasiswa',
            'id' => 123
        ],
        'peran' => 'Ketua'
    ],
    1 => [
        'nama_pemohon' => [
            'type' => 'dosen',
            'id' => 456
        ],
        'peran' => 'Pembimbing'
    ]
];
```

## Cara Testing

1. Buat jenis surat dengan field tabel
2. Tambahkan kolom dengan tipe 'pemohon'
3. Buat surat baru
4. Pilih jenis surat tersebut
5. Isi tabel dengan memilih pemohon dari dropdown
6. Tambah beberapa baris
7. Submit form
8. Periksa data tersimpan dengan benar di database

## File yang Dimodifikasi

1. `resources/views/admin/surat/create.blade.php`
   - Fungsi `renderTableCell()` - baris ~235-270
   - Fungsi `addTableRow_${key}()` - baris ~280-315
   - Fungsi `reindexTableRows_${key}()` - baris ~318-330
   - Wire pemohon selects - baris ~420-437

2. `app/Http/Controllers/AdminSuratController.php`
   - Validasi table field - baris ~208-235

## Catatan Penting

- Kolom pemohon di tabel menggunakan struktur nested (type + id)
- Data tersimpan di kolom `data` (JSON) di tabel `surats`
- Validasi memastikan type hanya 'mahasiswa' atau 'dosen'
- ID harus integer positif
- Semua field di tabel bersifat nullable untuk fleksibilitas
