<?php

require_once "../config/db.php";

$message = "";

$users = $db->users;

$adminEmail = "admin@medisync.com";

$existingAdmin = $users->findOne([
    "email" => $adminEmail
]);

if ($existingAdmin) {

    $message = "Admin account already exists.";

} else {

    $password = "Admin@123";

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $users->insertOne([
        "name" => "MediSync Admin",
        "email" => $adminEmail,
        "mobile" => "9999999999",
        "password" => $hashedPassword,
        "role" => "admin",
        "mobile_verified" => true,
        "created_at" => new MongoDB\BSON\UTCDateTime()
    ]);

    $message = "Admin account created successfully.";
}

?>

<!DOCTYPE html>
<html>

<head>

    <title>MediSync - Admin Setup</title>

</head>

<body>

    <h2><?= htmlspecialchars($message) ?></h2>

    <p>
        Admin Email:
        <strong>admin@medisync.com</strong>
    </p>

    <p>
        Admin Password:
        <strong>Admin@123</strong>
    </p>

    <p>
        After creating the admin, delete
        <strong>create_admin.php</strong>
        for security.
    </p>

</body>

</html>