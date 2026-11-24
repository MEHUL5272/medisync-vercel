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


$appointments = $db->appointments;

$message = "";
$messageType = "";


/* =========================
   UPDATE STATUS
========================= */

if (isset($_POST["update_status"])) {

    $appointmentId =
        trim($_POST["appointment_id"] ?? "");

    $status =
        trim($_POST["status"] ?? "");


    $allowedStatus = [
        "pending",
        "confirmed",
        "cancelled",
        "completed"
    ];


    if (
        $appointmentId === "" ||
        !in_array($status, $allowedStatus)
    ) {

        $message = "Invalid appointment data.";
        $messageType = "error";

    } else {

        try {

            $objectId =
                new MongoDB\BSON\ObjectId(
                    $appointmentId
                );


            $result =
                $appointments->updateOne(

                    [
                        "_id" => $objectId
                    ],

                    [
                        '$set' => [

                            "status" => $status,

                            "updated_at" =>
                                new MongoDB\BSON\UTCDateTime()

                        ]
                    ]
                );


            if ($result->getModifiedCount() > 0) {

                $message =
                    "Appointment status updated successfully.";

                $messageType = "success";

            } else {

                $message =
                    "No changes were made.";

                $messageType = "error";
            }


        } catch (Exception $e) {

            $message =
                "Invalid appointment ID.";

            $messageType = "error";
        }
    }
}


/* =========================
   DELETE APPOINTMENT
========================= */

if (isset($_POST["delete_appointment"])) {

    $appointmentId =
        trim($_POST["appointment_id"] ?? "");


    try {

        $objectId =
            new MongoDB\BSON\ObjectId(
                $appointmentId
            );


        $result =
            $appointments->deleteOne([
                "_id" => $objectId
            ]);


        if ($result->getDeletedCount() > 0) {

            $message =
                "Appointment deleted successfully.";

            $messageType = "success";

        } else {

            $message =
                "Appointment not found.";

            $messageType = "error";
        }


    } catch (Exception $e) {

        $message =
            "Invalid appointment ID.";

        $messageType = "error";
    }
}


/* =========================
   SEARCH
========================= */

$search =
    trim($_GET["search"] ?? "");


$filter = [];


if ($search !== "") {

    $filter['$or'] = [

        [
            "user_name" => [
                '$regex' => $search,
                '$options' => "i"
            ]
        ],

        [
            "user_email" => [
                '$regex' => $search,
                '$options' => "i"
            ]
        ],

        [
            "doctor_name" => [
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
   GET APPOINTMENTS
========================= */

$appointmentList =
    $appointments->find(

        $filter,

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
    MediSync - Manage Appointments
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

    align-items: center;

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


/* =========================
   MESSAGE
========================= */

.message {

    padding: 12px;

    border-radius: 7px;

    margin-bottom: 20px;

    text-align: center;
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


.btn {

    border: none;

    padding: 11px 18px;

    border-radius: 6px;

    color: white;

    cursor: pointer;

    text-decoration: none;

    display: inline-block;

    font-size: 14px;
}


.search-btn {
    background: #198754;
}


.reset-btn {
    background: #6c757d;
}


/* =========================
   TABLE
========================= */

.table-box {

    background: white;

    border-radius: 12px;

    box-shadow:
        0 0 12px rgba(0,0,0,0.08);

    overflow-x: auto;
}


table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1200px;
}


th {

    background: #198754;

    color: white;

    padding: 14px;

    text-align: left;

    white-space: nowrap;
}


td {

    padding: 14px;

    border-bottom: 1px solid #eee;

    vertical-align: top;
}


tr:hover td {
    background: #f8faf9;
}


.user-name {

    font-weight: bold;
}


.doctor-name {

    font-weight: bold;

    color: #198754;
}


.small {

    color: #666;

    font-size: 13px;

    margin-top: 4px;
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


.cancelled {

    background: #f8d7da;

    color: #842029;
}


.completed {

    background: #cfe2ff;

    color: #084298;
}


/* =========================
   ACTIONS
========================= */

.actions {

    min-width: 190px;
}


.status-form {

    display: flex;

    gap: 7px;

    margin-bottom: 8px;
}


.status-form select {

    padding: 8px;

    border: 1px solid #ccc;

    border-radius: 5px;

    flex: 1;
}


.update-btn {

    background: #0d6efd;

    border: none;

    color: white;

    padding: 8px 10px;

    border-radius: 5px;

    cursor: pointer;
}


/* =========================
   EDIT BUTTON
========================= */

.edit-btn {

    display: block;

    width: 100%;

    background: #ffc107;

    color: #212529;

    padding: 9px;

    border-radius: 5px;

    text-align: center;

    text-decoration: none;

    font-weight: bold;

    margin-bottom: 8px;
}


.edit-btn:hover {

    background: #e0a800;

    color: #000;
}


/* =========================
   DELETE BUTTON
========================= */

.delete-btn {

    width: 100%;

    background: #dc3545;

    border: none;

    color: white;

    padding: 8px;

    border-radius: 5px;

    cursor: pointer;
}


.delete-btn:hover {
    background: #bb2d3b;
}


/* =========================
   EMPTY
========================= */

.empty {

    background: white;

    padding: 50px;

    text-align: center;

    border-radius: 12px;

    color: #777;
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


    .search-form {

        flex-direction: column;
    }


    .search-form .btn {

        width: 100%;

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
        📅 Appointment Management
    </h1>

    <p>
        View and manage all patient appointments.
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
            placeholder="Search by patient, email, doctor or specialization..."
        >


        <button
            type="submit"
            class="btn search-btn"
        >
            🔍 Search
        </button>


        <a
            href="appointments.php"
            class="btn reset-btn"
        >
            Reset
        </a>

    </form>

</div>



<!-- =========================
     APPOINTMENTS
========================= -->

<?php if (count($appointmentList) === 0): ?>

    <div class="empty">

        <h2>
            📭 No Appointments Found
        </h2>

        <p>
            There are currently no appointments.
        </p>

    </div>


<?php else: ?>


<div class="table-box">

<table>

<thead>

<tr>

    <th>
        Patient
    </th>

    <th>
        Doctor
    </th>

    <th>
        Date & Time
    </th>

    <th>
        Reason
    </th>

    <th>
        Status
    </th>

    <th>
        Actions
    </th>

</tr>

</thead>


<tbody>


<?php foreach (
    $appointmentList as $appointment
): ?>


<?php

$status =
    $appointment["status"]
    ?? "pending";

$appointmentId =
    (string)$appointment["_id"];

?>


<tr>


<!-- PATIENT -->

<td>

    <div class="user-name">

        👤

        <?= htmlspecialchars(
            $appointment["user_name"]
            ?? "Unknown"
        ) ?>

    </div>


    <div class="small">

        📧

        <?= htmlspecialchars(
            $appointment["user_email"]
            ?? "-"
        ) ?>

    </div>


    <div class="small">

        📞

        <?= htmlspecialchars(
            $appointment["user_mobile"]
            ?? "-"
        ) ?>

    </div>

</td>



<!-- DOCTOR -->

<td>

    <div class="doctor-name">

        👨‍⚕️

        <?= htmlspecialchars(
            $appointment["doctor_name"]
            ?? "Unknown"
        ) ?>

    </div>


    <div class="small">

        🩺

        <?= htmlspecialchars(
            $appointment["specialization"]
            ?? "-"
        ) ?>

    </div>

</td>



<!-- DATE TIME -->

<td>

    📅

    <?= htmlspecialchars(
        $appointment["appointment_date"]
        ?? "-"
    ) ?>


    <div class="small">

        🕐

        <?= htmlspecialchars(
            $appointment["appointment_time"]
            ?? "-"
        ) ?>

    </div>

</td>



<!-- REASON -->

<td>

    <?= htmlspecialchars(
        $appointment["reason"]
        ?? "No reason provided"
    ) ?>

</td>



<!-- STATUS -->

<td>

    <span
        class="status <?= htmlspecialchars($status) ?>"
    >

        <?= htmlspecialchars($status) ?>

    </span>

</td>



<!-- ACTIONS -->

<td class="actions">


    <!-- EDIT -->

    <a
        href="edit_appointment.php?id=<?= urlencode($appointmentId) ?>"
        class="edit-btn"
    >

        ✏️ Edit

    </a>



    <!-- UPDATE STATUS -->

    <form
        method="POST"
        class="status-form"
    >

        <input
            type="hidden"
            name="appointment_id"
            value="<?= htmlspecialchars(
                $appointmentId
            ) ?>"
        >


        <select name="status">

            <option
                value="pending"
                <?= $status === "pending"
                    ? "selected"
                    : ""
                ?>
            >
                Pending
            </option>


            <option
                value="confirmed"
                <?= $status === "confirmed"
                    ? "selected"
                    : ""
                ?>
            >
                Confirmed
            </option>


            <option
                value="completed"
                <?= $status === "completed"
                    ? "selected"
                    : ""
                ?>
            >
                Completed
            </option>


            <option
                value="cancelled"
                <?= $status === "cancelled"
                    ? "selected"
                    : ""
                ?>
            >
                Cancelled
            </option>

        </select>


        <button
            type="submit"
            name="update_status"
            class="update-btn"
        >
            Update
        </button>

    </form>



    <!-- DELETE -->

    <form
        method="POST"
        onsubmit="return confirm('Are you sure you want to delete this appointment?');"
    >

        <input
            type="hidden"
            name="appointment_id"
            value="<?= htmlspecialchars(
                $appointmentId
            ) ?>"
        >


        <button
            type="submit"
            name="delete_appointment"
            class="delete-btn"
        >

            🗑️ Delete

        </button>

    </form>


</td>


</tr>


<?php endforeach; ?>


</tbody>

</table>

</div>


<?php endif; ?>


</div>


</body>

</html>