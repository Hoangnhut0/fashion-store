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
                <h5 class="mb-0">+ Category</h5>
                <small class="text-muted float-end">Default label</small>
            </div>
            <div class="card-body">
                <form action="modules/category/handle.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label" for="basic-default-fullname">Category Name</label>
                        <input type="text" class="form-control" id="basic-default-fullname" name="nameCategory" placeholder="T-shirt" />
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
                    <button type="submit" class="btn btn-primary text-white" name="add">Add</button>
                </form>
            </div>
        </div>
    </div>            
</div>