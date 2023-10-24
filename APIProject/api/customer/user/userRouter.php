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

// use UsersSystem\user_details;

// @$request     = $db01->real_escape_string(@$_POST['request']);
@$request     = $db01->real_escape_string(@$request);
// @$specific    = $db01->real_escape_string(@$_POST['specific']);
// var_dump($_SESSION);
listFiles(__DIR__ . "/Collections");
if ($request == "userinfo") {
    @$user_details = new UsersSystem\user_details($db01);
    @$responseData = $user_details->getUserinfo($auth['VRXTvc'], $auth['VRXTphone_num']);
} elseif ($request == "updateinfo") {
    @$userupdate_class = new UsersSystem\user_details($db01);
    @$responseData = $userupdate_class->user_edit_details(sanitize_input_data($_POST, $db01), $auth['VRXTvc'], $auth['VRXTphone_num']);
} elseif ($request == "walletid") {
    @$walletid_details = new WalletSystem\wallet_user($db01);
    @$responseData = $walletid_details->get_walletid_details($auth['VRXTvc'], $auth['VRXTphone_num']);
} elseif ($request == "userinfo") {
} else {
    $responseData = httpreq(403);
}


if (isset($specific) and $specific != "") {
    if ($specific == "all") {
    } else {
    }
}

// unset($responseData['password']);
echo json_encode($responseData);
mysqli_close(@$db01);
