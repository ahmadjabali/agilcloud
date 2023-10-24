<?php


namespace AgileUserSystem;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

class agile_user
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }


    public function login_agile_by_email($input)
    {

        // Define the required fields
        $required_fields  = array(
            // "email",
            "email",
            "password",
        );

        $missing_fields = array();

        // Check if the required fields are missing
        foreach ($required_fields as $field) {
            if (!array_key_exists($field, $input)) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            // Return an error response if required fields are missing
            $responseData = [
                'message' => "The following fields are missing: " . implode(', ', $missing_fields),
            ];
            httpreq(400);
            return $responseData;
        } else {

            $session_id_key = "mc" . md5(rand(1, 10) . time());
            // $user0      = $this->db01->real_escape_string($_POST['email']);
            // $pass0      = $this->db01->real_escape_string($_POST['password']);
            // $user1     = mysqli_real_escape_string($this->db01, $user0);
            // $mypassword = mysqli_real_escape_string($this->db01, $pass0);

            $sql        = "SELECT * FROM agil_admin WHERE email = '" . $input['email'] . "'";

            $result     = mysqli_query($this->db01, $sql);

            $row        = mysqli_fetch_assoc($result);

            $count      = mysqli_num_rows($result);

            if ($count == 1) {

                if (password_verify($input['password'], $row['pkhash'])) {
                    unset($row['pkhash']);
                    if ($row['op'] == ':=') {
                        $userid = $row['id'];

                        $update0 = mysqli_query($this->db01, "UPDATE agil_admin SET last_ip_address='" . get_client_ip() . "'  WHERE email = '" . $input['email'] . "' LIMIT 1 ") or die("MError0001");

                        if (isset($update0)) {
                            $sessions_data = [
                                'admin' => true,
                                'VRXTlogged' => true,
                                'VRXTvc'  => $row['verification_code'],
                                'VRXTid'  => $userid,
                                'VRXTemail' => $row['email']
                            ];

                            $this->redis->set($session_id_key, json_encode(@$sessions_data));
                            unset($row['pkhash']);
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
        }

        // mysqli_close(@$this->db01);
        // $this->redis->close();
        return $responseData;
    }
}
