<?php

session_start();

if (
    !isset($_SESSION["user_id"]) ||
    ($_SESSION["role"] ?? "") !== "admin"
) {
    header("Location: ../user/login.php");
    exit;
}

require_once "../config/db.php";

$users = $db->users;
$doctors = $db->doctors;
$appointments = $db->appointments;


/* =========================
   COUNTS
========================= */

$totalUsers = $users->countDocuments([
    "role" => "user"
]);

$totalDoctors = $doctors->countDocuments();

$activeDoctors = $doctors->countDocuments([
    "status" => "active"
]);

/* =========================
   APPOINTMENT FILTER
   HIDE ADMIN APPOINTMENTS
========================= */

$appointmentFilter = [
    "user_email" => [
        '$ne' => "admin@medisync.com"
    ]
];

$totalAppointments = $appointments->countDocuments(
    $appointmentFilter
);

$pendingAppointments = $appointments->countDocuments([
    '$and' => [
        $appointmentFilter,
        ["status" => "pending"]
    ]
]);

$confirmedAppointments = $appointments->countDocuments([
    '$and' => [
        $appointmentFilter,
        ["status" => "confirmed"]
    ]
]);

$cancelledAppointments = $appointments->countDocuments([
    '$and' => [
        $appointmentFilter,
        ["status" => "cancelled"]
    ]
]);

$completedAppointments = $appointments->countDocuments([
    '$and' => [
        $appointmentFilter,
        ["status" => "completed"]
    ]
]);


/* =========================
   TODAY
========================= */

$today = date("Y-m-d");

$todayAppointments = $appointments->find(
    [
        '$and' => [
            $appointmentFilter,
            [
                "appointment_date" => $today
            ]
        ]
    ],
    [
        "sort" => [
            "appointment_time" => 1
        ],
        "limit" => 10
    ]
)->toArray();


/* =========================
   UPCOMING
========================= */

$upcomingAppointments = $appointments->find(
    [
        '$and' => [
            $appointmentFilter,
            [
                "appointment_date" => [
                    '$gt' => $today
                ]
            ],
            [
                "status" => [
                    '$ne' => "cancelled"
                ]
            ]
        ]
    ],
    [
        "sort" => [
            "appointment_date" => 1,
            "appointment_time" => 1
        ],
        "limit" => 10
    ]
)->toArray();


/* =========================
   RECENT
========================= */

$recentAppointments = $appointments->find(
    $appointmentFilter,
    [
        "sort" => [
            "created_at" => -1
        ],
        "limit" => 10
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

    <title>MediSync - Admin Dashboard</title>


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
        }


        .navbar h2 {
            margin: 0;
        }


        .navbar a {

            color: white;

            text-decoration: none;

            margin-left: 18px;
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

            max-width: 1350px;

            margin: 30px auto;
        }


        /* =========================
           WELCOME
        ========================= */

        .welcome {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .welcome h1 {

            margin: 0 0 8px;
        }


        .welcome p {

            margin: 0;

            color: #666;
        }


        /* =========================
           STAT CARDS
        ========================= */

        .cards {

            display: grid;

            grid-template-columns:
                repeat(4, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }


        .card {

            background: white;

            padding: 22px;

            border-radius: 12px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .card-icon {

            font-size: 32px;
        }


        .card h3 {

            margin: 10px 0 5px;

            color: #555;

            font-size: 15px;
        }


        .number {

            font-size: 30px;

            font-weight: bold;

            color: #198754;
        }


        /* =========================
           QUICK ACTIONS
        ========================= */

        .quick-actions {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }


        .action {

            background: white;

            padding: 20px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .action h3 {

            margin-top: 0;
        }


        .action p {

            color: #666;
        }


        .action a {

            display: inline-block;

            background: #198754;

            color: white;

            text-decoration: none;

            padding: 10px 18px;

            border-radius: 6px;
        }


        .action a:hover {

            background: #157347;
        }


        /* =========================
           SECTION
        ========================= */

        .section {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .section h2 {

            margin-top: 0;
        }


        .table-container {

            overflow-x: auto;
        }


        table {

            width: 100%;

            border-collapse: collapse;

            min-width: 850px;
        }


        th {

            background: #212529;

            color: white;

            padding: 12px;

            text-align: left;
        }


        td {

            border-bottom: 1px solid #ddd;

            padding: 12px;
        }


        tr:hover {

            background: #f8f9fa;
        }


        /* =========================
           STATUS
        ========================= */

        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

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


        .booked {

            background: #e2e3e5;

            color: #41464b;
        }


        .visit {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            background: #e2e3e5;

            color: #41464b;

            font-size: 12px;
        }


        .empty {

            text-align: center;

            color: #777;

            padding: 25px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media(max-width: 1000px) {

            .cards {

                grid-template-columns:
                    repeat(2, 1fr);
            }


            .quick-actions {

                grid-template-columns:
                    repeat(2, 1fr);
            }

        }


        @media(max-width: 650px) {

            .cards {

                grid-template-columns: 1fr;
            }


            .quick-actions {

                grid-template-columns: 1fr;
            }


            .navbar {

                flex-direction: column;

                gap: 15px;

                text-align: center;
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


        <a href="appointments.php">
            Appointments
        </a>


        <a href="../user/logout.php" class="logout">
            Logout
        </a>

    </div>

</div>


<div class="container">


    <!-- =========================
         WELCOME
    ========================= -->

    <div class="welcome">

        <h1>
            Welcome to Admin Dashboard 👋
        </h1>

        <p>
            Manage MediSync users, doctors and appointments.
        </p>

    </div>


    <!-- =========================
         STAT CARDS
    ========================= -->

    <div class="cards">


        <div class="card">

            <div class="card-icon">
                👤
            </div>

            <h3>
                Total Users
            </h3>

            <div class="number">
                <?= $totalUsers ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                👨‍⚕️
            </div>

            <h3>
                Total Doctors
            </h3>

            <div class="number">
                <?= $totalDoctors ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                📅
            </div>

            <h3>
                Total Appointments
            </h3>

            <div class="number">
                <?= $totalAppointments ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🟡
            </div>

            <h3>
                Pending Appointments
            </h3>

            <div class="number">
                <?= $pendingAppointments ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🟢
            </div>

            <h3>
                Confirmed
            </h3>

            <div class="number">
                <?= $confirmedAppointments ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ✅
            </div>

            <h3>
                Completed
            </h3>

            <div class="number">
                <?= $completedAppointments ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                ❌
            </div>

            <h3>
                Cancelled
            </h3>

            <div class="number">
                <?= $cancelledAppointments ?>
            </div>

        </div>


        <div class="card">

            <div class="card-icon">
                🩺
            </div>

            <h3>
                Active Doctors
            </h3>

            <div class="number">
                <?= $activeDoctors ?>
            </div>

        </div>


    </div>


    <!-- =========================
         QUICK ACTIONS
    ========================= -->

    <div class="quick-actions">


        <div class="action">

            <h3>
                👨‍⚕️ Manage Doctors
            </h3>

            <p>
                Add, edit, delete and manage doctors.
            </p>

            <a href="doctors.php">
                Manage Doctors
            </a>

        </div>


        <div class="action">

            <h3>
                📅 Manage Appointments
            </h3>

            <p>
                Confirm, cancel and complete appointments.
            </p>

            <a href="appointments.php">
                Manage Appointments
            </a>

        </div>


        <div class="action">

            <h3>
                👥 Manage Users
            </h3>

            <p>
                View and manage registered users.
            </p>

            <a href="users.php">
                Manage Users
            </a>

        </div>


    </div>


    <!-- =========================
         TODAY
    ========================= -->

    <div class="section">

        <h2>
            📅 Today's Appointments
        </h2>


        <?php if (count($todayAppointments) === 0): ?>

            <div class="empty">

                No appointments scheduled for today.

            </div>

        <?php else: ?>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                User
                            </th>

                            <th>
                                Doctor
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
                                Reason
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $todayAppointments
                            as $appointment
                        ): ?>


                            <?php

                            $status =
                                strtolower(
                                    $appointment["status"]
                                    ?? "booked"
                                );

                            ?>


                            <tr>

                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["user_name"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["doctor_name"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["appointment_time"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["reason"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>

                                    <span
                                        class="status <?= htmlspecialchars($status) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ) ?>

                                    </span>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </div>


    <!-- =========================
         UPCOMING
    ========================= -->

    <div class="section">

        <h2>
            🕐 Upcoming Appointments
        </h2>


        <?php if (
            count($upcomingAppointments) === 0
        ): ?>

            <div class="empty">

                No upcoming appointments.

            </div>

        <?php else: ?>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                Date
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
                                User
                            </th>

                            <th>
                                Doctor
                            </th>

                            <th>
                                Visit Type
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $upcomingAppointments
                            as $appointment
                        ): ?>


                            <?php

                            $status =
                                strtolower(
                                    $appointment["status"]
                                    ?? "booked"
                                );

                            ?>


                            <tr>

                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["appointment_date"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["appointment_time"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["user_name"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["doctor_name"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>

                                    <span class="visit">

                                        <?= htmlspecialchars(
                                            $appointment["visit_type"]
                                            ?? "first_visit"
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span
                                        class="status <?= htmlspecialchars($status) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ) ?>

                                    </span>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </div>


    <!-- =========================
         RECENT
    ========================= -->

    <div class="section">

        <h2>
            📋 Recent Appointments
        </h2>


        <?php if (
            count($recentAppointments) === 0
        ): ?>

            <div class="empty">

                No appointments found.

            </div>

        <?php else: ?>


            <div class="table-container">

                <table>

                    <thead>

                        <tr>

                            <th>
                                User
                            </th>

                            <th>
                                Email
                            </th>

                            <th>
                                Doctor
                            </th>

                            <th>
                                Date
                            </th>

                            <th>
                                Time
                            </th>

                            <th>
                                Visit Type
                            </th>

                            <th>
                                Status
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                        <?php foreach (
                            $recentAppointments
                            as $appointment
                        ): ?>


                            <?php

                            $status =
                                strtolower(
                                    $appointment["status"]
                                    ?? "booked"
                                );

                            ?>


                            <tr>

                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["user_name"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["user_email"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["doctor_name"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["appointment_date"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>
                                    <?= htmlspecialchars(
                                        $appointment["appointment_time"]
                                        ?? "-"
                                    ) ?>
                                </td>


                                <td>

                                    <span class="visit">

                                        <?= htmlspecialchars(
                                            $appointment["visit_type"]
                                            ?? "first_visit"
                                        ) ?>

                                    </span>

                                </td>


                                <td>

                                    <span
                                        class="status <?= htmlspecialchars($status) ?>"
                                    >

                                        <?= htmlspecialchars(
                                            $status
                                        ) ?>

                                    </span>

                                </td>

                            </tr>


                        <?php endforeach; ?>


                    </tbody>

                </table>

            </div>


        <?php endif; ?>


    </div>


</div>


</body>

</html>