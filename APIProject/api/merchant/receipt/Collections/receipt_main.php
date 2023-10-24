<?php
////////////////////////////////////////////////////////////////////////////////////////////////// -->

namespace ReceiptSystem;

require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/validateNumbe.php");

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
    public function get_last_six_months($merchant_id)
    {
        $sql = "SELECT * FROM purchase WHERE merchant_id = ? AND time_created >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";

        $stmt = mysqli_prepare($this->db01, $sql);
        mysqli_stmt_bind_param($stmt, "s", $merchant_id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        $userInfoArray = array();

        while ($row = mysqli_fetch_assoc($result)) {
            $userInfoArray[] = $row;
        }

        return $userInfoArray;
    }

    public function get_data_in_date_range($input, $merchant_id)
    {
        // Define the start and end date in the format 'YYYY-MM-DD'
        $startDate = $input['start-date'];
        $endDate = $input['end-date'];

        // Create the SQL query with placeholders
        $sql = "SELECT * FROM purchase 
                WHERE merchant_id = ? 
                AND time_created BETWEEN ? AND ?";

        // Prepare the SQL statement
        $stmt = mysqli_prepare($this->db01, $sql);

        // Bind parameters to the placeholders
        mysqli_stmt_bind_param($stmt, "sss", $merchant_id, $startDate, $endDate);

        // Execute the prepared statement
        mysqli_stmt_execute($stmt);

        // Get the result set
        $result = mysqli_stmt_get_result($stmt);

        $userInfoArray = array();

        while ($row = mysqli_fetch_assoc($result)) {
            // Add the data to the userInfoArray
            $userInfoArray[] = $row;
        }

        return $userInfoArray;
    }
}
