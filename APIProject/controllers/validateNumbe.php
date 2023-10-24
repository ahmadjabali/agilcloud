<?php

namespace NumberValidator;

class SaudiPhoneNumberValidator
{
    public static function validate($phoneNumber)
    {
        // Remove all non-digit characters from the phone number
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Check if the phone number matches the pattern for Saudi Arabian mobile numbers without the leading 0
        if (preg_match('/^5\d{8}$/', $phoneNumber)) {
            return 1;
        } else {
            return 0;
        }
    }
}


class SaudiNationalIDNumberValidator
{
    public static function validate($phoneNumber)
    {
        // Remove all non-digit characters from the phone number
        $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

        // Check if the phone number matches the pattern for Saudi ArabianNational ID numbers without the leading 1
        if (preg_match('/^1\d{9}$/', $phoneNumber)) {
            return 1;
        } else {
            return 0;
        }
    }
}



// // Test the class method
// $testNumber = "591234567";
// if (SaudiPhoneNumberValidator::validate($testNumber)) {
//     echo "Valid Saudi Arabian mobile phone number.";
// } else {
//     echo "Invalid Saudi Arabian mobile phone number.";
// }


// // Test the class
// $validator = new SaudiPhoneNumberValidator();
// $testNumber = "591234567";
// if ($validator->validateMobileNumber($testNumber)) {
//     echo "Valid Saudi Arabian mobile phone number.";
// } else {
//     echo "Invalid Saudi Arabian mobile phone number.";
// }