<?php

require '../pageFormat/base.php';
require '../pageFormat/head.php';

if (!isset($_SESSION['user_id'])) {
    temp('info', 'You need to log in to access the profile page.');
    redirect('login.php');
    exit();
}

if (is_post()) {
    $phone_number = $_POST['phone_number'] ?? null;

    $stmt = $_db->prepare("UPDATE user SET phone_number = ? WHERE user_id = ?");
    $stmt->execute([$phone_number, $_SESSION['user_id']]);

    temp('info', 'Profile updated successfully.');
    header('Location: profile.php');
    exit();
}

// Retrieve current user data
$stmt = $_db->prepare("SELECT * FROM user WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    temp('info', 'User not found. Please log in again.');
    header('Location: login.php');
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
</head>

<body>
    <header>
        <h1>Edit Profile</h1>
    </header>
    <main>
        <form method="post">
            <label for="phone_number">Phone Number:</label><br>
            <input type="text" name="phone_number" id="phone_number" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>" maxlength="30"><br><br>
            <button type="submit">Update Profile</button>
        </form>
    </main>
</body>

</html>