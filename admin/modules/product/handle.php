<?php
// Database connection
include('../../../config/connect.php');
session_start();

try {
    // Handle product deletion
    if (isset($_GET['delete']) && $_GET['delete'] === 'true' && isset($_GET['idproduct'])) {
        $productID = intval($_GET['idproduct']);

        // Start transaction
        mysqli_begin_transaction($conn);

        // Delete main product image from filesystem
        $imageQuery = "SELECT image FROM products WHERE id = ?";
        $imageStmt = mysqli_prepare($conn, $imageQuery);
        mysqli_stmt_bind_param($imageStmt, 'i', $productID);
        mysqli_stmt_execute($imageStmt);
        $result = mysqli_stmt_get_result($imageStmt);
        $product = mysqli_fetch_assoc($result);

        if ($product && !empty($product['image'])) {
            $imagePath = 'img/' . $product['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        // Delete additional images
        $deleteImagesQuery = "SELECT image_url FROM product_images WHERE product_id = ?";
        $stmtImages = mysqli_prepare($conn, $deleteImagesQuery);
        mysqli_stmt_bind_param($stmtImages, 'i', $productID);
        mysqli_stmt_execute($stmtImages);
        $imagesResult = mysqli_stmt_get_result($stmtImages);

        while ($image = mysqli_fetch_assoc($imagesResult)) {
            $imagePath = 'img/' . $image['image_url'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $deleteImagesQuery = "DELETE FROM product_images WHERE product_id = ?";
        $stmtImagesDelete = mysqli_prepare($conn, $deleteImagesQuery);
        mysqli_stmt_bind_param($stmtImagesDelete, 'i', $productID);
        mysqli_stmt_execute($stmtImagesDelete);

        // Delete stock sizes and the product
        $deleteSizesQuery = "DELETE FROM product_sizes WHERE product_id = ?";
        $stmtSizes = mysqli_prepare($conn, $deleteSizesQuery);
        mysqli_stmt_bind_param($stmtSizes, 'i', $productID);
        mysqli_stmt_execute($stmtSizes);

        $deleteProductQuery = "DELETE FROM products WHERE id = ?";
        $stmtProduct = mysqli_prepare($conn, $deleteProductQuery);
        mysqli_stmt_bind_param($stmtProduct, 'i', $productID);
        mysqli_stmt_execute($stmtProduct);

        mysqli_commit($conn);
        $_SESSION['success'] = 'Product deleted successfully.';
    }

    // Handle adding product
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addProduct'])) {
        $nameProduct = mysqli_real_escape_string($conn, $_POST['nameProduct']);
        $category_id = intval($_POST['category_id']);
        $brand_id = intval($_POST['brand_id']);
        $price = floatval($_POST['price']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $stock = $_POST['stock'];

        // Upload main image
        $imageName = uploadImage('image', 'img/');

        if ($imageName) {
            $productQuery = "INSERT INTO products (nameProduct, image, price, id_brand, id_category, description) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $productQuery);
            mysqli_stmt_bind_param($stmt, 'ssdiis', $nameProduct, $imageName, $price, $brand_id, $category_id, $description);
            
            if (mysqli_stmt_execute($stmt)) {
                $product_id = mysqli_insert_id($conn);

                foreach ($stock as $size_id => $quantity) {
                    $quantity = intval($quantity);
                    $stockQuery = "INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)";
                    $stockStmt = mysqli_prepare($conn, $stockQuery);
                    mysqli_stmt_bind_param($stockStmt, 'iii', $product_id, $size_id, $quantity);
                    mysqli_stmt_execute($stockStmt);
                }

                uploadAdditionalImages($_FILES['images'], $product_id, 'img/');
                $_SESSION['success'] = 'Product added successfully.';
            } else {
                throw new Exception('Failed to add product.');
            }
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    // Handle editing product
    elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editProduct'])) {
        $productID = intval($_POST['productID']);
        $nameProduct = mysqli_real_escape_string($conn, $_POST['nameProduct']);
        $category_id = intval($_POST['category_id']);
        $brand_id = intval($_POST['brand_id']);
        $price = floatval($_POST['price']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $stock = $_POST['stock'];

        mysqli_begin_transaction($conn);

        $updateProductQuery = "UPDATE products SET nameProduct = ?, price = ?, id_brand = ?, id_category = ?, description = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $updateProductQuery);
        mysqli_stmt_bind_param($stmt, 'sdiisi', $nameProduct, $price, $brand_id, $category_id, $description, $productID);
        mysqli_stmt_execute($stmt);

        foreach ($stock as $size_id => $quantity) {
            $quantity = intval($quantity);
            $checkStockQuery = "SELECT * FROM product_sizes WHERE product_id = ? AND size_id = ?";
            $checkStmt = mysqli_prepare($conn, $checkStockQuery);
            mysqli_stmt_bind_param($checkStmt, 'ii', $productID, $size_id);
            mysqli_stmt_execute($checkStmt);
            $result = mysqli_stmt_get_result($checkStmt);

            if (mysqli_num_rows($result) > 0) {
                $updateStockQuery = "UPDATE product_sizes SET stock = ? WHERE product_id = ? AND size_id = ?";
                $updateStmt = mysqli_prepare($conn, $updateStockQuery);
                mysqli_stmt_bind_param($updateStmt, 'iii', $quantity, $productID, $size_id);
                mysqli_stmt_execute($updateStmt);
            } else {
                $insertStockQuery = "INSERT INTO product_sizes (product_id, size_id, stock) VALUES (?, ?, ?)";
                $insertStmt = mysqli_prepare($conn, $insertStockQuery);
                mysqli_stmt_bind_param($insertStmt, 'iii', $productID, $size_id, $quantity);
                mysqli_stmt_execute($insertStmt);
            }
        }

        if (!empty($_FILES['image']['name'])) {
            $imageName = uploadImage('image', 'img/');

            // Delete old image
            $oldImageQuery = "SELECT image FROM products WHERE id = ?";
            $oldImageStmt = mysqli_prepare($conn, $oldImageQuery);
            mysqli_stmt_bind_param($oldImageStmt, 'i', $productID);
            mysqli_stmt_execute($oldImageStmt);
            $oldImageResult = mysqli_stmt_get_result($oldImageStmt);
            $oldImage = mysqli_fetch_assoc($oldImageResult);

            if ($oldImage && !empty($oldImage['image'])) {
                $oldImagePath = 'img/' . $oldImage['image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $updateImageQuery = "UPDATE products SET image = ? WHERE id = ?";
            $imageStmt = mysqli_prepare($conn, $updateImageQuery);
            mysqli_stmt_bind_param($imageStmt, 'si', $imageName, $productID);
            mysqli_stmt_execute($imageStmt);
        }

        if (isset($_FILES['images']) && $_FILES['images']['error'][0] === UPLOAD_ERR_OK) {
            // Fetch old images from product_images
            $fetchOldImagesQuery = "SELECT image_url FROM product_images WHERE product_id = ?";
            $fetchOldStmt = mysqli_prepare($conn, $fetchOldImagesQuery);
            mysqli_stmt_bind_param($fetchOldStmt, 'i', $productID);
            mysqli_stmt_execute($fetchOldStmt);
            $oldImagesResult = mysqli_stmt_get_result($fetchOldStmt);

            // Delete old images from filesystem and database
            while ($oldImage = mysqli_fetch_assoc($oldImagesResult)) {
                $oldImagePath = 'img/' . $oldImage['image_url'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $deleteOldImagesQuery = "DELETE FROM product_images WHERE product_id = ?";
            $deleteOldStmt = mysqli_prepare($conn, $deleteOldImagesQuery);
            mysqli_stmt_bind_param($deleteOldStmt, 'i', $productID);
            mysqli_stmt_execute($deleteOldStmt);

            // Upload new images
            uploadAdditionalImages($_FILES['images'], $productID, 'img/');
        }

        mysqli_commit($conn);
        $_SESSION['success'] = 'Product updated successfully.';
    }
} catch (Exception $e) {
    if (isset($conn)) {
        mysqli_rollback($conn);
    }
    $_SESSION['error'] = $e->getMessage();
}

header('Location: ../../index.php?action=product&query=list');
exit;

function uploadImage($fileKey, $uploadDir) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $imageTmpPath = $_FILES[$fileKey]['tmp_name'];
        $imageName = basename($_FILES[$fileKey]['name']);
        $imagePath = $uploadDir . $imageName;
        if (move_uploaded_file($imageTmpPath, $imagePath)) {
            return $imageName;
        }
    }
    return null;
}

function uploadAdditionalImages($files, $product_id, $uploadDir) {
    if (isset($files['name'][0]) && $files['error'][0] === UPLOAD_ERR_OK) {
        $totalFiles = count($files['name']);
        for ($i = 0; $i < $totalFiles; $i++) {
            $imageTmpPath = $files['tmp_name'][$i];
            $imageName = basename($files['name'][$i]);
            $imagePath = $uploadDir . $imageName;

            if (move_uploaded_file($imageTmpPath, $imagePath)) {
                global $conn;
                $insertImageQuery = "INSERT INTO product_images (product_id, image_url) VALUES (?, ?)";
                $stmt = mysqli_prepare($conn, $insertImageQuery);
                mysqli_stmt_bind_param($stmt, 'is', $product_id, $imageName);
                mysqli_stmt_execute($stmt);
            }
        }
    }
}
