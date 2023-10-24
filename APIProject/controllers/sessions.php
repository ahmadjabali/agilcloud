<?php
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
// @$apikey = $db01->real_escape_string($_POST['apikey']);
// if (isset($_POST['apikey']) and $apikey) {
//     $auth = $redis->get($apikey);
//     $auth = json_decode($auth, true);
//     if (@$auth['VRXTlogged'] == true) {
//     } else {
//         $responseData = [
//             'message' => 'Error 400'
//         ];
//         echo json_encode($responseData);
//         exit();
//     }
// } else {
//     $responseData = [
//         'message' => 'Error 400'
//     ];
//     echo json_encode($responseData);
//     exit();
// }

@$apikey = $db01->real_escape_string($_POST['auth-token']);
if (isset($_POST['auth-token']) and $apikey) {
    $auth = $redis->get($apikey);
    $auth = json_decode($auth, true);
    if (@$auth['VRXTlogged'] == true) {
    } else {
        $responseData = [
            'message' => '401 Unauthorized',
            'code' => 1
        ];
        http_response_code(401);
        echo json_encode($responseData);
        exit();
    }
} else {
    $responseData = [
        'message' => '401 Unauthorized',
        'code' => 0
    ];
    http_response_code(401);
    echo json_encode($responseData);
    exit();
}