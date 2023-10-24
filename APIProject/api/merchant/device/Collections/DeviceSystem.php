<?php

namespace DeviceSystem;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/risk_engine.php");

use RiskEngineSystem\MerchantCheckRisk;

class device
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

    public function getlist($input)
    {

        $hash_session = $input['auth-token'];
        if ($m = json_decode($this->redis->get($hash_session))) {
            if (property_exists($m, 'VRXTlogged') && property_exists($m, 'merchant_id_admin')) {
                // ready here (you need to implement ready logic)
                // If ready is valid, proceed; otherwise, return an error response
                if ($m->VRXTlogged == true && $m->merchant_id_admin == true) {
                    $merchant_id = $m->merchant_id;
                    $sql = "SELECT * FROM device_merchant WHERE merchant_id = '$merchant_id' ";
                    $result = mysqli_query($this->db01, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $userInfoArray[] = $row;
                    }

                    return $userInfoArray;
                } elseif ($m->VRXTlogged == true && $m->merchant_id_admin == true) {

                    $merchant_id = $m->merchant_id;
                    $sql = "SELECT * FROM device_merchant WHERE merchant_id = '$merchant_id' ";
                    $result = mysqli_query($this->db01, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $userInfoArray[] = $row;
                    }

                    return $userInfoArray;
                } else {
                    $user_connecting = $m->VRXTid;
                    $sql = "SELECT * FROM device_merchant WHERE user_connecting = '$user_connecting' ";
                    $result = mysqli_query($this->db01, $sql);
                    while ($row = mysqli_fetch_assoc($result)) {
                        $userInfoArray[] = $row;
                    }

                    return $userInfoArray;
                }
            }
        } else {
            // Session data not found in Redis
            $responseData = ['error' => 'Session not found', 'hash' => $hash_session];
        }
    }


    public function request_id_device($userid, $merchant_id_reg_div, $merchant_id)
    {

        if ($merchant_id_reg_div != true or $merchant_id == null) {
            return $responseData = ['error' => 'Invalid role'];
        }

        ///////////////////////////////////////////

        $sql = "SELECT * FROM merchant_user WHERE merchant_id = '$merchant_id' and id = '$userid' ";
        $result = mysqli_query($this->db01, $sql);
        $row = mysqli_fetch_assoc($result);
        $session_id_key = "dv" . md5(rand(1, 10) . time());

        $sessions_data = [
            'VRXTlogged' => true,
            'VRXTvc'  => $row['verification_code'],
            'VRXTid'  => $userid,
            'VRXTemail' => $row['email'],
            'ready' => true,
            'merchant_id' => $row['merchant_id'],
            'branch_id' => $row['branch_id'],
        ];

        $this->redis->set($session_id_key, json_encode(@$sessions_data));
        $responseData = [
            'id_device' => $session_id_key,
            'Message' => 'request successfully',
            'restrect' => 'devices'
        ];
        http_response_code(200);
        return $responseData;
    }


    public function register_id_device($input)
    {

        $required_fields  = array(
            "device-id",
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
            $device_id = $input['device-id'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($device_id))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'ready')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->ready != true) {
                        httpreq(400);
                        return $responseData = ['error' => 'Invalid register', 'hash' => $device_id];
                        // exit();
                    } else {

                        // $row[''];


                        // Generate a random MD5 for the id field
                        $id = md5(uniqid());

                        // Define the values for other fields
                        $verificationCode = $m->VRXTvc;
                        $branchId = $m->branch_id;
                        $merchantId = $m->merchant_id;
                        $userConnecting = $m->VRXTid;
                        $deviceStatus = "Online";
                        $serialNumber = "your_serial_number";

                        // Prepare the SQL statement with placeholders
                        $sql = "INSERT INTO device_merchant (id, verification_code,merchant_id, branch_id, user_connecting, device_status, serial_number) VALUES (?, ?, ?, ?, ?, ?,?)";

                        $stmt = mysqli_prepare($this->db01, $sql);

                        if ($stmt) {
                            // Bind values to the placeholders
                            mysqli_stmt_bind_param($stmt, "sssssss", $id, $verificationCode, $merchantId, $branchId, $userConnecting, $deviceStatus, $serialNumber);

                            // Execute the prepared statement
                            if (mysqli_stmt_execute($stmt)) {
                                $sessions_data = [
                                    'VRXTlogged' => true,
                                    'VRXTvc'  => $m->VRXTvc,
                                    'VRXTid'  => $m->VRXTid,
                                    'VRXTemail' => $m->VRXTemail,
                                    'ready' => true,
                                    'ready_reg' => true,
                                    'merchant_id' => $m->merchant_id,
                                    'branch_id' => $m->branch_id,
                                ];

                                $this->redis->set($device_id, json_encode(@$sessions_data));
                                $responseData = [
                                    'auth-token' => $device_id,
                                    'merchant-user' => $m->VRXTid,
                                    'merchant' => $m->merchant_id,
                                    'branch' => $m->branch_id,
                                    'Message' => 'Register successfully',
                                    'Active device' => 'You API key is active for device',
                                    'restrect' => 'devices'
                                ];


                                http_response_code(200);
                                return $responseData;
                            } else {
                                return "Error: " . mysqli_error($this->db01);
                            }

                            // Close the prepared statement
                            mysqli_stmt_close($stmt);
                        } else {
                            return "Error in preparing the SQL statement: " . mysqli_error($this->db01);
                        }
                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    httpreq(400);
                    return $responseData = ['error' => 'Invalid Session role', 'hash' => $device_id];
                }
            } else {
                // Session data not found in Redis
                httpreq(400);
                return $responseData = ['error' => 'device id not found', 'hash' => $device_id];
            }
        }
    }

    public function order($input)
    {

        $required_fields  = array(
            "auth-token",
            "user-wallet-id",
            "receipt-information",
            "total-price",
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
            $device_id = $input['auth-token'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($device_id))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'ready_reg')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->ready_reg != true) {
                        httpreq(400);
                        return $responseData = ['error' => 'Invalid ', 'hash' => $device_id];
                        // exit();
                    } else {

                        if (MerchantCheckRisk::checkRisk($input['total-price'], $this->db01)) {
                        } else {
                            return $responseData = [
                                'message' => "The purchase is risky, limit has been reached",
                            ];
                        }

                        $walletId = $input['user-wallet-id'];
                        $sqlWalletCheck = "SELECT id FROM customer WHERE walletid = ?";
                        $stmtWalletCheck = $this->db01->prepare($sqlWalletCheck);

                        if ($stmtWalletCheck) {
                            $stmtWalletCheck->bind_param("s", $walletId);
                            $stmtWalletCheck->execute();
                            $resultWalletCheck = $stmtWalletCheck->get_result();

                            if ($resultWalletCheck->num_rows === 0) {
                                $responseData = [
                                    'message' => "Error: Wallet ID does not exist.",
                                ];
                                httpreq(400);
                                return $responseData;
                            } else {
                                // Wallet ID exists, fetch the associated ID
                                $row = $resultWalletCheck->fetch_assoc();
                                $customerId = $row['id'];

                                // Now you can use $customerId for further processing
                            }

                            $stmtWalletCheck->close();
                        } else {
                            $responseData = [
                                'message' => "Error: " . $this->db01->error,
                            ];



                            httpreq(400);
                            return $responseData;
                        }


                        /////////////////////////////////////////////////
                        // Define the data you want to insert
                        $verificationCode = $this->randomid_vc();
                        $idinv = $this->randomid_vc();
                        // $customerId = $m->merchant_id;
                        $merchantId = $m->merchant_id;
                        $items = $input['receipt-information'];
                        $totalAmount = $_POST['total-price'];
                        $issueDate = date('Y-m-d H:i:s'); // Current date and time in MySQL datetime format
                        // $invoiceStatus = $_POST['invoice_status'];
                        $invoiceStatus = "upaid";

                        // Prepare the SQL statement with placeholders
                        $sql = "INSERT INTO purchase (id,verification_code, customer_id, merchant_id, items, total_amount, issue_date, invoice_status) 
        VALUES (?, ?, ?, ?, ?, ?, ?,?)";

                        $stmt = $this->db01->prepare($sql);

                        if ($stmt) {
                            // Bind the parameters to the placeholders
                            $stmt->bind_param(
                                "sssssdss", // Adjust the data types as needed (s for string, i for integer, d for double)
                                $idinv,
                                $verificationCode,
                                $customerId,
                                $merchantId,
                                $items,
                                $totalAmount,
                                $issueDate,
                                $invoiceStatus
                            );

                            // Execute the prepared statement
                            if ($stmt->execute()) {

                                // Return a success response or perform further actions as needed
                                $f = 3;
                                function dv(float $a, float $b): float
                                {
                                    return $a / $b;
                                }
                                $addvS = 0;
                                $amount = dv($totalAmount, $f);

                                for ($i = 1; $i < $f + 1; $i++) {
                                    // echo $i;

                                    // Define the data you want to insert
                                    $verificationCode = $this->randomid_vc();
                                    $id = $this->randomid_vc();
                                    $purchaseId = $idinv;
                                    $paymentInformation = json_encode([
                                        'n' => '1'
                                    ]);

                                    if ($addvS == 0) {
                                        $debtStatus = "paid";
                                    } else {
                                        $debtStatus = "unpaid";
                                    }

                                    // Prepare the SQL statement with placeholders
                                    $sql = "INSERT INTO transaction_customer (id, verification_code, purchase_id, amount, payment_information, debt_status,customer_id) VALUES (?, ?, ?, ?, ?, ?,?)";

                                    $stmt = $this->db01->prepare($sql);

                                    if ($stmt) {
                                        // Bind the parameters to the placeholders
                                        $stmt->bind_param(
                                            "sssdsss", // Adjust the data types as needed (s for string, d for double)
                                            $id,
                                            $verificationCode,
                                            $purchaseId,
                                            $amount,
                                            $paymentInformation,
                                            $debtStatus,
                                            $customerId,
                                        );

                                        // Execute the prepared statement
                                        if ($stmt->execute()) {
                                            $addvS++;
                                        } else {
                                        }
                                    } else {
                                    }

                                    /////
                                }

                                $responseData = [

                                    "message" => "Purchase record inserted successfully"
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

                        ///////////////////////////////////////////////////////
                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    httpreq(400);
                    return $responseData = ['error' => 'Invalid Session role', 'hash' => $device_id];
                }
            } else {
                // Session data not found in Redis
                httpreq(400);
                return $responseData = ['error' => 'device id not found', 'hash' => $device_id];
            }



            ////
        }
    }
}
