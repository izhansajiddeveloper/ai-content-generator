<?php
include 'includes/db.php';
include 'includes/header.php';

$message = '';

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        $message = "Email already registered!";
    } else {
        // Insert user
        $insert = mysqli_query($conn, "INSERT INTO users (name, email, password) VALUES ('$name', '$email', '$password')");
        if ($insert) {
            header('Location: login.php');
            exit;
        } else {
            $message = "Registration failed. Try again!";
        }
    }
}
?>

<div class="max-w-md mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4 text-center">Register</h2>

    <?php if ($message): ?>
        <p class="text-red-500 mb-4"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label class="block mb-2">Name</label>
        <input type="text" name="name" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Email</label>
        <input type="email" name="email" required class="w-full mb-4 p-2 border rounded">

        <label class="block mb-2">Password</label>
        <input type="password" name="password" required class="w-full mb-4 p-2 border rounded">

        <button type="submit" name="register" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
    </form>

    <p class="mt-4 text-center text-sm">Already have an account? <a href="login.php" class="text-blue-200 hover:underline">Login here</a></p>
</div>

<?php
include 'includes/footer.php';
?>