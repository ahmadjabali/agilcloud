<?php

namespace WalletSystem;

class wallet_user
{
    private $db01;

    public function __construct($db01)
    {
        $this->db01 = $db01;
    }

    public function get_walletid_details($verificationCode, $phone_num)
    {
        $sql = "SELECT `walletid` FROM customer WHERE verification_code = '" . $verificationCode . "' and phone_num = '" . $phone_num . "' LIMIT 1 ";
        $result = mysqli_query($this->db01, $sql);
        $UserInfo = mysqli_fetch_assoc($result);
        unset($UserInfo['password']);
        return $UserInfo;
    }
}
