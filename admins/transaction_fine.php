<?php
/**
 * @Created by          : Muhammad Irfan (irfan.pule2@gmail.com)
 * @Date                : 18/11/2021 23:44
 * 
 * */


// key to authenticate
if (!defined('INDEX_AUTH')) {
define('INDEX_AUTH', '1');    
}

// key to get full database access
define('DB_ACCESS', 'fa');

if (!defined('SB')) {
    // main system configuration
    require '../../../sysconfig.inc.php';
    // start the session
    require SB.'admin/default/session.inc.php';
}
// IP based access limitation
require LIB.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');

require SIMBIO.'simbio_GUI/form_maker/simbio_form_table_AJAX.inc.php';
require SIMBIO.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO.'simbio_DB/simbio_dbop.inc.php';
require SIMBIO.'simbio_DB/datagrid/simbio_dbgrid.inc.php';
require SIMBIO.'simbio_GUI/paging/simbio_paging.inc.php';

?>

<div class="menuBox">
    <div class="menuBoxInner memberIcon">
        <div class="per_title">
            <h2><?php echo __('Transaction Fine'); ?></h2>
        </div>
        <div class="sub_section">
        <div class="btn-group">
        </div>
        <form name="search" action="<?=$_SERVER['REQUEST_URI']?>" id="search" method="get" class="form-inline"><?php echo __('Search'); ?>
            <input type="text" name="keywords" class="form-control col-md-3" />
            <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="s-btn btn btn-default" />
        </form>
        </div>
    </div>
</div>

<?php

// table spec
$table_spec = 'payment_transactions AS t';

// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn('t.transaction_id AS \'' . __('Id') . '\'',
    't.transaction_time AS \''.__('Time').'\'',
    't.member_id AS \''.__('Member Id').'\'',
    't.transaction_status AS \''.__('Status').'\'',
    't.order_id AS \''.__('Order Id').'\'',
    't.payment_type AS \''.__('Payment Type').'\'',
    't.bank AS \''.__('Bank').'\'',
    't.gross_amount AS \''.__('Gross Amount').'\'');

if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $keywords = $dbs->escape_string($_GET['keywords']);
    $criteria .= "t.member_id LIKE '%$keywords%' OR t.transaction_status LIKE '%$keywords%' OR t.bank LIKE '%$keywords%' OR t.payment_type LIKE '%$keywords%'";
    $datagrid->setSQLCriteria($criteria);
}


// set table and table header attributes
$datagrid->table_attr = 'align="center" class="table table-bordered" cellpadding="5" cellspacing="0"';
$datagrid->table_header_attr = 'class="dataListHeaderPrinted" style="font-weight: bold;"';
$datagrid->using_AJAX = false;

// // put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20);
$result .= '<div id="pageContent">' . $datagrid->num_rows . ' ' . __('transaction(s) data') . $actions . $datagrid_result . '</div>';
echo $result;