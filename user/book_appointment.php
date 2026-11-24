<?php

session_start();

require_once "../config/db.php";


/* =========================
   LOGIN CHECK
========================= */

if (
    !isset($_SESSION["user_id"]) ||
    $_SESSION["user_id"] === ""
) {
    header("Location: login.php");
    exit;
}


/* =========================
   DATABASE
========================= */

$doctors = $db->doctors;
$appointments = $db->appointments;
$users = $db->users;


/* =========================
   VARIABLES
========================= */

$message = "";
$messageType = "";

$doctor = null;


/* =========================
   GET DOCTOR ID
========================= */

$doctorIdString = trim($_GET["doctor"] ?? "");

if ($doctorIdString === "") {

    header("Location: doctors.php");
    exit;
}


try {

    $doctorId = new MongoDB\BSON\ObjectId(
        $doctorIdString
    );

} catch (Exception $e) {

    header("Location: doctors.php");
    exit;
}


/* =========================
   GET ACTIVE DOCTOR
========================= */

$doctor = $doctors->findOne([
    "_id" => $doctorId,
    "status" => "active"
]);


if (!$doctor) {

    header("Location: doctors.php");
    exit;
}


/* =========================
   GET CURRENT USER
========================= */

try {

    $userId = new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );

    $currentUser = $users->findOne([
        "_id" => $userId
    ]);

} catch (Exception $e) {

    $currentUser = null;
}


if (!$currentUser) {

    session_unset();
    session_destroy();

    header("Location: login.php");
    exit;
}


/* =========================
   BOOK APPOINTMENT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $appointmentDate = trim(
        $_POST["appointment_date"] ?? ""
    );

    $appointmentTime = trim(
        $_POST["appointment_time"] ?? ""
    );

    $reason = trim(
        $_POST["reason"] ?? ""
    );


    /* =========================
       VALIDATION
    ========================= */

    if (
        $appointmentDate === "" ||
        $appointmentTime === ""
    ) {

        $message =
            "Please select appointment date and time.";

        $messageType = "error";

    } elseif (
        !preg_match(
            "/^\d{4}-\d{2}-\d{2}$/",
            $appointmentDate
        )
    ) {

        $message = "Invalid appointment date.";

        $messageType = "error";

    } elseif (
        !preg_match(
            "/^\d{2}:\d{2}$/",
            $appointmentTime
        )
    ) {

        $message = "Invalid appointment time.";

        $messageType = "error";

    } elseif (
        $appointmentDate < date("Y-m-d")
    ) {

        $message =
            "Please select today or a future date.";

        $messageType = "error";

    } else {


        /* =========================
           CHECK DOCTOR SLOT
        ========================= */

        $existingAppointment =
            $appointments->findOne([

                "doctor_id" =>
                    $doctor["_id"],

                "appointment_date" =>
                    $appointmentDate,

                "appointment_time" =>
                    $appointmentTime,

                "status" => [
                    '$in' => [
                        "pending",
                        "confirmed"
                    ]
                ]

            ]);


        if ($existingAppointment) {

            $message =
                "This time slot is already booked. Please select another time.";

            $messageType = "error";

        } else {


            /* =========================
               CHECK USER SLOT
            ========================= */

            $userAlreadyBooked =
                $appointments->findOne([

                    "user_id" =>
                        $currentUser["_id"],

                    "appointment_date" =>
                        $appointmentDate,

                    "appointment_time" =>
                        $appointmentTime,

                    "status" => [
                        '$in' => [
                            "pending",
                            "confirmed"
                        ]
                    ]

                ]);


            if ($userAlreadyBooked) {

                $message =
                    "You already have an appointment at this date and time.";

                $messageType = "error";

            } else {


                /* =========================
                   INSERT APPOINTMENT
                ========================= */

                try {

                    $result =
                        $appointments->insertOne([

                            "user_id" =>
                                $currentUser["_id"],

                            "user_name" =>
                                $currentUser["name"] ?? "",

                            "user_email" =>
                                $currentUser["email"] ?? "",

                            "user_mobile" =>
                                $currentUser["mobile"] ?? "",

                            "doctor_id" =>
                                $doctor["_id"],

                            "doctor_name" =>
                                $doctor["name"] ?? "",

                            "doctor_email" =>
                                $doctor["email"] ?? "",

                            "specialization" =>
                                $doctor["specialization"] ?? "",

                            "appointment_date" =>
                                $appointmentDate,

                            "appointment_time" =>
                                $appointmentTime,

                            "reason" =>
                                $reason,

                            "status" =>
                                "pending",

                            "visit_type" =>
                                "first_visit",

                            "follow_up_date" =>
                                "",

                            "follow_up_time" =>
                                "",

                            "created_at" =>
                                new MongoDB\BSON\UTCDateTime(),

                            "updated_at" =>
                                new MongoDB\BSON\UTCDateTime()

                        ]);


                    if (
                        $result->getInsertedCount() === 1
                    ) {

                        header(
                            "Location: appointments.php?booked=1"
                        );

                        exit;

                    } else {

                        $message =
                            "Appointment could not be booked.";

                        $messageType = "error";
                    }


                } catch (Exception $e) {

                    $message =
                        "Booking Error: " .
                        $e->getMessage();

                    $messageType = "error";
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
        MediSync - Book Appointment
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

            padding: 16px 5%;

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

            align-items: center;

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

            margin: 35px auto;
        }


        .back {

            display: inline-block;

            margin-bottom: 20px;

            color: #198754;

            text-decoration: none;

            font-weight: bold;
        }


        .box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 0 15px rgba(0,0,0,0.08);
        }


        /* =========================
           DOCTOR
        ========================= */

        .doctor {

            background: #e8f7ef;

            padding: 22px;

            border-radius: 10px;

            margin-bottom: 25px;

            border-left: 5px solid #198754;
        }


        .doctor h2 {

            margin-top: 0;

            color: #198754;
        }


        .doctor p {

            margin: 9px 0;

            color: #555;
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

        .grid {

            display: grid;

            grid-template-columns:
                repeat(2, 1fr);

            gap: 20px;
        }


        .full {

            grid-column: 1 / -1;
        }


        label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;
        }


        input,
        textarea {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        input:focus,
        textarea:focus {

            outline: none;

            border-color: #198754;
        }


        textarea {

            min-height: 120px;

            resize: vertical;
        }


        .book-btn {

            width: 100%;

            background: #198754;

            color: white;

            border: none;

            padding: 14px;

            border-radius: 6px;

            font-size: 16px;

            cursor: pointer;

            font-weight: bold;
        }


        .book-btn:hover {

            background: #157347;
        }


        .note {

            margin-top: 18px;

            padding: 12px;

            background: #fff3cd;

            color: #664d03;

            border-radius: 6px;

            font-size: 14px;
        }


        /* =========================
           MOBILE
        ========================= */

        @media(max-width: 650px) {

            .navbar {

                flex-direction: column;

                text-align: center;
            }


            .navbar-links {

                justify-content: center;
            }


            .grid {

                grid-template-columns: 1fr;
            }


            .full {

                grid-column: auto;
            }


            .box {

                padding: 20px;
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
        🩺 MediSync
    </h2>


    <div class="navbar-links">

        <a href="../index.php">
            Home
        </a>

        <a href="dashboard.php">
            Dashboard
        </a>

        <a href="doctors.php">
            Doctors
        </a>

        <a href="appointments.php">
            My Appointments
        </a>

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>



<div class="container">


    <a
        href="doctors.php"
        class="back"
    >
        ← Back to Doctors
    </a>



    <div class="box">


        <?php if ($message !== ""): ?>

            <div class="message error">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>



        <!-- =========================
             DOCTOR DETAILS
        ========================= -->

        <div class="doctor">

            <h2>

                👨‍⚕️

                <?= htmlspecialchars(
                    $doctor["name"] ?? "Doctor"
                ) ?>

            </h2>


            <p>

                🩺

                <strong>
                    Specialization:
                </strong>

                <?= htmlspecialchars(
                    $doctor["specialization"]
                    ?? "-"
                ) ?>

            </p>


            <p>

                📞

                <strong>
                    Mobile:
                </strong>

                <?= htmlspecialchars(
                    $doctor["mobile"] ?? "-"
                ) ?>

            </p>


            <p>

                💼

                <strong>
                    Experience:
                </strong>

                <?= htmlspecialchars(
                    $doctor["experience"] ?? "-"
                ) ?>

            </p>


            <p>

                🕐

                <strong>
                    Available Time:
                </strong>

                <?= htmlspecialchars(
                    $doctor["available_time"] ?? "-"
                ) ?>

            </p>

        </div>



        <h2>
            📅 Book Appointment
        </h2>



        <form method="POST">

            <div class="grid">


                <!-- DATE -->

                <div>

                    <label>
                        Appointment Date *
                    </label>

                    <input
                        type="date"
                        name="appointment_date"
                        min="<?= date("Y-m-d") ?>"
                        required
                    >

                </div>


                <!-- TIME -->

                <div>

                    <label>
                        Appointment Time *
                    </label>

                    <input
                        type="time"
                        name="appointment_time"
                        required
                    >

                </div>


                <!-- REASON -->

                <div class="full">

                    <label>
                        Reason / Symptoms
                    </label>

                    <textarea
                        name="reason"
                        placeholder="Describe your symptoms or reason for appointment..."
                    ></textarea>

                </div>


                <!-- BUTTON -->

                <div class="full">

                    <button
                        type="submit"
                        class="book-btn"
                    >
                        📅 Confirm Appointment
                    </button>

                </div>


            </div>

        </form>


        <div class="note">

            ℹ️ Your appointment will initially
            be marked as <strong>Pending</strong>.
            The doctor/admin can confirm it later.

        </div>


    </div>

</div>


</body>

</html>