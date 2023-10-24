<?php
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");

// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
function listFiles($folder)
{
    $files = scandir($folder);
    foreach ($files as $file) {
        if ($file == "." || $file == "..") {
            continue;
        }
        if (preg_match('/^skip_/', $file)) {
            continue;
        }
        if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
            // echo $folder . '/' . $file;
            require_once $folder . '/' . $file;
        }
    }
}
//  $sanitized_post_data = sanitize_input_data($_POST, $db01);

// use ReceiptSystem\Receipt;


@$request     = $db01->real_escape_string(@$request);
// @$specific    = $db01->real_escape_string(@$_POST['specific']);

listFiles(__DIR__ . "/Collections");

if ($request == "std") {
    @$stdReceipt = new ReceiptSystem\Receipt($db01, $redis);
    @$responseData = $stdReceipt->get_last_six_months($auth['merchant_id']);
} elseif ($request == "time") {
    @$timeReceipt = new ReceiptSystem\Receipt($db01, $redis);
    @$responseData = $timeReceipt->get_data_in_date_range(sanitize_input_data($_POST, $db01), $auth['merchant_id']);
} else {
    $responseData = httpreq(403);
}

// if (isset($specific) and $specific != "") {
//     if ($specific == "all") {
//     } else {

//     }
// }

// unset($responseData['password']);
echo json_encode($responseData);
mysqli_close(@$db01);
