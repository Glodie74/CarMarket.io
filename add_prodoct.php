<?php
// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database connection
include('includes/db.php');

// Ensure the user is logged in as admin
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Handle product addition form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $title = htmlspecialchars(trim($_POST['title']));
    $description = htmlspecialchars(trim($_POST['description']));
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $category = htmlspecialchars(trim($_POST['category']));
    $image = $_FILES['image']['name'];

    // Validate inputs
    if (empty($title) || empty($description) || empty($price) || empty($category) || empty($image)) {
        echo "<p style='color:red;'>Please fill in all fields and upload an image.</p>";
    } else {
        // Handle image upload
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($image);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png'];

        if (!in_array($imageFileType, $allowed_types)) {
            echo "<p style='color:red;'>Invalid image type. Only JPG, JPEG, and PNG are allowed.</p>";
        } elseif (!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            echo "<p style='color:red;'>Failed to upload image. Please try again.</p>";
        } else {
            // Insert product into the database
            $stmt = $conn->prepare("INSERT INTO products (title, description, price, category, image) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdss", $title, $description, $price, $category, $target_file);

            if ($stmt->execute()) {
                echo "<p style='color:green;'>Product added successfully!</p>";
            } else {
                echo "<p style='color:red;'>Failed to add product. Please try again.</p>";
            }

            $stmt->close();
        }
    }
}
?>

<h2>Add Product</h2>
<form method="POST" enctype="multipart/form-data">
    <label for="title">Title:</label>
    <input id="title" type="text" name="title" required><br><br>
    <label for="description">Description:</label>
    <textarea id="description" name="description" required></textarea><br><br>
    <label for="price">Price:</label>
    <input id="price" type="number" step="0.01" name="price" required><br><br>
    <label for="category">Category:</label>
    <input id="category" type="text" name="category" required><br><br>
    <label for="image">Image:</label>
    <input id="image" type="file" name="image" required><br><br>
    <input type="submit" value="Add Product">
</form>