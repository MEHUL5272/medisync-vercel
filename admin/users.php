<?php

session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

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
    header("Location: ../user/login.php");
    exit;
}


$users = $db->users;

$message = "";
$messageType = "";


/* =========================
   USER ACTIONS
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $userId = $_POST["user_id"] ?? "";


    try {

        if ($userId === "") {
            throw new Exception("Invalid user.");
        }


        $objectId =
            new MongoDB\BSON\ObjectId($userId);


        /* =========================
           BLOCK / UNBLOCK
        ========================= */

        if ($action === "toggle_status") {

            $user = $users->findOne([
                "_id" => $objectId
            ]);


            if (!$user) {
                throw new Exception("User not found.");
            }


            $currentStatus =
                $user["status"] ?? "active";


            $newStatus =
                $currentStatus === "blocked"
                ? "active"
                : "blocked";


            $users->updateOne(

                [
                    "_id" => $objectId
                ],

                [
                    '$set' => [

                        "status" =>
                            $newStatus,

                        "updated_at" =>
                            new MongoDB\BSON\UTCDateTime()

                    ]
                ]

            );


            $message =
                $newStatus === "blocked"
                ? "User blocked successfully."
                : "User unblocked successfully.";

            $messageType = "success";
        }


        /* =========================
           DELETE USER
        ========================= */

        elseif ($action === "delete") {

            if (
                (string)$objectId ===
                (string)$_SESSION["user_id"]
            ) {

                throw new Exception(
                    "You cannot delete your own admin account."
                );
            }


            $user = $users->findOne([
                "_id" => $objectId
            ]);


            if (!$user) {

                throw new Exception(
                    "User not found."
                );
            }


            if (
                ($user["role"] ?? "user") === "admin"
            ) {

                throw new Exception(
                    "Admin account cannot be deleted from here."
                );
            }


            $result =
                $users->deleteOne([
                    "_id" => $objectId
                ]);


            if (
                $result->getDeletedCount() > 0
            ) {

                $message =
                    "User deleted successfully.";

                $messageType =
                    "success";

            } else {

                throw new Exception(
                    "User could not be deleted."
                );
            }
        }

    } catch (Exception $e) {

        $message =
            $e->getMessage();

        $messageType =
            "error";
    }
}


/* =========================
   SEARCH
========================= */

$search =
    trim($_GET["search"] ?? "");


$filter = [

    "role" => [
        '$ne' => "admin"
    ]

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
   GET USERS
========================= */

$userList =
    $users->find(

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

    <title>
        MediSync - Manage Users
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

            max-width: 1250px;

            margin: 30px auto;
        }


        .box {

            background: white;

            padding: 25px;

            border-radius: 12px;

            box-shadow:
                0 0 12px rgba(0,0,0,0.08);

            margin-bottom: 25px;
        }


        /* =========================
           MESSAGE
        ========================= */

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


        /* =========================
           SEARCH
        ========================= */

        .search {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;
        }


        .search input {

            flex: 1;

            min-width: 250px;

            padding: 12px;

            border: 1px solid #ccc;

            border-radius: 6px;

            font-size: 15px;
        }


        button {

            border: none;

            padding: 10px 15px;

            border-radius: 6px;

            cursor: pointer;

            color: white;

            font-size: 14px;
        }


        .search-btn {

            background: #198754;
        }


        .reset {

            background: #6c757d;

            color: white;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 6px;
        }


        /* =========================
           USERS
        ========================= */

        .users {

            display: grid;

            grid-template-columns:
                repeat(auto-fit, minmax(320px, 1fr));

            gap: 20px;
        }


        .user-card {

            background: white;

            border: 1px solid #ddd;

            border-radius: 12px;

            padding: 22px;

            box-shadow:
                0 0 8px rgba(0,0,0,0.05);
        }


        .user-icon {

            width: 65px;

            height: 65px;

            border-radius: 50%;

            background: #d1e7dd;

            display: flex;

            justify-content: center;

            align-items: center;

            font-size: 32px;

            margin-bottom: 15px;
        }


        .user-card h3 {

            margin: 0 0 15px;

            color: #198754;
        }


        .info {

            margin: 9px 0;

            color: #555;

            line-height: 1.4;
        }


        .info strong {

            color: #333;
        }


        /* =========================
           STATUS
        ========================= */

        .status {

            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: bold;

            text-transform: capitalize;
        }


        .active {

            background: #d1e7dd;

            color: #0f5132;
        }


        .blocked {

            background: #f8d7da;

            color: #842029;
        }


        /* =========================
           ACTIONS
        ========================= */

        .actions {

            margin-top: 18px;

            padding-top: 15px;

            border-top: 1px solid #eee;

            display: flex;

            gap: 8px;

            flex-wrap: wrap;
        }


        .edit {

            background: #0d6efd;

            color: white;

            text-decoration: none;

            padding: 10px 15px;

            border-radius: 6px;

            display: inline-block;
        }


        .block {

            background: #ffc107;

            color: #212529;
        }


        .unblock {

            background: #198754;
        }


        .delete {

            background: #dc3545;
        }


        .empty {

            text-align: center;

            padding: 50px;

            color: #777;
        }


        @media(max-width: 650px) {

            .navbar {

                flex-direction: column;

                gap: 12px;

                text-align: center;
            }


            .navbar-links {

                justify-content: center;
            }


            .search {

                flex-direction: column;
            }


            .search input,
            .search button,
            .search a {

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


        <a href="users.php">
            Users
        </a>


        <a href="appointments.php">
            Appointments
        </a>


        <a
            href="../user/logout.php"
            class="logout"
        >
            Logout
        </a>

    </div>

</div>



<div class="container">


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

    <div class="box">

        <h2>
            👥 Manage Users
        </h2>


        <form
            method="GET"
            class="search"
        >

            <input
                type="text"
                name="search"
                value="<?= htmlspecialchars($search) ?>"
                placeholder="Search by name, email or mobile..."
            >


            <button
                type="submit"
                class="search-btn"
            >
                🔍 Search
            </button>


            <a
                href="users.php"
                class="reset"
            >
                Reset
            </a>

        </form>

    </div>



    <!-- =========================
         USER LIST
    ========================= -->

    <?php if (
        count($userList) === 0
    ): ?>


        <div class="box empty">

            <h3>
                No users found
            </h3>

            <p>
                No registered users match your search.
            </p>

        </div>


    <?php else: ?>


        <div class="users">


            <?php foreach (
                $userList
                as $user
            ): ?>


                <?php

                $userStatus =
                    $user["status"]
                    ?? "active";

                ?>


                <div class="user-card">


                    <div class="user-icon">
                        👤
                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $user["name"]
                            ?? "User"
                        ) ?>

                    </h3>


                    <div class="info">

                        📧

                        <strong>
                            Email:
                        </strong>

                        <?= htmlspecialchars(
                            $user["email"]
                            ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        📞

                        <strong>
                            Mobile:
                        </strong>

                        <?= htmlspecialchars(
                            $user["mobile"]
                            ?? "-"
                        ) ?>

                    </div>


                    <div class="info">

                        👤

                        <strong>
                            Role:
                        </strong>

                        <?= htmlspecialchars(
                            $user["role"]
                            ?? "user"
                        ) ?>

                    </div>


                    <div class="info">

                        📌

                        <strong>
                            Status:
                        </strong>


                        <span
                            class="status <?= htmlspecialchars(
                                $userStatus
                            ) ?>"
                        >

                            <?= htmlspecialchars(
                                $userStatus
                            ) ?>

                        </span>

                    </div>


                    <?php if (
                        isset($user["created_at"])
                    ): ?>

                        <div class="info">

                            📅

                            <strong>
                                Registered:
                            </strong>

                            <?php

                            try {

                                echo htmlspecialchars(
                                    $user["created_at"]
                                        ->toDateTime()
                                        ->format("d-m-Y")
                                );

                            } catch (Exception $e) {

                                echo "-";

                            }

                            ?>

                        </div>

                    <?php endif; ?>



                    <!-- =========================
                         ACTIONS
                    ========================= -->

                    <div class="actions">


                        <!-- EDIT -->

                        <a
                            href="edit_user.php?id=<?= urlencode(
                                (string)$user["_id"]
                            ) ?>"
                            class="edit"
                        >
                            ✏️ Edit
                        </a>



                        <!-- BLOCK / UNBLOCK -->

                        <form
                            method="POST"
                            style="display:inline;"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="toggle_status"
                            >


                            <input
                                type="hidden"
                                name="user_id"
                                value="<?= htmlspecialchars(
                                    (string)$user["_id"]
                                ) ?>"
                            >


                            <?php if (
                                $userStatus === "blocked"
                            ): ?>

                                <button
                                    type="submit"
                                    class="unblock"
                                >
                                    🟢 Unblock
                                </button>

                            <?php else: ?>

                                <button
                                    type="submit"
                                    class="block"
                                >
                                    🚫 Block
                                </button>

                            <?php endif; ?>

                        </form>



                        <!-- DELETE -->

                        <form
                            method="POST"
                            style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete this user?');"
                        >

                            <input
                                type="hidden"
                                name="action"
                                value="delete"
                            >


                            <input
                                type="hidden"
                                name="user_id"
                                value="<?= htmlspecialchars(
                                    (string)$user["_id"]
                                ) ?>"
                            >


                            <button
                                type="submit"
                                class="delete"
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