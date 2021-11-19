## Slembar
- Slembar -> `SLiMS Plugin Untuk Mbyar-mbayar`.
- Slembar adalah plugin [SLiMS](https://slims.web.id/web/) untuk melakukan pembayaran denda secara online.
- Slembar terintegrasi dengan [Midtrans](https://midtrans.com/) sebagai payment gateway yang menyediakan banyak tipe pembayaran.
- Slembar memanfaatkan fitur [SNAP Midtrans](https://snap-docs.midtrans.com/) 

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
- Mendapatkan notifikasi disetiap status transaksinya


### Instalasi
- Unduh dan ekstrak
- pindahkan pada directory `plugins`
- cari file `api/v1/routers.php`, tambahkan beberapa baris code ini pada file tsb tepak pada `require` yang terakhir
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
require 'plugins/payments/api/NotificationHandlerController.php';
/*----------  Require: akhir bari  ----------*/
```
- masih pada file `api/v1/routers.php`, tambahkan code tepat di atas `$router->run();`
```php
.....
$router->map('GET', '/loan/summary/[*:date]', 'LoanController@getSummaryDate');

/*----------  Router: baris tambahan yang ini  ----------*/
$router->map('GET', '/payment/test', 'NotificationHandlerController@paymentTest');
$router->map('POST', '/payment/save-transaction', 'NotificationHandlerController@saveTransaction');
/*----------  Router: akhir baris  ----------*/

/*----------  Run matching route  ----------*/
$router->run();

// doesn't need template
exit();
```

### Simulasi Pembayaran Midtrans
Midtrans memiliki silulasi pembayaran yang dapat digunakan saat proses development atau testing. Dokumentasinya dapat dilihat [sini](https://snap-docs.midtrans.com/#testing-credentials)
