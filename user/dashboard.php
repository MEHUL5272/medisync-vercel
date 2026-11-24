<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/db.php";

$appointments = $db->appointments;


/* =========================
   USER ID
========================= */

$userId = new MongoDB\BSON\ObjectId(
    $_SESSION["user_id"]
);


/* =========================
   APPOINTMENT COUNTS
========================= */

$totalAppointments = $appointments->countDocuments([
    "user_id" => $userId
]);


$pendingAppointments = $appointments->countDocuments([
    "user_id" => $userId,
    "status" => "pending"
]);


$confirmedAppointments = $appointments->countDocuments([
    "user_id" => $userId,
    "status" => "confirmed"
]);


/* =========================
   FOLLOW-UP COUNT
========================= */

$followUpAppointments = $appointments->countDocuments([
    "user_id" => $userId,
    "follow_up_date" => [
        '$exists' => true,
        '$ne' => ""
    ]
]);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>MediSync - User Dashboard</title>


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


        .nav-links {

            display: flex;

            align-items: center;

            gap: 18px;
        }


        .nav-links a {

            color: white;

            text-decoration: none;
        }


        .logout {

            background: #dc3545;

            padding: 8px 15px;

            border-radius: 5px;
        }


        .container {

            width: 95%;

            max-width: 1200px;

            margin: 35px auto;
        }


        .welcome {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .welcome h2 {

            margin-top: 0;

            color: #198754;
        }


        .stats {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 30px;
        }


        .stat-card {

            background: white;

            padding: 25px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .stat-icon {

            font-size: 35px;

            margin-bottom: 10px;
        }


        .stat-card h3 {

            margin: 5px 0;

            font-size: 30px;

            color: #198754;
        }


        .stat-card p {

            margin: 0;

            color: #666;
        }


        .actions {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }


        .action-card {

            background: white;

            padding: 25px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .action-card h3 {

            margin-top: 0;
        }


        .action-card p {

            color: #666;

            min-height: 40px;
        }


        .action-card a {

            display: inline-block;

            margin-top: 10px;

            padding: 11px 20px;

            background: #198754;

            color: white;

            text-decoration: none;

            border-radius: 6px;
        }


        .action-card a:hover {

            background: #157347;
        }


        @media(max-width: 900px) {

            .stats {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .actions {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media(max-width: 600px) {

            .navbar {

                flex-direction: column;

                gap: 12px;

                text-align: center;
            }


            .nav-links {

                flex-wrap: wrap;

                justify-content: center;
            }


            .stats {

                grid-template-columns: 1fr;
            }


            .actions {

                grid-template-columns: 1fr;
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


    <div class="nav-links">

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


    <!-- WELCOME -->

    <div class="welcome">

        <h2>

            Welcome,

            <?= htmlspecialchars(
                $_SESSION["user_name"] ?? "User"
            ) ?>

            👋

        </h2>


        <p>

            Email:

            <?= htmlspecialchars(
                $_SESSION["user_email"] ?? "-"
            ) ?>

        </p>

    </div>


    <!-- STATISTICS -->

    <div class="stats">


        <div class="stat-card">

            <div class="stat-icon">
                📅
            </div>

            <h3>
                <?= $totalAppointments ?>
            </h3>

            <p>
                Total Appointments
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🟡
            </div>

            <h3>
                <?= $pendingAppointments ?>
            </h3>

            <p>
                Pending
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🟢
            </div>

            <h3>
                <?= $confirmedAppointments ?>
            </h3>

            <p>
                Confirmed
            </p>

        </div>


        <div class="stat-card">

            <div class="stat-icon">
                🔁
            </div>

            <h3>
                <?= $followUpAppointments ?>
            </h3>

            <p>
                Follow-ups
            </p>

        </div>


    </div>


    <!-- QUICK ACTIONS -->

    <div class="actions">


        <div class="action-card">

            <h3>
                👨‍⚕️ Find Doctor
            </h3>

            <p>
                Find a doctor and book an appointment.
            </p>

            <a href="doctors.php">
                Find Doctor
            </a>

        </div>


        <div class="action-card">

            <h3>
                📅 My Appointments
            </h3>

            <p>
                View and manage your appointments.
            </p>

            <a href="appointments.php">
                View Appointments
            </a>

        </div>


        <div class="action-card">

            <h3>
                👤 My Profile
            </h3>

            <p>
                View and update your profile information.
            </p>

            <a href="profile.php">
                My Profile
            </a>

        </div>


    </div>


</div>


</body>

</html>