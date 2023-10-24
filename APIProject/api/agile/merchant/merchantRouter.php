<?php
// Require the database controller file
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");

// Function to list files in a given folder
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

// Escape the request variable
@$request     = $db01->real_escape_string(@$request);

// List all the files in the Collections directory
listFiles(__DIR__ . "/Collections");

// Check which step of the registration process we are on and call the appropriate function
if ($request == "newmerchant") {
    @$newMerchant = new RegistrationSystemME\RegistrationME($db01, $redis);
    @$responseData = $newMerchant->insertFullMerchantUser(sanitize_input_data($_POST, $db01));
} elseif ($request == "get") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$Merchant_details = new MerchantSystem\merchant_user($db01, $redis);
    @$responseData = $Merchant_details->get_merchant_all(sanitize_input_data($_POST, $db01));
} elseif ($request == "get-id") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$Merchant_details = new MerchantSystem\merchant_user($db01, $redis);
    @$responseData = $Merchant_details->get_merchant_by_id(sanitize_input_data($_POST, $db01));
} elseif ($request == "get-com") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$Merchant_details = new MerchantSystem\merchant_user($db01, $redis);
    @$responseData = $Merchant_details->get_merchant_by_commercial(sanitize_input_data($_POST, $db01));
} elseif ($request == "freeze") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$Merchant_details = new MerchantSystem\merchant_user($db01, $redis);
    @$responseData = $Merchant_details->freeze_merchant_by_id(sanitize_input_data($_POST, $db01));
} elseif ($request == "std") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$stdReceipt = new ReceiptSystem\Receipt($db01, $redis);
    @$responseData = $stdReceipt->get_last_six_months(sanitize_input_data($_POST, $db01));
} elseif ($request == "time") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$stdReceipt = new ReceiptSystem\Receipt($db01, $redis);
    @$responseData = $stdReceipt->get_data_in_date_range(sanitize_input_data($_POST, $db01));
} else {
    // Return a 403 error if the request is not valid
    $responseData = httpreq(403);
}

// Unset the password from the response data 
// unset($responseData['password']);

// Echo the response data as JSON
echo json_encode($responseData);

// Close the database connection
mysqli_close(@$db01);
