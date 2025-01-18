<?php
// Lấy ID sản phẩm từ URL
$productID = intval($_GET['idproduct']);

// Truy vấn thông tin sản phẩm
$sql = "SELECT 
            p.id AS productID, 
            p.nameProduct, 
            p.image, 
            p.price, 
            p.description, 
            p.id_category, 
            p.id_brand
        FROM 
            products p
        WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $productID);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

// Nếu không tìm thấy sản phẩm, thông báo lỗi
if (!$product) {
    echo "Product not found!";
    exit;
}

// Fetch categories, brands, sizes and product images
$categoriesResult = mysqli_query($conn, "SELECT * FROM category");
$brandsResult = mysqli_query($conn, "SELECT * FROM brands");
$sizesResult = mysqli_query($conn, "SELECT * FROM sizes");

// Truy vấn số lượng tồn kho cho sản phẩm từ bảng product_sizes
$stockResult = mysqli_query($conn, "SELECT * FROM product_sizes WHERE product_id = $productID");
$productSizes = [];
while ($stock = mysqli_fetch_assoc($stockResult)) {
    $productSizes[$stock['product_id']][$stock['size_id']] = $stock['stock'];
}

// Truy vấn ảnh phụ từ bảng product_images
$imagesResult = mysqli_query($conn, "SELECT * FROM product_images WHERE product_id = $productID");
$additionalImages = mysqli_fetch_all($imagesResult, MYSQLI_ASSOC);
?>

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
        ?>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Edit Product</h5>
                <small class="text-muted float-end">Product Management</small>
            </div>

            <div class="card-body">
                <form action="modules/product/handle.php?idproduct=<?php echo $productID; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="productID" value="<?php echo $product['productID']; ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product-name" class="form-label">Product Name</label>
                            <input type="text" class="form-control" id="product-name" name="nameProduct" value="<?php echo htmlspecialchars($product['nameProduct']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product-image" class="form-label">Product Image (Main)</label>
                            <input type="file" class="form-control" id="product-image" name="image">
                            <img src="modules/product/img/<?php echo htmlspecialchars($product['image']); ?>" alt="Current Product Image" style="width: 100px; height: auto;">
                        </div>
                    </div>

                
                    <?php if (!empty($additionalImages)) { ?>
                        <?php foreach ($additionalImages as $additionalImage) { ?>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">Additional Image</label>
                                    <img src="modules/product/img/<?php echo htmlspecialchars($additionalImage['image_url']); ?>" alt="Additional Image" style="width: 100px; height: auto;">
                                    <input type="file" class="form-control" name="images[]" accept="image/*">
                                </div>
                            </div>
                        <?php } ?>
                    <?php } ?>


                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product-category" class="form-label">Category</label>
                            <select class="form-select" id="product-category" name="category_id" required>
                                <option value="" disabled>Select category</option>
                                <?php while ($category = mysqli_fetch_assoc($categoriesResult)) { ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['id_category'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['nameCategory']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="product-brand" class="form-label">Brand</label>
                            <select class="form-select" id="product-brand" name="brand_id" required>
                                <option value="" disabled>Select brand</option>
                                <?php while ($brand = mysqli_fetch_assoc($brandsResult)) { ?>
                                    <option value="<?php echo $brand['id']; ?>" <?php echo $brand['id'] == $product['id_brand'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($brand['nameBrand']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="product-price" class="form-label">Price</label>
                            <input type="number" class="form-control" id="product-price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="product-description" class="form-label">Description</label>
                            <textarea id="product-description" class="form-control" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
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
                                        <input type="number" class="form-control" name="stock[<?php echo $size['id']; ?>]" value="<?php echo isset($productSizes[$product['productID']][$size['id']]) ? $productSizes[$product['productID']][$size['id']] : ''; ?>" placeholder="Stock for <?php echo htmlspecialchars($size['nameSize']); ?>" required>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary text-white" name="editProduct">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>
