## Slembar
- Slembar -> `SLiMS Plugin Untuk Embyar-Mbayar`. Khusus untuk [SLiMS 9 Bulian](https://github.com/slims/slims9_bulian)
- Slembar adalah plugin [SLiMS](https://slims.web.id/web/) untuk melakukan pembayaran denda secara online.
- Slembar terintegrasi dengan [Midtrans](https://midtrans.com/) sebagai payment gateway yang menyediakan banyak tipe pembayaran.
- Slembar memanfaatkan fitur [SNAP Midtrans](https://snap-docs.midtrans.com/) 

  ![image](https://drive.google.com/uc?export=view&id=1_iygQeIBGNb_0VMH_XbmtS_kgtauyiWW)

### Fitur
- Member
    - Melihat data denda
    - Melihat data denda yang sudah dibayar (konvensional atau online)
    - Melihat data transaksi
    - Melihat detail transaksi
    - Melakukan pembayaran denda secara online
- Admin
    - Mengubah Midtrans Configuration
    - Melihat Transaction Fine
    - Ubah biaya admin
- Menerima notifikasi setiap perubahan status transaksi dari Midtrans
- Mengirim notifikasi ke member setiap ada perubahan status transaksi
- Tipe Pembayaran yang tersedia
    - Bank Transfer
        - BCA
        - BNI
        - BRI
        - Mandiri
        - Permata
        - ATM Bersama
    - Gopay
    - Shopeepay
    - Indomaret


### Pra-instalasi
- Sebelum menggunakan plugin ini, kita harus memiliki akun Midtrans, karena plugin ini harus diintegrasikan dengan payment gatewar Midtrans.
- Lakukan registrasi seperti biasa, ikuti arahan yang diberikan dari sistem midtrans.
- Lengkapi semua form seperti menjelaskan pembayaran nantinya digunakan untuk apa, data diri, informasi rek bank dll.
- Proses melengkapi data berpengaruh pada sistem Midtrans untuk menyiapkan payment type untuk digunakan saat development atau production


### Instalasi
- Unduh dan ekstrak, pastikan folder hasil ekstrak adalah `slembar`, jika bukan silahkan rename terlebih dahulu.
- Pindahkan pada directory `plugins`
- Masuk ke halaman admin, masuk menu `System` -> `Plugins`. Lihat apakah plugin `Slembar` muncul.
- Jika sudah maka aktifkan plugin dengan ubah switch button ke posisi aktif/enable.
- Lalu cari file `api/v1/routers.php`, tambahkan kode ini 
  ```php
  require 'plugins/slembar/api/NotificationHandlerController.php';
  ``` 
  pada file tsb tepat pada `require` yang terakhir. Contoh seperti di bawah ini.

  ```php
  /*----------  Require dependencies  ----------*/
  require 'lib/router.inc.php';
  require __DIR__ . '/controllers/HomeController.php';
  require __DIR__ . '/controllers/BiblioController.php';
  require __DIR__ . '/controllers/MemberController.php';
  require __DIR__ . '/controllers/SubjectController.php';
  require __DIR__ . '/controllers/ItemController.php';
  require __DIR__ . '/controllers/LoanController.php';

  /*----------  Require: baris ini yang tambahan  ----------*/
  require 'plugins/slembar/api/NotificationHandlerController.php';
  /*----------  Require: akhir bari  ----------*/
  ```
- masih pada file `api/v1/routers.php`, tambahkan kode ini
  ```php
  $router->map('GET', '/payment/test', 'NotificationHandlerController@paymentTest');
  $router->map('POST', '/payment/save-transaction', 'NotificationHandlerController@saveTransaction');
  ```
  tepat di atas `$router->run();`. Contoh seperti di bawah ini.

  ```php
  .....
  $router->map('GET', '/loan/summary/[*:date]', 'LoanController@getSummaryDate');

  /*----------  Router: baris tambahan yang ini  ----------*/
  $router->map('POST', '/payment/save-transaction', 'NotificationHandlerController@saveTransaction');
  $router->map('POST', '/payment/listen-notification', 'NotificationHandlerController@listenNotification');
  /*----------  Router: akhir baris  ----------*/

  /*----------  Run matching route  ----------*/
  $router->run();

  // doesn't need template
  exit();
  ```
- Siap digunakan.


### Konfigurasi SLiMS dan Midtrans
- Konfigurasi SLiMS
    - Setelah instalasi ada yang harus dikonfigurasi terlebih dahulu, caranya masuk sebagai admin lalu pilih menu `System` -> `Midtrans Config`
    - Pada halaman `Midtrans Config` diharusnya mengisi
        - `Midtrans Server Key`
        - `Midtrans Client Key`
        - `Environment Production`
        - `Admin Fee`
    - Setelah itu simpan konfigurasi
    ![image](https://drive.google.com/uc?export=view&id=1BZJZGaCYXFmQCvtaDMwvsBx4iqSl2gbR)
- Konfigurasi Midtrans
    - Masuk ke Midtrans
    - Pilih environtment `sandbox` (development) atau `production` (production), lalu pilih menu `Pengaturan` -> `Konfigurasi`
    - Disana terdapat `Pengaturan URL Redirect`, tambahkan url pada `Payment Notification URL` dengan url dibawah ini
      ```
      https://{host_name}/index.php?p=api/payment/listen-notification
      ```
      `host_name` disesuaikan dengan domain masing-masing ya. Untuk kasus development bisa menggunakan [ngrok](https://ngrok.com/) sebagai forwading agar tetap bisa menerima notifikasi realtime dari Midtrans
      ![image](https://drive.google.com/uc?export=view&id=10fdxT1P7uOpULEVbCxFCoOSONWm9UIgi)
- Plugin benar-benar siap digunakan.

### Development Tips
- Seperti yang dijelaskan di bab Konfigurasi bahwa untuk mendapatkan notifikasi realtime dari Midtrans saat development kita perlu mendaftarkan url webhook ke midtrans. Nah yang didaftarkan harus alamat yang valid, tidak bisa localhost.
- Jadi sebelum diupload ke cpanel / vps, kita bisa menggunakan `Ngrok` sebagai forwading yang host-nya bisa kita gunakan.
- Midtrans memiliki silulasi pembayaran yang dapat digunakan saat proses development atau testing. Dokumentasinya dapat dilihat [sini](https://snap-docs.midtrans.com/#testing-credentials)


### Screenshot & Video
- Untuk melihat beberapa hasil tangkapan layar bisa akses [disini](https://drive.google.com/drive/folders/1mdQJSlOeYU31YW2_5v0jtDPlEIVTlAOs)
- Untuk melihat video demo bisa akses [disini](https://drive.google.com/file/d/1F11Rt70mEC1PnbdtHRX4YswD37W5Mtd6/view?usp=sharing)
