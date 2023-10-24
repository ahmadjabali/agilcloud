<?php
////////////////////////////////////////////////////////////////////////////////////////////////// -->

namespace PaySystem;

// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

// use NumberValidator\SaudiPhoneNumberValidator;
// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

// use NumberValidator\SaudiNationalIDNumberValidator;

class pay_user
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }
    public function approve($input, $VRXTid)
    {


        $required_fields  = array(
            "transaction-id",
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

            $sql        = "SELECT * FROM transaction_customer WHERE id = '" . $input['transaction-id'] . "' AND customer_id = '" . $VRXTid . "' AND  debt_status='unpaid' ";
            $result     = mysqli_query($this->db01, $sql);
            $row        = mysqli_fetch_assoc($result);
            $count      = mysqli_num_rows($result);

            if ($count == 1) {
                $update0 = mysqli_query($this->db01, "UPDATE transaction_customer SET debt_status='paid'  WHERE id = '" . $input['transaction-id'] . "' AND customer_id = '" . $VRXTid . "'");
                if (isset($update0)) {
                    $responseData = [
                        "req" => "Approved"
                    ];
                    httpreq(200);
                    return $responseData;
                }
            } else {
                $responseData = ['error' => 'Invalid session data'];
            }
        }
    }
}
