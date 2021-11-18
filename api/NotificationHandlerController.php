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
        $va_number = $decoded['midtrans']['va_numbers'][0]['va_number'];
        $bank = $decoded['midtrans']['va_numbers'][0]['bank'];
        // sql insert string
        $sql_str = sprintf("INSERT INTO payment_transactions (
            member_id, transaction_id, transaction_time,
            transaction_status, order_id, payment_type,
            gross_amount, fraud_status, pdf_url, va_number, bank)
            VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            $decoded['memberID'], $decoded['midtrans']['transaction_id'], $decoded['midtrans']['transaction_time'],
            $decoded['midtrans']['transaction_status'], $decoded['midtrans']['order_id'], $decoded['midtrans']['payment_type'],
            $decoded['midtrans']['gross_amount'], $decoded['midtrans']['fraud_status'], $decoded['midtrans']['pdf_url'],
            $va_number, $bank);

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
}