<?php

namespace MerchantSystem;

class merchant_user
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
    }

    public function get_merchant_all($input)
    {
        // Define the required fields
        $required_fields  = array(
            "auth-token",
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
            $hash_session = $input['auth-token'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($hash_session))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->admin != true) {
                        httpreq(400);
                        return $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
                    } else {
                        $sql = "SELECT * FROM merchant";
                        $result = mysqli_query($this->db01, $sql);
                        while ($row = mysqli_fetch_assoc($result)) {
                            unset($row['pkhash']);
                            $userInfoArray[] = $row;
                        }
                        httpreq(200);
                        return $userInfoArray;

                        ////

                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    httpreq(400);
                    return $responseData = ['error' => 'Invalid session role', 'hash' => $hash_session];
                }
            } else {
                httpreq(400);
                // Session data not found in Redis
                return $responseData = ['error' => 'Session not found', 'hash' => $hash_session];
            }
        }
    }

    public function get_merchant_by_id($input)
    {
        // Define the required fields
        $required_fields  = array(
            "auth-token",
            "merchant-id",
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
            $hash_session = $input['auth-token'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($hash_session))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->admin != true) {
                        httpreq(400);
                        return $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
                    } else {
                        $sql        = "SELECT * FROM merchant WHERE id = '" . $input['merchant-id'] . "'";
                        $result     = mysqli_query($this->db01, $sql);
                        $row        = mysqli_fetch_assoc($result);
                        $count      = mysqli_num_rows($result);
                        if ($count == 1) {

                            unset($row['pkhash']);
                            httpreq(200);
                            return $row;
                            ////
                        } else {
                            // 'hash_session' property is missing in the session data
                            httpreq(400);
                            return $responseData = ['error' => 'not found'];
                        }
                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    httpreq(400);
                    return $responseData = ['error' => 'Invalid session role', 'hash' => $hash_session];
                }
            } else {
                httpreq(400);
                // Session data not found in Redis
                return $responseData = ['error' => 'Session not found', 'hash' => $hash_session];
            }
        }
    }

    public function get_merchant_by_commercial($input)
    {
        // Define the required fields
        $required_fields  = array(
            "auth-token",
            "commercial-id",
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
            $hash_session = $input['auth-token'];

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($hash_session))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->admin != true) {
                        httpreq(400);
                        return $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
                    } else {
                        $sql        = "SELECT * FROM merchant WHERE commercial_registration = '" . $input['commercial-id'] . "'";
                        $result     = mysqli_query($this->db01, $sql);
                        $row        = mysqli_fetch_assoc($result);
                        $count      = mysqli_num_rows($result);
                        if ($count == 1) {

                            unset($row['pkhash']);
                            httpreq(200);
                            return $row;
                            ////
                        } else {
                            // 'hash_session' property is missing in the session data
                            httpreq(400);
                            return $responseData = ['error' => 'not found'];
                        }
                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    httpreq(400);
                    return $responseData = ['error' => 'Invalid session role', 'hash' => $hash_session];
                }
            } else {
                httpreq(400);
                // Session data not found in Redis
                return $responseData = ['error' => 'Session not found', 'hash' => $hash_session];
            }
        }
    }

    public function freeze_merchant_by_id($input)
    {
        // Define the required fields
        $required_fields  = array(
            "auth-token",
            "merchant-id",
            "status",
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
            $hash_session = $input['auth-token'];
            $status = $input['status'];

            if ($status == "1") {
                $status = "==";
                $stusw = "Success Freeze";
            } elseif ($status == "0") {
                $status = ":=";
                $stusw = "Success unfreeze";
            } else {
                return $responseData = ['error' => 'status -> 0 or 1'];
            }

            // Retrieve session data from Redis (assuming $this->redis is properly configured)
            if ($m = json_decode($this->redis->get($hash_session))) {
                // Check if the 'hash_session' 'ID' and 'ready  properties exist in the decoded JSON
                if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
                    // ready here (you need to implement ready logic)
                    // If ready is valid, proceed; otherwise, return an error response
                    if ($m->VRXTlogged != true && $m->admin != true) {
                        httpreq(400);
                        return $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
                    } else {
                        $sql        = "SELECT * FROM merchant WHERE id = '" . $input['merchant-id'] . "' ";
                        $result     = mysqli_query($this->db01, $sql);
                        $row        = mysqli_fetch_assoc($result);
                        $count      = mysqli_num_rows($result);
                        if ($count == 1) {
                            $update0 = mysqli_query($this->db01, "UPDATE merchant SET op='$status'  WHERE id = '" . $row['merchant-id'] . "' LIMIT 1 ") or die("MError0001");

                            if (isset($update0)) {
                                return $responseData = [
                                    'message' => $stusw,
                                    'status' => $status
                                ];
                            } else {
                                return $responseData = ['error' => 'not found'];
                            }
                            ////
                        } else {
                            // 'hash_session' property is missing in the session data
                            httpreq(400);
                            return $responseData = ['error' => 'not found'];
                        }
                    }
                } else {
                    // 'hash_session' property is missing in the session data
                    httpreq(400);
                    return $responseData = ['error' => 'Invalid session role', 'hash' => $hash_session];
                }
            } else {
                httpreq(400);
                // Session data not found in Redis
                return $responseData = ['error' => 'Session not found', 'hash' => $hash_session];
            }
        }
    }
}
