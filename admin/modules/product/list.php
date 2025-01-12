<?php

$sql = "SELECT 
        p.id AS productID, 
        p.nameProduct, 
        p.image, 
        p.price, 
        c.nameCategory, 
        b.nameBrand, 
        p.description
    FROM 
        products p
    LEFT JOIN 
        category c ON p.id_category = c.id
    LEFT JOIN 
        brands b ON p.id_brand = b.id";
$query = mysqli_query($conn, $sql);

// Query for product sizes and stock
$sizeStockQuery = "SELECT ps.product_id, s.nameSize, ps.stock
    FROM product_sizes ps
    LEFT JOIN sizes s ON ps.size_id = s.id";
$sizeStockResult = mysqli_query($conn, $sizeStockQuery);

// Group sizes and stock by product_id
$productSizes = [];
while ($sizeRow = mysqli_fetch_assoc($sizeStockResult)) {
    $productSizes[$sizeRow['product_id']][] = $sizeRow;
}
?>

<div class="card">
    <h5 class="card-header">
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
        List Products
        <a href="?action=product&query=add" class="mt-2 btn btn-primary float-end text-white">+ Product</a>
    </h5>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Image</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Sizes and Stock</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                <?php 
                $i = 0;
                while ($row = mysqli_fetch_assoc($query)) { 
                    $i++;
                ?>
                <tr>
                    <td><strong><?php echo $i; ?></strong></td>
                    <td><?php echo htmlspecialchars($row['nameProduct']); ?></td>
                    <td>
                        <img src="modules/product/img/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['nameProduct']); ?>" style="width: 50px; height: auto;">
                    </td>
                    <td><?php echo htmlspecialchars(number_format($row['price'], 2)); ?> $</td>
                    <td><?php echo htmlspecialchars($row['nameCategory']); ?></td>
                    <td><?php echo htmlspecialchars($row['nameBrand']); ?></td>
                    <td>
                        <?php if (!empty($productSizes[$row['productID']])) { ?>
                            <div>
                                <?php foreach ($productSizes[$row['productID']] as $size) { ?>
                                    <span><?php echo htmlspecialchars($size['nameSize']) . ': ' . intval($size['stock']); ?></span><br>
                                <?php } ?>
                            </div>
                        <?php } else { ?>
                            <span>No sizes available</span>
                        <?php } ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <a 
                            class="btn btn-sm btn-primary" 
                            href="index.php?action=product&query=edit&idproduct=<?php echo $row['productID']; ?>">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a 
                            class="btn btn-sm btn-danger" 
                            href="modules/product/handle.php?idproduct=<?php echo $row['productID']; ?>&delete=true"
                            onclick="return confirm('Are you sure you want to delete this product?');">
                            <i class="bx bx-trash me-1"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php } ?>  
            </tbody>
        </table>
    </div>
</div>
