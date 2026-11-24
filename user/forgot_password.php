<?php

session_start();

require_once "../config/db.php";

$message = "";
$messageType = "";

/* =========================
   STEP 3: RESET PASSWORD
========================= */

if (isset($_POST["reset_password"])) {

    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        strlen($newPassword) < 8 ||
        !preg_match("/[A-Z]/", $newPassword) ||
        !preg_match("/[a-z]/", $newPassword) ||
        !preg_match("/[0-9]/", $newPassword) ||
        !preg_match("/[\W_]/", $newPassword)
    ) {

        $message = "Password must contain 8+ characters, uppercase, lowercase, number and special character.";
        $messageType = "error";

    } elseif ($newPassword !== $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "error";

    } else {

        $users = $db->users;

        $users->updateOne(
            [
                "mobile" => $_SESSION["forgot_mobile"]
            ],
            [
                '$set' => [
                    "password" => password_hash(
                        $newPassword,
                        PASSWORD_DEFAULT
                    )
                ]
            ]
        );

        unset(
            $_SESSION["forgot_mobile"],
            $_SESSION["forgot_otp"],
            $_SESSION["forgot_otp_time"]
        );

        $message = "Password reset successful! You can now login.";
        $messageType = "success";
    }
}


/* =========================
   STEP 2: VERIFY OTP
========================= */

elseif (isset($_POST["verify_otp"])) {

    $enteredOtp = trim($_POST["otp"] ?? "");

    if (
        isset($_SESSION["forgot_otp"]) &&
        isset($_SESSION["forgot_otp_time"]) &&
        time() - $_SESSION["forgot_otp_time"] <= 300
    ) {

        if ($enteredOtp == $_SESSION["forgot_otp"]) {

            $_SESSION["forgot_otp_verified"] = true;

            $message = "OTP verified successfully.";
            $messageType = "success";

        } else {

            $message = "Invalid OTP.";
            $messageType = "error";
        }

    } else {

        $message = "OTP expired. Please try again.";
        $messageType = "error";
    }
}


/* =========================
   STEP 1: MOBILE NUMBER
========================= */

elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

    $mobile = trim($_POST["mobile"] ?? "");

    if (!preg_match("/^[0-9]{10}$/", $mobile)) {

        $message = "Please enter a valid 10 digit mobile number.";
        $messageType = "error";

    } else {

        $users = $db->users;

        $user = $users->findOne([
            "mobile" => $mobile
        ]);

        if (!$user) {

            $message = "Mobile number is not registered.";
            $messageType = "error";

        } else {

            $otp = random_int(100000, 999999);

            $_SESSION["forgot_mobile"] = $mobile;
            $_SESSION["forgot_otp"] = $otp;
            $_SESSION["forgot_otp_time"] = time();

            $message = "Your Demo OTP is: " . $otp;
            $messageType = "success";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MediSync - Forgot Password</title>

    <style>

        body {
            font-family: Arial, sans-serif;
            background: #f2f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 400px;
            margin: 70px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-top: 12px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin: 8px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #198754;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background: #157347;
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }

        .success {
            background: #d1e7dd;
            color: #0f5132;
        }

        .error {
            background: #f8d7da;
            color: #842029;
        }

        .login {
            text-align: center;
            margin-top: 20px;
        }

    </style>

</head>

<body>

<div class="container">

    <h2>🔐 Forgot Password</h2>


    <?php if ($message !== ""): ?>

        <div class="message <?= $messageType ?>">

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <?php if (isset($_SESSION["forgot_otp_verified"])): ?>

        <!-- NEW PASSWORD -->

        <h3>🔑 Create New Password</h3>

        <form method="POST">

            <label>New Password</label>

            <input
                type="password"
                name="new_password"
                placeholder="Enter strong password"
                required
            >

            <label>Confirm Password</label>

            <input
                type="password"
                name="confirm_password"
                placeholder="Confirm password"
                required
            >

            <button type="submit" name="reset_password">
                Reset Password
            </button>

        </form>


    <?php elseif (isset($_SESSION["forgot_otp"])): ?>

        <!-- OTP -->

        <h3>📱 Verify Mobile Number</h3>

        <p>Enter the 6-digit Demo OTP.</p>

        <form method="POST">

            <label>OTP</label>

            <input
                type="text"
                name="otp"
                maxlength="6"
                pattern="[0-9]{6}"
                placeholder="Enter OTP"
                required
            >

            <button type="submit" name="verify_otp">
                Verify OTP
            </button>

        </form>


    <?php else: ?>

        <!-- MOBILE -->

        <p>Enter your registered mobile number.</p>

        <form method="POST">

            <label>Mobile Number</label>

            <input
                type="tel"
                name="mobile"
                pattern="[0-9]{10}"
                maxlength="10"
                placeholder="Enter 10 digit mobile number"
                required
            >

            <button type="submit">
                Send Demo OTP
            </button>

        </form>

    <?php endif; ?>


    <div class="login">

        <a href="login.php">
            Back to Login
        </a>

    </div>

</div>

</body>

</html>