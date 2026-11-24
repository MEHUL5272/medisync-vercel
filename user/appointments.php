<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";

$appointments = $db->appointments;

$message = "";
$messageType = "";


/* =========================
   UPDATED MESSAGE
========================= */

if (isset($_GET["updated"])) {

    $message =
        "Appointment updated successfully.";

    $messageType = "success";
}


/* =========================
   CANCEL APPOINTMENT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST["cancel_appointment"])) {

        try {

            $appointmentId =
                new MongoDB\BSON\ObjectId(
                    $_POST["appointment_id"]
                );


            $result =
                $appointments->updateOne(

                    [
                        "_id" =>
                            $appointmentId,

                        "user_id" =>
                            new MongoDB\BSON\ObjectId(
                                $_SESSION["user_id"]
                            )
                    ],

                    [
                        '$set' => [

                            "status" =>
                                "cancelled",

                            "updated_at" =>
                                new MongoDB\BSON\UTCDateTime()
                        ]
                    ]
                );


            if (
                $result->getModifiedCount() > 0
            ) {

                $message =
                    "Appointment cancelled successfully.";

                $messageType =
                    "success";

            } else {

                $message =
                    "Appointment could not be cancelled.";

                $messageType =
                    "error";
            }


        } catch (Exception $e) {

            $message =
                "Error: " . $e->getMessage();

            $messageType =
                "error";
        }
    }
}


/* =========================
   GET USER APPOINTMENTS
========================= */

$userId =
    new MongoDB\BSON\ObjectId(
        $_SESSION["user_id"]
    );


$appointmentList =
    $appointments->find(

        [
            "user_id" => $userId
        ],

        [
            "sort" => [

                "appointment_date" => 1,

                "appointment_time" => 1
            ]
        ]

    )->toArray();

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
        MediSync - My Appointments
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

            max-width: 1200px;

            margin: 30px auto;
        }


        .box {

            background: white;

            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 0 12px rgba(0,0,0,0.08);
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


        .appointments {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(300px, 1fr));

            gap: 20px;
        }


        .card {

            border: 1px solid #ddd;

            border-radius: 10px;

            padding: 20px;

            background: white;
        }


        .card h3 {

            margin-top: 0;

            color: #198754;
        }


        .info {

            margin: 10px 0;

            color: #555;
        }


        .info strong {

            color: #333;
        }


        .status {

            display: inline-block;

            padding: 7px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

            text-transform: capitalize;
        }


        .pending {

            background: #fff3cd;

            color: #664d03;
        }


        .confirmed {

            background: #d1e7dd;

            color: #0f5132;
        }


        .completed {

            background: #cff4fc;

            color: #055160;
        }


        .cancelled {

            background: #f8d7da;

            color: #842029;
        }


        .followup {

            margin-top: 15px;

            padding: 15px;

            background: #f2f6f9;

            border-radius: 8px;
        }


        .actions {

            display: flex;

            gap: 10px;

            margin-top: 15px;
        }


        .edit-btn {

            flex: 1;

            padding: 11px;

            background: #0d6efd;

            color: white;

            text-decoration: none;

            text-align: center;

            border-radius: 6px;
        }


        .cancel-btn {

            flex: 1;

            padding: 11px;

            border: none;

            border-radius: 6px;

            background: #dc3545;

            color: white;

            cursor: pointer;

            font-size: 15px;
        }


        .edit-btn:hover {

            background: #0b5ed7;
        }


        .cancel-btn:hover {

            background: #bb2d3b;
        }


        .empty {

            text-align: center;

            padding: 50px 20px;

            color: #777;
        }


        .book-btn {

            display: inline-block;

            margin-top: 15px;

            padding: 11px 18px;

            background: #198754;

            color: white;

            text-decoration: none;

            border-radius: 6px;
        }


        @media(max-width: 650px) {

            .navbar {

                flex-direction: column;

                gap: 12px;
            }


            .actions {

                flex-direction: column;
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


        <a href="logout.php">
            Logout
        </a>

    </div>

</div>


<div class="container">

    <div class="box">

        <h2>
            📅 My Appointments
        </h2>


        <!-- MESSAGE -->

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


        <!-- APPOINTMENTS -->

        <?php if (
            count($appointmentList) === 0
        ): ?>


            <div class="empty">

                <h3>
                    No Appointments
                </h3>

                <p>
                    You have not booked any appointment yet.
                </p>


                <a
                    href="doctors.php"
                    class="book-btn"
                >
                    👨‍⚕️ Find Doctor
                </a>

            </div>


        <?php else: ?>


            <div class="appointments">


                <?php foreach (
                    $appointmentList
                    as $appointment
                ): ?>


                    <?php

                    $status =
                        strtolower(
                            $appointment["status"]
                            ?? "pending"
                        );

                    ?>


                    <div class="card">


                        <h3>

                            👨‍⚕️

                            <?= htmlspecialchars(
                                $appointment["doctor_name"]
                                ?? "Doctor"
                            ) ?>

                        </h3>


                        <div class="info">

                            🩺

                            <strong>
                                Specialization:
                            </strong>

                            <?= htmlspecialchars(
                                $appointment["specialization"]
                                ?? "-"
                            ) ?>

                        </div>


                        <div class="info">

                            📅

                            <strong>
                                Date:
                            </strong>

                            <?= htmlspecialchars(
                                $appointment["appointment_date"]
                                ?? "-"
                            ) ?>

                        </div>


                        <div class="info">

                            ⏰

                            <strong>
                                Time:
                            </strong>

                            <?= htmlspecialchars(
                                $appointment["appointment_time"]
                                ?? "-"
                            ) ?>

                        </div>


                        <div class="info">

                            📝

                            <strong>
                                Reason:
                            </strong>

                            <?= htmlspecialchars(
                                $appointment["reason"]
                                ?? "-"
                            ) ?>

                        </div>


                        <div class="info">

                            🔄

                            <strong>
                                Visit:
                            </strong>

                            <?= htmlspecialchars(
                                $appointment["visit_type"]
                                ?? "first_visit"
                            ) ?>

                        </div>


                        <div class="info">

                            <strong>
                                Status:
                            </strong>


                            <span
                                class="status <?= htmlspecialchars(
                                    $status
                                ) ?>"
                            >

                                <?= htmlspecialchars(
                                    $status
                                ) ?>

                            </span>

                        </div>


                        <!-- FOLLOW UP -->

                        <?php

                        $followUpDate =
                            $appointment["follow_up_date"]
                            ?? "";

                        $followUpTime =
                            $appointment["follow_up_time"]
                            ?? "";

                        ?>


                        <?php if (
                            $followUpDate !== "" ||
                            $followUpTime !== ""
                        ): ?>


                            <div class="followup">

                                <strong>
                                    🔁 Follow-up Appointment
                                </strong>


                                <?php if (
                                    $followUpDate !== ""
                                ): ?>

                                    <div class="info">

                                        📅

                                        <strong>
                                            Date:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $followUpDate
                                        ) ?>

                                    </div>

                                <?php endif; ?>


                                <?php if (
                                    $followUpTime !== ""
                                ): ?>

                                    <div class="info">

                                        ⏰

                                        <strong>
                                            Time:
                                        </strong>

                                        <?= htmlspecialchars(
                                            $followUpTime
                                        ) ?>

                                    </div>

                                <?php endif; ?>


                            </div>

                        <?php endif; ?>


                        <!-- ACTIONS -->

                        <?php if (
                            $status === "pending" ||
                            $status === "confirmed"
                        ): ?>


                            <div class="actions">


                                <!-- EDIT -->

                                <a
                                    href="edit_appointment.php?id=<?= urlencode(
                                        (string)$appointment["_id"]
                                    ) ?>"
                                    class="edit-btn"
                                >

                                    ✏️ Edit Appointment

                                </a>


                                <!-- CANCEL -->

                                <form
                                    method="POST"
                                    style="flex:1;"
                                    onsubmit="return confirm('Are you sure you want to cancel this appointment?');"
                                >

                                    <input
                                        type="hidden"
                                        name="appointment_id"
                                        value="<?= htmlspecialchars(
                                            (string)$appointment["_id"]
                                        ) ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="cancel_appointment"
                                        class="cancel-btn"
                                    >

                                        ❌ Cancel Appointment

                                    </button>

                                </form>


                            </div>


                        <?php endif; ?>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </div>

</div>


</body>

</html>