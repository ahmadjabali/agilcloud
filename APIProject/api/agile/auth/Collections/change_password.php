<?php

namespace AgileUserSystem;

// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

class changepassword
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }

    public function change_password($input, $cv, $email)
    {

        // echo $email;

        // Define the required fields
        $required_fields  = array(
            // "email",
            "old-password",
            "new-password",
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

            // if ($input['password'] != $input['repassword']) {
            //     return $responseData = [
            //         // 'ApiKey' => $session_id_key,
            //         'message' => "Your Password Don't Match repassword."
            //     ];
            //     http_response_code(400);
            // }
            // $oldPassword = password_hash($input['old-password'], PASSWORD_DEFAULT);
            // $newPassword = password_hash($input['new-password'], PASSWORD_DEFAULT);

            // // $email = $input['email'];
            $oldPassword = $input['old-password'];
            $newPassword = $input['new-password'];

            // Check if the email exists in the database
            $sqlCheckEmail = "SELECT pkhash FROM agil_admin WHERE email = '" . $email . "'";
            $resultCheckEmail = mysqli_query($this->db01, $sqlCheckEmail);

            if (!$resultCheckEmail || mysqli_num_rows($resultCheckEmail) === 0) {
                $responseData = [
                    'message' => "Email not found.",
                ];
                http_response_code(400);
                return $responseData;
            }

            // Fetch the current password hash from the database
            $row = mysqli_fetch_assoc($resultCheckEmail);
            $storedHash = $row['pkhash'];

            // Verify the old password
            if (!password_verify($oldPassword, $storedHash)) {
                $responseData = [
                    'message' => "Incorrect old password.",
                ];
                http_response_code(400);
                return $responseData;
            }

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $updateQuery = "UPDATE agil_admin SET pkhash = '$hashedPassword' WHERE email = '" . $email . "'";
            $updateResult = mysqli_query($this->db01, $updateQuery);

            if ($updateResult) {
                $responseData = [
                    'message' => "Password updated successfully.",
                ];
                http_response_code(200);
                return $responseData;
            } else {
                $responseData = [
                    'message' => "Error: " . mysqli_error($this->db01),
                ];
                http_response_code(400);
                return $responseData;
            }
        }
    }
}