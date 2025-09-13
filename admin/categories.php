<?php
$page_title = 'Quản Lý Danh Mục - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

// Xử lý thêm danh mục
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            $message = 'Thêm danh mục thành công!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi khi thêm danh mục!';
            $message_type = 'danger';
        }
    } else {
        $message = 'Vui lòng nhập tên danh mục!';
        $message_type = 'warning';
    }
}

// Xử lý cập nhật danh mục
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_category'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if ($name && $id) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$name, $description, $id]);
            $message = 'Cập nhật danh mục thành công!';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Lỗi khi cập nhật danh mục!';
            $message_type = 'danger';
        }
    }
}

// Xử lý xóa danh mục
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Kiểm tra xem có sản phẩm nào đang sử dụng danh mục này không
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetch()['count'];

        if ($count > 0) {
            $message = "Không thể xóa danh mục này vì có {$count} sản phẩm đang sử dụng!";
            $message_type = 'warning';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Xóa danh mục thành công!';
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Lỗi khi xóa danh mục!';
        $message_type = 'danger';
    }
}

// Lấy danh sách danh mục với số lượng sản phẩm
$categories = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                          FROM categories c 
                          LEFT JOIN products p ON c.id = p.category_id 
                          GROUP BY c.id 
                          ORDER BY c.name")->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <h1 class="mb-4">Quản Lý Danh Mục</h1>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Form thêm danh mục -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Thêm Danh Mục Mới</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Tên Danh Mục *</label>
                                        <input type="text" class="form-control" id="name" name="name" required>
                                        <div class="invalid-feedback">
                                            Vui lòng nhập tên danh mục.
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Mô Tả</label>
                                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                                    </div>
                                    <button type="submit" name="add_category" class="btn btn-primary w-100">
                                        <i class="fas fa-plus"></i> Thêm Danh Mục
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách danh mục -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Danh Sách Danh Mục</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($categories): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Tên Danh Mục</th>
                                                    <th>Mô Tả</th>
                                                    <th>Số Sản Phẩm</th>
                                                    <th>Ngày Tạo</th>
                                                    <th>Thao Tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($categories as $category): ?>
                                                    <tr>
                                                        <td><?php echo $category['id']; ?></td>
                                                        <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                                                        <td><?php echo htmlspecialchars(substr($category['description'], 0, 50)) . (strlen($category['description']) > 50 ? '...' : ''); ?></td>
                                                        <td>
                                                            <span class="badge bg-primary"><?php echo $category['product_count']; ?></span>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                                                        <td>
                                                            <div class="btn-group" role="group">
                                                                <button type="button" class="btn btn-sm btn-warning" 
                                                                        data-bs-toggle="modal" data-bs-target="#editModal<?php echo $category['id']; ?>">
                                                                    <i class="fas fa-edit"></i>
                                                                </button>
                                                                <?php if ($category['product_count'] == 0): ?>
                                                                    <a href="?delete=<?php echo $category['id']; ?>" class="btn btn-sm btn-danger" 
                                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa danh mục này?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
                                                                <?php else: ?>
                                                                    <button type="button" class="btn btn-sm btn-secondary" disabled 
                                                                            title="Không thể xóa vì có sản phẩm đang sử dụng">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                <?php endif; ?>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Modal chỉnh sửa -->
                                                    <div class="modal fade" id="editModal<?php echo $category['id']; ?>" tabindex="-1">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">Chỉnh Sửa Danh Mục</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <form method="POST">
                                                                    <div class="modal-body">
                                                                        <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Tên Danh Mục *</label>
                                                                            <input type="text" class="form-control" name="name" 
                                                                                   value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                                        </div>
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Mô Tả</label>
                                                                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($category['description']); ?></textarea>
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                                        <button type="submit" name="update_category" class="btn btn-primary">
                                                                            <i class="fas fa-save"></i> Cập Nhật
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                        <h5>Chưa có danh mục nào</h5>
                                        <p class="text-muted">Hãy thêm danh mục đầu tiên!</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
