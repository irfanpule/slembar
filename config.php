<?php

$paymentconf['midtrans_server_key'] = "SB-Mid-server-PRQG_Dj0kr99OV7mXpI0ywnm";
$paymentconf['midtrans_client_key'] = "SB-Mid-client-3dulYwEgZPyrZqpS";
$paymentconf['midtrans_is_production'] = false;
$paymentconf['admin_fee'] = 5000;

if ($paymentconf['midtrans_is_production']) {
    $paymentconf['midtrans_url'] = "https://app.midtrans.com/snap/snap.js";
} else {
    $paymentconf['midtrans_url'] = "https://app.sandbox.midtrans.com/snap/snap.js";
}