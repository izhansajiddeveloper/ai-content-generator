<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    mysqli_query($conn, "DELETE FROM outputs WHERE id='$id' AND user_id='{$_SESSION['user_id']}'");
    header('Location: saved.php');
    exit;
}

// Fetch saved outputs for this user
$result = mysqli_query($conn, "SELECT o.*, c.category_name, l.level_name 
    FROM outputs o
    JOIN categories c ON o.category_id = c.id
    JOIN difficulty_levels l ON o.difficulty_id = l.id
    WHERE o.user_id='{$_SESSION['user_id']}' ORDER BY o.id DESC");
?>

<div class="max-w-4xl mx-auto mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center">Saved Outputs</h2>

    <?php if(mysqli_num_rows($result) > 0): ?>
        <div class="space-y-4">
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="p-4 bg-white rounded shadow">
                    <h3 class="text-lg font-semibold mb-1"><?php echo $row['output_type']; ?>: <?php echo $row['topic']; ?></h3>
                    <p class="text-gray-600 mb-2"><strong>Category:</strong> <?php echo $row['category_name']; ?> | <strong>Difficulty:</strong> <?php echo $row['level_name']; ?></p>
                    <div class="mb-2 p-2 bg-gray-100 rounded"><?php echo $row['content']; ?></div>
                    <div class="flex space-x-3">
                        <a href="saved.php?delete=<?php echo $row['id']; ?>" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Delete</a>
                        <button onclick="copyText('<?php echo addslashes($row['content']); ?>')" class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">Copy</button>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-center text-gray-500">No saved outputs yet.</p>
    <?php endif; ?>
</div>

<script>
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Copied to clipboard!');
    });
}
</script>

<?php
include 'includes/footer.php';
?>
