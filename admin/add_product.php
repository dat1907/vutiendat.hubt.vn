<?php
$page_title = 'Thêm Sản Phẩm - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $category_id = $_POST['category_id'] ? intval($_POST['category_id']) : null;
    $status = $_POST['status'];
    $image_url = '';

    // Handle file upload
    $upload_error = false;
    if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['product_image']['type'];
        $file_size = $_FILES['product_image']['size'];
        
        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_extension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('product_') . '.' . $file_extension;
            $upload_path = '../uploads/products/' . $file_name;
            
            // Ensure directory exists
            if (!is_dir('../uploads/products/')) {
                mkdir('../uploads/products/', 0755, true);
            }
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $upload_path)) {
                $image_url = 'uploads/products/' . $file_name;
            } else {
                $message = 'Lỗi khi tải lên hình ảnh!';
                $message_type = 'danger';
                $upload_error = true;
            }
        } else {
            $message = 'File không hợp lệ! Chỉ chấp nhận JPG, PNG, GIF, WEBP và kích thước tối đa 5MB.';
            $message_type = 'danger';
            $upload_error = true;
        }
    }

    if ($name && $price > 0 && !$upload_error) {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, quantity, category_id, image_url, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $quantity, $category_id, $image_url, $status]);
            $message = 'Thêm sản phẩm thành công!';
            $message_type = 'success';
            // Reset form
            $_POST = [];
        } catch (PDOException $e) {
            $message = 'Lỗi khi thêm sản phẩm: ' . $e->getMessage();
            $message_type = 'danger';
        }
    } else if (!$upload_error) {
        $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        $message_type = 'warning';
    }
}

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Thêm Sản Phẩm Mới</h1>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" class="needs-validation" enctype="multipart/form-data" novalidate>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên Sản Phẩm *</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                        <div class="invalid-feedback">
                                            Vui lòng nhập tên sản phẩm.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô Tả</label>
                                        <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label">Giá (VNĐ) *</label>
                                                <input type="number" class="form-control" id="price" name="price" min="0" step="1000"
                                                       value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
                                                <div class="invalid-feedback">
                                                    Vui lòng nhập giá sản phẩm.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label">Số Lượng</label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="0"
                                                       value="<?php echo isset($_POST['quantity']) ? $_POST['quantity'] : '0'; ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="product_image" class="form-label">Hình Ảnh Sản Phẩm</label>
                                        <input type="file" class="form-control" id="product_image" name="product_image" 
                                               accept="image/jpeg,image/png,image/gif,image/webp">
                                        <div class="form-text">Chọn file ảnh (JPG, PNG, GIF, WEBP). Kích thước tối đa: 5MB.</div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="category_id" class="form-label">Danh Mục</label>
                                        <select class="form-select" id="category_id" name="category_id">
                                            <option value="">Chọn danh mục</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="status" class="form-label">Trạng Thái</label>
                                        <select class="form-select" id="status" name="status">
                                            <option value="active" <?php echo (!isset($_POST['status']) || $_POST['status'] == 'active') ? 'selected' : ''; ?>>Hoạt động</option>
                                            <option value="inactive" <?php echo (isset($_POST['status']) && $_POST['status'] == 'inactive') ? 'selected' : ''; ?>>Không hoạt động</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Xem Trước Hình Ảnh</label>
                                        <div id="image-preview" class="border rounded p-3 text-center">
                                            <img id="preview-img" src="https://via.placeholder.com/200x150?text=No+Image" 
                                                 class="img-fluid rounded" style="max-height: 150px;" alt="Preview">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="products.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Hủy
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Lưu Sản Phẩm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('product_image').addEventListener('change', function() {
    const file = this.files[0];
    const preview = document.getElementById('preview-img');
    
    if (file) {
        // Check file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Chỉ chấp nhận file JPG, PNG, GIF, WEBP!');
            this.value = '';
            preview.src = 'https://via.placeholder.com/200x150?text=No+Image';
            return;
        }
        
        // Check file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Kích thước file không được vượt quá 5MB!');
            this.value = '';
            preview.src = 'https://via.placeholder.com/200x150?text=No+Image';
            return;
        }
        
        // Show preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        preview.src = 'https://via.placeholder.com/200x150?text=No+Image';
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
