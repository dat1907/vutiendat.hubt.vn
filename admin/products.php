<?php
$page_title = 'Quản Lý Sản Phẩm - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

// Xử lý xóa sản phẩm
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Xóa sản phẩm thành công!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Lỗi khi xóa sản phẩm!';
        $message_type = 'danger';
    }
}

// Lấy danh sách sản phẩm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";

if ($search) {
    $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
}

if ($category) {
    $sql .= " AND p.category_id = :category";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if ($category) {
    $stmt->bindValue(':category', $category);
}

$stmt->execute();
$products = $stmt->fetchAll();

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
                    <h1>Quản Lý Sản Phẩm</h1>
                    <a href="add_product.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Sản Phẩm
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Bộ lọc -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="category" class="form-select">
                                    <option value="">Tất cả danh mục</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách sản phẩm -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($products): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Hình Ảnh</th>
                                            <th>Tên Sản Phẩm</th>
                                            <th>Danh Mục</th>
                                            <th>Giá</th>
                                            <th>Số Lượng</th>
                                            <th>Trạng Thái</th>
                                            <th>Ngày Tạo</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products as $product): ?>
                                            <tr>
                                                <td><?php echo $product['id']; ?></td>
                                                <td>
                                                    <?php if ($product['image_url'] && file_exists('../' . $product['image_url'])): ?>
                                                        <img src="../<?php echo htmlspecialchars($product['image_url']); ?>" 
                                                             class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" alt="Product">
                                                    <?php else: ?>
                                                        <img src="https://via.placeholder.com/50x50?text=No+Image" 
                                                             class="img-thumbnail" style="width: 50px; height: 50px;" alt="No Image">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars(substr($product['description'], 0, 50)) . '...'; ?></small>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['category_name'] ?: 'Chưa phân loại'); ?></td>
                                                <td><?php echo number_format($product['price']); ?> VNĐ</td>
                                                <td>
                                                    <span class="badge bg-<?php echo $product['quantity'] <= 5 ? 'warning' : 'success'; ?>">
                                                        <?php echo $product['quantity']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo $product['status'] == 'active' ? 'Hoạt động' : 'Không hoạt động'; ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger btn-delete" 
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-box fa-3x text-muted mb-3"></i>
                                <h5>Không có sản phẩm nào</h5>
                                <p class="text-muted">Hãy thêm sản phẩm đầu tiên của bạn!</p>
                                <a href="add_product.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm Sản Phẩm
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
