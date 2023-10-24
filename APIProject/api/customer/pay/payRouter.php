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


@$request     = $db01->real_escape_string(@$request);
// @$specific    = $db01->real_escape_string(@$_POST['specific']);

listFiles(__DIR__ . "/Collections");

if ($request == "req") {
    @$stdReceipt = new PaySystem\pay_user($db01, $redis);
    @$responseData = $stdReceipt->approve(sanitize_input_data($_POST, $db01), $auth['VRXTid']);
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