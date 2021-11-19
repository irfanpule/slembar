<?php
/**
 * @Created by          : Muhammad Irfan (irfan.pule2@gmail.com)
 * @Date                : 17/11/2021 16:40
 * @File name           : 1_CreateTransactionTable.php
 *
 */

class CreateTransactionTable extends \SLiMS\Migration\Migration
{

    function up()
    {
        \SLiMS\DB::getInstance()->query("CREATE TABLE `payment_transactions` ( 
            `transaction_id` varchar(100) COLLATE 'utf8mb4_unicode_ci' NOT NULL, 
            `member_id` varchar(20) COLLATE 'utf8mb4_unicode_ci' NOT NULL, 
            `transaction_time` varchar(100) COLLATE 'utf8mb4_unicode_ci', 
            `transaction_status` varchar(20) COLLATE 'utf8mb4_unicode_ci', 
            `order_id` varchar(100) COLLATE 'utf8mb4_unicode_ci' NOT NULL, 
            `payment_type` varchar(20) COLLATE 'utf8mb4_unicode_ci', 
            `gross_amount` varchar(150) COLLATE 'utf8mb4_unicode_ci', 
            `fraud_status` varchar(20) COLLATE 'utf8mb4_unicode_ci', 
            `pdf_url` varchar(220) COLLATE 'utf8mb4_unicode_ci', 
            `va_number` varchar(30) COLLATE 'utf8mb4_unicode_ci', 
            `bank` varchar(50) COLLATE 'utf8mb4_unicode_ci', 
            PRIMARY KEY (`transaction_id`)) 
            ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    }

    function down()
    {
        \SLiMS\DB::getInstance()->query('DROP TABLE `payment_transactions`');
    }
}