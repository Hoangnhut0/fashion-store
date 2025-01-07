<?php
// Kiểm tra và lấy thông tin danh mục
if (isset($_GET['idcategory']) && is_numeric($_GET['idcategory'])) {
    $id = mysqli_real_escape_string($conn, $_GET['idcategory']);
    $sql = "SELECT * FROM category WHERE id = '$id'";
    $query = mysqli_query($conn, $sql);

    if (mysqli_num_rows($query) == 0) {
        echo "<p>Category not found.</p>";
        exit;
    }
} else {
    echo "<p>Invalid category ID.</p>";
    exit;
}
?>
<div class="row">
    <div class="col-xl">
        <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                unset($_SESSION['error']); // Xóa thông báo sau khi hiển thị
            }

            if (isset($_SESSION['success'])) {
                echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
                unset($_SESSION['success']); // Xóa thông báo sau khi hiển thị
            }
        ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Category</h5>
                <small class="text-muted float-end">Update category details</small>
            </div>
            <div class="card-body">
                <form action="modules/category/handle.php?idcategory=<?php echo $id; ?>" method="POST" enctype="multipart/form-data">
                    <?php while ($row = mysqli_fetch_assoc($query)) { ?>
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Category Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="basic-default-fullname" 
                            name="nameCategory" 
                            value="<?php echo htmlspecialchars($row['nameCategory']); ?>" 
                            required
                        />
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea 
                            class="form-control" 
                            name="description" 
                            required><?php echo htmlspecialchars($row['description']); ?></textarea>
                    </div>
                    <button 
                        type="submit" 
                        onclick="return confirm('Are you sure you want to update this category?');" 
                        class="btn btn-primary text-white" 
                        name="update">
                        Update
                    </button>

                    <?php } ?>
                </form>
            </div>
        </div>
    </div>            
</div>
