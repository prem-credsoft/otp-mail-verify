<?php
session_start();

require 'vendor/autoload.php'; // Include Composer autoload for all libraries including PHPMailer and Dotenv

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '.env.local');
$dotenv->load();

// Database connection setup
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "otp_verify";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['submit_email'])) {
    $email = $_POST['email'];
    $otp = rand(100000, 999999);
    $otp_created_at = date('Y-m-d H:i:s');

    // Insert OTP into the database
    $stmt = $conn->prepare("INSERT INTO users (email, otp, otp_created_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE otp=?, otp_created_at=?");
    $stmt->bind_param('sssss', $email, $otp, $otp_created_at, $otp, $otp_created_at);
    $stmt->execute();

    // Send OTP email
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'];
        $mail->Password   = $_ENV['MAIL_PASSWORD'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom($_ENV['MAIL_FROM_EMAIL'], $_ENV['MAIL_FROM_NAME']);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'OTP Verification';
        $mail->Body    = 'Your OTP is ' . $otp;

        $mail->send();
        $_SESSION['email'] = $email;
        echo 'OTP has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if (isset($_POST['submit_otp'])) {
    $email = $_SESSION['email'];
    $otp = $_POST['otp'];

    // Verify OTP
    $stmt = $conn->prepare("SELECT otp, otp_created_at FROM users WHERE email=?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $user['otp'] == $otp) {
        $otp_created_at = new DateTime($user['otp_created_at']);
        $now = new DateTime();
        $diff = $now->diff($otp_created_at);

        if ($diff->i <= 10) { // OTP is valid for 10 minutes
            $stmt = $conn->prepare("UPDATE users SET is_verified=1 WHERE email=?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'OTP verified successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'OTP expired']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid OTP']);
    }
}
?>

