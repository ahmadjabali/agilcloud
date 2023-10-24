<?php


namespace RegistrationSystem;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

use NumberValidator\SaudiPhoneNumberValidator;

class Registration
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }
    public function AddNewCustomerbyID($input)
    {
        $id_num = $input['id_num'];
        $sql        = "SELECT * FROM customer WHERE national_id = '" . $id_num  . "'";
        $result     = mysqli_query($this->db01, $sql);
        $count      = mysqli_num_rows($result);
        if ($count == 1) {
            $responseData = [
                'message' => 'You have already registered'
            ];
            httpreq(400);
        } else {

            $required_fields  = array("id_num");
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
                        'ID' => $id_num,
                        'hash_session' => $session_id_key,
                    ];
                    $this->redis->set($session_id_key, json_encode(@$sessions_data), 300);
                    $responseData = $sessions_data;
                }
            }
        }
        httpreq(200);
        return $responseData;
        // return $row;
    }

    public function validataOTP($input)
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
                if (property_exists($m, 'hash_session') && property_exists($m, 'ID') && property_exists($m, 'otp')) {
                    // Validate OTP here (you need to implement OTP validation logic)
                    // If OTP is valid, proceed; otherwise, return an error response

                    // if ($input['hash_session'] != $m->hash_session) {
                    //     $responseData = ['error' => 'Invalid session data', 'hash' => $hash_session];
                    // }
                    if ($input['otp'] != $m->otp) {
                        $responseData = ['error' => 'Invalid OTP', 'hash' => $hash_session];
                        // exit();
                    } else {

                        // Assuming OTP validation passes, update session data
                        $session_id_key = $m->hash_session;
                        $sessions_data = [
                            'ready' => "true",
                            'ID' => $m->ID,
                            'hash_session' => $session_id_key,
                        ];
                        $this->redis->set($session_id_key, json_encode(@$sessions_data), 3600);

                        // Return a success response with updated session data
                        $responseData = $sessions_data;
                        httpreq(200);
                        return $responseData;
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

    public function insertCustemer($input)
    {
        // Define the required fields
        $required_fields  = array(
            "hash_session",
            "f_name_en",
            "m_name_en",
            "g_name_en",
            "l_name_en",
            "f_name_ar",
            "m_name_ar",
            "g_name_ar",
            "l_name_ar",
            "email",
            "phone_num",
            "short_address",
            "street_one",
            "street_two",
            "google_maps_url",
            "short_address",
            "language",
            "district",
            "city",
            "province"
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
            // All required fields are present
            $hash_session = $input['hash_session'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($hash_session))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'hash_session') && property_exists($m, 'ID') && property_exists($m, 'ready')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->ready != "true") {
                        $responseData = ['error' => 'Invalid OTP', 'hash' => $hash_session];
                        // exit();
                    } else {

                        if (SaudiPhoneNumberValidator::validate($input['phone_num'])) {
                            $user0phone_num      = $this->db01->real_escape_string($input['phone_num']);
                            $user1phone_num     = mysqli_real_escape_string($this->db01, $user0phone_num);
                            $session_id_key = "mc" . md5(random_bytes(10) . time() . $user1phone_num);
                            $sql        = "SELECT * FROM customer WHERE phone_num = '" . $user1phone_num . "'";
                            $result     = mysqli_query($this->db01, $sql);
                            $row        = mysqli_fetch_assoc($result);
                            $count      = mysqli_num_rows($result);

                            if ($count != 0) {
                                return $responseData = [
                                    'message' => "You have already registered with this Phone Number",
                                ];
                            }
                        } else {
                            // echo "Invalid Saudi Arabian mobile phone number.";
                            return $responseData = [
                                'message' => "Invalid Saudi Arabian mobile phone number.",
                            ];
                        }
                        // Add data to the customer table
                        // if ($input['password'] === $input['repassword']) {
                        $randomid_user = sha1('vrxt' . md5(random_bytes(10) . time()));
                        $randomid_address = sha1('vrxt' . md5(random_bytes(10) . time()));
                        $randomid_vc = sha1('vrxt' . md5(random_bytes(7) . time()));
                        $walletid = md5(sha1(random_bytes(7) . $randomid_user . $randomid_address . $randomid_vc . time()));
                        // $sql = "INSERT INTO address (short_address, street_one, street_two,district,city,province, google_maps_url, id) 
                        // VALUES ('$input[short_address]', '$input[street_one]', '$input[street_two]','$input[district]','$input[city]','$input[province]', '$input[google_maps_url]', '$randomid_address')";

                        $sql = "INSERT INTO address (short_address, street_one, street_two, district, city, province, google_maps_url, id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $this->db01->prepare($sql);

                        if ($stmt) {
                            $stmt->bind_param("ssssssss", $input['short_address'], $input['street_one'], $input['street_two'], $input['district'], $input['city'], $input['province'], $input['google_maps_url'], $randomid_address);
                            if ($stmt->execute()) {

                                // Prepare the SQL statement with placeholders
                                $sql = "INSERT INTO customer (id, f_name_en, m_name_en, g_name_en, l_name_en, f_name_ar, m_name_ar, g_name_ar, l_name_ar, email, phone_num, pkhash, national_id, language, address_id, verification_code,walletid) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

                                $stmt = $this->db01->prepare($sql);

                                if ($stmt) {
                                    // Bind the parameters to the placeholders
                                    $stmt->bind_param("sssssssssssssssss", $randomid_user, $input['f_name_en'], $input['m_name_en'], $input['g_name_en'], $input['l_name_en'], $input['f_name_ar'], $input['m_name_ar'], $input['g_name_ar'], $input['l_name_ar'], $input['email'], $input['phone_num'], $input['password'], $m->ID, $input['language'], $randomid_address, $randomid_vc, $walletid);

                                    // Execute the prepared statement
                                    if ($stmt->execute()) {
                                        // Return a success response with updated session data
                                        // $this->redis->get($hash_session);
                                        $this->redis->delete($hash_session);
                                        $responseData = [
                                            "message" => "done"
                                        ];
                                        httpreq(200);
                                        return $responseData;
                                    } else {
                                        // Return an error response if execution fails
                                        $responseData = [
                                            'message' => "Error: " . $stmt->error,
                                        ];
                                        httpreq(400);
                                        return $responseData;
                                    }

                                    // Close the prepared statement
                                    $stmt->close();
                                } else {
                                    // Handle the case where the statement couldn't be prepared
                                    $responseData = [
                                        'message' => "Error: " . $this->db01->error,
                                    ];
                                    httpreq(400);
                                    return $responseData;
                                }
                            } else {
                                // Return an error response if any validation checks fail
                                $responseData = [
                                    'message' => "Error: " . $sql . "<br>" . $this->db01->error,
                                ];
                                httpreq(400);
                                return $responseData;
                            }
                        } else {
                            // Return an error response if any validation checks fail
                            $responseData = [
                                'message' => "Error: " . $sql . "<br>" . $this->db01->error,
                            ];
                            httpreq(400);
                            return $responseData;
                        }
                        // } else {
                        //     // Return an error response if the passwords do not match
                        //     $responseData = [
                        //         'message' => "Error: Passwords do not match"
                        //     ];
                        //     httpreq(400);
                        //     return $responseData;
                        // }
                    }
                } else {
                    // 'hash_session' property is missing in the session data
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
