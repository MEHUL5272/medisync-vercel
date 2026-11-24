<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";

$appointments = $db->appointments;
$doctors = $db->doctors;

$userId = new MongoDB\BSON\ObjectId(
    $_SESSION["user_id"]
);

$message = "";
$messageType = "";


/* =========================
   GET APPOINTMENT ID
========================= */

$appointmentId = trim(
    $_GET["id"] ?? $_POST["appointment_id"] ?? ""
);

try {

    $objectId = new MongoDB\BSON\ObjectId(
        $appointmentId
    );

} catch (Exception $e) {

    header("Location: appointments.php");
    exit;
}


/* =========================
   GET USER APPOINTMENT
========================= */

$appointment = $appointments->findOne([
    "_id" => $objectId,
    "user_id" => $userId
]);


/* =========================
   APPOINTMENT NOT FOUND
========================= */

if (!$appointment) {

    header("Location: appointments.php");
    exit;
}


$status = strtolower(
    $appointment["status"] ?? "pending"
);


/* =========================
   ONLY PENDING / CONFIRMED
   CAN BE EDITED
========================= */

if (
    $status !== "pending" &&
    $status !== "confirmed"
) {

    header("Location: appointments.php");
    exit;
}


/* =========================
   UPDATE APPOINTMENT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_appointment"])
) {

    $appointmentDate =
        trim($_POST["appointment_date"] ?? "");

    $appointmentTime =
        trim($_POST["appointment_time"] ?? "");

    $reason =
        trim($_POST["reason"] ?? "");


    /* =========================
       VALIDATION
    ========================= */

    if (
        $appointmentDate === "" ||
        $appointmentTime === "" ||
        $reason === ""
    ) {

        $message =
            "Please fill all fields.";

        $messageType = "error";

    } elseif (
        $appointmentDate < date("Y-m-d")
    ) {

        $message =
            "Please select a future date.";

        $messageType = "error";

    } else {


        /* =========================
           CHECK SAME SLOT
        ========================= */

        $existingAppointment =
            $appointments->findOne([
                "_id" => [
                    '$ne' => $objectId
                ],

                "doctor_id" =>
                    $appointment["doctor_id"],

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
               UPDATE
            ========================= */

            $result =
                $appointments->updateOne(
                    [
                        "_id" => $objectId,

                        "user_id" => $userId
                    ],

                    [
                        '$set' => [

                            "appointment_date" =>
                                $appointmentDate,

                            "appointment_time" =>
                                $appointmentTime,

                            "reason" =>
                                $reason,

                            "updated_at" =>
                                new MongoDB\BSON\UTCDateTime()
                        ]
                    ]
                );


            if (
                $result->getModifiedCount() > 0 ||
                (
                    $appointment["appointment_date"]
                    === $appointmentDate
                    &&
                    $appointment["appointment_time"]
                    === $appointmentTime
                    &&
                    ($appointment["reason"] ?? "")
                    === $reason
                )
            ) {

                header(
                    "Location: appointments.php?updated=1"
                );

                exit;

            } else {

                $message =
                    "Appointment could not be updated.";

                $messageType = "error";
            }
        }
    }


    /* Refresh appointment data */

    $appointment =
        $appointments->findOne([
            "_id" => $objectId,
            "user_id" => $userId
        ]);
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
        MediSync - Edit Appointment
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


        .container {

            width: 95%;

            max-width: 700px;

            margin: 35px auto;
        }


        .box {

            background: white;

            padding: 30px;

            border-radius: 12px;

            box-shadow:
                0 0 12px rgba(0,0,0,0.08);
        }


        h2 {

            color: #146c43;
        }


        .doctor {

            background: #e8f7ef;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;
        }


        .doctor strong {

            color: #198754;
        }


        label {

            display: block;

            font-weight: bold;

            margin-top: 15px;

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


        textarea {

            min-height: 110px;

            resize: vertical;
        }


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


        .buttons {

            display: flex;

            gap: 10px;

            margin-top: 25px;
        }


        .btn {

            flex: 1;

            padding: 12px;

            border: none;

            border-radius: 6px;

            text-align: center;

            text-decoration: none;

            cursor: pointer;

            font-size: 15px;
        }


        .update {

            background: #198754;

            color: white;
        }


        .back {

            background: #6c757d;

            color: white;
        }


        .update:hover {

            background: #157347;
        }


        .back:hover {

            background: #5c636a;
        }


        @media(max-width: 600px) {

            .navbar {

                flex-direction: column;

                gap: 12px;
            }


            .navbar a {

                margin-left: 8px;

                margin-right: 8px;
            }


            .buttons {

                flex-direction: column;
            }

        }

    </style>

</head>


<body>


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

        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">

    <div class="box">

        <h2>
            ✏️ Edit Appointment
        </h2>


        <?php if ($message !== ""): ?>

            <div
                class="message <?= htmlspecialchars($messageType) ?>"
            >

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <div class="doctor">

            👨‍⚕️

            <strong>
                <?= htmlspecialchars(
                    $appointment["doctor_name"]
                    ?? "Doctor"
                ) ?>
            </strong>

            <br>

            🩺

            <?= htmlspecialchars(
                $appointment["specialization"]
                ?? "-"
            ) ?>

        </div>


        <form method="POST">


            <input
                type="hidden"
                name="appointment_id"
                value="<?= htmlspecialchars(
                    $appointmentId
                ) ?>"
            >


            <label>
                📅 Appointment Date
            </label>


            <input
                type="date"
                name="appointment_date"
                min="<?= date("Y-m-d") ?>"
                value="<?= htmlspecialchars(
                    $appointment["appointment_date"]
                    ?? ""
                ) ?>"
                required
            >


            <label>
                ⏰ Appointment Time
            </label>


            <input
                type="time"
                name="appointment_time"
                value="<?= htmlspecialchars(
                    $appointment["appointment_time"]
                    ?? ""
                ) ?>"
                required
            >


            <label>
                📝 Reason / Problem
            </label>


            <textarea
                name="reason"
                placeholder="Enter your health problem..."
                required
            ><?= htmlspecialchars(
                $appointment["reason"] ?? ""
            ) ?></textarea>


            <div class="buttons">


                <button
                    type="submit"
                    name="update_appointment"
                    class="btn update"
                >

                    💾 Update Appointment

                </button>


                <a
                    href="appointments.php"
                    class="btn back"
                >

                    ← Back

                </a>


            </div>


        </form>

    </div>

</div>


</body>

</html>