<?php
session_start();

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_input = strtoupper($_POST["captcha"]);

    if ($user_input == $_SESSION["captcha_code"]) {
        $message = "<p style='color:green;'>Success! Correct Captcha Code.</p>";
    } else {
        $message = "<p style='color:red;'>Error! Wrong Captcha Code.</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>PHP Verification Captcha</title>
</head>
<body>

<h2>PHP Verification captcha</h2>

<?php echo $message; ?>

<form method="post">

    <p>
        <img src="captcha.php?rand=<?php echo rand(); ?>">
    </p>

    <input type="text" name="captcha" placeholder="Captcha code" required>
    <br><br>
    <button type="submit">Submit</button>

</form>

</body>
</html>
