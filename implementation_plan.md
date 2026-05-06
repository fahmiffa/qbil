# Fitur Laporan Pemasukan & Pengeluaran (Buku Kas)

Untuk membuat sistem laporan keuangan yang sesuai *best practices* di Laravel, kita perlu memisahkan data transaksi keuangan ke dalam satu tabel khusus (`transactions` atau `cashflows`). Hal ini memungkinkan pelacakan semua arus kas dari berbagai sumber (Tagihan, Voucher, Pengeluaran Operasional) di satu tempat.

## 1. Skema Database (Tabel `transactions`)
Tabel ini akan mencatat setiap uang masuk dan keluar.
- `id`
- `user_id`: ID Admin/Pemilik sistem.
- `type`: `income` (Pemasukan) atau `expense` (Pengeluaran).
- `amount`: Jumlah uang (Desimal/BigInt).
- `category`: Kategori (misal: "Tagihan Bulanan", "Voucher Hotspot", "Beli Alat", "Gaji").
- `description`: Keterangan detail.
- `reference_type` & `reference_id`: (Opsional) Polymorphic relation untuk menautkan transaksi ke tabel `Invoice` atau `VoucherOrder` sebagai bukti asal muasal dana.
- `transaction_date`: Tanggal transaksi (bisa di-backdate oleh admin).

## 2. Pencatatan Otomatis (Automation)
Sistem penagihan yang baik harus meminimalisir input manual untuk Pemasukan:
- **Invoices**: Ketika Invoice ditandai sebagai `paid` (baik manual via admin maupun otomatis via payment gateway), sistem otomatis membuat 1 baris di tabel `transactions` (Income).
- **Voucher Orders**: Sama seperti invoice, saat voucher dibayar, otomatis masuk ke `transactions`.

## 3. Pencatatan Manual
Admin memerlukan halaman khusus untuk mencatat:
- **Pengeluaran Operasional**: Beli kabel LAN, langganan internet pusat, bayar listrik, dsb.
- **Pemasukan Lain-lain**: Biaya registrasi/pemasangan tiang baru yang mungkin tidak masuk di tagihan bulanan reguler.

## 4. UI Dashboard & Laporan (Livewire Component)
Kita akan membuat halaman khusus `/finance-report`:
1.  **Summary Cards**:
    - Total Pemasukan (Bulan Ini)
    - Total Pengeluaran (Bulan Ini)
    - Saldo Bersih / Profit
2.  **Filter**: Berdasarkan rentang tanggal (Start Date - End Date).
3.  **Grafik (Chart)**: Menampilkan tren pemasukan vs pengeluaran per hari dalam 1 bulan (Bisa menggunakan Chart.js atau ApexCharts).
4.  **Tabel Rincian**: Daftar histori lengkap masuk dan keluarnya uang.

---

### Tahapan Eksekusi:
1. Membuat Migration & Model `Transaction`.
2. Menambahkan *Hook* (Event/Observer/Service) di fungsi pembayaran Invoice & Voucher.
3. Membuat komponen Livewire untuk CRUD pencatatan manual (Pengeluaran).
4. Membuat komponen Livewire untuk Halaman Laporan (Rekapitulasi).

Apakah arsitektur ini sudah sesuai dengan kebutuhan Anda? Jika disetujui, saya akan mulai membuat file Migrasi & Modelnya.
