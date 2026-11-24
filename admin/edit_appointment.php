 <?php

session_start();

require_once "../config/db.php";


/* =========================
   ADMIN LOGIN CHECK
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


$appointments = $db->appointments;
$users = $db->users;
$doctors = $db->doctors;

$message = "";
$messageType = "";


/* =========================
   GET APPOINTMENT ID
========================= */

$appointmentId =
    $_GET["id"] ??
    $_POST["appointment_id"] ??
    "";


if ($appointmentId === "") {
    header("Location: appointments.php");
    exit;
}


try {

    $objectId =
        new MongoDB\BSON\ObjectId($appointmentId);

} catch (Exception $e) {

    header("Location: appointments.php");
    exit;
}


/* =========================
   UPDATE APPOINTMENT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $appointmentDate =
        trim($_POST["appointment_date"] ?? "");

    $appointmentTime =
        trim($_POST["appointment_time"] ?? "");

    $reason =
        trim($_POST["reason"] ?? "");

    $status =
        $_POST["status"] ?? "pending";


    /* =========================
       VALIDATION
    ========================= */

    if (
        $appointmentDate === "" ||
        $appointmentTime === ""
    ) {

        $message =
            "Appointment date and time are required.";

        $messageType =
            "error";

    } elseif (
        !in_array(
            $status,
            [
                "pending",
                "confirmed",
                "completed",
                "cancelled"
            ]
        )
    ) {

        $message =
            "Invalid appointment status.";

        $messageType =
            "error";

    } else {


        /* =========================
           UPDATE DATABASE
        ========================= */

        $result =
            $appointments->updateOne(

                [
                    "_id" => $objectId
                ],

                [
                    '$set' => [

                        "appointment_date" =>
                            $appointmentDate,

                        "appointment_time" =>
                            $appointmentTime,

                        "reason" =>
                            $reason,

                        "status" =>
                            $status,

                        "updated_at" =>
                            new MongoDB\BSON\UTCDateTime()

                    ]
                ]

            );


        if ($result->getMatchedCount() > 0) {

            header(
                "Location: appointments.php?updated=1"
            );

            exit;

        } else {

            $message =
                "Appointment not found.";

            $messageType =
                "error";
        }
    }
}


/* =========================
   GET APPOINTMENT
========================= */

$appointment =
    $appointments->findOne([
        "_id" => $objectId
    ]);


if (!$appointment) {

    header("Location: appointments.php");
    exit;
}


/* =========================
   GET PATIENT
========================= */

$patient = null;

if (isset($appointment["user_id"])) {

    try {

        $patient =
            $users->findOne([
                "_id" =>
                    new MongoDB\BSON\ObjectId(
                        (string)$appointment["user_id"]
                    )
            ]);

    } catch (Exception $e) {

        $patient = null;
    }
}


/* =========================
   GET DOCTOR
========================= */

$doctor = null;

if (isset($appointment["doctor_id"])) {

    try {

        $doctor =
            $doctors->findOne([
                "_id" =>
                    new MongoDB\BSON\ObjectId(
                        (string)$appointment["doctor_id"]
                    )
            ]);

    } catch (Exception $e) {

        $doctor = null;
    }
}


$patientName =
    $patient["name"]
    ?? $appointment["user_name"]
    ?? "Patient";


$doctorName =
    $doctor["name"]
    ?? $appointment["doctor_name"]
    ?? "Doctor";


$currentStatus =
    $appointment["status"]
    ?? "pending";

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


        /* NAVBAR */

        .navbar {

            background: #198754;

            color: white;

            padding: 18px 5%;

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


        /* CONTAINER */

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

            text-align: center;

            color: #198754;

            margin-top: 0;
        }


        .subtitle {

            text-align: center;

            color: #666;

            margin-bottom: 25px;
        }


        /* DETAILS */

        .details {

            background: #f2f6f9;

            padding: 18px;

            border-radius: 8px;

            margin-bottom: 25px;
        }


        .details p {

            margin: 9px 0;

            color: #555;
        }


        .details strong {
            color: #222;
        }


        /* MESSAGE */

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


        /* FORM */

        label {

            display: block;

            margin-top: 15px;

            margin-bottom: 7px;

            font-weight: bold;
        }


        input,
        textarea,
        select {

            width: 100%;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        input:focus,
        textarea:focus,
        select:focus {

            outline: none;

            border-color: #198754;
        }


        textarea {

            min-height: 100px;

            resize: vertical;
        }


        /* BUTTONS */

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


<!-- NAVBAR -->

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


<!-- MAIN -->

<div class="container">

    <div class="box">

        <h1>
            ✏️ Edit Appointment
        </h1>

        <div class="subtitle">
            Update appointment information
        </div>


        <?php if ($message !== ""): ?>

            <div
                class="message <?= htmlspecialchars($messageType) ?>"
            >

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>


        <!-- PATIENT / DOCTOR -->

        <div class="details">

            <p>

                👤

                <strong>
                    Patient:
                </strong>

                <?= htmlspecialchars(
                    $patientName
                ) ?>

            </p>


            <p>

                👨‍⚕️

                <strong>
                    Doctor:
                </strong>

                <?= htmlspecialchars(
                    $doctorName
                ) ?>

            </p>

        </div>


        <!-- FORM -->

        <form method="POST">

            <input
                type="hidden"
                name="appointment_id"
                value="<?= htmlspecialchars(
                    (string)$appointment["_id"]
                ) ?>"
            >


            <!-- DATE -->

            <label>
                Appointment Date *
            </label>

            <input
                type="date"
                name="appointment_date"
                value="<?= htmlspecialchars(
                    $appointment["appointment_date"]
                    ?? $appointment["date"]
                    ?? ""
                ) ?>"
                required
            >


            <!-- TIME -->

            <label>
                Appointment Time *
            </label>

            <input
                type="time"
                name="appointment_time"
                value="<?= htmlspecialchars(
                    $appointment["appointment_time"]
                    ?? $appointment["time"]
                    ?? ""
                ) ?>"
                required
            >


            <!-- REASON -->

            <label>
                Reason
            </label>

            <textarea
                name="reason"
                placeholder="Enter appointment reason"
            ><?= htmlspecialchars(
                $appointment["reason"] ?? ""
            ) ?></textarea>


            <!-- STATUS -->

            <label>
                Appointment Status
            </label>

            <select name="status">

                <option
                    value="pending"
                    <?= $currentStatus === "pending"
                        ? "selected"
                        : ""
                    ?>
                >
                    Pending
                </option>


                <option
                    value="confirmed"
                    <?= $currentStatus === "confirmed"
                        ? "selected"
                        : ""
                    ?>
                >
                    Confirmed
                </option>


                <option
                    value="completed"
                    <?= $currentStatus === "completed"
                        ? "selected"
                        : ""
                    ?>
                >
                    Completed
                </option>


                <option
                    value="cancelled"
                    <?= $currentStatus === "cancelled"
                        ? "selected"
                        : ""
                    ?>
                >
                    Cancelled
                </option>

            </select>


            <!-- BUTTONS -->

            <div class="buttons">

                <button
                    type="submit"
                    class="update"
                >
                    💾 Update Appointment
                </button>


                <a
                    href="appointments.php"
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