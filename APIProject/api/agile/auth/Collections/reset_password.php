<?php

namespace AgileUserSystem;

// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

class resetpassword
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }

    public function reset_password($input)
    {

        // echo $email;

        // Define the required fields
        $required_fields  = array(
            "email",
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

            $email = $input['email'];
            // $oldPassword = $input['old-password'];
            $newPassword = sha1('vrxt' . md5(random_bytes(7) . time()));;

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

            // Verify the old password
            // if (!password_verify($oldPassword, $storedHash)) {
            //     $responseData = [
            //         'message' => "Incorrect old password.",
            //     ];
            //     http_response_code(400);
            //     return $responseData;
            // }

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the password in the database
            $updateQuery = "UPDATE agil_admin SET pkhash = '$hashedPassword' WHERE email = '" . $email . "'";
            $updateResult = mysqli_query($this->db01, $updateQuery);

            if ($updateResult) {

                // Get the user's email address (you should have a way to retrieve this)
                $userEmail = $email; // Replace with the user's email

                // Compose the email message
                $subject = "Password Reset";
                $message = "Your new password is: " . $newPassword;

                // Send the email
                $from = "noreply@agile.com"; // Replace with your email address
                $headers = "From: $from\r\n";
                $headers .= "Reply-To: $from\r\n";
                $headers .= "Content-type: text/plain\r\n";

                if (mail($userEmail, $subject, $message, $headers)) {
                    $responseData = [
                        'message' => "Password reset email sent successfully.",
                        'new Password' => $newPassword
                    ];
                } else {
                    $responseData = [
                        'message' => "Failed to send password reset email.",
                        'new Password' => $newPassword,
                        'error' => error_get_last()['message']
                    ];
                }
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
