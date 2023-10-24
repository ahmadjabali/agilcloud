<?php
////////////////////////////////////////////////////////////////////////////////////////////////// -->

namespace CustomerSystem;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

use NumberValidator\SaudiPhoneNumberValidator;
// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

// use NumberValidator\SaudiNationalIDNumberValidator;

class Customer
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }
    public function send_sms_token($input)
    {
        $id_num = $input['phone_num'];
        $sql        = "SELECT * FROM customer WHERE phone_num = '" . $id_num  . "'";
        $result     = mysqli_query($this->db01, $sql);
        $count      = mysqli_num_rows($result);

        if ($count == 1) {

            // $required_fields  = array("first_name", "middle_name", "last_name", "email", "phone_num", "id_type", "id_num");
            $required_fields  = array("phone_num");
            $missing_fields = array();

            foreach ($required_fields as $field) {
                if (!array_key_exists($field, $input)) {
                    $missing_fields[] = $field;
                }
            }

            if (!empty($missing_fields)) {
                $responseData = [
                    'message' => "The following fields are missing: " . implode(', ', $missing_fields),
                ];
                httpreq(400);
                return $responseData;
            } else {

                // require_once('/vendor/autoload.php');
                require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/vendor/autoload.php");

                $client = new \GuzzleHttp\Client();

                $sandotp_1 = strval(random_int(1, 9)) . random_int(0, 9) . random_int(0, 9) . random_int(0, 9) . random_int(0, 9) . random_int(0, 9);
                $response = $client->request('POST', 'http://localhost/input', [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'json'    => ['otp' => $sandotp_1]
                ]);
                // var_dump($response);
                $req = json_decode($response->getBody());
                if ($response->getStatusCode() == "200") {
                    $session_id_key = md5(rand(1, 9) . $sandotp_1 . $id_num);
                    $sessions_data = [
                        'otp' => $sandotp_1,
                        'phone_num' => $id_num,
                        'hash_session' => $session_id_key,
                    ];
                    $this->redis->set($session_id_key, json_encode(@$sessions_data), 300);
                    $responseData = $sessions_data;
                }
            }
        } else {

            $responseData = [
                'message' => 'there are no record with this Phone Number'
            ];
            httpreq(400);
        }
        httpreq(200);
        return $responseData;
        // return $row;
    }

    public function get_token($input)
    {
        // Define the required fields
        $required_fields = array("otp", "hash_session");
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
            // All required fields are present
            $hash_session = $input['hash_session'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($hash_session))) {
                // Check if the 'hash_session' and 'ID' properties exist in the decoded JSON
                if (property_exists($m, 'hash_session') && property_exists($m, 'phone_num') && property_exists($m, 'otp')) {
                    // Validate OTP here (you need to implement OTP validation logic)
                    // If OTP is valid, proceed; otherwise, return an error response

                    // if ($input['hash_session'] != $m->hash_session) {
                    //     $responseData = ['error' => 'Invalid session data', 'hash' => $hash_session];
                    // }
                    if ($input['otp'] != $m->otp) {
                        return $responseData = ['error' => 'Invalid OTP', 'hash' => $hash_session];
                        // exit();
                    } else {


                        if ($m->phone_num != "") {

                            $user0phone_num      = $this->db01->real_escape_string($m->phone_num);
                            $user1phone_num     = mysqli_real_escape_string($this->db01, $user0phone_num);
                            $session_id_key = "mc" . md5(random_bytes(10) . time() . $user1phone_num);
                            $sql        = "SELECT * FROM customer WHERE phone_num = '" . $user1phone_num . "'";
                            $result     = mysqli_query($this->db01, $sql);
                            $row        = mysqli_fetch_assoc($result);
                            $count      = mysqli_num_rows($result);

                            if ($count == 1) {
                                if ($row['op'] == ':=') {

                                    $update0 = mysqli_query($this->db01, "UPDATE customer SET last_ip_address='" . get_client_ip() . "'  WHERE phone_num = '$user1phone_num' LIMIT 1 ") or die("MError0001");
                                    if (isset($update0)) {


                                        // Assuming OTP validation passes, update session data
                                        $session_id_key = $m->hash_session;
                                        $sessions_data = [
                                            'VRXTlogged' => true,
                                            'VRXTvc' => $row['verification_code'],
                                            'VRXTemail' => $row['email'],
                                            'VRXTid' => $row['id'],
                                            'VRXTphone_num' => $m->phone_num,
                                            'userdata' => $row,
                                            'auth-token' => $session_id_key,
                                        ];



                                        $this->redis->set($session_id_key, json_encode(@$sessions_data), 3600);

                                        // Return a success response with updated session data
                                        $responseData = $sessions_data;
                                        httpreq(200);
                                        return $responseData;
                                    }
                                } elseif ($row['op'] == '==') {

                                    $responseData = [
                                        'message' => 'Your Account has been Disabled or banned'
                                    ];
                                    http_response_code(401);

                                    $output = "";
                                } elseif ($row['op'] == '*=') {
                                    $responseData = [
                                        'message' => 'Please Verify Your Account'
                                    ];
                                    http_response_code(401);
                                }
                            } else {
                                $responseData = ['error' => 'Invalid session data', 'hash' => $hash_session];
                            }
                        } else {
                            $responseData = ['error' => 'Invalid session data', 'hash' => $hash_session];
                        }
                    }
                } else {
                    // 'hash_session' or 'ID' property is missing in the session data
                    $responseData = ['error' => 'Invalid session data', 'hash' => $hash_session];
                }
            } else {
                // Session data not found in Redis
                $responseData = ['error' => 'Session not found', 'hash' => $hash_session];
            }

            // Return an error response if any validation checks fail
            httpreq(400);
            return $responseData;
        }
    }
}
