<?php

session_start();

require_once "../config/db.php";

$message = "";
$messageType = "";


/* =========================
   LOGIN
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    /* =========================
       VALIDATION
    ========================= */

    if ($email === "" || $password === "") {

        $message = "Email and password are required.";
        $messageType = "error";

    } else {

        $users = $db->users;


        /* =========================
           FIND USER
        ========================= */

        $user = $users->findOne([
            "email" => $email
        ]);


        if (!$user) {

            $message = "Invalid email or password.";
            $messageType = "error";

        } elseif (
            !isset($user["password"]) ||
            !password_verify(
                $password,
                $user["password"]
            )
        ) {

            $message = "Invalid email or password.";
            $messageType = "error";

        } else {


            /* =========================
               CLEAR OLD SESSION
            ========================= */

            $_SESSION = [];


            /* =========================
               CREATE LOGIN SESSION
            ========================= */

            $_SESSION["user_id"] =
                (string)$user["_id"];

            $_SESSION["user_name"] =
                $user["name"] ?? "";

            $_SESSION["user_email"] =
                $user["email"] ?? "";


            $role = $user["role"] ?? "user";


            $_SESSION["role"] = $role;
            $_SESSION["user_role"] = $role;


            /* =========================
               REDIRECT BY ROLE
            ========================= */

            if ($role === "admin") {

                header(
                    "Location: ../admin/dashboard.php"
                );

                exit;
            }


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

    <title>MediSync - Login</title>


    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        body {

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #e8f7ef 0%,
                    #f4f8fa 45%,
                    #dff5ec 100%
                );

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 25px;

            color: #222;
        }


        /* =========================
           MAIN WRAPPER
        ========================= */

        .login-wrapper {

            width: 100%;

            max-width: 1050px;

            min-height: 620px;

            background: white;

            border-radius: 24px;

            overflow: hidden;

            display: grid;

            grid-template-columns: 1fr 1fr;

            box-shadow:
                0 25px 70px rgba(
                    0,
                    0,
                    0,
                    0.12
                );
        }


        /* =========================
           LEFT SIDE
        ========================= */

        .brand-section {

            background:
                linear-gradient(
                    145deg,
                    #198754,
                    #146c43
                );

            color: white;

            padding: 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;

            position: relative;

            overflow: hidden;
        }


        .brand-section::before {

            content: "";

            position: absolute;

            width: 260px;

            height: 260px;

            border-radius: 50%;

            background:
                rgba(255,255,255,0.08);

            top: -80px;

            right: -80px;
        }


        .brand-section::after {

            content: "";

            position: absolute;

            width: 180px;

            height: 180px;

            border-radius: 50%;

            background:
                rgba(255,255,255,0.06);

            bottom: -70px;

            left: -60px;
        }


        .brand-content {

            position: relative;

            z-index: 2;
        }


        .logo {

            display: flex;

            align-items: center;

            gap: 12px;

            font-size: 27px;

            font-weight: bold;

            margin-bottom: 45px;
        }


        .logo-icon {

            width: 48px;

            height: 48px;

            background: white;

            color: #198754;

            border-radius: 14px;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 26px;

            box-shadow:
                0 8px 20px rgba(
                    0,
                    0,
                    0,
                    0.12
                );
        }


        .brand-content h1 {

            font-size: 43px;

            line-height: 1.15;

            margin-bottom: 20px;
        }


        .brand-content h1 span {

            color: #b9f2d2;
        }


        .brand-content > p {

            font-size: 16px;

            line-height: 1.8;

            color: #e3f8ec;

            max-width: 430px;

            margin-bottom: 35px;
        }


        /* =========================
           FEATURES
        ========================= */

        .features {

            display: flex;

            flex-direction: column;

            gap: 15px;
        }


        .feature {

            display: flex;

            align-items: center;

            gap: 12px;

            font-size: 14px;
        }


        .feature-icon {

            width: 34px;

            height: 34px;

            border-radius: 10px;

            background:
                rgba(255,255,255,0.15);

            display: flex;

            align-items: center;

            justify-content: center;
        }


        /* =========================
           RIGHT SIDE
        ========================= */

        .form-section {

            padding: 55px 60px;

            display: flex;

            flex-direction: column;

            justify-content: center;
        }


        .form-top {

            margin-bottom: 30px;
        }


        .welcome {

            color: #198754;

            font-size: 14px;

            font-weight: bold;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 8px;
        }


        .form-top h2 {

            font-size: 32px;

            color: #12372a;

            margin-bottom: 9px;
        }


        .form-top p {

            color: #777;

            font-size: 14px;

            line-height: 1.6;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {

            padding: 13px 15px;

            border-radius: 9px;

            margin-bottom: 20px;

            font-size: 14px;

            text-align: left;
        }


        .error {

            background: #fff0f1;

            color: #b42318;

            border: 1px solid #ffd5d8;
        }


        /* =========================
           FORM
        ========================= */

        .form-group {

            margin-bottom: 20px;
        }


        .form-group label {

            display: block;

            font-size: 14px;

            font-weight: bold;

            color: #333;

            margin-bottom: 8px;
        }


        .input-wrapper {

            position: relative;
        }


        .input-icon {

            position: absolute;

            left: 14px;

            top: 50%;

            transform:
                translateY(-50%);

            font-size: 17px;

            color: #198754;
        }


        .input-wrapper input {

            width: 100%;

            height: 49px;

            padding:
                0 45px;

            border:
                1px solid #d8e2dc;

            border-radius: 10px;

            font-size: 14px;

            background: #fbfdfc;

            transition: 0.2s;
        }


        .input-wrapper input:focus {

            outline: none;

            border-color: #198754;

            background: white;

            box-shadow:
                0 0 0 4px
                rgba(
                    25,
                    135,
                    84,
                    0.10
                );
        }


        .input-wrapper input::placeholder {

            color: #aaa;
        }


        /* =========================
           PASSWORD BUTTON
        ========================= */

        .password-toggle {

            position: absolute;

            right: 13px;

            top: 50%;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            cursor: pointer;

            color: #777;

            font-size: 16px;

            width: auto;

            padding: 4px;
        }


        .password-toggle:hover {

            color: #198754;
        }


        /* =========================
           LOGIN BUTTON
        ========================= */

        .login-button {

            width: 100%;

            height: 50px;

            border: none;

            border-radius: 10px;

            background:
                linear-gradient(
                    135deg,
                    #198754,
                    #146c43
                );

            color: white;

            font-size: 15px;

            font-weight: bold;

            cursor: pointer;

            box-shadow:
                0 8px 18px
                rgba(
                    25,
                    135,
                    84,
                    0.22
                );

            transition: 0.2s;
        }


        .login-button:hover {

            transform: translateY(-2px);

            box-shadow:
                0 12px 24px
                rgba(
                    25,
                    135,
                    84,
                    0.30
                );
        }


        .login-button:active {

            transform: translateY(0);
        }


        /* =========================
           LINKS
        ========================= */

        .form-links {

            text-align: center;

            margin-top: 24px;
        }


        .forgot {

            display: inline-block;

            margin-bottom: 18px;

            color: #198754;

            text-decoration: none;

            font-size: 14px;

            font-weight: bold;
        }


        .forgot:hover {

            text-decoration: underline;
        }


        .signup-text {

            color: #777;

            font-size: 14px;
        }


        .signup-text a {

            color: #198754;

            font-weight: bold;

            text-decoration: none;
        }


        .signup-text a:hover {

            text-decoration: underline;
        }


        /* =========================
           HOME LINK
        ========================= */

        .home-link {

            text-align: center;

            margin-top: 25px;

            padding-top: 20px;

            border-top:
                1px solid #edf1ee;
        }


        .home-link a {

            color: #666;

            text-decoration: none;

            font-size: 13px;
        }


        .home-link a:hover {

            color: #198754;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media(max-width: 850px) {

            .login-wrapper {

                grid-template-columns: 1fr;

                max-width: 520px;
            }


            .brand-section {

                padding: 35px;

                min-height: 330px;
            }


            .logo {

                margin-bottom: 25px;
            }


            .brand-content h1 {

                font-size: 34px;
            }


            .brand-content > p {

                margin-bottom: 22px;
            }


            .features {

                display: grid;

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .form-section {

                padding: 40px;
            }

        }


        @media(max-width: 520px) {

            body {

                padding: 12px;
            }


            .login-wrapper {

                border-radius: 17px;
            }


            .brand-section {

                padding: 30px 25px;
            }


            .brand-content h1 {

                font-size: 29px;
            }


            .features {

                grid-template-columns: 1fr;
            }


            .form-section {

                padding: 32px 25px;
            }


            .form-top h2 {

                font-size: 27px;
            }

        }

    </style>

</head>


<body>


<div class="login-wrapper">


    <!-- =========================
         BRAND SECTION
    ========================= -->

    <section class="brand-section">

        <div class="brand-content">


            <div class="logo">

                <div class="logo-icon">
                    🩺
                </div>

                MediSync

            </div>


            <h1>

                Your Health,

                <span>
                    Our Priority.
                </span>

            </h1>


            <p>

                Manage your healthcare journey
                easily. Connect with doctors,
                book appointments and keep
                everything organized in one place.

            </p>


            <div class="features">


                <div class="feature">

                    <div class="feature-icon">
                        ✓
                    </div>

                    Easy Appointment Booking

                </div>


                <div class="feature">

                    <div class="feature-icon">
                        ✓
                    </div>

                    Trusted Healthcare Professionals

                </div>


                <div class="feature">

                    <div class="feature-icon">
                        ✓
                    </div>

                    Simple & Secure Healthcare Management

                </div>


            </div>


        </div>

    </section>



    <!-- =========================
         LOGIN FORM
    ========================= -->

    <section class="form-section">


        <div class="form-top">

            <div class="welcome">
                Welcome Back
            </div>

            <h2>
                Sign in to MediSync
            </h2>

            <p>
                Enter your account details
                to continue.
            </p>

        </div>



        <?php if ($message !== ""): ?>

            <div
                class="message <?= htmlspecialchars($messageType) ?>"
            >

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>



        <form method="POST">


            <!-- EMAIL -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>


                <div class="input-wrapper">

                    <span class="input-icon">
                        ✉️
                    </span>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Enter your email"
                        autocomplete="email"
                        value="<?= htmlspecialchars(
                            $_POST["email"] ?? ""
                        ) ?>"
                        required
                    >

                </div>

            </div>



            <!-- PASSWORD -->

            <div class="form-group">

                <label for="password">
                    Password
                </label>


                <div class="input-wrapper">

                    <span class="input-icon">
                        🔒
                    </span>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="password-toggle"
                        onclick="togglePassword()"
                        aria-label="Show password"
                    >
                        👁️
                    </button>

                </div>

            </div>



            <!-- LOGIN -->

            <button
                type="submit"
                class="login-button"
            >
                🔐 Login to MediSync
            </button>


        </form>



        <div class="form-links">


            <a
                href="forgot_password.php"
                class="forgot"
            >
                Forgot Password?
            </a>


            <div class="signup-text">

                Don't have an account?

                <a href="signup.php">
                    Create Account
                </a>

            </div>


        </div>



        <div class="home-link">

            <a href="../index.php">
                ← Back to MediSync Home
            </a>

        </div>


    </section>


</div>



<script>

function togglePassword() {

    const password =
        document.getElementById("password");

    const button =
        document.querySelector(
            ".password-toggle"
        );


    if (password.type === "password") {

        password.type = "text";

        button.textContent = "🙈";

    } else {

        password.type = "password";

        button.textContent = "👁️";
    }
}

</script>


</body>

</html>