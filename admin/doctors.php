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


/* =========================
   DATABASE
========================= */

$doctors = $db->doctors;

$message = "";
$messageType = "";


/* =========================
   DELETE DOCTOR
========================= */

if (isset($_POST["delete_doctor"])) {

    $doctorId = trim($_POST["doctor_id"] ?? "");

    try {

        if ($doctorId === "") {
            throw new Exception("Empty doctor ID");
        }

        $objectId = new MongoDB\BSON\ObjectId($doctorId);

        $result = $doctors->deleteOne([
            "_id" => $objectId
        ]);

        if ($result->getDeletedCount() > 0) {

            $message = "Doctor deleted successfully.";
            $messageType = "success";

        } else {

            $message = "Doctor not found.";
            $messageType = "error";
        }

    } catch (Exception $e) {

        $message = "Invalid doctor ID.";
        $messageType = "error";
    }
}


/* =========================
   ADD DOCTOR
========================= */

if (isset($_POST["add_doctor"])) {

    $name = trim($_POST["name"] ?? "");

    $specialization = trim(
        $_POST["specialization"] ?? ""
    );

    $email = trim($_POST["email"] ?? "");

    $mobile = trim($_POST["mobile"] ?? "");

    $experience = trim(
        $_POST["experience"] ?? ""
    );

    $availableTime = trim(
        $_POST["available_time"] ?? ""
    );


    if (
        $name === "" ||
        $specialization === "" ||
        $email === "" ||
        $mobile === "" ||
        $experience === "" ||
        $availableTime === ""
    ) {

        $message = "All fields are required.";
        $messageType = "error";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message = "Please enter a valid email.";
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

    } else {

        $emailExists = $doctors->findOne([
            "email" => $email
        ]);

        $mobileExists = $doctors->findOne([
            "mobile" => $mobile
        ]);


        if ($emailExists) {

            $message =
                "Doctor with this email already exists.";

            $messageType = "error";

        } elseif ($mobileExists) {

            $message =
                "Doctor with this mobile number already exists.";

            $messageType = "error";

        } else {

            $doctors->insertOne([

                "name" => $name,

                "specialization" =>
                    $specialization,

                "email" => $email,

                "mobile" => $mobile,

                "experience" => $experience,

                "available_time" =>
                    $availableTime,

                "status" => "active",

                "created_at" =>
                    new MongoDB\BSON\UTCDateTime(),

                "updated_at" =>
                    new MongoDB\BSON\UTCDateTime()

            ]);

            $message =
                "Doctor added successfully.";

            $messageType = "success";
        }
    }
}


/* =========================
   UPDATE DOCTOR
========================= */

if (isset($_POST["update_doctor"])) {

    $doctorId = trim(
        $_POST["doctor_id"] ?? ""
    );

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


    if (
        $doctorId === "" ||
        $name === "" ||
        $specialization === "" ||
        $email === "" ||
        $mobile === "" ||
        $experience === "" ||
        $availableTime === ""
    ) {

        $message = "All fields are required.";
        $messageType = "error";

    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message = "Please enter a valid email.";
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
            ["active", "inactive"],
            true
        )
    ) {

        $message = "Invalid doctor status.";
        $messageType = "error";

    } else {

        try {

            $objectId =
                new MongoDB\BSON\ObjectId(
                    $doctorId
                );


            /* CHECK DUPLICATE EMAIL */

            $emailExists =
                $doctors->findOne([

                    "email" => $email,

                    "_id" => [
                        '$ne' => $objectId
                    ]

                ]);


            /* CHECK DUPLICATE MOBILE */

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
                    "Another doctor already uses this mobile.";

                $messageType = "error";

            } else {

                $doctors->updateOne(

                    [
                        "_id" => $objectId
                    ],

                    [
                        '$set' => [

                            "name" => $name,

                            "specialization" =>
                                $specialization,

                            "email" => $email,

                            "mobile" => $mobile,

                            "experience" =>
                                $experience,

                            "available_time" =>
                                $availableTime,

                            "status" => $status,

                            "updated_at" =>
                                new MongoDB\BSON\UTCDateTime()

                        ]
                    ]
                );


                $message =
                    "Doctor updated successfully.";

                $messageType = "success";
            }

        } catch (Exception $e) {

            $message =
                "Invalid doctor ID.";

            $messageType = "error";
        }
    }
}


/* =========================
   SEARCH
========================= */

$search = trim(
    $_GET["search"] ?? ""
);


/*
   IMPORTANT:
   Use single quotes around $or.
   Otherwise PHP thinks $or is a variable.
*/

$filter = [];


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
        ],

        [
            "email" => [
                '$regex' => $search,
                '$options' => "i"
            ]
        ],

        [
            "mobile" => [
                '$regex' => $search,
                '$options' => "i"
            ]
        ]

    ];
}


/* =========================
   GET DOCTORS
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

    <title>MediSync - Manage Doctors</title>


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


        .container {

            width: 95%;

            max-width: 1400px;

            margin: 35px auto;
        }


        .header {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .header h1 {

            margin: 0 0 8px;

            color: #146c43;
        }


        .header p {

            margin: 0;

            color: #666;
        }


        .message {

            padding: 12px;

            border-radius: 7px;

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


        .add-box {

            background: white;

            padding: 25px;

            border-radius: 12px;

            margin-bottom: 25px;

            box-shadow:
                0 0 10px rgba(0,0,0,0.08);
        }


        .add-box h2 {

            margin-top: 0;

            color: #198754;
        }


        .form-grid {

            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 18px;
        }


        .form-group label {

            display: block;

            font-weight: bold;

            margin-bottom: 7px;
        }


        input,
        select {

            width: 100%;

            padding: 11px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 14px;
        }


        input:focus,
        select:focus {

            outline: none;

            border-color: #198754;
        }


        .full {

            grid-column: 1 / -1;
        }


        button {

            border: none;

            cursor: pointer;
        }


        .add-btn {

            background: #198754;

            color: white;

            padding: 12px 20px;

            border-radius: 6px;

            font-size: 15px;
        }


        .add-btn:hover {

            background: #146c43;
        }


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
        }


        .search-btn {

            background: #198754;

            color: white;

            padding: 11px 18px;

            border-radius: 6px;
        }


        .search-btn:hover {

            background: #146c43;
        }


        .reset-btn {

            background: #6c757d;

            color: white;

            padding: 11px 18px;

            border-radius: 6px;

            text-decoration: none;

            display: inline-flex;

            align-items: center;
        }


        .reset-btn:hover {

            background: #5c636a;
        }


        .doctors {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(330px, 1fr));

            gap: 20px;
        }


        .doctor-card {

            background: white;

            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 0 12px rgba(0,0,0,0.08);
        }


        .doctor-icon {

            width: 65px;

            height: 65px;

            background: #d1e7dd;

            border-radius: 50%;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 32px;

            margin-bottom: 15px;
        }


        .doctor-card h3 {

            margin: 0 0 7px;

            color: #198754;
        }


        .specialization {

            font-weight: bold;

            margin-bottom: 15px;
        }


        .info {

            margin: 8px 0;

            color: #555;

            word-break: break-word;
        }


        .status {

            display: inline-block;

            padding: 6px 10px;

            border-radius: 20px;

            font-size: 12px;

            font-weight: bold;

            text-transform: capitalize;

            margin-top: 8px;
        }


        .active {

            background: #d1e7dd;

            color: #0f5132;
        }


        .inactive {

            background: #f8d7da;

            color: #842029;
        }


        .actions {

            margin-top: 20px;

            display: flex;

            gap: 8px;
        }


        .edit-btn {

            flex: 1;

            background: #0d6efd;

            color: white;

            padding: 10px;

            border-radius: 6px;

            text-align: center;

            text-decoration: none;
        }


        .edit-btn:hover {

            background: #0b5ed7;
        }


        .delete-form {

            flex: 1;
        }


        .delete-btn {

            width: 100%;

            background: #dc3545;

            color: white;

            padding: 10px;

            border-radius: 6px;

            font-size: 14px;
        }


        .delete-btn:hover {

            background: #bb2d3b;
        }


        .empty {

            background: white;

            padding: 50px;

            text-align: center;

            border-radius: 12px;

            color: #777;
        }


        @media(max-width: 800px) {

            .form-grid {

                grid-template-columns: 1fr;
            }


            .full {

                grid-column: auto;
            }

        }


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


            .search-btn,
            .reset-btn {

                width: 100%;

                justify-content: center;
            }


            .actions {

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


    <!-- =========================
         HEADER
    ========================= -->

    <div class="header">

        <h1>
            👨‍⚕️ Doctor Management
        </h1>

        <p>
            Add, edit, activate, deactivate and delete doctors.
        </p>

    </div>



    <!-- =========================
         MESSAGE
    ========================= -->

    <?php if ($message !== ""): ?>

        <div
            class="message <?= htmlspecialchars($messageType) ?>"
        >

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>



    <!-- =========================
         ADD DOCTOR
    ========================= -->

    <div class="add-box">

        <h2>
            ➕ Add New Doctor
        </h2>


        <form method="POST">

            <div class="form-grid">


                <div class="form-group">

                    <label>
                        Doctor Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        placeholder="Dr. John Smith"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Specialization
                    </label>

                    <input
                        type="text"
                        name="specialization"
                        placeholder="Cardiologist"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        placeholder="doctor@example.com"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Mobile
                    </label>

                    <input
                        type="tel"
                        name="mobile"
                        placeholder="9876543210"
                        maxlength="10"
                        pattern="[0-9]{10}"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Experience
                    </label>

                    <input
                        type="text"
                        name="experience"
                        placeholder="10 Years"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Available Time
                    </label>

                    <input
                        type="text"
                        name="available_time"
                        placeholder="10 AM - 2 PM"
                        required
                    >

                </div>


                <div class="full">

                    <button
                        type="submit"
                        name="add_doctor"
                        class="add-btn"
                    >
                        ➕ Add Doctor
                    </button>

                </div>


            </div>

        </form>

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
                placeholder="Search doctor, specialization, email or mobile..."
            >


            <button
                type="submit"
                class="search-btn"
            >
                🔍 Search
            </button>


            <a
                href="doctors.php"
                class="reset-btn"
            >
                Reset
            </a>

        </form>

    </div>



    <!-- =========================
         DOCTORS
    ========================= -->

    <?php if (count($doctorList) === 0): ?>

        <div class="empty">

            <h2>
                👨‍⚕️ No Doctors Found
            </h2>

            <p>
                Add a doctor or change your search.
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

                        🩺

                        <?= htmlspecialchars(
                            $doctor["specialization"] ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        📧

                        <?= htmlspecialchars(
                            $doctor["email"] ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        📞

                        <?= htmlspecialchars(
                            $doctor["mobile"] ?? "-"
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


                    <?php

                    $doctorStatus =
                        $doctor["status"] ?? "active";

                    ?>


                    <span
                        class="status <?= htmlspecialchars(
                            $doctorStatus
                        ) ?>"
                    >

                        <?= htmlspecialchars(
                            $doctorStatus
                        ) ?>

                    </span>



                    <!-- =========================
                         ACTIONS
                    ========================= -->

                    <div class="actions">


                        <a
                            href="edit_doctor.php?id=<?= urlencode(
                                (string)$doctor["_id"]
                            ) ?>"
                            class="edit-btn"
                        >
                            ✏️ Edit
                        </a>


                        <form
                            method="POST"
                            class="delete-form"
                            onsubmit="return confirm('Are you sure you want to delete this doctor?');"
                        >

                            <input
                                type="hidden"
                                name="doctor_id"
                                value="<?= htmlspecialchars(
                                    (string)$doctor["_id"]
                                ) ?>"
                            >


                            <button
                                type="submit"
                                name="delete_doctor"
                                class="delete-btn"
                            >
                                🗑️ Delete
                            </button>

                        </form>


                    </div>


                </div>


            <?php endforeach; ?>


        </div>


    <?php endif; ?>


</div>


</body>

</html>