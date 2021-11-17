<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 17/11/2021 16:40
 * @File name           : 1_CreateTransactionTable.php
 *
 */

class CreateTransactionTable extends \SLiMS\Migration\Migration
{

    function up()
    {
        \SLiMS\DB::getInstance()->query("CREATE TABLE `payment_transaction` (
            `member_id` varchar(20) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            `transaction_id` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            `transaction_time` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            `transaction_status` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            `order_id` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            `payment_type` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL,
            `gross_amount` varchar(255) COLLATE 'utf8mb4_unicode_ci' NOT NULL
          ) ENGINE='MyISAM' COLLATE 'utf8mb4_unicode_ci';");
    }

    function down()
    {
        \SLiMS\DB::getInstance()->query('DROP TABLE `payment_transaction`');
    }
}