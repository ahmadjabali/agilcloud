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

    public function getUserinfo($verificationCode, $phone_num)
    {
        $sql = "SELECT c.*, a.*
                FROM customer c
                INNER JOIN address a ON c.address_id = a.id
                WHERE c.verification_code = '" . $verificationCode . "' AND c.phone_num = '" . $phone_num . "'
                LIMIT 1";

        $result = mysqli_query($this->db01, $sql);
        $userInfo = mysqli_fetch_assoc($result);

        if ($userInfo) {
            // Remove the 'password' field from the result
            unset($userInfo['password']);
            return $userInfo;
        } else {
            return null; // No matching record found
        }
    }


    public function user_edit_details($input, $verificationCode, $phone_num)
    {
        return "under develop.";
    }
}
