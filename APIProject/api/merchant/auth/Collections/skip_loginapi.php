<?php
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

// if(isset($_POST['username']) and isset($_POST['password'])){
//     $session_id_key = md5(rand(1,10));
//     $responseData = [
//         'ApiKey' => $session_id_key,
//         'Message' => 'Login successfully'
//     ];
//     http_response_code(200);
// }else{
//     $responseData = [
//         'message' => 'Your Email or Password is Incorrect'
//     ];
//     http_response_code(400);
// }
// echo json_encode($responseData);
if (isset($_POST['phone_num'])) {


    $user0phone_num      = $db01->real_escape_string($_POST['phone_num']);
    $user1phone_num     = mysqli_real_escape_string($db01, $user0phone_num);
    $session_id_key = "mc" . md5(rand(1, 10) . time() . $user1phone_num);
    $sql        = "SELECT * FROM users_customer WHERE phone_num = '" . $user1phone_num . "'";
    $result     = mysqli_query($db01, $sql);
    $row        = mysqli_fetch_assoc($result);
    $count      = mysqli_num_rows($result);

    if ($count == 1) {

        if ($row['phone_num'] == $user1phone_num) {

            if ($row['op'] == ':=') {
                $userid = "";

                $update0 = mysqli_query($db01, "UPDATE users_customer SET last_ipAddress='" . get_client_ip() . "'  WHERE phone_num = '$user1phone_num' LIMIT 1 ") or die("MError0001");

                if (isset($update0)) {

                    // $_SESSION['VRXTlogged'] = "true";
                    // $_SESSION['VRXTvc'] = $row['verificationCode'];
                    // $_SESSION['VRXTemail'] = $row['email'];

                    $into_active_sessions = mysqli_query($db01, "INSERT INTO active_session (`iduser`,`sessionKey`) VALUES ('" . $userid . "', '" . $session_id_key . "') ");
                    // echo $into_active_sessions;

                    if ($into_active_sessions) {
                        $sql_active_session        = "SELECT * FROM active_session WHERE iduser = '" . $userid . "'";
                        $result_active_session     = mysqli_query($db01, $sql_active_session);
                        $count_active_session      = mysqli_num_rows($result_active_session);
                        $sessions_data = [
                            'VRXTlogged' => false,
                            'VRXTvc'  => $row['verificationCode'],
                            'VRXTid'  => $userid,
                            'VRXTemail' => $row['email']
                        ];

                        $redis->set($session_id_key, json_encode(@$sessions_data));

                        $responseData = [
                            'auth-token' => $session_id_key,
                            'Message' => 'Login successfully, New Api Key.',
                            'Active sessions' => 'You have ' . $count_active_session . ' api key active',
                            // 'restrect' => 'devices'
                            'restrict' => 'webbrowser'
                        ];
                        http_response_code(200);
                    } else {
                        // $responseData = [
                        //     'ApiKey' => $session_id_key,
                        //     'Message' => 'Login successfully, New Api Key.',
                        //     'Active sessions'=>'You have '.$count_active_session.' api key active'
                        // ];
                        // http_response_code(200);
                    }


                    // if($count_active_session >= 5){
                    //    $$responseData = [
                    //     'session'=>'You have '.$count_active_session.' api key active'
                    //     ];
                    // }

                    // header("location:/");
                } else {

                    // header("location:/signout");
                }
            } elseif ($row['op'] == '==') {

                $responseData = [
                    'message' => 'Your Account has been Disabled or banned'
                ];
                http_response_code(401);

                $output = "";
            } elseif ($row['op'] == '*=') {
                $responseData = [
                    // 'ApiKey' => $session_id_key,
                    'message' => 'Please Verify Your Account'
                ];
                http_response_code(401);
            }
        } else {
            $responseData = [
                'message' => 'Your Email or Password is Incorrect'
            ];
            http_response_code(400);
        }
    } else {
        $responseData = [
            // 'ApiKey' => $session_id_key,
            'message' => 'Your Email or Password is Incorrect'
        ];
        http_response_code(400);
    }
} else {
    $responseData = [
        'message' => 'Your Email or Password is Incorrect'
    ];
    http_response_code(400);
}

echo json_encode(@$responseData);
mysqli_close(@$db01);
$redis->close();
