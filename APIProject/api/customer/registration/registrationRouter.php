<?php
// Require the database controller file
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");

// Function to list files in a given folder
function listFiles($folder)
{
    // Get an array of files in the given folder
    $files = scandir($folder);

    // Loop through each file
    foreach ($files as $file) {
        // Skip any hidden files or files that start with 'skip_'
        if ($file == "." || $file == ".." || preg_match('/^skip_/', $file)) {
            continue;
        }

        // If the file is a .php file, include it
        if (pathinfo($file, PATHINFO_EXTENSION) == "php") {
            // echo $folder . '/' . $file;
            require_once $folder . '/' . $file;
        }
    }
}

// Use the Registration class from the RegistrationSystem namespace
use RegistrationSystem\Registration;

// Escape the request variable
@$request     = $db01->real_escape_string(@$request);

// List all the files in the Collections directory
listFiles(__DIR__ . "/Collections");

// Check which step of the registration process we are on and call the appropriate function
if ($request == "step1") {
    @$newCustomer = new Registration($db01, $redis);
    @$responseData = $newCustomer->AddNewCustomerbyID(sanitize_input_data($_POST, $db01));
} elseif ($request == "step2") {
    @$validataOTP = new Registration($db01, $redis);
    @$responseData = $validataOTP->validataOTP(sanitize_input_data($_POST, $db01));
} elseif ($request == "step3") {
    @$insertCustemer = new Registration($db01, $redis);
    @$responseData = $insertCustemer->insertCustemer(sanitize_input_data($_POST, $db01));
} else {
    // Return a 403 error if the request is not valid
    $responseData = httpreq(403);
}

// Unset the password from the response data 
// unset($responseData['password']);

// Echo the response data as JSON
echo json_encode($responseData);

// Close the database connection
mysqli_close(@$db01);
