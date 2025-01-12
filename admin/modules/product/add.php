<div class="row">
    <div class="col-xl">
        <?php
            if (isset($_SESSION['error'])) {
                echo "<div class='alert alert-danger'>" . $_SESSION['error'] . "</div>";
                unset($_SESSION['error']);
            }

            if (isset($_SESSION['success'])) {
                echo "<div class='alert alert-success'>" . $_SESSION['success'] . "</div>";
                unset($_SESSION['success']);
            }

            // Fetch categories, brands, and sizes
            $categoriesResult = mysqli_query($conn, "SELECT * FROM category");
            $brandsResult = mysqli_query($conn, "SELECT * FROM brands");
            $sizesResult = mysqli_query($conn, "SELECT * FROM sizes");
        ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">+ Add Product</h5>
                <small class="text-muted float-end">Product Management</small>
            </div>

            <div class="card-body">
                <form action="modules/product/handle.php" method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product-name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product-name" name="nameProduct" placeholder="Enter product name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product-image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="product-image" name="image" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product-category" class="form-label">Category</label>
                            <select class="form-select" id="product-category" name="category_id" required>
                                <option value="" disabled selected>Select category</option>
                                <?php while ($category = mysqli_fetch_assoc($categoriesResult)) { ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['nameCategory']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="product-brand" class="form-label">Brand</label>
                            <select class="form-select" id="product-brand" name="brand_id" required>
                                <option value="" disabled selected>Select brand</option>
                                <?php while ($brand = mysqli_fetch_assoc($brandsResult)) { ?>
                                    <option value="<?php echo $brand['id']; ?>">
                                        <?php echo htmlspecialchars($brand['nameBrand']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product-price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="product-price" name="price" placeholder="Enter product price" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product-description" class="form-label">Description</label>
                            <textarea id="product-description" class="form-control" name="description" rows="3" placeholder="Enter product description"></textarea>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sizes and Stock</label>
                        <div id="sizes-wrapper">
                            <?php while ($size = mysqli_fetch_assoc($sizesResult)) { ?>
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-4">
                                        <label class="form-label">Size: <?php echo htmlspecialchars($size['nameSize']); ?></label>
                                    </div>
                                    <div class="col-md-8">
                                        <input type="number" class="form-control" name="stock[<?php echo $size['id']; ?>]" placeholder="Stock for <?php echo htmlspecialchars($size['nameSize']); ?>" required>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary text-white w-100" name="addProduct">Add Product</button>
                </form>
            </div>
        </div>
    </div>
</div>
