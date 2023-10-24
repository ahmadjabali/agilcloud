<?php

namespace BranchSystem;

class branch_details
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

    public function getBranchsInfo($merchant_id_admin, $merchant_id)
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


        $sql = "SELECT c.*, a.*
        FROM branch c
        INNER JOIN address a ON c.address_id = a.id
        WHERE c.merchant_id = '" . $merchant_id . "'";
        $result = mysqli_query($this->db01, $sql);
        while ($row = mysqli_fetch_assoc($result)) {
            $userInfoArray[] = $row;
        }

        return $userInfoArray;
    }

    public function branch_edit_details($input, $verificationCode, $phone_num)
    {
        return "under develop.";
    }
    public function branch_add_details($input, $merchant_id_admin, $merchant_id)
    {
        if ($merchant_id_admin != true or $merchant_id == null) {
            return $responseData = ['error' => 'Invalid role'];
        }

        $required_fields  = array(

            "branch_name",
            "short_address",
            "street_one",
            "street_two",
            "google_maps_url",
            "short_address",
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

            $randomid_address = sha1('vrxt' . md5(random_bytes(10) . time()));
            $randomid_branch = sha1('vrxt' . md5(random_bytes(7) . time()));

            $sql = "INSERT INTO address (short_address, street_one, street_two, district, city, province, google_maps_url, id,verification_code) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
            $stmt = $this->db01->prepare($sql);

            if ($stmt) {
                $stmt->bind_param("sssssssss", $input['short_address'], $input['street_one'], $input['street_two'], $input['district'], $input['city'], $input['province'], $input['google_maps_url'], $randomid_address, $this->randomid_vc());
                if ($stmt->execute()) {
                    // Prepare the SQL statement with placeholders
                    $sql = "INSERT INTO branch (id, verification_code,merchant_id,main_branch,branch_name,address_id) VALUES (?,?,?,?,?,?)";

                    $stmt = $this->db01->prepare($sql);

                    if ($stmt) {
                        $main_branch = 0;
                        // Bind the parameters to the placeholders
                        $stmt->bind_param(
                            "ssssss",
                            $randomid_branch,
                            $this->randomid_vc(),
                            $merchantId,
                            $main_branch,
                            $input['branch_name'],
                            $randomid_address,
                        );

                        // Execute the prepared statement
                        if ($stmt->execute()) {

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
                    // Return an error response if execution fails
                    $responseData = [
                        'message' => "Error: " . $stmt->error,
                    ];
                    httpreq(400);
                    return $responseData;
                }
            } else {
                // Return an error response if execution fails
                $responseData = [
                    'message' => "Error: " . $stmt->error,
                ];
                httpreq(400);
                return $responseData;
            }


            ///
        }




        // return "under develop.";
    }
    public function branch_delete_details($input, $merchant_id_admin, $merchant_id)
    {
        if ($merchant_id_admin != true or $merchant_id == null) {
            return $responseData = ['error' => 'Invalid role'];
        }

        $required_fields  = array(
            "branch-id",
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
            $sql = "DELETE FROM branch WHERE merchant_id = '" . $merchant_id . "' AND id = '" . $input['branch-id'] . "'";

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
