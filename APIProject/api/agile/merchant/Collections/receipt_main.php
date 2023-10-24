<?php
////////////////////////////////////////////////////////////////////////////////////////////////// -->

namespace ReceiptSystem;

// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

// use NumberValidator\SaudiPhoneNumberValidator;
// require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

// use NumberValidator\SaudiNationalIDNumberValidator;

class Receipt
{
    private $db01;
    private $redis;
    public function __construct($db01, $redis)
    {
        $this->db01 = $db01;
        $this->redis = $redis;
        //return $this->getUserinfo();
    }
    public function get_last_six_months($input)
    {

        $required_fields  = array(
            "merchant-id"
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

            $VRXTid = $input['merchant-id'];

            $sql = "SELECT id FROM purchase WHERE merchant_id = '$VRXTid' AND time_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
            $result = mysqli_query($this->db01, $sql);

            $userInfoArray = array();

            while ($row = mysqli_fetch_assoc($result)) {
                // Get the purchase ID from the current purchase row
                $purchaseId = $row['id'];

                // Execute a query to fetch data from transaction_customer
                $transactionSql = "SELECT * FROM transaction_customer WHERE purchase_id = '$purchaseId'";
                $transactionResult = mysqli_query($this->db01, $transactionSql);

                // Fetch the transaction_customer data and add it to the userInfoArray
                while ($transactionRow = mysqli_fetch_assoc($transactionResult)) {
                    // Add the transaction_customer data to the userInfoArray
                    $userInfoArray[] = $transactionRow;
                }
            }

            return $userInfoArray;
        }
    }

    public function get_data_in_date_range($input)
    {

        $required_fields  = array(
            "start-date",
            "end-date",
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

            // Define the start and end date in the format 'YYYY-MM-DD'
            // $startDate = '1970-12-30';
            // $endDate = '2023-12-30';
            $startDate = $input['start-date'];
            $endDate = $input['end-date'];
            $VRXTid = $input['merchant-id'];

            $sql = "SELECT id FROM purchase 
                WHERE merchant_id = '$VRXTid' 
                AND time_created BETWEEN '$startDate' AND '$endDate'";

            $result = mysqli_query($this->db01, $sql);

            $userInfoArray = array();

            while ($row = mysqli_fetch_assoc($result)) {
                // Get the purchase ID from the current purchase row
                $purchaseId = $row['id'];
                // Execute a query to fetch data from transaction_customer
                $transactionSql = "SELECT * FROM transaction_customer WHERE purchase_id = '$purchaseId'";
                $transactionResult = mysqli_query($this->db01, $transactionSql);

                // Fetch the transaction_customer data and add it to the userInfoArray
                while ($transactionRow = mysqli_fetch_assoc($transactionResult)) {
                    // Add the transaction_customer data to the userInfoArray
                    $userInfoArray[] = $transactionRow;
                }
            }

            return $userInfoArray;
        }
    }
}
