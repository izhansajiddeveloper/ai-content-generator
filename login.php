<?php

include 'includes/db.php';
include 'includes/header.php';

$message = '';

if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: generator.php');
        exit;
    } else {
        $message = "Invalid email or password!";
    }
}
?>

<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-center">Login</h2>

    <?php if($message): ?>
        <p class="text-red-500 mb-4"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label class="block mb-2">Email</label>
        <input type="email" name="email" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Password</label>
        <input type="password" name="password" required class="w-full mb-4 p-2 border rounded">

        <button type="submit" name="login" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
    </form>

    <p class="mt-4 text-center text-sm">Don't have an account? <a href="register.php" class="text-blue-200 hover:underline">Register here</a></p>
</div>

<?php
include 'includes/footer.php';
?>
