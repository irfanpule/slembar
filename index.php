<?php
/**
 * @Created by          : Muhammad Irfan (irfan.pule2@gmail.com)
 * @Date                : 17/11/2021 14:44
 * @File name           : overdue_dines.php
 * 
 * */


// be sure that this file not accessed directly
if (!defined('INDEX_AUTH')) {
    die("can not access this file directly");
} elseif (INDEX_AUTH != 1) {
    die("can not access this file directly");
}

// // IP based access limitation
do_checkIP('opac');
do_checkIP('opac-member');

// Required flie
require SIMBIO . 'simbio_DB/simbio_dbop.inc.php';
require LIB . 'member_logon.inc.php';

// check if member already logged in
$is_member_login = utility::isMemberLogin();

if (!$is_member_login) {
    header('Location: index.php?p=member');
}

require SIMBIO . 'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO . 'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO . 'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO . 'simbio_UTILS/simbio_date.inc.php';
require 'config.php';
require 'PaymentController.php';

$payment = new PaymentController($paymentconf, $dbs, trim($_SESSION['mid']));

if ($_GET['status_fine'] == 'paid') {
    echo $payment->showFinesPaid();
} elseif ($_GET['status_fine'] == 'paymentConfirm') {
    echo $payment->paymentConfirm();
} elseif ($_GET['status_fine'] == 'transaction') {
    echo $payment->showTransactionList();  
} else {
    echo $payment->showFinesUnpaid();
}
