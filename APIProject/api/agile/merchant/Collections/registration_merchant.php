<?php


namespace RegistrationSystemME;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

use NumberValidator\SaudiPhoneNumberValidator;

class RegistrationME
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }

    private function randomid_vc()
    {
        return sha1('vrxt' . md5(random_bytes(7) . time()));
    }

    private function generateRandomColorPair()
    {
        $bgColor = sprintf('%06X', mt_rand(0, 0xFFFFFF));
        $r = hexdec(substr($bgColor, 0, 2));
        $g = hexdec(substr($bgColor, 2, 2));
        $b = hexdec(substr($bgColor, 4, 2));
        $yiq = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;
        $textColor = ($yiq >= 128) ? 'black' : 'white';
        return array("b" => $bgColor, "t" => $textColor);
    }

    public function insertFullMerchantUser($input)
    {
        // Define the required fields
        $required_fields  = array(
            "hash_session",
            "f_name_en",
            "m_name_en",
            "g_name_en",
            "l_name_en",

            "phone_num",
            "user_role",
            "email",
            "phone_num",
            "password",
            "repassword",

            "commercial_registration",
            "business_name",
            "currency",

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
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->admin != true) {
                        $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
                        // exit();
                    } else {

                        // Check if the provided email already exists in the merchant_user table
                        $email = $input['email'];
                        $sqlEmailCheck = "SELECT id FROM merchant_user WHERE email = ?";
                        $stmtEmailCheck = $this->db01->prepare($sqlEmailCheck);
                        if ($stmtEmailCheck) {
                            $stmtEmailCheck->bind_param("s", $email);
                            $stmtEmailCheck->execute();
                            $resultEmailCheck = $stmtEmailCheck->get_result();

                            if ($resultEmailCheck->num_rows > 0) {
                                $responseData = [
                                    'message' => "Error: Email already exists.",
                                ];
                                httpreq(400);
                                return $responseData;
                            }

                            $stmtEmailCheck->close();
                        } else {
                            $responseData = [
                                'message' => "Error: " . $this->db01->error,
                            ];
                            httpreq(400);
                            return $responseData;
                        }



                        if (SaudiPhoneNumberValidator::validate($input['phone_num'])) {
                            $user0phone_num      = $this->db01->real_escape_string($input['phone_num']);
                            $user1phone_num     = mysqli_real_escape_string($this->db01, $user0phone_num);
                            $session_id_key = "mc" . md5(random_bytes(10) . time() . $user1phone_num);
                            $sql        = "SELECT * FROM merchant_user WHERE phone_num = '" . $user1phone_num . "'";
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
                        if ($input['password'] === $input['repassword']) {
                            $randomid_user = sha1('vrxt' . md5(random_bytes(10) . time()));
                            $randomid_address = sha1('vrxt' . md5(random_bytes(10) . time()));

                            $randomid_merchant = sha1('vrxt' . md5(random_bytes(7) . time()));
                            $randomid_branch = sha1('vrxt' . md5(random_bytes(7) . time()));

                            // $sql = "INSERT INTO address (short_address, street_one, street_two,district,city,province, google_maps_url, id) 
                            // VALUES ('$input[short_address]', '$input[street_one]', '$input[street_two]','$input[district]','$input[city]','$input[province]', '$input[google_maps_url]', '$randomid_address')";

                            $sql1 = "INSERT INTO address (short_address, street_one, street_two, district, city, province, google_maps_url, id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt1 = $this->db01->prepare($sql1);

                            if ($stmt1) {
                                $stmt1->bind_param("ssssssss", $input['short_address'], $input['street_one'], $input['street_two'], $input['district'], $input['city'], $input['province'], $input['google_maps_url'], $randomid_address);
                                if ($stmt1->execute()) {


                                    $sql2 = "INSERT INTO merchant (id,verification_code,business_name,commercial_registration,img_url) 
                                    VALUES (?, ?, ?, ?,?)";
                                    $stmt2 = $this->db01->prepare($sql2);

                                    if ($stmt2) {
                                        $randomColorPair = $this->generateRandomColorPair();
                                        $imglogo = "https://placehold.co/256x256/" . $randomColorPair['b'] . "/" . $randomColorPair['t'] . "/png?text=" . $input['business_name'];
                                        $stmt2->bind_param("sssss", $randomid_merchant, $this->randomid_vc(), $input['business_name'], $input['commercial_registration'], $imglogo);
                                        if ($stmt2->execute()) {



                                            $sql3 = "INSERT INTO branch (id,verification_code,merchant_id,main_branch,branch_name,address_id) 
                                            VALUES (?, ?, ?, ?, ?, ?)";
                                            $stmt3 = $this->db01->prepare($sql3);

                                            if ($stmt3) {
                                                $main = "main";
                                                $mainnum = 1;
                                                $stmt3->bind_param("ssssss", $randomid_branch, $this->randomid_vc(), $randomid_merchant, $mainnum, $main, $randomid_address);
                                                if ($stmt3->execute()) {

                                                    $sql4 = "INSERT INTO merchant_user (id,verification_code,f_name_en,m_name_en,g_name_en,l_name_en,phone_num,user_role,merchant_id,branch_id,email,pkhash) 
                                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?,?,?,?,?)";
                                                    $hashed_password = password_hash($input['password'], PASSWORD_DEFAULT);

                                                    $stmt4 = $this->db01->prepare($sql4);
                                                    $email = $input['email'];
                                                    $owner = "owner";
                                                    if ($stmt4) {
                                                        $stmt4->bind_param("ssssssssssss", $randomid_user, $this->randomid_vc(), $input['f_name_en'], $input['m_name_en'], $input['g_name_en'], $input['l_name_en'], $input['phone_num'], $owner, $randomid_merchant, $randomid_branch, $email, $hashed_password);
                                                        if ($stmt4->execute()) {
                                                            $responseData = [
                                                                'message' => "Done: ",
                                                            ];
                                                            httpreq(200);
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
                                                    //
                                                }
                                            } else {
                                                // Return an error response if any validation checks fail
                                                $responseData = [
                                                    'message' => "Error: " . $sql . "<br>" . $this->db01->error,
                                                ];
                                                httpreq(400);
                                                return $responseData;
                                            }

                                            //
                                        }
                                    } else {
                                        // Return an error response if any validation checks fail
                                        $responseData = [
                                            'message' => "Error: " . $sql . "<br>" . $this->db01->error,
                                        ];
                                        httpreq(400);
                                        return $responseData;
                                    }
                                    //////
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
                        } else {
                            // Return an error response if the passwords do not match
                            $responseData = [
                                'message' => "Error: Passwords do not match"
                            ];
                            httpreq(400);
                            return $responseData;
                        }
                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    $responseData = ['error' => 'Invalid session role', 'hash' => $hash_session];
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
