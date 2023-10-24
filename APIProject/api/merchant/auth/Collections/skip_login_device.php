<?php
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");

if (isset($_POST['email']) and isset($_POST['password'])) {

    $session_id_key = "mc" . md5(rand(1, 10) . time());
    $user0      = $db01->real_escape_string($_POST['email']);
    $pass0      = $db01->real_escape_string($_POST['password']);
    $user1     = mysqli_real_escape_string($db01, $user0);
    $mypassword = mysqli_real_escape_string($db01, $pass0);

    $sql        = "SELECT * FROM merchant WHERE email = '" . $user1 . "'";

    $result     = mysqli_query($db01, $sql);

    $row        = mysqli_fetch_assoc($result);

    $count      = mysqli_num_rows($result);

    if ($count == 1) {

        if (password_verify($mypassword, $row['password'])) {
            unset($row['password']);
            if ($row['op'] == ':=') {
                $userid = $row['id'];

                $update0 = mysqli_query($db01, "UPDATE merchant SET last_ip_address='" . get_client_ip() . "'  WHERE email = '$user1' LIMIT 1 ") or die("MError0001");

                if (isset($update0)) {

                    $sessions_data = [
                        'VRXTlogged' => true,
                        'VRXTvc'  => $row['verificationCode'],
                        'VRXTid'  => $userid,
                        'VRXTemail' => $row['email']
                    ];

                    $redis->set($session_id_key, json_encode(@$sessions_data));
                    unset($row['password']);
                    $responseData = [
                        'auth-token' => $session_id_key,
                        'Message' => 'Login successfully',
                        'Active sessions' => 'You API key is active',
                        // 'restrect' => 'devices'
                        'restrict' => 'webbrowser',
                        'sessions data' => $sessions_data,
                        'database data' => $row,
                    ];
                    http_response_code(200);
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
