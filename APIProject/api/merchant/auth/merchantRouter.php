<?php


require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
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

@$request     = $db01->real_escape_string(@$request);
// @$specific    = $db01->real_escape_string(@$_POST['specific']);

listFiles(__DIR__ . "/Collections");

if ($request == "admin") {
    @$newCustomer = new MerchantUserSystem\merchant_user($db01, $redis);
    @$responseData = $newCustomer->login_merchant_by_email(sanitize_input_data($_POST, $db01));
} elseif ($request == "resetpassword") {
    @$newCustomer = new MerchantUserSystem\resetpassword($db01, $redis);
    @$responseData = $newCustomer->reset_password(sanitize_input_data($_POST, $db01));
} elseif ($request == "changepass") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$newCustomer = new MerchantUserSystem\changepassword($db01, $redis);
    @$responseData = $newCustomer->change_password(sanitize_input_data($_POST, $db01), $auth['VRXTvc'], $auth['VRXTemail']);
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
