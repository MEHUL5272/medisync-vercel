<?php

session_start();

require_once "../config/db.php";

$users = $db->users;

$message = "";
$messageType = "";


/* =========================
   ALREADY LOGGED-IN ADMIN
========================= */

if (
    isset($_SESSION["user_id"]) &&
    (
        ($_SESSION["role"] ?? "") === "admin" ||
        ($_SESSION["user_role"] ?? "") === "admin"
    )
) {

    header("Location: dashboard.php");
    exit;
}


/* =========================
   ADMIN LOGIN
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    if (
        $email === "" ||
        $password === ""
    ) {

        $message =
            "Email and password are required.";

        $messageType =
            "error";

    } else {

        $admin =
            $users->findOne([
                "email" => $email,
                "role" => "admin"
            ]);


        if (!$admin) {

            $message =
                "Admin account not found.";

            $messageType =
                "error";

        } elseif (
            !isset($admin["password"]) ||
            !password_verify(
                $password,
                $admin["password"]
            )
        ) {

            $message =
                "Invalid admin email or password.";

            $messageType =
                "error";

        } elseif (
            isset($admin["status"]) &&
            $admin["status"] === "blocked"
        ) {

            $message =
                "This admin account is blocked.";

            $messageType =
                "error";

        } else {

            /* =========================
               CLEAR OLD SESSION
            ========================= */

            session_regenerate_id(true);


            /* =========================
               ADMIN SESSION
            ========================= */

            $_SESSION["user_id"] =
                (string)$admin["_id"];

            $_SESSION["user_name"] =
                $admin["name"] ?? "Admin";

            $_SESSION["user_email"] =
                $admin["email"];

            $_SESSION["role"] =
                "admin";

            $_SESSION["user_role"] =
                "admin";


            /* =========================
               ADMIN DASHBOARD
            ========================= */

            header(
                "Location: dashboard.php"
            );

            exit;
        }
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
        MediSync - Admin Login
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f2f6f9;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;
        }


        .login-box {

            width: 400px;

            max-width: 90%;

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 0 20px rgba(0,0,0,0.1);
        }


        .admin-icon {

            text-align: center;

            font-size: 45px;

            margin-bottom: 10px;
        }


        h2 {

            text-align: center;

            margin-top: 0;

            margin-bottom: 25px;
        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;
        }


        input {

            width: 100%;

            padding: 12px;

            margin-bottom: 18px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        button {

            width: 100%;

            padding: 12px;

            border: none;

            border-radius: 6px;

            background: #198754;

            color: white;

            font-size: 16px;

            cursor: pointer;
        }


        button:hover {

            background: #157347;
        }


        .message {

            padding: 11px;

            border-radius: 6px;

            margin-bottom: 18px;

            text-align: center;
        }


        .error {

            background: #f8d7da;

            color: #842029;
        }


        .user-login {

            text-align: center;

            margin-top: 20px;
        }


        .user-login a {

            color: #198754;

            text-decoration: none;

            font-weight: bold;
        }


        .user-login a:hover {

            text-decoration: underline;
        }

    </style>

</head>


<body>


<div class="login-box">


    <div class="admin-icon">
        🔐
    </div>


    <h2>
        MediSync Admin Login
    </h2>


    <?php if ($message !== ""): ?>

        <div
            class="message <?= htmlspecialchars($messageType) ?>"
        >

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <form method="POST">


        <label>
            Admin Email
        </label>


        <input
            type="email"
            name="email"
            placeholder="admin@medisync.com"
            autocomplete="email"
            required
        >


        <label>
            Password
        </label>


        <input
            type="password"
            name="password"
            placeholder="Enter admin password"
            autocomplete="current-password"
            required
        >


        <button type="submit">
            🔐 Admin Login
        </button>


    </form>


    <div class="user-login">

        User Login?

        <a href="../user/login.php">
            Login Here
        </a>

    </div>


</div>


</body>

</html>