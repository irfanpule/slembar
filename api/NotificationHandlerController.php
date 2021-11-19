<?php

require_once 'api/v1/controllers/Controller.php';
require 'plugins/payments/utils.php';


class NotificationHandlerController extends Controller {

    protected $sysconf;

    /**
     * @var mysqli
     */
    protected $db;

    function __construct($sysconf, $obj_db)
    {
        $this->sysconf = $sysconf;
        $this->db = $obj_db;
    }

    public function paymentTest()
    {
        $return = array(
            "status" => "ok",
            "message" => "sip"
        );
        parent::withJson($return);
    }

    public function saveTransaction() {
        //Receive the RAW post data.
        $content = trim(file_get_contents("php://input"));

        //Attempt to decode the incoming RAW post data from JSON.
        $decoded = json_decode($content, true);
        $sql_str = $this->queryUpdateOrCreate($decoded);

        $this->db->query($sql_str);
        if ($this->db->error) {
            $return = array(
                "status" => 'error',
                "message" => $this->db->error
            );
        } else {
            $return = array(
                "status" => 'success',
                "message" => 'Berhasil simpan data transaksi'
            );
            utility::writeLogs($this->db, 'payment_transactions', $decoded['midtrans']['transaction_id'], 'slembar', $msg, 'save transcation');
        }
        parent::withJson($return);
    }

    public function listenNotification() {
        $content = trim(file_get_contents("php://input"));
        $decoded = json_decode($content, true);

        $transaction_status = $decoded['transaction_status'];
        $transaction_id = $decoded['transaction_id'];
        $order_id = $decoded['order_id'];
        $type = $decoded['payment_type'];

        $query_select = $this->db->query("SELECT transaction_id FROM payment_transactions WHERE transaction_id='$transaction_id'");
        if ($this->db->error) {
            $return = array(
                "status" => 'error',
                "message" => "select: ".$this->db->error
            );
            parent::withJson($return);
            exit();
        } 

        if ($query_select->num_rows > 0) {
            // update status transaksi
            $this->db->query("UPDATE payment_transactions SET transaction_status='$transaction_status' WHERE transaction_id='$transaction_id'");
            if ($this->db->error) {
                $return = array(
                    "status" => 'error',
                    "message" => "update: ".$this->db->error
                );
                parent::withJson($return);
                exit();
            }

            if ($transaction_status == 'settlement') {
                // Status ini menyatakan bahwa pembayaran sudah berhasil
                // Ambil member_id dari table payment_transaction
                $query = $this->db->query("SELECT transaction_id, member_id FROM payment_transactions WHERE transaction_id='$transaction_id'");
                $obj = $query->fetch_object();
                $memberID = $obj->member_id;

                // update data denda member pada table fines
                $this->db->query("UPDATE fines SET credit=debet WHERE member_id='$memberID'");
                $msg = "Transaction order_id: " . $order_id ." successfully transfered using " . $type;
            } else if ($transaction_status == 'pending') {
                // TODO set payment status in merchant's database to 'Pending'
                $msg = "Waiting customer to finish transaction order_id: " . $order_id . " using " . $type;
            } else if ($transaction_status == 'deny') {
                // TODO set payment status in merchant's database to 'Denied'
                $msg = "Payment using " . $type . " for transaction order_id: " . $order_id . " is denied.";
            } else if ($transaction_status == 'expire') {
                // TODO set payment status in merchant's database to 'expire'
                $msg =  "Payment using " . $type . " for transaction order_id: " . $order_id . " is expired.";
            } else if ($transaction_status == 'cancel') {
                // TODO set payment status in merchant's database to 'Denied'
                $msg = "Payment using " . $type . " for transaction order_id: " . $order_id . " is canceled.";
            }
            
            // send email notification
            sendNotificationEmail($memberID, $msg, $transaction_id);
            utility::writeLogs($this->db, 'payment_transactions', $memberID, 'slembar', $msg, 'update status');

            $return = array(
                "status" => "success",
                "message" => $msg
            );
        }else {
            $return = array(
                "status" => "failed",
                "message" => "transaction id not found"
            );
        }
        parent::withJson($return);
    }

    private function queryUpdateOrCreate($decoded) {
        $transaction_id = $decoded['midtrans']['transaction_id'];
        $va_number = $decoded['midtrans']['va_numbers'][0]['va_number'];
        $bank = $decoded['midtrans']['va_numbers'][0]['bank'];

        $query = $this->db->query("SELECT transaction_id FROM payment_transactions WHERE transaction_id = '$transaction_id'");

        if ($query->num_rows > 0) {
            // sql update
            $sql_str = sprintf("UPDATE payment_transactions SET
                member_id='%s',  transaction_time='%s', transaction_status='%s', order_id='%s', payment_type='%s',
                gross_amount='%s', fraud_status='%s', pdf_url='%s', va_number='%s', bank='%s' WHERE transaction_id='%s'",
                $decoded['memberID'], $decoded['midtrans']['transaction_time'], $decoded['midtrans']['transaction_status'], 
                $decoded['midtrans']['order_id'], $decoded['midtrans']['payment_type'], $decoded['midtrans']['gross_amount'], 
                $decoded['midtrans']['fraud_status'], $decoded['midtrans']['pdf_url'], $va_number, $bank, $transaction_id);
        } else {
            // sql insert 
            $sql_str = sprintf("INSERT INTO payment_transactions (
                member_id, transaction_id, transaction_time,
                transaction_status, order_id, payment_type,
                gross_amount, fraud_status, pdf_url, va_number, bank)
                VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                $decoded['memberID'], $transaction_id, $decoded['midtrans']['transaction_time'],
                $decoded['midtrans']['transaction_status'], $decoded['midtrans']['order_id'], $decoded['midtrans']['payment_type'],
                $decoded['midtrans']['gross_amount'], $decoded['midtrans']['fraud_status'], $decoded['midtrans']['pdf_url'],
                $va_number, $bank);
        }
        return $sql_str;       
    }
}