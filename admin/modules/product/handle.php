<?php
// Database connection
include('../../../config/connect.php');
session_start();

// Handle product deletion
if (isset($_GET['delete']) && $_GET['delete'] === 'true' && isset($_GET['idproduct'])) {
    $productID = intval($_GET['idproduct']);

    // Start transaction
    mysqli_begin_transaction($conn);

    try {
        // Fetch product image to delete from the filesystem
        $imageQuery = "SELECT image FROM products WHERE id = ?";
        $imageStmt = mysqli_prepare($conn, $imageQuery);
        mysqli_stmt_bind_param($imageStmt, 'i', $productID);
        mysqli_stmt_execute($imageStmt);
        $result = mysqli_stmt_get_result($imageStmt);
        $product = mysqli_fetch_assoc($result);

        if ($product && !empty($product['image'])) {
            $imagePath = 'img/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath); // Delete the image file
            }
        }

        // Delete stock sizes related to the product
        $deleteSizesQuery = "DELETE FROM product_sizes WHERE product_id = ?";
        $stmtSizes = mysqli_prepare($conn, $deleteSizesQuery);
        mysqli_stmt_bind_param($stmtSizes, 'i', $productID);
        mysqli_stmt_execute($stmtSizes);

        // Delete the product
        $deleteProductQuery = "DELETE FROM products WHERE id = ?";
        $stmtProduct = mysqli_prepare($conn, $deleteProductQuery);
        mysqli_stmt_bind_param($stmtProduct, 'i', $productID);
        mysqli_stmt_execute($stmtProduct);

        // Commit transaction
        mysqli_commit($conn);
        $_SESSION['success'] = 'Product deleted successfully.';
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error'] = 'Failed to delete product. Please try again.';
    }

    // Redirect back to the product list
    header('Location: ../../index.php?action=product&query=list');
    exit;
}

// Handle other POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addProduct'])) {
    // Fetch form data
    $nameProduct = mysqli_real_escape_string($conn, $_POST['nameProduct']);
    $category_id = intval($_POST['category_id']);
    $brand_id = intval($_POST['brand_id']);
    $price = floatval($_POST['price']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $stock = $_POST['stock']; // Array for size-specific stock

    // Handle product image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES['image']['tmp_name'];
        $imageName = basename($_FILES['image']['name']);
        $uploadDir = 'img/';
        $imagePath = $uploadDir . $imageName;

        if (move_uploaded_file($imageTmpPath, $imagePath)) {
            // Insert product data
            $productQuery = "INSERT INTO products (nameProduct, image, price, id_brand, id_category, description) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $productQuery);
            mysqli_stmt_bind_param($stmt, 'ssdiis', $nameProduct, $imageName, $price, $brand_id, $category_id, $description);

            if (mysqli_stmt_execute($stmt)) {
                $product_id = mysqli_insert_id($conn);

                // Insert size-specific stock data
                foreach ($stock as $size_id => $quantity) {
                    $quantity = intval($quantity);
                    $stockQuery = "INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)";
                    $stockStmt = mysqli_prepare($conn, $stockQuery);
                    mysqli_stmt_bind_param($stockStmt, 'iii', $product_id, $size_id, $quantity);
                    mysqli_stmt_execute($stockStmt);
                }

                $_SESSION['success'] = 'Product added successfully.';
            } else {
                $_SESSION['error'] = 'Failed to add product. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'Failed to upload image. Please try again.';
        }
    } else {
        $_SESSION['error'] = 'Please upload a valid image.';
    }

    // Redirect back to the form
    header('Location: ../../index.php?action=product&query=list');
    exit;
} else {
    $_SESSION['error'] = 'Invalid request.';
    header('Location: ../../index.php?action=product&query=add');
    exit;
}
