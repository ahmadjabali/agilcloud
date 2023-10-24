<?php
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/vendor/autoload.php"); // Path to PHPMailer autoload.php
require("{$_SERVER['DOCUMENT_ROOT']}/APIProject/controllers/database.php");
// Database configuration
// $dbHost = 'your_database_host';
// $dbUser = 'your_database_username';
// $dbPass = 'your_database_password';
// $dbName = 'your_database_name';

// Email configuration
$smtpHost = 'your_smtp_server';
$smtpUsername = 'your_smtp_username';
$smtpPassword = 'your_smtp_password';
$senderEmail = 'your_sender_email';

// Create a PHPMailer instance
$mail = new PHPMailer\PHPMailer\PHPMailer();

// Set up SMTP
$mail->isSMTP();
$mail->Host = $smtpHost;
$mail->SMTPAuth = true;
$mail->Username = $smtpUsername;
$mail->Password = $smtpPassword;
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

// Create a database connection
// $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

// Check for connection errors
if ($db01->connect_error) {
    die("Connection failed: " . $db01->connect_error);
}

// Date for comparison (today)
// $todayDate = date("Y-m-d");


// Query to select unpaid transactions and greater than or equal to today's date
// $sql = "SELECT * FROM transaction_customer WHERE debt_status = 'unpaid' AND DATE(time_created) >= CURDATE()";
$sql = "SELECT c.*, tc.* FROM customer c INNER JOIN transaction_customer tc ON c.id = tc.customer_id WHERE tc.debt_status = 'unpaid' AND DATE(tc.time_created) >= CURDATE()";

// Execute the query
$result = $db01->query($sql);

// Check for errors
if (!$result) {
    die("Error: " . $db01->error);
}

// Process and send email for each unpaid transaction.
while ($row = $result->fetch_assoc()) {
    $recipientEmail = $row['email']; // Replace with the actual recipient email field in your table
    $transactionId = $row['transaction_id']; // Replace with the actual transaction ID field in your table

    // Create the email content
    $mail->setFrom($senderEmail, 'Agile');
    $mail->addAddress($recipientEmail);
    $mail->Subject = 'Payment Reminder';
    $mail->Body = "Dear recipient,\n\nThis is a payment reminder for transaction ID: $transactionId. Please make the payment promptly.";

    // Send the email
    if (!$mail->send()) {
        echo "Email could not be sent. Mailer Error: " . $mail->ErrorInfo;
    } else {
        echo "Email sent successfully to $recipientEmail for transaction ID: $transactionId<br>";
    }
}

// Close the database connection
$db01->close();

echo "Cron job executed successfully!";
