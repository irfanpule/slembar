<?php
/**
 * @Created by          : Muhammad Irfan (irfan.pule2@gmail.com)
 * @Date                : 18/11/2021 13:44
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

?>

<div class="menuBox">
  <div class="menuBoxInner systemIcon">
    <div class="per_title">
      <h2><?php echo __('Midtrans Configuration'); ?></h2>
    </div>
    <div class="infoBox">
      <?php echo __('Modify Midtrans Config'); ?>
    </div>
  </div>
</div>

<?php
if (!function_exists('addOrUpdateSetting')) {
    function addOrUpdateSetting($name, $value) {
        global $dbs;
        $sql_op = new simbio_dbop($dbs);
        $data['setting_value'] = $dbs->escape_string(serialize($value));

        $query = $dbs->query("SELECT setting_value FROM setting WHERE setting_name = '{$name}'");
        if ($query->num_rows > 0) {
            // update
            $sql_op->update('setting', $data, "setting_name='{$name}'");
        } else {
            // insert
            $data['setting_name'] = $name;
            $sql_op->insert('setting', $data);
        }
    }
}

if (isset($_POST['updateData'])) {

    $midtrans_server_key = trim($_POST['midtrans_server_key']);
    if ($midtrans_server_key != '') {
        addOrUpdateSetting('midtrans_server_key', $midtrans_server_key);
    } else {
        utility::jsToastr(__('Midtrans Configuration'), __('Midtrans Client Key must be filled'), 'error');
        exit();
    }

    $midtrans_client_key = trim($_POST['midtrans_client_key']);
    if ($midtrans_client_key != '') {
        addOrUpdateSetting('midtrans_client_key', $midtrans_client_key);
    } else {
        utility::jsToastr(__('Midtrans Configuration'), __('Midtrans Server Key must be filled'), 'error');
        exit();
    }

    $midtrans_is_production = trim($_POST['midtrans_is_production']);
    if ($midtrans_is_production != '') {
        addOrUpdateSetting('midtrans_is_production', $midtrans_is_production);
        if ($midtrans_is_production == '1') {
            addOrUpdateSetting('midtrans_url', "https://app.midtrans.com/snap/snap.js");
        } else {
            addOrUpdateSetting('midtrans_url', "https://app.sandbox.midtrans.com/snap/snap.js");
        }
    } else {
        utility::jsToastr(__('Midtrans Configuration'), __('Midtrans Environment Prodcution'), 'error');
        exit();
    }

    $payment_admin_fee = trim($_POST['payment_admin_fee']);
    if ($payment_admin_fee != '') {
        addOrUpdateSetting('payment_admin_fee', $payment_admin_fee);
    } else {
        utility::jsToastr(__('Midtrans Configuration'), __('Admin Fee'), 'error');
        exit();
    }

    // write log
    utility::writeLogs($dbs, 'staff', $_SESSION['uid'], 'system', $_SESSION['realname'].' change Midtran configuration', 'Midtrans Config', 'Replace');
    utility::jsToastr(__('Midtrans Configuration'), __('Settings saved. Refreshing page'), 'success'); 
    exit();
}

// load settings from database
utility::loadSettings($dbs);

// create new instance
$form = new simbio_form_table_AJAX('mainForm', $_SERVER['REQUEST_URI'], 'post');
$form->submit_button_attr = 'name="updateData" value="'.__('Save Settings').'" class="btn btn-default"';

// form table attributes
$form->table_attr = 'id="dataList" class="s-table table"';
$form->table_header_attr = 'class="alterCell font-weight-bold"';
$form->table_content_attr = 'class="alterCell2"';

// version status
$form->addAnything(__('Plugin Version'), '<strong> 0.0.1 </strong>');
$form->addTextField('text', 'midtrans_server_key', __('Midtrans Client Key'), $sysconf['midtrans_server_key'], 'class="form-control"');
$form->addTextField('text', 'midtrans_client_key', __('Midtrans Server Key'), $sysconf['midtrans_client_key'], 'class="form-control"');

$options = null;
$options[] = array('0', __('False'));
$options[] = array('1', __('True'));
$form->addSelectList('midtrans_is_production', __('Environment Production'), $options, $sysconf['midtrans_is_production']?$sysconf['midtrans_is_production']:'0','class="form-control col-3"');

$form->addTextField('text', 'payment_admin_fee', __('Admin Fee'), $sysconf['payment_admin_fee']?$sysconf['payment_admin_fee']:'2000', 'class="form-control" style="width: 10%;"');

echo $form->printOut();