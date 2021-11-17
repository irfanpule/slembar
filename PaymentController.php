<?php
/**
 * @Created by          : Muhammad Irfan (irfan.pule2@gmail.com)
 * @Date                : 11/16/21 14.54
 * @File name           : PaymentController.php
 */

require_once 'midtrans-php-master/Midtrans.php';


class PaymentController
{
    protected $paymentconf;
    protected $db;

    function __construct($paymentconf, $db, $memberID) {
        $this->paymentconf = $paymentconf;
        $this->db = $db;
        $this->memberID = $memberID;
        $this->configMidtrans();
    }

    private function configMidtrans() {
        /** 
         * Midtrans documentation to get snap token 
         * https://docs.midtrans.com/en/snap/integration-guide?id=_1-acquiring-transaction-token-on-backend
         * */ 

        // Set your Merchant Server Key. Get value from sysconf
        \Midtrans\Config::$serverKey = $this->paymentconf['midtrans_server_key'];
        // // Set to Development/Sandbox Environment (default). Set to true for Production Environment (accept real transaction).
        \Midtrans\Config::$isProduction = $this->paymentconf['midtrans_is_production'];
        \Midtrans\Config::$overrideNotifUrl = SWB . "index.php?p=denda&status_fine=notification";
    }
    
    private function getMenu(){
        $actions = '<div class="memberFineAction">';
        $actions .= '<a href="index.php?p=denda&status_fine=unpaid" class="btn btn-link">' . __('Unpaid') . '</a> ';
        $actions .= '<a href="index.php?p=denda&status_fine=paid" class="btn btn-link ">' . __('Paid') . '</a> ';
        $actions .= '<a href="index.php?p=denda&status_fine=transaction" class="btn btn-link ">' . __('Transaction') . '</a> ';
        $actions .= '</div>';
        return $actions;
    }

    private function getFineData() {
        $sql = "SELECT * from fines WHERE member_id='{$this->memberID}' AND (debet!=credit)";
        $query = $this->db->query($sql);
        return $query;
    }

    public function showFinesPaid($num_recs_show = 20) {

        // table spec
        $table_spec = 'fines AS f';

        // create datagrid
        $datagrid = new simbio_datagrid();
        $datagrid->setSQLColumn('f.fines_id AS \'' . __('Id') . '\'',
            'f.description AS \''.__('Description/Name').'\'',
            'f.fines_date AS \''.__('Fines Date').'\'',
            'f.debet AS \''.__('Debit').'\'',
            'f.credit AS \''.__('Credit').'\'');
        $datagrid->setSQLorder("f.fines_date DESC");
        $criteria = 'f.member_id=\''.$this->db->escape_string($this->memberID).'\' ';
        
        // condition paid
        $criteria .= ' AND (f.debet=f.credit) ';
        // to remove debet and credit zero value
        $criteria .= ' AND (f.debet!=0) AND (f.credit!=0)';       
        $datagrid->setSQLCriteria($criteria);
        
        // set table and table header attributes
        $datagrid->table_attr = 'align="center" class="memberBasketList table table-striped" cellpadding="5" cellspacing="0"';
        $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $datagrid->using_AJAX = false;

        // put the result into variables
        $datagrid_result = $datagrid->createDataGrid($this->db, $table_spec, $num_recs_show);
        $actions = $this->getMenu();
        $result .= '<div class="memberFineInfo">' . $datagrid->num_rows . ' ' . __('fine(s) paid') . $actions . '</div>' . "\n" . $datagrid_result;
        return $result;
    }

    private function baseFinesUnpaid($num_recs_show = 20)
    {
        // table spec
        $table_spec = 'fines AS f';

        // create datagrid
        $datagrid = new simbio_datagrid();
        $datagrid->setSQLColumn('f.fines_id AS \'' . __('Id') . '\'',
            'f.description AS \''.__('Description/Name').'\'',
            'f.fines_date AS \''.__('Fines Date').'\'',
            'f.debet AS \''.__('Debit').'\'');
        $datagrid->setSQLorder("f.fines_date DESC");
        $criteria = 'f.member_id=\''.$this->db->escape_string($this->memberID).'\' ';
        
        // condition unpaid
        $criteria .= ' AND (f.debet!=f.credit) ';
        $datagrid->setSQLCriteria($criteria);
        
        // set table and table header attributes
        $datagrid->table_attr = 'align="center" class="table table-striped" cellpadding="5" cellspacing="0"';
        $datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
        $datagrid->using_AJAX = false;

        // put the result into variables
        $datagrid_result = $datagrid->createDataGrid($this->db, $table_spec, $num_recs_show);
        $actions = $this->getMenu();
        $table_view .= '<div class="memberFineInfo">' . $datagrid->num_rows . ' ' . __('fine(s) unpaid') . $actions . '</div>' . "\n" . $datagrid_result;
        return $table_view;
    }

    public function showFinesUnpaid($num_recs_show = 20)
    {  
        $table_view .= $this->baseFinesUnpaid();
        $result .=  $table_view . '<a href="'. SWB .'index.php?p=denda&status_fine=paymentConfirm" class="btn btn-primary btn-block"><i class="fas fa-sign-out-alt mr-2"></i>Bayar Sekarang</a>';
        return $result;
    }
    
    public function paymentConfirm() 
    {
        $table_view = $this->baseFinesUnpaid();
        $query = $this->getFineData();

        // collect all fines
        $items = array();
        while ($data = $query->fetch_assoc()) {
            $item = array(
                'id' => $data['fines_id'],
                'price' => $data['debet'],
                'quantity' => 1,
                'name' => $data['description']
            );
            array_push($items, $item);
        }
        // add admin fee
        $item = array(
            'id' => 'adm'.rand(),
            'price' => $this->paymentconf['admin_fee'],
            'quantity' => 1,
            'name' => "Admin Fee"
        );
        array_push($items, $item);

        // // Required
        $transaction_details = array(
            'order_id' => rand(), // no decimal allowed for creditcard
        );

        $sql = "SELECT member_name, member_email, member_phone from member WHERE member_id='{$this->memberID}'";
        $query = $this->db->query($sql);
        $obj = $query->fetch_object();
        $customer_details = array(
            'first_name'    => $obj->member_name,
            'last_name'     => "",
            'email'         => $obj->member_email,
            'phone'         => $obj->member_phone
        );

        // Fill transaction details
        $transaction = array(
            'transaction_details' => $transaction_details,
            'customer_details' => $customer_details,
            'item_details' => $items,
        );

        $snap_token = '';
        try {
            $snap_token = \Midtrans\Snap::getSnapToken($transaction);
        }
        catch (\Exception $e) {
            echo $e->getMessage();
        }
        
        $jsScript = <<<HTML
            <button id="pay-button" class="btn btn-primary btn-block"><i class="fas fa-sign-out-alt mr-2"></i>Bayar Sekarang</button>
            <pre><div id="result-json">JSON result will appear here after payment:<br></div></pre> 

            <!-- TODO: Remove ".sandbox" from script src URL for production environment. Also input your client key in "data-client-key" -->
            <script src="{$this->paymentconf['midtrans_url']}" data-client-key="{$this->paymentconf['midtrans_client_key']}"></script>
            <script type="text/javascript">
                $(document).ready(function () {
                    // SnapToken acquired from previous step
                    showSnap();
                });

                $('#pay-button').on('click', function() {
                    showSnap();
                });

                function showSnap() {
                    snap.pay('{$snap_token}', {
                        // Optional
                        onSuccess: function(result){
                            /* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                        },
                        // Optional
                        onPending: function(result){
                            /* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                        },
                        // Optional
                        onError: function(result){
                            /* You may add your own js here, this is just example */ document.getElementById('result-json').innerHTML += JSON.stringify(result, null, 2);
                        }
                    });
                }
            </script>
        HTML;
        return $table_view . $jsScript;
    }

    public function showTransactionList() {
        $actions = $this->getMenu();
        return $actions."no data";
    }
}