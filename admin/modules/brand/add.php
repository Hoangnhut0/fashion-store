<?php
    $sql = "SELECT * FROM category";
    $query = mysqli_query($conn, $sql);
    $categories = [];
    while ($row = mysqli_fetch_assoc($query)) {
        $categories[] = $row;
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
                <h5 class="mb-0">+ Brand</h5>
                <small class="text-muted float-end">Default label</small>
            </div>
            <div class="card-body">
                <form action="modules/brand/handle.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Brand Name</label>
                        <input type="text" class="form-control" id="basic-default-fullname" name="nameBrand" placeholder="TSUN" />
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-message">Description</label>
                        <textarea 
                            id="basic-default-message"
                            class="form-control"
                            placeholder=""
                            name="description">
                        </textarea>
                    </div>
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
                                            />
                                            <label class="form-check-label" for="category-<?php echo $categories[$i]['id']; ?>">
                                                <?php echo $categories[$i]['nameCategory']; ?>
                                            </label>
                                        </div>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary text-white" name="add">Add</button>
                </form>
            </div>
        </div>
    </div>            
</div>
