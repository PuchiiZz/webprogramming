<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include database connection
    include 'db_connection.php';

    // Get form data
    $code = $_POST['code'];
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];

    // Validate and sanitize inputs
    $code = htmlspecialchars(trim($code));
    $name = htmlspecialchars(trim($name));
    $category = htmlspecialchars(trim($category));
    $price = (float) $price;

    // Handle file upload if an image is provided
    $target_dir = "uploads/";
    $image_url = '';
    if (!empty($_FILES['product_image']['name'])) {
        $image = $_FILES['product_image'];
        $image_extension = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));

        if (in_array($image_extension, ['jpg', 'jpeg', 'png']) && $image['size'] <= 5 * 1024 * 1024) {
            $image_name = uniqid() . '.' . $image_extension;
            $image_url = $target_dir . $image_name;
            move_uploaded_file($image['tmp_name'], $image_url);
        }
    }
}