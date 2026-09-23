# Kontrak: Penahanan Kode oleh Persetujuan Cookie

**Feature**: 023-site-settings | **Date**: 2026-09-23

Kontrak perilaku bilah persetujuan dan penahanan slot kode (FR-050 sampai FR-058, FR-073 sampai FR-075).

---

## §1. Kategori

| Kategori | Dapat ditolak | Perilaku |
| --- | --- | --- |
| Diperlukan agar situs berfungsi | Tidak | Selalu aktif, tidak muncul sebagai pilihan yang dapat dimatikan (FR-052) |
| Analitik | Ya | Slot bertanda `analytics` ditahan sampai disetujui (FR-054) |
| Pemasaran | Ya | Slot bertanda `marketing` ditahan sampai disetujui (FR-054) |

Slot bertanda `none` tidak pernah ditahan.

---

## §2. Keadaan pengunjung

| Keadaan | Bilah tampil | Slot `analytics` / `marketing` |
| --- | --- | --- |
| Persetujuan dimatikan admin | Tidak | Dijalankan seperti biasa (FR-057) |
| Diaktifkan, pengunjung belum memilih | Ya | **Tidak dijalankan** (FR-054) |
| Pengunjung menekan terima | Tidak | Dijalankan |
| Pengunjung menekan tolak | Tidak | Tidak dijalankan |
| Pengunjung menyetujui sebagian kategori | Tidak | Hanya kategori yang disetujui yang dijalankan |
| Pengunjung kembali pada kunjungan berikutnya | Tidak | Mengikuti pilihan tersimpan (FR-055) |

---

## §3. Bentuk bilah

- Tidak menghalangi interaksi; isi halaman tetap dapat digulir, ditekan, dan diisi selama pengunjung belum memutuskan (FR-073).
- Memuat tombol terima seluruh kategori dan tombol tolak seluruh kategori yang dapat ditolak, dengan penonjolan visual setara. Tombol tolak tidak boleh lebih sulit dijangkau daripada tombol terima (FR-074).
- Memuat tautan menuju pengaturan per kategori (FR-074).

---

## §4. Membuka kembali

- Tautan pengaturan cookie berada di footer setiap halaman publik, sebaris dengan tautan legal (FR-056, FR-075).
- Tautan hanya dirender saat persetujuan diaktifkan admin (FR-075).
- Membuka tautan menampilkan kendali per kategori berisi pilihan yang sedang berlaku, dan perubahan langsung berlaku (FR-056).

---

## §5. Mekanisme penahanan

Slot yang terikat kategori dirender ke halaman dalam bentuk yang **tidak dieksekusi peramban** sampai kategorinya disetujui, lalu diaktifkan tanpa memuat ulang halaman.

Konsekuensi yang harus dipenuhi:

- Isi slot tetap dimuat apa adanya sebagai kode saat diaktifkan (FR-044).
- Keluaran halaman tidak berbeda antar pengunjung di sisi server; keputusan persetujuan dibaca di sisi peramban, sehingga halaman tetap dapat di-cache (research.md R8).
- Kegagalan skrip yang diaktifkan tidak boleh menghalangi isi halaman tampil dan dinavigasi (FR-049).

---

## §6. Penyimpanan keputusan

- Disimpan pada perangkat pengunjung, tidak pernah di sisi server (FR-058).
- Tidak ada catatan persetujuan yang dapat ditinjau admin; ketiadaan ini adalah batas cakupan yang diputuskan sadar, bukan kelalaian.
- Bila penyimpanan perangkat tidak tersedia atau dikosongkan pengunjung, pengunjung diperlakukan sebagai belum memilih: bilah tampil kembali dan slot yang dapat ditolak tidak dijalankan.
