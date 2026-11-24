<?php

session_start();

require_once "../config/db.php";


/* =========================
   LOGIN CHECK
========================= */

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit;
}


$userId = new MongoDB\BSON\ObjectId(
    $_SESSION["user_id"]
);

$users = $db->users;

$message = "";
$messageType = "";


/* =========================
   GET USER
========================= */

$user = $users->findOne([
    "_id" => $userId
]);


if (!$user) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}


/* =========================
   DELETE ACCOUNT
========================= */

if (isset($_POST["delete_account"])) {

    $users->deleteOne([
        "_id" => $userId
    ]);

    session_unset();
    session_destroy();

    header("Location: signup.php");
    exit;
}


/* =========================
   UPDATE PROFILE
========================= */

if (isset($_POST["update_profile"])) {

    $name = trim(
        $_POST["name"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    $mobile = trim(
        $_POST["mobile"] ?? ""
    );


    if (
        $name === "" ||
        $email === "" ||
        $mobile === ""
    ) {

        $message =
            "All fields are required.";

        $messageType = "error";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email.";

        $messageType = "error";

    } elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $mobile
        )
    ) {

        $message =
            "Mobile number must contain exactly 10 digits.";

        $messageType = "error";

    } else {


        /* Check email */

        $emailExists = $users->findOne([

            "email" => $email,

            "_id" => [
                '$ne' => $userId
            ]

        ]);


        /* Check mobile */

        $mobileExists = $users->findOne([

            "mobile" => $mobile,

            "_id" => [
                '$ne' => $userId
            ]

        ]);


        if ($emailExists) {

            $message =
                "Email already registered by another user.";

            $messageType = "error";

        } elseif ($mobileExists) {

            $message =
                "Mobile number already registered by another user.";

            $messageType = "error";

        } else {


            $users->updateOne(

                [
                    "_id" => $userId
                ],

                [
                    '$set' => [

                        "name" => $name,

                        "email" => $email,

                        "mobile" => $mobile,

                        "updated_at" =>
                            new MongoDB\BSON\UTCDateTime()

                    ]
                ]

            );


            $_SESSION["user_name"] =
                $name;

            $_SESSION["user_email"] =
                $email;


            $message =
                "Profile updated successfully.";

            $messageType =
                "success";


            /* Refresh user */

            $user = $users->findOne([
                "_id" => $userId
            ]);
        }
    }
}


/* =========================
   CHANGE PASSWORD
========================= */

if (isset($_POST["change_password"])) {

    $currentPassword =
        $_POST["current_password"] ?? "";

    $newPassword =
        $_POST["new_password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    if (
        $currentPassword === "" ||
        $newPassword === "" ||
        $confirmPassword === ""
    ) {

        $message =
            "All password fields are required.";

        $messageType = "error";

    } elseif (
        !password_verify(
            $currentPassword,
            $user["password"]
        )
    ) {

        $message =
            "Current password is incorrect.";

        $messageType = "error";

    } elseif (
        strlen($newPassword) < 8 ||
        !preg_match(
            "/[A-Z]/",
            $newPassword
        ) ||
        !preg_match(
            "/[a-z]/",
            $newPassword
        ) ||
        !preg_match(
            "/[0-9]/",
            $newPassword
        ) ||
        !preg_match(
            "/[\W_]/",
            $newPassword
        )
    ) {

        $message =
            "Password must contain 8+ characters, uppercase, lowercase, number and special character.";

        $messageType = "error";

    } elseif (
        $newPassword !== $confirmPassword
    ) {

        $message =
            "New passwords do not match.";

        $messageType = "error";

    } elseif (
        password_verify(
            $newPassword,
            $user["password"]
        )
    ) {

        $message =
            "New password must be different from current password.";

        $messageType = "error";

    } else {


        $hashedPassword =
            password_hash(
                $newPassword,
                PASSWORD_DEFAULT
            );


        $users->updateOne(

            [
                "_id" => $userId
            ],

            [
                '$set' => [

                    "password" =>
                        $hashedPassword,

                    "updated_at" =>
                        new MongoDB\BSON\UTCDateTime()

                ]
            ]

        );


        $message =
            "Password changed successfully.";

        $messageType =
            "success";


        $user = $users->findOne([
            "_id" => $userId
        ]);
    }
}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        MediSync - My Profile
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f2f6f9;
        }


        .navbar {

            background: #198754;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;
        }


        .navbar h2 {
            margin: 0;
        }


        .navbar a {

            color: white;

            text-decoration: none;

            margin-left: 18px;
        }


        .logout {

            background: #dc3545;

            padding: 8px 14px;

            border-radius: 5px;
        }


        .container {

            width: 95%;

            max-width: 850px;

            margin: 30px auto;
        }


        .box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 12px rgba(0,0,0,0.08);
        }


        h2 {

            margin-top: 0;

            color: #198754;
        }


        label {

            display: block;

            margin-top: 15px;

            margin-bottom: 6px;

            font-weight: bold;
        }


        input {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        button {

            width: 100%;

            padding: 12px;

            border: none;

            border-radius: 6px;

            color: white;

            font-size: 15px;

            cursor: pointer;

            margin-top: 18px;
        }


        .update {

            background: #198754;
        }


        .password {

            background: #0d6efd;
        }


        .delete {

            background: #dc3545;
        }


        .message {

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

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


        .back {

            text-align: center;

            margin-top: 20px;
        }


        .back a {

            color: #198754;

            text-decoration: none;

            font-weight: bold;
        }


        .danger-box {

            border: 1px solid #f1aeb5;

            background: #fff5f5;
        }


        .note {

            color: #666;

            font-size: 14px;

            margin-top: 5px;
        }


        @media(max-width: 600px) {

            .navbar {

                flex-direction: column;

                gap: 12px;

                text-align: center;
            }

        }

    </style>

</head>


<body>


<!-- NAVBAR -->

<div class="navbar">

    <h2>
        🩺 MediSync
    </h2>


    <div>

        <a href="dashboard.php">
            Dashboard
        </a>


        <a href="doctors.php">
            Doctors
        </a>


        <a href="appointments.php">
            My Appointments
        </a>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>


<div class="container">


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div
            class="message <?= htmlspecialchars($messageType) ?>"
        >

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- PROFILE -->

    <div class="box">

        <h2>
            👤 My Profile
        </h2>


        <form method="POST">


            <label>
                Full Name
            </label>

            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars(
                    $user["name"] ?? ""
                ) ?>"
                required
            >


            <label>
                Email
            </label>

            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars(
                    $user["email"] ?? ""
                ) ?>"
                required
            >


            <label>
                Mobile Number
            </label>

            <input
                type="tel"
                name="mobile"
                value="<?= htmlspecialchars(
                    $user["mobile"] ?? ""
                ) ?>"
                maxlength="10"
                pattern="[0-9]{10}"
                required
            >


            <button
                type="submit"
                name="update_profile"
                class="update"
            >

                💾 Update Profile

            </button>


        </form>

    </div>


    <!-- CHANGE PASSWORD -->

    <div class="box">

        <h2>
            🔐 Change Password
        </h2>


        <p class="note">
            Password must contain 8+ characters,
            uppercase, lowercase, number and special character.
        </p>


        <form method="POST">


            <label>
                Current Password
            </label>

            <input
                type="password"
                name="current_password"
                required
            >


            <label>
                New Password
            </label>

            <input
                type="password"
                name="new_password"
                required
            >


            <label>
                Confirm New Password
            </label>

            <input
                type="password"
                name="confirm_password"
                required
            >


            <button
                type="submit"
                name="change_password"
                class="password"
            >

                🔐 Change Password

            </button>


        </form>

    </div>


    <!-- DELETE ACCOUNT -->

    <div class="box danger-box">

        <h2>
            ⚠️ Delete Account
        </h2>


        <p>
            Deleting your account cannot be undone.
        </p>


        <form
            method="POST"
            onsubmit="return confirm('Are you sure you want to permanently delete your account?');"
        >

            <button
                type="submit"
                name="delete_account"
                class="delete"
            >

                🗑️ Delete My Account

            </button>

        </form>

    </div>


    <div class="back">

        <a href="dashboard.php">

            ← Back to Dashboard

        </a>

    </div>


</div>


</body>

</html>