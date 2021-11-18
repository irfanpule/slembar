<?php

/**
 * Plugin Name: Payment Midtrans
 * Plugin URI: <isikan alamat url dari repository plugin anda>
 * Description: Plugin payment terintegrasi dengan midtrans untuk banyak metode pembayaran
 * Version: 0.0.1
 * Author: Muhammad irfan
 * Author URI: <isikan url dari profil anda>
 */


// get plugin instance
$plugin = \SLiMS\Plugins::getInstance();

// register plugins
$plugin->registerMenu('opac', 'Denda', __DIR__ . '/members/index.php', 'Halaman denda member');
$plugin->registerMenu('system', 'Midtrans Config', __DIR__ . '/admins/index.php', 'Halaman untuk mengubah konfigurasi Midtrans');
