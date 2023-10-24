<?php


require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");

function listFiles($folder)
{
    // Get an array of files in the given folder
    $files = scandir($folder);
    // Loop through each file
    foreach ($files as $file) {
        // Skip any hidden files or files that start with 'skip_'
        if ($file == "." || $file == ".." || preg_match('/^skip_/', $file)) {
            continue;
        }

        // If the file is a .php file, include it
        if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
            // echo $folder . '/' . $file;
            require_once $folder . '/' . $file;
        }
    }
}

@$request     = $db01->real_escape_string(@$request);
listFiles(__DIR__ . "/Collections");

if ($request == "get") {
    @$Customer_details = new CustomerSystem\customer_user($db01, $redis);
    @$responseData = $Customer_details->get_customer_users(sanitize_input_data($_POST, $db01));
} elseif ($request == "get-id") {
    @$Customer_details = new CustomerSystem\customer_user($db01, $redis);
    @$responseData = $Customer_details->get_customer_by_id(sanitize_input_data($_POST, $db01));
} elseif ($request == "get-phone") {
    @$Customer_details = new CustomerSystem\customer_user($db01, $redis);
    @$responseData = $Customer_details->get_customer_by_phone(sanitize_input_data($_POST, $db01));
} elseif ($request == "get-email") {
    @$Customer_details = new CustomerSystem\customer_user($db01, $redis);
    @$responseData = $Customer_details->get_customer_by_email(sanitize_input_data($_POST, $db01));
} elseif ($request == "freeze") {
    @$Customer_details = new CustomerSystem\customer_user($db01, $redis);
    @$responseData = $Customer_details->freeze_customer_by_id(sanitize_input_data($_POST, $db01));
} elseif ($request == "std") {
    @$stdReceipt = new ReceiptSystem\Receipt($db01, $redis);
    @$responseData = $stdReceipt->get_last_six_months(sanitize_input_data($_POST, $db01));
} elseif ($request == "time") {
    @$stdReceipt = new ReceiptSystem\Receipt($db01, $redis);
    @$responseData = $stdReceipt->get_data_in_date_range(sanitize_input_data($_POST, $db01));
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