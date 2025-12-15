<?php
session_start(); // start session to check login
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Learning Companion</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50">

    <header class="bg-blue-600 text-white shadow">
        <div class="max-w-7xl mx-auto flex justify-between items-center p-4">

            <!-- Logo -->
            <div class="flex items-center space-x-3">
                <img src="https://upload.wikimedia.org/wikipedia/commons/4/48/Markdown-mark.svg" alt="Logo" class="w-10 h-10">
                <span class="text-2xl font-bold">AI Learning Companion</span>
            </div>

            <!-- Navigation -->
            <nav class="space-x-6 hidden md:flex">
                <a href="index.php" class="hover:underline">Home</a>

                <!-- Generate Link -->
                <a href="<?php echo isset($_SESSION['user_id']) ? 'generator.php' : 'login.php'; ?>" class="hover:underline">Generate</a>

                <!-- Saved Link -->
                <a href="<?php echo isset($_SESSION['user_id']) ? 'saved.php' : 'login.php'; ?>" class="hover:underline">Saved</a>

                <a href="about.php" class="hover:underline">About</a>
                <a href="contact.php" class="hover:underline">Contact</a>
            </nav>

            <!-- Auth Buttons -->
            <div class="space-x-3">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="logout.php" class="bg-white text-blue-600 px-4 py-2 rounded font-semibold hover:bg-gray-200">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="bg-white text-blue-600 px-4 py-2 rounded font-semibold hover:bg-gray-200">Login</a>
                    <a href="register.php" class="bg-yellow-400 text-black px-4 py-2 rounded font-semibold hover:bg-yellow-300">Register</a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
                <button id="menu-btn" class="focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden bg-blue-500 md:hidden">
            <a href="index.php" class="block px-4 py-2 hover:bg-blue-400">Home</a>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'generator.php' : 'login.php'; ?>" class="block px-4 py-2 hover:bg-blue-400">Generate</a>
            <a href="<?php echo isset($_SESSION['user_id']) ? 'saved.php' : 'login.php'; ?>" class="block px-4 py-2 hover:bg-blue-400">Saved</a>
            <a href="about.php" class="block px-4 py-2 hover:bg-blue-400">About</a>
            <a href="contact.php" class="block px-4 py-2 hover:bg-blue-400">Contact</a>
        </div>
    </header>

    <script>
        const btn = document.getElementById('menu-btn');
        const menu = document.getElementById('mobile-menu');

        btn.addEventListener('click', () => {
            menu.classList.toggle('hidden');
        });
    </script>