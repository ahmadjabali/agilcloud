<?php

namespace UsersSystem;

class user_details
{
    private $db01;

    public function __construct($db01)
    {
        $this->db01 = $db01;
        //return $this->getUserinfo();
    }

    private function randomid_vc()
    {
        return sha1('vrxt' . md5(random_bytes(7) . time()));
    }

    public function getUsersinfo($merchant_id_admin, $merchant_id)
    {
        // if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
        //     // ready here (you need to implement ready logic)
        //     // If ready is valid, proceed; otherwise, return an error response
        //     if ($m->VRXTlogged != true && $m->admin != true) {
        //         $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
        //         // exit();
        //     } else {
        //     }
        // }

        if ($merchant_id_admin != true or $merchant_id == null) {
            return $responseData = ['error' => 'Invalid role'];
        }

        $sql = "SELECT * FROM merchant_user WHERE merchant_id = '$merchant_id' ";
        $result = mysqli_query($this->db01, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            // Remove the pkhash from each row
            unset($row['pkhash']);
            if ($row['user_role'] == "owner") {
                continue;
            }
            // Add the row to the userInfoArray
            $userInfoArray[] = $row;
        }

        return $userInfoArray;
    }

    public function user_edit_details($input, $verificationCode, $phone_num)
    {
        return "under develop.";
    }
    public function user_add_details($input, $merchant_id_admin, $merchant_id)
    {
        if ($merchant_id_admin != true or $merchant_id == null) {
            return $responseData = ['error' => 'Invalid role'];
        }

        $required_fields  = array(

            "email",

            "f_name_en",
            "m_name_en",
            "g_name_en",
            "l_name_en",

            // "merchant_id",
            "branch_id",

            "password",
            "repassword"
        );

        $missing_fields = array();

        // Check if the required fields are missing
        foreach ($required_fields as $field) {
            if (!array_key_exists($field, $input)) {
                $missing_fields[] = $field;
            }
        }
        $randomid_user = sha1('vrxt' . md5(random_bytes(10) . time()));
        if (!empty($missing_fields)) {
            // Return an error response if required fields are missing
            $responseData = [
                'message' => "The following fields are missing: " . implode(', ', $missing_fields),
            ];
            httpreq(400);
            return $responseData;
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

            // Ensure that the passwords match
            if ($input['password'] !== $input['repassword']) {
                $responseData = [
                    'message' => "Error: Passwords do not match.",
                ];
                httpreq(400);
                return $responseData;
            }

            // Check if merchant_id exists in the merchant table
            $merchantId = $merchant_id;
            $sqlMerchantCheck = "SELECT id FROM merchant WHERE id = ?";
            $stmtMerchantCheck = $this->db01->prepare($sqlMerchantCheck);
            if ($stmtMerchantCheck) {
                $stmtMerchantCheck->bind_param("s", $merchantId);
                $stmtMerchantCheck->execute();
                $resultMerchantCheck = $stmtMerchantCheck->get_result();

                if ($resultMerchantCheck->num_rows === 0) {
                    $responseData = [
                        'message' => "Error: Merchant ID does not exist.",
                    ];
                    httpreq(400);
                    return $responseData;
                }

                $stmtMerchantCheck->close();
            } else {
                $responseData = [
                    'message' => "Error: " . $this->db01->error,
                ];
                httpreq(400);
                return $responseData;
            }

            // Check if branch_id exists in the branch table
            $branchId = $input['branch_id'];
            $sqlBranchCheck = "SELECT id FROM branch WHERE id = ?";
            $stmtBranchCheck = $this->db01->prepare($sqlBranchCheck);
            if ($stmtBranchCheck) {
                $stmtBranchCheck->bind_param("s", $branchId);
                $stmtBranchCheck->execute();
                $resultBranchCheck = $stmtBranchCheck->get_result();

                if ($resultBranchCheck->num_rows === 0) {
                    $responseData = [
                        'message' => "Error: Branch ID does not exist.",
                    ];
                    httpreq(400);
                    return $responseData;
                }

                $stmtBranchCheck->close();
            } else {
                $responseData = [
                    'message' => "Error: " . $this->db01->error,
                ];
                httpreq(400);
                return $responseData;
            }

            // Prepare the SQL statement with placeholders
            $sql = "INSERT INTO merchant_user (id,verification_code,email, f_name_en, m_name_en, g_name_en, l_name_en, merchant_id, branch_id, pkhash,phone_num,user_role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db01->prepare($sql);

            if ($stmt) {
                // Hash the password
                $hashedPassword = password_hash($input['password'], PASSWORD_DEFAULT);
                $role_user = "user";
                // Bind the parameters to the placeholders
                $stmt->bind_param(
                    "ssssssssssss",
                    $randomid_user,
                    $this->randomid_vc(),
                    $input['email'],
                    $input['f_name_en'],
                    $input['m_name_en'],
                    $input['g_name_en'],
                    $input['l_name_en'],
                    $merchantId,
                    $branchId,
                    $hashedPassword,
                    $input['phone_num'],
                    $role_user,

                );

                // Execute the prepared statement
                if ($stmt->execute()) {
                    // Return a success response
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



            ///
        }




        // return "under develop.";
    }
    public function user_delete_details($input, $merchant_id_admin, $merchant_id)
    {
        if ($merchant_id_admin != true or $merchant_id == null) {
            return $responseData = ['error' => 'Invalid role'];
        }

        $required_fields  = array(
            "user-id",
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
            // Create a SQL DELETE query
            $sql = "DELETE FROM merchant_user WHERE merchant_id = '" . $merchant_id . "' AND id = '" . $input['user-id'] . "'";

            // Execute the DELETE query
            $result = mysqli_query($this->db01, $sql);

            if ($result) {
                // Check if any rows were affected by the DELETE operation
                if (mysqli_affected_rows($this->db01) > 0) {
                    // Rows were deleted successfully
                    $responseData = [
                        'message' => "User deleted successfully.",
                    ];
                    http_response_code(200);
                    return $responseData;
                } else {
                    // No rows matched the condition
                    $responseData = [
                        'message' => "No user found matching the criteria.",
                    ];
                    http_response_code(404);
                    return $responseData;
                }
            } else {
                // Handle the error if the DELETE query fails
                $responseData = [
                    'message' => "Error: " . mysqli_error($this->db01),
                ];
                http_response_code(400);
                return $responseData;
            }

            ///
        }
    }
}
