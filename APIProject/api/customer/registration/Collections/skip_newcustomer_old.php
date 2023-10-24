<?php

namespace RegistrationSystem;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

use NumberValidator\SaudiPhoneNumberValidator;

class Registration
{
    private $db01;

    public function __construct($db01)
    {
        $this->db01 = $db01;
        //return $this->getUserinfo();
    }

    public function AddNewCustomerbyPhone($input)
    {

        $sql        = "SELECT * FROM users_customer WHERE phone_num = '" . $input['phone_num'] . "'";
        $result     = mysqli_query($this->db01, $sql);
        $count      = mysqli_num_rows($result);
        if ($count == 1) {
            $responseData = [
                'message' => 'You have already registered with this Phone Number'
            ];
            httpreq(400);
        } else {

            $required_fields  = array("first_name", "middle_name", "last_name", "email", "phone_num", "id_type", "id_num");
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

                // $hash_password = password_hash($input['password'], PASSWORD_DEFAULT);;
                $verificationCode = md5((rand(1, 10) . time()));

                preg_match_all('/\d+/', $input['phone_num'], $matches);

                $numbers = $matches[0];
                $phone_num = (int) implode('', $numbers);

                // $testNumber = "591234567";

                if (SaudiPhoneNumberValidator::validate($phone_num)) {
                } else {
                    // echo "Invalid Saudi Arabian mobile phone number.";
                    return $responseData = [
                        'message' => "Invalid Saudi Arabian mobile phone number.",
                    ];
                }

                // $sql = "INSERT INTO users_customer () VALUES ('" . $input['companyName'] . "')";
                $sql = "INSERT INTO users_customer (`firstName`, `middleName`, `lastName`, `email`, `phone_num`, `id_type`, `id_num`,`verificationCode`) VALUES 
('" . $input['first_name'] . "', '" . $input['middle_name'] . "', '" . $input['last_name'] . "', '" . $input['email'] . "', '" . $input['phone_num'] . "', '" . $input['id_type'] . "', '" . $input['id_num'] . "','" . $verificationCode . "')";

                $result1 = mysqli_query($this->db01, $sql);
                if ($result1) {
                    $sql = " SELECT * FROM users_customer WHERE customerID = LAST_INSERT_ID()";
                    $result_customer    = mysqli_query($this->db01, $sql);
                    $row_customer       = mysqli_fetch_assoc($result_customer);
                    if ($result_customer) {
                        $responseData = [
                            'message' => "Register successfully",
                            'Phone Number for test only' => $row_customer['phone_num'],
                            'Email for test only' => $row_customer['email'],
                            'Details for test only' => $row_customer
                        ];
                    }
                }
                // $row        = mysqli_fetch_assoc($result);
            }
        }
        return $responseData;
        // return $row;
    }
}
