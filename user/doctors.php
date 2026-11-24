<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

require_once "../config/db.php";


/* =========================
   GET DOCTORS
========================= */

$doctors = $db->doctors;


/* =========================
   SEARCH
========================= */

$search = trim($_GET["search"] ?? "");


$filter = [
    "status" => "active"
];


if ($search !== "") {

    $filter['$or'] = [

        [
            "name" => [
                '$regex' => $search,
                '$options' => "i"
            ]
        ],

        [
            "specialization" => [
                '$regex' => $search,
                '$options' => "i"
            ]
        ]

    ];
}


/* =========================
   GET DOCTOR LIST
========================= */

$doctorList = $doctors->find(
    $filter,
    [
        "sort" => [
            "name" => 1
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

    <title>MediSync - Find Doctor</title>


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

            font-size: 15px;
        }


        .navbar a:hover {
            text-decoration: underline;
        }


        .login-btn {

            background: white;

            color: #198754 !important;

            padding: 8px 15px;

            border-radius: 6px;

            font-weight: bold;
        }


        .signup-btn {

            background: #0f5132;

            color: white !important;

            padding: 8px 15px;

            border-radius: 6px;

            font-weight: bold;
        }


        /* =========================
           CONTAINER
        ========================= */

        .container {

            width: 95%;

            max-width: 1200px;

            margin: 35px auto;
        }


        /* =========================
           PAGE HEADER
        ========================= */

        .page-header {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .page-header h1 {

            margin: 0 0 8px;

            color: #146c43;
        }


        .page-header p {

            margin: 0;

            color: #666;
        }


        /* =========================
           SEARCH
        ========================= */

        .search-box {

            background: white;

            padding: 20px;

            border-radius: 10px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .search-form {

            display: flex;

            gap: 10px;
        }


        .search-form input {

            flex: 1;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        button {

            border: none;

            padding: 12px 18px;

            border-radius: 6px;

            color: white;

            cursor: pointer;

            font-size: 15px;
        }


        .search {

            background: #198754;
        }


        .reset {

            background: #6c757d;
        }


        /* =========================
           DOCTORS
        ========================= */

        .doctors {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(280px, 1fr));

            gap: 20px;
        }


        .doctor-card {

            background: white;

            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 0 12px rgba(0,0,0,0.08);

            transition: 0.2s;
        }


        .doctor-card:hover {

            transform: translateY(-4px);

            box-shadow:
                0 5px 18px rgba(0,0,0,0.12);
        }


        .doctor-icon {

            width: 75px;

            height: 75px;

            background: #d1e7dd;

            border-radius: 50%;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 38px;

            margin-bottom: 15px;
        }


        .doctor-card h3 {

            margin: 0 0 8px;
        }


        .specialization {

            color: #198754;

            font-weight: bold;

            margin-bottom: 15px;
        }


        .info {

            margin: 8px 0;

            color: #555;

            word-break: break-word;
        }


        /* =========================
           BOOK BUTTON
        ========================= */

        .book {

            display: block;

            width: 100%;

            text-align: center;

            text-decoration: none;

            background: #198754;

            color: white;

            padding: 11px;

            border-radius: 6px;

            margin-top: 18px;

            font-weight: bold;
        }


        .book:hover {

            background: #157347;
        }


        .login-book {

            background: #0d6efd;
        }


        .login-book:hover {

            background: #0b5ed7;
        }


        /* =========================
           EMPTY
        ========================= */

        .empty {

            background: white;

            padding: 40px;

            text-align: center;

            border-radius: 10px;

            color: #777;
        }


        /* =========================
           FOOTER
        ========================= */

        footer {

            margin-top: 50px;

            background: #12372a;

            color: white;

            text-align: center;

            padding: 25px;
        }


        footer p {

            margin: 5px;

            color: #d7e7df;
        }


        /* =========================
           MOBILE
        ========================= */

        @media(max-width: 600px) {

            .navbar {

                flex-direction: column;

                text-align: center;
            }


            .navbar-links {

                justify-content: center;
            }


            .search-form {

                flex-direction: column;
            }


            .search-form button {

                width: 100%;
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


        <a href="doctors.php">
            Doctors
        </a>


        <a href="../index.php#services">
            Services
        </a>


        <a href="../index.php#about">
            About
        </a>


        <a href="../index.php#contact">
            Contact
        </a>


        <?php if (isset($_SESSION["user_id"])): ?>


            <?php if (
                ($_SESSION["role"] ?? "") === "admin"
            ): ?>

                <a href="../admin/dashboard.php">
                    Dashboard
                </a>

            <?php else: ?>

                <a href="dashboard.php">
                    Dashboard
                </a>

            <?php endif; ?>


            <a href="logout.php">
                Logout
            </a>


        <?php else: ?>


            <a
                href="login.php"
                class="login-btn"
            >
                Login
            </a>


            <a
                href="signup.php"
                class="signup-btn"
            >
                Sign Up
            </a>


        <?php endif; ?>

    </div>

</div>



<div class="container">


    <!-- =========================
         HEADER
    ========================= -->

    <div class="page-header">

        <h1>
            👨‍⚕️ Find a Doctor
        </h1>

        <p>
            Browse our available doctors
            and find the right healthcare
            professional for you.
        </p>

    </div>



    <!-- =========================
         SEARCH
    ========================= -->

    <div class="search-box">

        <form
            method="GET"
            class="search-form"
        >

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search doctor or specialization..."
            >


            <button
                type="submit"
                class="search"
            >
                🔍 Search
            </button>


            <a href="doctors.php">

                <button
                    type="button"
                    class="reset"
                >
                    Reset
                </button>

            </a>

        </form>

    </div>



    <!-- =========================
         DOCTOR LIST
    ========================= -->

    <?php if (count($doctorList) === 0): ?>


        <div class="empty">

            <h3>
                No doctors found
            </h3>

            <p>
                Please try another search.
            </p>

        </div>


    <?php else: ?>


        <div class="doctors">


            <?php foreach ($doctorList as $doctor): ?>


                <div class="doctor-card">


                    <div class="doctor-icon">
                        👨‍⚕️
                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $doctor["name"] ?? "Doctor"
                        ) ?>

                    </h3>


                    <div class="specialization">

                        <?= htmlspecialchars(
                            $doctor["specialization"]
                            ?? "General Physician"
                        ) ?>

                    </div>


                    <div class="info">

                        📞

                        <?= htmlspecialchars(
                            $doctor["mobile"] ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        📧

                        <?= htmlspecialchars(
                            $doctor["email"] ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        💼 Experience:

                        <?= htmlspecialchars(
                            $doctor["experience"] ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        🕐 Available:

                        <?= htmlspecialchars(
                            $doctor["available_time"] ?? "-"
                        ) ?>

                    </div>



                    <!-- =========================
                         BOOK APPOINTMENT
                    ========================= -->

                    <?php if (isset($_SESSION["user_id"])): ?>


                        <a
                            href="book_appointment.php?doctor=<?= urlencode(
                                (string)$doctor["_id"]
                            ) ?>"
                            class="book"
                        >
                            📅 Book Appointment
                        </a>


                    <?php else: ?>


                        <a
                            href="login.php"
                            class="book login-book"
                        >
                            🔐 Login to Book Appointment
                        </a>


                    <?php endif; ?>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>



<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3>
        🩺 MediSync
    </h3>

    <p>
        Healthcare management made simple.
    </p>

    <p>
        © <?= date("Y") ?> MediSync.
        All rights reserved.
    </p>

</footer>


</body>

</html>