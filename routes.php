<?php
error_reporting(-1);
// header('Content-Type: application/json');
// header("Access-Control-Allow-Origin: *");

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Max-Age: 1728000");
    header("");
    exit();
}

$PN_DIR = "APIProject/";
require_once __DIR__ . '/router.php';
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/sanitize_input_data.php");
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/get_client.php");
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/httpreq.php");
// ##################################################
@$sanitized_post_data = sanitize_input_data($_POST, $db01);
@$sanitized_get_data = sanitize_input_data($_GET, $db01);
// require("{$_SERVER['DOCUMENT_ROOT']}/" . $PN_DIR . "/controllers/database.php");
// ##################################################
get('/phpinfo', function () {
    header("Content-Type: text/html");
    echo phpinfo();
});
// ##################################################
//                  * API / *
any('/', function () {
    // Get the posted data
    $data = json_decode(file_get_contents("php://input"));

    // Check if any data is missing
    if (!isset($data->username) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit();
    }

    // Process the data
    $username = $data->username;
    $password = $data->password;

    // TODO: Process the data (e.g., authenticate the user)

    // Send a response
    http_response_code(200);
    echo json_encode(["message" => "Data received", "username" => $username, "password" => $password]);
});

any('/reply', function () {
    header('Content-Type: application/json');
    // Check if the request method is GET
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        // Access GET data using $_GET superglobal
        $data = $_GET;
    } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Access POST data using $_POST superglobal
        $data = $_POST;
    } else {
        // For other request methods, read raw data from the request body
        $data = json_decode(file_get_contents("php://input"));
    }
    httpreq(200);
    // Convert the data back into a JSON string
    echo json_encode($data);
});


any('/input', function () {
    echo file_get_contents("php://input");
    httpreq(200);
});


// ##################################################
//                  * API Admin agile *

// auth
post('/admin/auth/$request', $PN_DIR . 'api/agile/auth/agileRouter.php');

// customer
post('/admin/customer/$request', $PN_DIR . 'api/agile/customer/customerRouter.php');

// Receipt customer
post('/admin/customer/receipt/$request', $PN_DIR . 'api/agile/customer/customerRouter.php');


// Registration
post('/admin/merchant/$request', $PN_DIR . 'api/agile/merchant/merchantRouter.php');

// Receipt merchant
post('/admin/merchant/receipt/$request', $PN_DIR . 'api/agile/merchant/merchantRouter.php');


/////////////////////////////////

//user customer
post('/admin/user/customer/$request', $PN_DIR . 'api/agile/user_customer/userRouter.php');

//user merchant
post('/admin/user/merchant/$request', $PN_DIR . 'api/agile/user_merchant/userRouter.php');

//user admin
post('/admin/user/admin/$request', $PN_DIR . 'api/agile/user_admin/userRouter.php');


// ##################################################
//                  * API device merchant *

// auth
post('/merchant/device/$request', $PN_DIR . 'api/merchant/device/deviceRouter.php');

// ##################################################
//                  * API merchant *

// auth
post('/merchant/auth/$request', $PN_DIR . 'api/merchant/auth/merchantRouter.php');


//user
post('/merchant/user/$request', $PN_DIR . 'api/merchant/user/userRouter.php');

// Receipt
post('/merchant/receipt/$request', $PN_DIR . 'api/merchant/receipt/receiptRouter.php');

//branch
post('/merchant/branch/$request', $PN_DIR . 'api/merchant/branch/branchRouter.php');

// ##################################################
//                  * API Customer  *

// Registration
post('/customer/register/$request', $PN_DIR . 'api/customer/registration/registrationRouter.php');

//auth
post('/customer/auth/$request', $PN_DIR . 'api/customer/auth/customerRouter.php');

//user
post('/customer/user/$request', $PN_DIR . 'api/customer/user/userRouter.php');

// Receipt
post('/customer/receipt/$request', $PN_DIR . 'api/customer/receipt/receiptRouter.php');

// Pay
post('/customer/pay/$request', $PN_DIR . 'api/customer/pay/payRouter.php');

// ##################################################
//                  * API auth active sessions *

// post('/users', $PN_DIR . 'api/users/userRouter.php');
// post('/users/$request', $PN_DIR . 'api/users/userRouter.php');


// ##################################################




// post('/', function(){
//     $user_details = new user_details($db01);
//     $responseData = $user_details->getUserinfo();
//     http_response_code(200);
//     echo json_encode(@$responseData);
// });

// For GET or POST
// The 404.php which is inside the views folder will be called
// The 404.php has access to $_GET and $_POST
any('/404', $PN_DIR . 'views/404.php');
mysqli_close(@$db01);
