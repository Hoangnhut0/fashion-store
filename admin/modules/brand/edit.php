<?php
// Lấy thông tin brand cần chỉnh sửa
$sql_brand = "SELECT * FROM brands WHERE id = '$_GET[idbrand]'";
$query_brand = mysqli_query($conn, $sql_brand);
$brand = mysqli_fetch_assoc($query_brand);

// Lấy danh sách category
$sql = "SELECT * FROM category";
$query = mysqli_query($conn, $sql);
$categories = [];
while ($row = mysqli_fetch_assoc($query)) {
    $categories[] = $row;
}

// Lấy danh sách các category đã liên kết với brand
$sql_selected_categories = "SELECT category_id FROM category_brand WHERE brand_id = '$_GET[idbrand]'";
$query_selected_categories = mysqli_query($conn, $sql_selected_categories);
$selected_categories = [];
while ($row = mysqli_fetch_assoc($query_selected_categories)) {
    $selected_categories[] = $row['category_id'];
}
?>
<div class="row">
    <div class="col-xl">
    <?php
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
            unset($_SESSION['success']); 
        }

        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
            unset($_SESSION['error']); 
        }
    ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Brand</h5>
                <small class="text-muted float-end">Update the brand information</small>
            </div>
            <div class="card-body">
                <form action="modules/brand/handle.php?idbrand=<?php echo htmlspecialchars($_GET['idbrand']); ?>" method="POST" enctype="multipart/form-data">
                    <!-- Hidden field để gửi ID brand -->
                    <input type="hidden" name="idbrand" value="<?php echo htmlspecialchars($brand['id']); ?>" />

                    <!-- Brand Name -->
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Brand Name</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="basic-default-fullname" 
                            name="nameBrand" 
                            value="<?php echo htmlspecialchars($brand['nameBrand']); ?>" 
                            placeholder="Brand Name" 
                        />
                    </div>
                    
                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-message">Description</label>
                        <textarea 
                            id="basic-default-message"
                            class="form-control"
                            placeholder="Enter description"
                            name="description"><?php echo htmlspecialchars($brand['description']); ?></textarea>
                    </div>

                    <!-- Categories -->
                    <div class="mt-2 mb-3">
                        <label class="form-label">Categories</label>
                        <div class="row">
                            <?php 
                            $max_rows = 5; // Tối đa 5 dòng mỗi cột
                            $max_columns = 4; // Tối đa 4 cột
                            $total_categories = count($categories);
                            $categories_per_column = min(ceil($total_categories / $max_columns), $max_rows);

                            for ($col = 0; $col < $max_columns; $col++) {
                                $start = $col * $categories_per_column;
                                $end = min(($start + $categories_per_column), $total_categories);

                                if ($start >= $total_categories) break;
                            ?>
                                <div class="col-md-3"> <!-- Mỗi cột chiếm 1/4 màn hình -->
                                    <?php for ($i = $start; $i < $end; $i++) { ?>
                                        <div class="form-check mb-2"> <!-- Khoảng cách giữa các checkbox -->
                                            <input 
                                                class="form-check-input" 
                                                type="checkbox" 
                                                name="categories[]" 
                                                value="<?php echo $categories[$i]['id']; ?>" 
                                                id="category-<?php echo $categories[$i]['id']; ?>"
                                                <?php if (in_array($categories[$i]['id'], $selected_categories)) echo "checked"; ?> />
                                            <label class="form-check-label" for="category-<?php echo $categories[$i]['id']; ?>">
                                                <?php echo htmlspecialchars($categories[$i]['nameCategory']); ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <button type="submit" onclick="return confirm('Are you sure you want to update this brand?');" class="btn btn-primary text-white" name="update">Update</button>
                </form>
            </div>
        </div>
    </div>            
</div> 
