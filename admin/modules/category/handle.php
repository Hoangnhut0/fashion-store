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
    // Xử lý xóa danh mục
    $stmt = $conn->prepare("DELETE FROM category WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $_SESSION['success'] = "Category deleted successfully!";
    header('Location: ../../?action=category&query=list');
    exit;
} else {
    $_SESSION['error'] = "Invalid request.";
    header('Location: ../../?action=category&query=add'); // Quay lại trang add.php
    exit;
}
?>
