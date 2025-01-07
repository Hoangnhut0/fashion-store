<?php
include('../../../config/connect.php');
session_start();

$nameBrand = isset($_POST['nameBrand']) ? mysqli_real_escape_string($conn, $_POST['nameBrand']) : null;
$description = isset($_POST['description']) ? mysqli_real_escape_string($conn, $_POST['description']) : null;
$categories = isset($_POST['categories']) ? $_POST['categories'] : [];

if (isset($_POST['add'])) {
    // Thêm brand
    if (empty($nameBrand)) {
        $_SESSION['error'] = "Brand Name is required!";
        header("Location: ../../?action=brand&query=add");
        exit;
    }

    if (empty($categories)) {
        $_SESSION['error'] = "Please select at least one category!";
        header("Location: ../../?action=brand&query=add");
        exit;
    }

    $sql_check_brand = "SELECT * FROM brands WHERE nameBrand = '$nameBrand'";
    $result_check = mysqli_query($conn, $sql_check_brand);
    if (mysqli_num_rows($result_check) > 0) {
        $_SESSION['error'] = "Brand Name already exists!";
        header("Location: ../../?action=brand&query=add");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $sql_insert_brand = "INSERT INTO brands (nameBrand, description) VALUES ('$nameBrand', '$description')";
        mysqli_query($conn, $sql_insert_brand);

        $brandId = mysqli_insert_id($conn);
        foreach ($categories as $categoryId) {
            $categoryId = (int)$categoryId;
            $sql_insert_brand_category = "INSERT INTO category_brand (category_id, brand_id) VALUES ($categoryId, $brandId)";
            mysqli_query($conn, $sql_insert_brand_category);
        }

        mysqli_commit($conn);
        $_SESSION['success'] = "Brand and categories added successfully!";
        header("Location: ../../?action=brand&query=list");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../../?action=brand&query=add");
        exit;
    }
} elseif (isset($_POST['update'])) {
    // Sửa brand
    $idbrand = isset($_POST['idbrand']) ? (int)$_POST['idbrand'] : null;

    if (empty($idbrand) || empty($nameBrand)) {
        $_SESSION['error'] = "Brand ID and Name are required!";
        header("Location: ../../?action=brand&query=edit&idbrand=$idbrand");
        exit;
    }

    if (empty($categories)) {
        $_SESSION['error'] = "Please select at least one category!";
        header("Location: ../../?action=brand&query=edit&idbrand=$idbrand");
        exit;
    }

    $sql_check_brand = "SELECT * FROM brands WHERE nameBrand = '$nameBrand' AND id != $idbrand";
    $result_check = mysqli_query($conn, $sql_check_brand);
    if (mysqli_num_rows($result_check) > 0) {
        $_SESSION['error'] = "Brand Name already exists!";
        header("Location: ../../?action=brand&query=edit&idbrand=$idbrand");
        exit;
    }

    mysqli_begin_transaction($conn);
    try {
        $sql_update_brand = "UPDATE brands SET nameBrand = '$nameBrand', description = '$description' WHERE id = $idbrand";
        mysqli_query($conn, $sql_update_brand);

        $sql_delete_category_brand = "DELETE FROM category_brand WHERE brand_id = $idbrand";
        mysqli_query($conn, $sql_delete_category_brand);

        foreach ($categories as $categoryId) {
            $categoryId = (int)$categoryId;
            $sql_insert_brand_category = "INSERT INTO category_brand (category_id, brand_id) VALUES ($categoryId, $idbrand)";
            mysqli_query($conn, $sql_insert_brand_category);
        }

        mysqli_commit($conn);
        $_SESSION['success'] = "Brand updated successfully!";
        header("Location: ../../?action=brand&query=list");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../../?action=brand&query=edit&idbrand=$idbrand");
        exit;
    }
} elseif (isset($_GET['delete']) && isset($_GET['idbrand'])) {
    // Xóa brand
    $idbrand = (int)$_GET['idbrand'];

    mysqli_begin_transaction($conn);
    try {
        $sql_delete_category_brand = "DELETE FROM category_brand WHERE brand_id = $idbrand";
        mysqli_query($conn, $sql_delete_category_brand);

        $sql_delete_brand = "DELETE FROM brands WHERE id = $idbrand";
        mysqli_query($conn, $sql_delete_brand);

        mysqli_commit($conn);
        $_SESSION['success'] = "Brand deleted successfully!";
        header("Location: ../../?action=brand&query=list");
        exit;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: ../../?action=brand&query=list");
        exit;
    }
} else {
    // Request không hợp lệ
    $_SESSION['error'] = "Invalid request.";
    header("Location: ../../?action=brand&query=list");
    exit;
}
?>
