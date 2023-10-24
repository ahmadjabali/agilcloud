<?php
$randomid_user = sha1('vrxt' . md5(random_bytes(10) . time()));
$randomid_address = sha1('vrxt' . md5(random_bytes(10) . time()));
$randomid_vc = sha1('vrxt' . md5(random_bytes(7) . time()));
echo $walletid = md5(sha1(random_bytes(7) . $randomid_user . $randomid_address . $randomid_vc . time()));


/////////////////////////////////
// Define the required fields
$required_fields  = array(
    // "email",
    "email",
    "password",
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
}

////////////////////////////

if (property_exists($m, 'VRXTlogged') && property_exists($m, 'admin')) {
    // ready here (you need to implement ready logic)
    // If ready is valid, proceed; otherwise, return an error response
    if ($m->VRXTlogged != true && $m->admin != true) {
        $responseData = ['error' => 'Invalid role', 'hash' => $hash_session];
        // exit();
    } else {
    }
}


///////////////////////////////////////////////////////
// public //
function get_customer_users($input)
{
    // Define the required fields
    $required_fields  = array(
        "get",
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
                    $responseData = [
                        'message' => ' '
                    ];
                    httpreq(200);
                    return $responseData;
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