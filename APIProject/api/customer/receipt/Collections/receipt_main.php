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
    public function get_last_six_months($VRXTid)
    {
        // $sql = "SELECT * FROM purchase WHERE customer_id = '$VRXTid' AND time_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH) ";
        $sql = "SELECT purchase.*, merchant.business_name,merchant.img_url FROM purchase
        CROSS JOIN merchant ON purchase.merchant_id = merchant.id
        WHERE purchase.customer_id = '$VRXTid' AND purchase.time_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";

        $result = mysqli_query($this->db01, $sql);
        $userInfoPurchaseArray = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $userInfoArray = array();
            $transactionRow = array();
            $purchaseId = $row['id'];
            $transactionSql = "SELECT * FROM transaction_customer WHERE purchase_id = '$purchaseId'";
            $transactionResult = mysqli_query($this->db01, $transactionSql);
            while ($transactionRow = mysqli_fetch_assoc($transactionResult)) {
                // $userInfoArray[] = $transactionRow;
                array_push($userInfoArray, $transactionRow);
            }
            $row['transactions'] = $userInfoArray;
            array_push($userInfoPurchaseArray, $row);
        }

        return $userInfoPurchaseArray;
    }

    public function get_data_in_date_range($input, $VRXTid)
    {
        // Define the start and end date in the format 'YYYY-MM-DD'
        // $startDate = '1970-12-30';
        // $endDate = '2023-12-30';
        $startDate = $input['start-date'];
        $endDate = $input['end-date'];

        $sql = "SELECT * FROM purchase 
                WHERE customer_id = '$VRXTid' 
                AND time_created BETWEEN '$startDate' AND '$endDate'";

        $result = mysqli_query($this->db01, $sql);
        $userInfoPurchaseArray = array();


        while ($row = mysqli_fetch_assoc($result)) {
            $userInfoArray = array();
            $transactionRow = array();
            $purchaseId = $row['id'];
            $transactionSql = "SELECT * FROM transaction_customer WHERE purchase_id = '$purchaseId'";
            $transactionResult = mysqli_query($this->db01, $transactionSql);
            while ($transactionRow = mysqli_fetch_assoc($transactionResult)) {
                // $userInfoArray[] = $transactionRow;
                array_push($userInfoArray, $transactionRow);
            }
            $row['transactions'] = $userInfoArray;
            array_push($userInfoPurchaseArray, $row);
        }

        return $userInfoPurchaseArray;
    }
}
