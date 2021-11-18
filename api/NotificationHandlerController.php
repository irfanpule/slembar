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
}