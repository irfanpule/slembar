<?php

/**
 * Plugin Name: Slembar - payment plugin
 * Plugin URI: https://github.com/irfanpule/slembar
 * Description: SLiMS plugin untuk embayar-bayar. Saat ini dikususnya untuk memudahkan pembayaran online denda. Slembar menggunakan payment gateway Midtrans.
 * Version: 0.0.1
 * Author: Muhammad irfan
 * Author URI: https://www.linkedin.com/in/irfan-pule/
 */


// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// register plugins
$plugin->registerMenu('opac', 'Denda', __DIR__ . '/members/index.php', 'Halaman denda member');
$plugin->registerMenu('system', 'Midtrans Config', __DIR__ . '/admins/midtrans_config.php', 'Halaman untuk mengubah konfigurasi Midtrans');
$plugin->registerMenu('reporting', 'Transaction Fine', __DIR__ . '/admins/transaction_fine.php', 'Halaman untuk melihat seluruh transaksi pembayaran denda');
