<?php

namespace UsersSystem;

class newEmployee
{
    private $db01;
    public function __construct($db01)
    {
        $this->db01 = $db01;
        //return $this->getUserinfo();
    }

    public function newEmployee($input, $VRXTvc, $VRXTemail)
    {
        $sql        = "SELECT * FROM employees WHERE email = '" . $VRXTemail . "'";
        $result     = mysqli_query($this->db01, $sql);
        $count      = mysqli_num_rows($result);
        if ($count == 1) {
            $responseData = [
                // 'message' => 'You have already registered with this email'
                'message' => 'This Employee email already have been added to the system'
            ];
            httpreq(400);
        } else {

            $required_fields  = array("firstName", "lastName", "email", "password", "phone_num");
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

                $sql        = "SELECT * FROM users WHERE email = '" . $VRXTemail . "' AND verificationCode = '" . $VRXTvc . "'";
                $result0          = mysqli_query($this->db01, $sql);
                $row_user       = mysqli_fetch_assoc($result0);
                if ($row_user) {
                    $hash_password = password_hash($input['password'], PASSWORD_DEFAULT);;
                    $verificationCode = md5((rand(1, 10) . time()));

                    preg_match_all('/\d+/', $input['phone_num'], $matches);
                    $numbers = $matches[0];
                    $phone_num = (int) implode('', $numbers);

                    $sql = "INSERT INTO employees (`companyID`,`firstName`,`lastName`,`email`,`password`,`phone_num`,`verificationCode`,`last_ipAddress`) VALUES ('" . $row_user['companyID'] . "','" . $input['firstName'] . "','" . $input['lastName'] . "','" . $input['email'] . "','" . $hash_password . "','" . $phone_num . "','" . $verificationCode . "','" . get_client_ip() . "')";
                    $result1        = mysqli_query($this->db01, $sql);
                    if ($result1) {
                        $sql = " SELECT * FROM employees WHERE userID = LAST_INSERT_ID()";
                        $result2          = mysqli_query($this->db01, $sql);
                        $row_users       = mysqli_fetch_assoc($result2);
                        if ($result2) {
                            $responseData = [
                                'message' => "Register successfully",
                                'email for test only' => $input['email'],
                                'password before hash for test only' => $input['password'],
                                'details for test only' => $row_users
                            ];
                        }
                    }
                }
            }
        }
        return $responseData;
        // return $row;
    }
}
