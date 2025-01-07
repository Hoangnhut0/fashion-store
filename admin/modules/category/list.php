<?php
// Truy vấn danh sách danh mục
$sql = "SELECT * FROM category";
$query = mysqli_query($conn, $sql);
?>

<div class="card">
    <h5 class="card-header">
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
        List Category 
        <a href="?action=category&query=add" class="mt-2 btn btn-primary float-end text-white">+ Category</a>
    </h5>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category Name</th>
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
                    <td><?php echo htmlspecialchars($row['nameCategory']); ?></td>
                    <td><?php echo htmlspecialchars($row['description']); ?></td>
                    <td>
                        <a 
                            class="btn btn-sm btn-primary" 
                            href="index.php?action=category&query=edit&idcategory=<?php echo $row['id']; ?>">
                            <i class="bx bx-edit-alt me-1"></i> Edit
                        </a>
                        <a 
                            class="btn btn-sm btn-danger" 
                            href="modules/category/handle.php?idcategory=<?php echo $row['id']; ?>&delete=true"
                            onclick="return confirm('Are you sure you want to delete this category?');">
                            <i class="bx bx-trash me-1"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php } ?>  
            </tbody>
        </table>
    </div>
</div>
