<?php
$messages = file_exists('messages.txt') ? file('messages.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    file_put_contents('messages.txt', $_POST['message'] . PHP_EOL, FILE_APPEND);
    header('Location: /');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head><title>PHP Guestbook</title></head>
<body>
    <h1>Guestbook</h1>
    <form method="POST">
        <input type="text" name="message" placeholder="Enter your message" required>
        <button type="submit">Submit</button>
    </form>
    <h2>Messages:</h2>
    <ul>
        <?php foreach ($messages as $msg) { echo "<li>" . htmlspecialchars($msg) . "</li>"; } ?>
    </ul>
</body>
</html>