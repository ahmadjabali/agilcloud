<?php

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
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

if ($request == "request") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$newClass = new DeviceSystem\device($db01, $redis);
    @$responseData = $newClass->request_id_device($auth['VRXTid'], $auth['merchant_id_reg_div'], $auth['merchant_id']);
} elseif ($request == "register") {
    @$newClass = new DeviceSystem\device($db01, $redis);
    @$responseData = $newClass->register_id_device(sanitize_input_data($_POST, $db01));
} elseif ($request == "getlist") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$newClass = new DeviceSystem\device($db01, $redis);
    @$responseData = $newClass->getlist(sanitize_input_data($_POST, $db01));
} elseif ($request == "order") {
    require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sessions.php");
    @$newClass = new DeviceSystem\device($db01, $redis);
    @$responseData = $newClass->order(sanitize_input_data($_POST, $db01), $auth['VRXTvc'], $auth['VRXTemail']);
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
