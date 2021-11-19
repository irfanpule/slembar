<?php

require_once 'api/v1/controllers/Controller.php';


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
        }
        parent::withJson($return);
    }

    private function queryUpdateOrCreate($decoded) {
        $transaction_id = $decoded['midtrans']['transaction_id'];
        $va_number = $decoded['midtrans']['va_numbers'][0]['va_number'];
        $bank = $decoded['midtrans']['va_numbers'][0]['bank'];

        $query= $this->db->query("SELECT transaction_id FROM payment_transactions WHERE transaction_id = '$transaction_id'");

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