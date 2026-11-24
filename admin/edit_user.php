<?php

session_start();

require_once "../config/db.php";


/* =========================
   ADMIN CHECK
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    (
        ($_SESSION["role"] ?? "") !== "admin" &&
        ($_SESSION["user_role"] ?? "") !== "admin"
    )
) {
    header("Location: login.php");
    exit;
}


$users = $db->users;

$message = "";
$messageType = "";


/* =========================
   GET USER ID
========================= */

$userId = $_GET["id"] ?? $_POST["user_id"] ?? "";


if ($userId === "") {

    header("Location: users.php");
    exit;
}


try {

    $objectId =
        new MongoDB\BSON\ObjectId($userId);

} catch (Exception $e) {

    header("Location: users.php");
    exit;
}


/* =========================
   GET USER
========================= */

$user = $users->findOne([
    "_id" => $objectId
]);


if (!$user) {

    header("Location: users.php");
    exit;
}


/* =========================
   UPDATE USER
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST["name"] ?? "");

    $email =
        trim($_POST["email"] ?? "");

    $mobile =
        trim($_POST["mobile"] ?? "");

    $status =
        $_POST["status"] ?? "active";

    $newPassword =
        $_POST["password"] ?? "";


    /* =========================
       VALIDATION
    ========================= */

    if (
        $name === "" ||
        $email === "" ||
        $mobile === ""
    ) {

        $message =
            "Please fill all required fields.";

        $messageType =
            "error";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email.";

        $messageType =
            "error";

    } elseif (
        !preg_match(
            "/^[0-9]{10}$/",
            $mobile
        )
    ) {

        $message =
            "Mobile number must contain exactly 10 digits.";

        $messageType =
            "error";

    } elseif (
        !in_array(
            $status,
            ["active", "blocked"]
        )
    ) {

        $message =
            "Invalid user status.";

        $messageType =
            "error";

    } else {


        /* =========================
           CHECK EMAIL
        ========================= */

        $emailExists =
            $users->findOne([

                "email" => $email,

                "_id" => [
                    '$ne' => $objectId
                ]

            ]);


        if ($emailExists) {

            $message =
                "Email already belongs to another user.";

            $messageType =
                "error";

        } else {


            /* =========================
               UPDATE DATA
            ========================= */

            $updateData = [

                "name" =>
                    $name,

                "email" =>
                    $email,

                "mobile" =>
                    $mobile,

                "status" =>
                    $status,

                "updated_at" =>
                    new MongoDB\BSON\UTCDateTime()

            ];


            /* =========================
               PASSWORD UPDATE
            ========================= */

            if (
                trim($newPassword) !== ""
            ) {

                if (
                    strlen($newPassword) < 6
                ) {

                    $message =
                        "Password must be at least 6 characters.";

                    $messageType =
                        "error";

                } else {

                    $updateData["password"] =
                        password_hash(
                            $newPassword,
                            PASSWORD_DEFAULT
                        );
                }
            }


            /* =========================
               UPDATE DATABASE
            ========================= */

            if ($messageType !== "error") {

                $result =
                    $users->updateOne(

                        [
                            "_id" =>
                                $objectId
                        ],

                        [
                            '$set' =>
                                $updateData
                        ]

                    );


                if (
                    $result->getMatchedCount() > 0
                ) {

                    header(
                        "Location: users.php?updated=1"
                    );

                    exit;

                } else {

                    $message =
                        "User could not be updated.";

                    $messageType =
                        "error";
                }
            }
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
        MediSync - Edit User
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


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            background: #198754;

            color: white;

            padding: 18px 30px;

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 15px;
        }


        .navbar h2 {
            margin: 0;
        }


        .navbar a {

            color: white;

            text-decoration: none;

            margin-left: 18px;
        }


        .navbar a:hover {
            text-decoration: underline;
        }


        .logout {

            background: #dc3545;

            padding: 8px 14px;

            border-radius: 5px;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            width: 95%;

            max-width: 700px;

            margin: 40px auto;
        }


        .box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 0 15px rgba(0,0,0,0.08);
        }


        h1 {

            margin-top: 0;

            color: #198754;

            text-align: center;
        }


        .subtitle {

            text-align: center;

            color: #666;

            margin-bottom: 25px;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {

            padding: 12px;

            border-radius: 6px;

            margin-bottom: 20px;

            text-align: center;

            font-weight: bold;
        }


        .success {

            background: #d1e7dd;

            color: #0f5132;
        }


        .error {

            background: #f8d7da;

            color: #842029;
        }


        /* =========================
           FORM
        ========================= */

        label {

            display: block;

            font-weight: bold;

            margin-top: 15px;

            margin-bottom: 7px;
        }


        input,
        select {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;

            outline: none;
        }


        input:focus,
        select:focus {

            border-color: #198754;
        }


        .password-note {

            color: #777;

            font-size: 13px;

            margin-top: 5px;
        }


        /* =========================
           BUTTONS
        ========================= */

        .buttons {

            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        button,
        .cancel {

            flex: 1;

            padding: 13px;

            border: none;

            border-radius: 6px;

            font-size: 15px;

            cursor: pointer;

            text-align: center;

            text-decoration: none;
        }


        .update {

            background: #198754;

            color: white;
        }


        .update:hover {

            background: #157347;
        }


        .cancel {

            background: #6c757d;

            color: white;
        }


        .cancel:hover {

            background: #5c636a;
        }


        /* =========================
           USER INFO
        ========================= */

        .user-id {

            background: #f8f9fa;

            padding: 10px;

            border-radius: 6px;

            font-size: 13px;

            color: #777;

            margin-bottom: 20px;

            word-break: break-all;
        }


        @media(max-width: 600px) {

            .navbar {

                flex-direction: column;

                text-align: center;
            }


            .navbar a {

                margin: 5px;
            }


            .buttons {

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<div class="navbar">

    <h2>
        🩺 MediSync Admin
    </h2>


    <div>

        <a href="dashboard.php">
            Dashboard
        </a>


        <a href="doctors.php">
            Doctors
        </a>


        <a href="users.php">
            Users
        </a>


        <a href="appointments.php">
            Appointments
        </a>


        <a
            href="logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>



<!-- =========================
     MAIN
========================= -->

<div class="container">


    <div class="box">


        <h1>
            ✏️ Edit User
        </h1>


        <div class="subtitle">
            Update registered user information.
        </div>


        <?php if ($message !== ""): ?>

            <div
                class="message <?= htmlspecialchars(
                    $messageType
                ) ?>"
            >

                <?= htmlspecialchars(
                    $message
                ) ?>

            </div>

        <?php endif; ?>


        <div class="user-id">

            User ID:

            <?= htmlspecialchars(
                $userId
            ) ?>

        </div>


        <form method="POST">


            <input
                type="hidden"
                name="user_id"
                value="<?= htmlspecialchars(
                    (string)$user["_id"]
                ) ?>"
            >


            <!-- NAME -->

            <label>
                Full Name *
            </label>


            <input
                type="text"
                name="name"
                value="<?= htmlspecialchars(
                    $user["name"] ?? ""
                ) ?>"
                placeholder="Enter full name"
                required
            >



            <!-- EMAIL -->

            <label>
                Email *
            </label>


            <input
                type="email"
                name="email"
                value="<?= htmlspecialchars(
                    $user["email"] ?? ""
                ) ?>"
                placeholder="Enter email"
                required
            >



            <!-- MOBILE -->

            <label>
                Mobile Number *
            </label>


            <input
                type="tel"
                name="mobile"
                value="<?= htmlspecialchars(
                    $user["mobile"] ?? ""
                ) ?>"
                maxlength="10"
                pattern="[0-9]{10}"
                placeholder="10 digit mobile number"
                required
            >



            <!-- STATUS -->

            <label>
                Account Status
            </label>


            <select name="status">


                <option
                    value="active"
                    <?= (
                        ($user["status"] ?? "active")
                        === "active"
                    )
                        ? "selected"
                        : ""
                    ?>
                >
                    🟢 Active
                </option>


                <option
                    value="blocked"
                    <?= (
                        ($user["status"] ?? "")
                        === "blocked"
                    )
                        ? "selected"
                        : ""
                    ?>
                >
                    🔴 Blocked
                </option>


            </select>



            <!-- PASSWORD -->

            <label>
                New Password
            </label>


            <input
                type="password"
                name="password"
                placeholder="Leave blank to keep current password"
                minlength="6"
            >


            <div class="password-note">

                Leave this field blank if you don't want
                to change the user's password.

            </div>



            <!-- BUTTONS -->

            <div class="buttons">


                <button
                    type="submit"
                    class="update"
                >
                    💾 Update User
                </button>


                <a
                    href="users.php"
                    class="cancel"
                >
                    ↩️ Cancel
                </a>


            </div>


        </form>


    </div>


</div>


</body>

</html>