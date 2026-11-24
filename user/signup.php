<?php

session_start();

require_once "../config/db.php";

$message = "";
$messageType = "";


/* =========================
   OTP VERIFICATION
========================= */

if (isset($_POST["verify_otp"])) {

    $enteredOtp = trim($_POST["otp"] ?? "");


    if (
        isset($_SESSION["signup_otp"]) &&
        isset($_SESSION["signup_otp_time"]) &&
        time() - $_SESSION["signup_otp_time"] <= 300
    ) {

        if ($enteredOtp == $_SESSION["signup_otp"]) {

            $users = $db->users;


            /* Check email again */

            $existingUser = $users->findOne([
                "email" => $_SESSION["signup_email"]
            ]);


            if ($existingUser) {

                $message = "Email already registered.";
                $messageType = "error";

            } else {


                /* Create user */

                $users->insertOne([

                    "name" =>
                        $_SESSION["signup_name"],

                    "email" =>
                        $_SESSION["signup_email"],

                    "mobile" =>
                        $_SESSION["signup_mobile"],

                    "password" =>
                        $_SESSION["signup_password"],

                    "role" =>
                        "user",

                    "mobile_verified" =>
                        true,

                    "created_at" =>
                        new MongoDB\BSON\UTCDateTime()

                ]);


                /* Clear signup session */

                unset(

                    $_SESSION["signup_otp"],

                    $_SESSION["signup_otp_time"],

                    $_SESSION["signup_name"],

                    $_SESSION["signup_email"],

                    $_SESSION["signup_mobile"],

                    $_SESSION["signup_password"]

                );


                $message =
                    "Signup successful! You can now login.";

                $messageType = "success";
            }

        } else {

            $message = "Invalid OTP.";
            $messageType = "error";
        }

    } else {

        $message =
            "OTP expired. Please signup again.";

        $messageType = "error";
    }
}


/* =========================
   SIGNUP FORM
========================= */

elseif ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST["name"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $mobile =
        trim($_POST["mobile"] ?? "");

    $password =
        $_POST["password"] ?? "";

    $confirmPassword =
        $_POST["confirm_password"] ?? "";


    /* =========================
       VALIDATION
    ========================= */

    if (
        $name === "" ||
        $email === "" ||
        $mobile === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {

        $message =
            "All fields are required.";

        $messageType = "error";

    }


    elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $messageType = "error";
    }


    elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $mobile
        )
    ) {

        $message =
            "Mobile number must contain exactly 10 digits.";

        $messageType = "error";
    }


    elseif (
        strlen($password) < 8 ||
        !preg_match(
            "/[A-Z]/",
            $password
        ) ||
        !preg_match(
            "/[a-z]/",
            $password
        ) ||
        !preg_match(
            "/[0-9]/",
            $password
        ) ||
        !preg_match(
            "/[\W_]/",
            $password
        )
    ) {

        $message =
            "Password must contain 8+ characters, uppercase, lowercase, number and special character.";

        $messageType = "error";
    }


    elseif (
        $password !== $confirmPassword
    ) {

        $message =
            "Passwords do not match.";

        $messageType = "error";
    }


    else {

        $users = $db->users;


        /* =========================
           CHECK EMAIL
        ========================= */

        $existingEmail =
            $users->findOne([
                "email" => $email
            ]);


        /* =========================
           CHECK MOBILE
        ========================= */

        $existingMobile =
            $users->findOne([
                "mobile" => $mobile
            ]);


        if ($existingEmail) {

            $message =
                "Email already registered.";

            $messageType = "error";

        }


        elseif ($existingMobile) {

            $message =
                "Mobile number already registered.";

            $messageType = "error";

        }


        else {


            /* =========================
               GENERATE OTP
            ========================= */

            $otp =
                random_int(
                    100000,
                    999999
                );


            /* =========================
               STORE TEMP DATA
            ========================= */

            $_SESSION["signup_name"] =
                $name;

            $_SESSION["signup_email"] =
                $email;

            $_SESSION["signup_mobile"] =
                $mobile;


            $_SESSION["signup_password"] =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            $_SESSION["signup_otp"] =
                $otp;

            $_SESSION["signup_otp_time"] =
                time();


            /* =========================
               DEMO OTP
            ========================= */

            $message =
                "Your Demo OTP is: " . $otp;

            $messageType =
                "success";
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
        MediSync - Create Account
    </title>


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

        .signup-wrapper {

            width: 100%;

            max-width: 1080px;

            min-height: 650px;

            background: white;

            border-radius: 24px;

            overflow: hidden;

            display: grid;

            grid-template-columns: 0.9fr 1.1fr;

            box-shadow:
                0 25px 70px
                rgba(
                    0,
                    0,
                    0,
                    0.12
                );
        }


        /* =========================
           LEFT BRAND
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

            width: 300px;

            height: 300px;

            border-radius: 50%;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.08
                );

            top: -100px;

            right: -100px;
        }


        .brand-section::after {

            content: "";

            position: absolute;

            width: 190px;

            height: 190px;

            border-radius: 50%;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.06
                );

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

            margin-bottom: 40px;
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
                0 8px 20px
                rgba(
                    0,
                    0,
                    0,
                    0.12
                );
        }


        .brand-content h1 {

            font-size: 40px;

            line-height: 1.18;

            margin-bottom: 18px;
        }


        .brand-content h1 span {

            color: #b9f2d2;
        }


        .brand-content > p {

            font-size: 15px;

            line-height: 1.8;

            color: #e3f8ec;

            max-width: 390px;

            margin-bottom: 32px;
        }


        /* =========================
           BENEFITS
        ========================= */

        .benefits {

            display: flex;

            flex-direction: column;

            gap: 15px;
        }


        .benefit {

            display: flex;

            align-items: center;

            gap: 12px;

            font-size: 14px;
        }


        .benefit-icon {

            width: 35px;

            height: 35px;

            border-radius: 10px;

            background:
                rgba(
                    255,
                    255,
                    255,
                    0.15
                );

            display: flex;

            align-items: center;

            justify-content: center;
        }


        /* =========================
           FORM SECTION
        ========================= */

        .form-section {

            padding: 45px 55px;

            display: flex;

            flex-direction: column;

            justify-content: center;
        }


        .form-top {

            margin-bottom: 22px;
        }


        .welcome {

            color: #198754;

            font-size: 13px;

            font-weight: bold;

            text-transform: uppercase;

            letter-spacing: 1px;

            margin-bottom: 7px;
        }


        .form-top h2 {

            font-size: 31px;

            color: #12372a;

            margin-bottom: 8px;
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

            padding: 12px 14px;

            border-radius: 9px;

            margin-bottom: 18px;

            font-size: 13px;

            line-height: 1.5;
        }


        .success {

            background: #e7f7ee;

            color: #0f5132;

            border:
                1px solid #c7ead6;
        }


        .error {

            background: #fff0f1;

            color: #b42318;

            border:
                1px solid #ffd5d8;
        }


        /* =========================
           FORM GRID
        ========================= */

        .form-grid {

            display: grid;

            grid-template-columns:
                1fr 1fr;

            gap: 0 15px;
        }


        .form-group {

            margin-bottom: 15px;
        }


        .full {

            grid-column:
                1 / -1;
        }


        .form-group label {

            display: block;

            font-size: 13px;

            font-weight: bold;

            color: #333;

            margin-bottom: 7px;
        }


        /* =========================
           INPUT
        ========================= */

        .input-wrapper {

            position: relative;
        }


        .input-icon {

            position: absolute;

            left: 13px;

            top: 50%;

            transform:
                translateY(-50%);

            color: #198754;

            font-size: 16px;
        }


        .input-wrapper input {

            width: 100%;

            height: 46px;

            padding:
                0 40px;

            border:
                1px solid #d8e2dc;

            border-radius: 9px;

            font-size: 13px;

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
           PASSWORD TOGGLE
        ========================= */

        .password-toggle {

            position: absolute;

            right: 10px;

            top: 50%;

            transform:
                translateY(-50%);

            border: none;

            background: transparent;

            cursor: pointer;

            color: #777;

            font-size: 15px;

            width: auto;

            padding: 4px;
        }


        .password-toggle:hover {

            color: #198754;
        }


        /* =========================
           PASSWORD INFO
        ========================= */

        .password-info {

            font-size: 11px;

            color: #888;

            margin-top: 5px;

            line-height: 1.5;
        }


        /* =========================
           MAIN BUTTON
        ========================= */

        .main-button {

            width: 100%;

            height: 49px;

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

            margin-top: 5px;

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


        .main-button:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 12px 24px
                rgba(
                    25,
                    135,
                    84,
                    0.30
                );
        }


        /* =========================
           BOTTOM LINKS
        ========================= */

        .bottom-links {

            text-align: center;

            margin-top: 20px;
        }


        .bottom-links p {

            color: #777;

            font-size: 13px;
        }


        .bottom-links a {

            color: #198754;

            text-decoration: none;

            font-weight: bold;
        }


        .bottom-links a:hover {

            text-decoration: underline;
        }


        .home-link {

            text-align: center;

            margin-top: 20px;

            padding-top: 16px;

            border-top:
                1px solid #edf1ee;
        }


        .home-link a {

            color: #666;

            text-decoration: none;

            font-size: 12px;
        }


        .home-link a:hover {

            color: #198754;
        }


        /* =========================
           OTP SECTION
        ========================= */

        .otp-container {

            text-align: center;
        }


        .otp-icon {

            width: 75px;

            height: 75px;

            border-radius: 22px;

            background: #e8f7ef;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 38px;

            margin:
                0 auto 20px;
        }


        .otp-container h3 {

            color: #12372a;

            font-size: 23px;

            margin-bottom: 8px;
        }


        .otp-container > p {

            color: #777;

            font-size: 13px;

            line-height: 1.6;

            margin-bottom: 20px;
        }


        .otp-input {

            text-align: center;

            letter-spacing: 8px;

            font-size: 22px !important;

            font-weight: bold;

            padding:
                0 15px !important;
        }


        .otp-note {

            margin-top: 13px;

            padding: 10px;

            background: #f4f8f6;

            border-radius: 8px;

            color: #666;

            font-size: 11px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media(max-width: 850px) {

            .signup-wrapper {

                grid-template-columns: 1fr;

                max-width: 560px;
            }


            .brand-section {

                min-height: 300px;

                padding: 35px;
            }


            .logo {

                margin-bottom: 23px;
            }


            .brand-content h1 {

                font-size: 32px;
            }


            .brand-content > p {

                margin-bottom: 20px;
            }


            .benefits {

                display: grid;

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .form-section {

                padding: 40px;
            }
        }


        @media(max-width: 560px) {

            body {

                padding: 12px;
            }


            .signup-wrapper {

                border-radius: 17px;
            }


            .brand-section {

                padding: 30px 25px;
            }


            .brand-content h1 {

                font-size: 28px;
            }


            .benefits {

                grid-template-columns: 1fr;
            }


            .form-section {

                padding: 30px 25px;
            }


            .form-grid {

                grid-template-columns: 1fr;
            }


            .full {

                grid-column: auto;
            }


            .form-top h2 {

                font-size: 27px;
            }
        }

    </style>

</head>


<body>


<div class="signup-wrapper">


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

                Start Your

                <span>
                    Health Journey.
                </span>

            </h1>


            <p>

                Create your MediSync account and
                make healthcare management simpler.
                Connect with doctors and manage
                appointments from one place.

            </p>


            <div class="benefits">


                <div class="benefit">

                    <div class="benefit-icon">
                        ✓
                    </div>

                    Easy Doctor Discovery

                </div>


                <div class="benefit">

                    <div class="benefit-icon">
                        ✓
                    </div>

                    Quick Appointment Booking

                </div>


                <div class="benefit">

                    <div class="benefit-icon">
                        ✓
                    </div>

                    Secure Account

                </div>


                <div class="benefit">

                    <div class="benefit-icon">
                        ✓
                    </div>

                    Manage Your Healthcare

                </div>


            </div>


        </div>

    </section>



    <!-- =========================
         FORM SECTION
    ========================= -->

    <section class="form-section">


        <?php if (isset($_SESSION["signup_otp"])): ?>


            <!-- =========================
                 OTP
            ========================= -->

            <div class="form-top">

                <div class="welcome">
                    Account Verification
                </div>

                <h2>
                    Verify Your Mobile
                </h2>

                <p>
                    One final step to create
                    your MediSync account.
                </p>

            </div>


            <?php if ($message !== ""): ?>

                <div
                    class="message <?= htmlspecialchars($messageType) ?>"
                >

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <div class="otp-container">


                <div class="otp-icon">
                    📱
                </div>


                <h3>
                    Enter OTP
                </h3>


                <p>

                    Enter the 6-digit Demo OTP
                    generated for your mobile
                    number.

                </p>


                <form method="POST">


                    <div class="form-group">

                        <div class="input-wrapper">

                            <span class="input-icon">
                                🔐
                            </span>


                            <input
                                type="text"
                                name="otp"
                                class="otp-input"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                                placeholder="000000"
                                autocomplete="one-time-code"
                                required
                            >

                        </div>

                    </div>


                    <button
                        type="submit"
                        name="verify_otp"
                        class="main-button"
                    >
                        ✓ Verify & Create Account
                    </button>


                </form>


                <div class="otp-note">

                    ⏱️ OTP is valid for 5 minutes.

                </div>


            </div>


        <?php else: ?>


            <!-- =========================
                 SIGNUP FORM
            ========================= -->

            <div class="form-top">

                <div class="welcome">
                    Create Account
                </div>

                <h2>
                    Join MediSync
                </h2>

                <p>
                    Enter your details to create
                    your healthcare account.
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


                <div class="form-grid">


                    <!-- NAME -->

                    <div class="form-group full">

                        <label for="name">
                            Full Name
                        </label>


                        <div class="input-wrapper">

                            <span class="input-icon">
                                👤
                            </span>


                            <input
                                type="text"
                                id="name"
                                name="name"
                                placeholder="Enter your full name"
                                value="<?= htmlspecialchars(
                                    $_POST["name"] ?? ""
                                ) ?>"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>



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
                                placeholder="Enter email"
                                value="<?= htmlspecialchars(
                                    $_POST["email"] ?? ""
                                ) ?>"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>



                    <!-- MOBILE -->

                    <div class="form-group">

                        <label for="mobile">
                            Mobile Number
                        </label>


                        <div class="input-wrapper">

                            <span class="input-icon">
                                📱
                            </span>


                            <input
                                type="tel"
                                id="mobile"
                                name="mobile"
                                pattern="[0-9]{10}"
                                maxlength="10"
                                inputmode="numeric"
                                placeholder="10 digit mobile"
                                value="<?= htmlspecialchars(
                                    $_POST["mobile"] ?? ""
                                ) ?>"
                                autocomplete="tel"
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
                                placeholder="Create password"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword('password', this)"
                            >
                                👁️
                            </button>

                        </div>


                        <div class="password-info">

                            8+ characters, uppercase,
                            lowercase, number & special character.

                        </div>

                    </div>



                    <!-- CONFIRM PASSWORD -->

                    <div class="form-group">

                        <label for="confirm_password">
                            Confirm Password
                        </label>


                        <div class="input-wrapper">

                            <span class="input-icon">
                                🔐
                            </span>


                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm password"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                onclick="togglePassword('confirm_password', this)"
                            >
                                👁️
                            </button>

                        </div>

                    </div>


                    <!-- BUTTON -->

                    <div class="full">

                        <button
                            type="submit"
                            class="main-button"
                        >
                            🚀 Create My Account
                        </button>

                    </div>


                </div>


            </form>


        <?php endif; ?>



        <!-- =========================
             LOGIN LINK
        ========================= -->

        <div class="bottom-links">

            <p>

                Already have an account?

                <a href="login.php">
                    Login
                </a>

            </p>

        </div>


        <!-- HOME -->

        <div class="home-link">

            <a href="../index.php">
                ← Back to MediSync Home
            </a>

        </div>


    </section>


</div>



<script>


/* =========================
   PASSWORD TOGGLE
========================= */

function togglePassword(
    inputId,
    button
) {

    const input =
        document.getElementById(inputId);


    if (input.type === "password") {

        input.type = "text";

        button.textContent = "🙈";

    } else {

        input.type = "password";

        button.textContent = "👁️";
    }
}


/* =========================
   OTP ONLY NUMBERS
========================= */

const otpInput =
    document.querySelector(
        'input[name="otp"]'
    );


if (otpInput) {

    otpInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value.replace(
                    /[^0-9]/g,
                    ""
                );

        }
    );
}


/* =========================
   MOBILE ONLY NUMBERS
========================= */

const mobileInput =
    document.querySelector(
        'input[name="mobile"]'
    );


if (mobileInput) {

    mobileInput.addEventListener(
        "input",
        function () {

            this.value =
                this.value.replace(
                    /[^0-9]/g,
                    ""
                );

        }
    );
}

</script>


</body>

</html>