<?php

namespace RiskEngineSystem;

class MerchantCheckRisk
{
    public static function checkRisk($price, $db01)
    {
        /////  ## this is a simple risk engine with limit 2000 SR for every single purchase
        if ($price <= "2000") {
            return true;
        } else {
            return false;
        }
    }
}

class CustomerCheckRisk
{
    public static function checkRisk($input, $db01)
    {
        return 0;
    }
}
