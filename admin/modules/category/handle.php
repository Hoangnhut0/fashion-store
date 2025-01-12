<?php
include('../../../config/connect.php');
session_start(); // Bắt đầu session để lưu thông báo

// Kiểm tra và gán giá trị đầu vào
$id = isset($_GET['idcategory']) ? mysqli_real_escape_string($conn, $_GET['idcategory']) : null;
$namecategory = isset($_POST['nameCategory']) ? mysqli_real_escape_string($conn, $_POST['nameCategory']) : null;
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : null;

if (isset($_POST['add'])) {
    // Xử lý thêm mới danh mục
    if ($namecategory && $description) {
        // Kiểm tra xem tên category đã tồn tại hay chưa
        $stmt_check = $conn->prepare("SELECT id FROM category WHERE nameCategory = ?");
        $stmt_check->bind_param("s", $namecategory);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Nếu tên category đã tồn tại, lưu thông báo lỗi vào session
            $_SESSION['error'] = "Category name already exists. Please choose a different name.";
            header('Location: ../../?action=category&query=add'); // Quay lại trang add.php
            exit;
        } else {
            // Nếu không tồn tại, thêm mới category
            $stmt = $conn->prepare("INSERT INTO category (nameCategory, description) VALUES (?, ?)");
            $stmt->bind_param("ss", $namecategory, $description);
            $stmt->execute();
            $_SESSION['success'] = "Category added successfully!";
            header('Location: ../../?action=category&query=list'); // Điều hướng về trang danh sách
            exit;
        }
        $stmt_check->close();
    } else {
        $_SESSION['error'] = "Missing category name or description.";
        header('Location: ../../?action=category&query=add'); // Quay lại trang add.php
        exit;
    }
} elseif (isset($_POST['update'])) {
    // Xử lý cập nhật danh mục
    if ($id && $namecategory && $description) {
        // Kiểm tra xem tên category có trùng với danh mục khác hay không
        $stmt_check = $conn->prepare("SELECT id FROM category WHERE nameCategory = ? AND id != ?");
        $stmt_check->bind_param("si", $namecategory, $id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Nếu tên category đã tồn tại với ID khác, lưu thông báo lỗi vào session
            $_SESSION['error'] = "Category name already exists. Please choose a different name.";
            header('Location: ../../?action=category&query=add'); // Quay lại trang add.php
            exit;
        } else {
            // Nếu không tồn tại, cập nhật category
            $stmt = $conn->prepare("UPDATE category SET nameCategory = ?, description = ? WHERE id = ?");
            $stmt->bind_param("ssi", $namecategory, $description, $id);
            $stmt->execute();
            $_SESSION['success'] = "Category updated successfully!";
            header('Location: ../../?action=category&query=list'); // Điều hướng về trang danh sách
            exit;
        }
        $stmt_check->close();
    } else {
        $_SESSION['error'] = "Missing category ID, name, or description.";
        header('Location: ../../?action=category&query=add'); // Quay lại trang add.php
        exit;
    }
} elseif (isset($_GET['delete']) && $id) {
    mysqli_begin_transaction($conn); // Bắt đầu transaction
    try {
        // Kiểm tra xem category có được tham chiếu trong bảng products không
        $stmt_check_products = $conn->prepare("SELECT COUNT(*) AS product_count FROM products WHERE id_category = ?");
        $stmt_check_products->bind_param("i", $id);
        $stmt_check_products->execute();
        $result = $stmt_check_products->get_result();
        $row = $result->fetch_assoc();

        if ($row['product_count'] > 0) {
            // Nếu có sản phẩm tham chiếu đến category, hiển thị lỗi
            $_SESSION['error'] = "Cannot delete this category because it is referenced in the products table!";
            header('Location: ../../?action=category&query=list'); // Quay lại trang danh sách
            exit;
        }

        // Nếu không có tham chiếu, tiến hành xóa dữ liệu liên quan trong bảng category_brand
        $stmt_delete_category_brand = $conn->prepare("DELETE FROM category_brand WHERE category_id = ?");
        $stmt_delete_category_brand->bind_param("i", $id);
        $stmt_delete_category_brand->execute();

        // Xóa danh mục trong bảng category
        $stmt_delete_category = $conn->prepare("DELETE FROM category WHERE id = ?");
        $stmt_delete_category->bind_param("i", $id);
        $stmt_delete_category->execute();

        // Hoàn tất transaction
        mysqli_commit($conn);

        $_SESSION['success'] = "Category and related entries deleted successfully!";
        header('Location: ../../?action=category&query=list'); // Điều hướng về trang danh sách
        exit;
    } catch (Exception $e) {
        // Nếu xảy ra lỗi, rollback transaction
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error occurred: " . $e->getMessage();
        header('Location: ../../?action=category&query=list'); // Quay lại trang danh sách
        exit;
    }
}

?>
