<?php

session_start();

require_once "../config/db.php";


/* =========================
   ADMIN CHECK
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: login.php");
    exit;
}


$doctors = $db->doctors;

$message = "";
$messageType = "";


/* =========================
   DOCTOR ID
========================= */

$doctorId = trim($_GET["id"] ?? "");

if ($doctorId === "") {
    header("Location: doctors.php");
    exit;
}


try {

    $objectId =
        new MongoDB\BSON\ObjectId($doctorId);

} catch (Exception $e) {

    header("Location: doctors.php");
    exit;
}


/* =========================
   GET DOCTOR
========================= */

$doctor = $doctors->findOne([
    "_id" => $objectId
]);


if (!$doctor) {

    header("Location: doctors.php");
    exit;
}


/* =========================
   UPDATE DOCTOR
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim(
        $_POST["name"] ?? ""
    );

    $specialization = trim(
        $_POST["specialization"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    $mobile = trim(
        $_POST["mobile"] ?? ""
    );

    $experience = trim(
        $_POST["experience"] ?? ""
    );

    $availableTime = trim(
        $_POST["available_time"] ?? ""
    );

    $status = trim(
        $_POST["status"] ?? "active"
    );


    /* =========================
       VALIDATION
    ========================= */

    if (
        $name === "" ||
        $specialization === "" ||
        $email === "" ||
        $mobile === "" ||
        $experience === "" ||
        $availableTime === ""
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

    } elseif (
        !in_array(
            $status,
            ["active", "inactive"]
        )
    ) {

        $message =
            "Invalid doctor status.";

        $messageType = "error";

    } else {

        /* =========================
           DUPLICATE EMAIL CHECK
        ========================= */

        $emailExists =
            $doctors->findOne([

                "email" => $email,

                "_id" => [
                    '$ne' => $objectId
                ]

            ]);


        /* =========================
           DUPLICATE MOBILE CHECK
        ========================= */

        $mobileExists =
            $doctors->findOne([

                "mobile" => $mobile,

                "_id" => [
                    '$ne' => $objectId
                ]

            ]);


        if ($emailExists) {

            $message =
                "Another doctor already uses this email.";

            $messageType = "error";

        } elseif ($mobileExists) {

            $message =
                "Another doctor already uses this mobile number.";

            $messageType = "error";

        } else {

            /* =========================
               UPDATE
            ========================= */

            $doctors->updateOne(

                [
                    "_id" => $objectId
                ],

                [
                    '$set' => [

                        "name" =>
                            $name,

                        "specialization" =>
                            $specialization,

                        "email" =>
                            $email,

                        "mobile" =>
                            $mobile,

                        "experience" =>
                            $experience,

                        "available_time" =>
                            $availableTime,

                        "status" =>
                            $status,

                        "updated_at" =>
                            new MongoDB\BSON\UTCDateTime()

                    ]
                ]

            );


            header(
                "Location: doctors.php?updated=1"
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
        MediSync - Edit Doctor
    </title>


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family: Arial, sans-serif;

            background: #f2f6f9;

            color: #222;
        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar {

            background: #198754;

            color: white;

            padding: 17px 5%;

            display: flex;

            justify-content: space-between;

            align-items: center;

            flex-wrap: wrap;

            gap: 15px;
        }


        .navbar h2 {
            margin: 0;
        }


        .navbar-links {

            display: flex;

            gap: 18px;

            flex-wrap: wrap;
        }


        .navbar a {

            color: white;

            text-decoration: none;
        }


        .navbar a:hover {
            text-decoration: underline;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            width: 95%;

            max-width: 850px;

            margin: 40px auto;
        }


        .card {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 0 15px rgba(0,0,0,0.08);
        }


        .card h1 {

            margin-top: 0;

            color: #198754;

            margin-bottom: 8px;
        }


        .subtitle {

            color: #666;

            margin-bottom: 25px;
        }


        /* =========================
           MESSAGE
        ========================= */

        .message {

            padding: 12px;

            border-radius: 7px;

            margin-bottom: 20px;

            text-align: center;
        }


        .error {

            background: #f8d7da;

            color: #842029;
        }


        /* =========================
           FORM
        ========================= */

        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;
        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;
        }


        .form-group input,
        .form-group select {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;

            outline: none;
        }


        .form-group input:focus,
        .form-group select:focus {

            border-color: #198754;
        }


        .full {

            grid-column: 1 / -1;
        }


        /* =========================
           BUTTONS
        ========================= */

        .buttons {

            display: flex;

            gap: 12px;

            margin-top: 10px;
        }


        .update-btn {

            flex: 1;

            background: #198754;

            color: white;

            border: none;

            padding: 13px;

            border-radius: 6px;

            font-size: 16px;

            cursor: pointer;
        }


        .update-btn:hover {

            background: #157347;
        }


        .cancel-btn {

            flex: 1;

            background: #6c757d;

            color: white;

            padding: 13px;

            border-radius: 6px;

            text-align: center;

            text-decoration: none;

            font-size: 16px;
        }


        .cancel-btn:hover {

            background: #5c636a;
        }


        /* =========================
           DOCTOR INFO
        ========================= */

        .doctor-id {

            background: #f8f9fa;

            padding: 10px;

            border-radius: 6px;

            margin-bottom: 25px;

            color: #666;

            font-size: 13px;

            word-break: break-all;
        }


        @media(max-width: 650px) {

            .navbar {

                flex-direction: column;

                text-align: center;
            }


            .navbar-links {

                justify-content: center;
            }


            .form-grid {

                grid-template-columns: 1fr;
            }


            .full {

                grid-column: auto;
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


    <div class="navbar-links">

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="doctors.php">
            Doctors
        </a>

        <a href="appointments.php">
            Appointments
        </a>

        <a href="users.php">
            Users
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>



<div class="container">


    <div class="card">


        <h1>
            ✏️ Edit Doctor
        </h1>


        <div class="subtitle">
            Update doctor information and availability.
        </div>


        <?php if ($message !== ""): ?>

            <div class="message error">

                <?= htmlspecialchars(
                    $message
                ) ?>

            </div>

        <?php endif; ?>


        <div class="doctor-id">

            Doctor ID:

            <?= htmlspecialchars(
                $doctorId
            ) ?>

        </div>


        <form method="POST">


            <div class="form-grid">


                <!-- NAME -->

                <div class="form-group">

                    <label>
                        Doctor Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="<?= htmlspecialchars(
                            $doctor["name"] ?? ""
                        ) ?>"
                        placeholder="Dr. John Smith"
                        required
                    >

                </div>



                <!-- SPECIALIZATION -->

                <div class="form-group">

                    <label>
                        Specialization
                    </label>

                    <input
                        type="text"
                        name="specialization"
                        value="<?= htmlspecialchars(
                            $doctor["specialization"] ?? ""
                        ) ?>"
                        placeholder="Cardiologist"
                        required
                    >

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?= htmlspecialchars(
                            $doctor["email"] ?? ""
                        ) ?>"
                        placeholder="doctor@example.com"
                        required
                    >

                </div>



                <!-- MOBILE -->

                <div class="form-group">

                    <label>
                        Mobile
                    </label>

                    <input
                        type="tel"
                        name="mobile"
                        value="<?= htmlspecialchars(
                            $doctor["mobile"] ?? ""
                        ) ?>"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        placeholder="9876543210"
                        required
                    >

                </div>



                <!-- EXPERIENCE -->

                <div class="form-group">

                    <label>
                        Experience
                    </label>

                    <input
                        type="text"
                        name="experience"
                        value="<?= htmlspecialchars(
                            $doctor["experience"] ?? ""
                        ) ?>"
                        placeholder="10 Years"
                        required
                    >

                </div>



                <!-- AVAILABLE TIME -->

                <div class="form-group">

                    <label>
                        Available Time
                    </label>

                    <input
                        type="text"
                        name="available_time"
                        value="<?= htmlspecialchars(
                            $doctor["available_time"] ?? ""
                        ) ?>"
                        placeholder="10 AM - 2 PM"
                        required
                    >

                </div>



                <!-- STATUS -->

                <div class="form-group full">

                    <label>
                        Doctor Status
                    </label>

                    <select name="status">

                        <option
                            value="active"
                            <?= ($doctor["status"] ?? "active") === "active"
                                ? "selected"
                                : "" ?>
                        >
                            🟢 Active
                        </option>


                        <option
                            value="inactive"
                            <?= ($doctor["status"] ?? "") === "inactive"
                                ? "selected"
                                : "" ?>
                        >
                            🔴 Inactive
                        </option>

                    </select>

                </div>



                <!-- BUTTONS -->

                <div class="full buttons">

                    <button
                        type="submit"
                        class="update-btn"
                    >
                        💾 Update Doctor
                    </button>


                    <a
                        href="doctors.php"
                        class="cancel-btn"
                    >
                        ↩️ Cancel
                    </a>

                </div>


            </div>


        </form>


    </div>


</div>


</body>

</html>