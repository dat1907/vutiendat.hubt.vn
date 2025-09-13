<?php
$page_title = 'Tin Nhắn Liên Hệ - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

// Xử lý cập nhật trạng thái tin nhắn
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $contact_id = (int)$_POST['contact_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE contacts SET status = ? WHERE id = ?");
        $stmt->execute([$status, $contact_id]);
        $message = 'Cập nhật trạng thái tin nhắn thành công!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Lỗi khi cập nhật trạng thái!';
        $message_type = 'danger';
    }
}

// Xử lý xóa tin nhắn
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Xóa tin nhắn thành công!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Lỗi khi xóa tin nhắn!';
        $message_type = 'danger';
    }
}

// Lấy danh sách tin nhắn
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT * FROM contacts WHERE 1=1";

if ($search) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR subject LIKE :search)";
}

if ($status_filter) {
    $sql .= " AND status = :status";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if ($status_filter) {
    $stmt->bindValue(':status', $status_filter);
}

$stmt->execute();
$contacts = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <h1 class="mb-4">Tin Nhắn Liên Hệ</h1>

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
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, email hoặc tiêu đề..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="new" <?php echo $status_filter == 'new' ? 'selected' : ''; ?>>Mới</option>
                                    <option value="read" <?php echo $status_filter == 'read' ? 'selected' : ''; ?>>Đã đọc</option>
                                    <option value="replied" <?php echo $status_filter == 'replied' ? 'selected' : ''; ?>>Đã trả lời</option>
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

                <!-- Danh sách tin nhắn -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($contacts): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên</th>
                                            <th>Email</th>
                                            <th>Tiêu Đề</th>
                                            <th>Nội Dung</th>
                                            <th>Trạng Thái</th>
                                            <th>Ngày Gửi</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($contacts as $contact): ?>
                                            <tr class="<?php echo $contact['status'] == 'new' ? 'table-warning' : ''; ?>">
                                                <td><?php echo $contact['id']; ?></td>
                                                <td><strong><?php echo htmlspecialchars($contact['name']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($contact['email']); ?></td>
                                                <td><?php echo htmlspecialchars($contact['subject'] ?: 'Không có tiêu đề'); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($contact['message'], 0, 50)) . '...'; ?></small>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="contact_id" value="<?php echo $contact['id']; ?>">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="new" <?php echo $contact['status'] == 'new' ? 'selected' : ''; ?>>Mới</option>
                                                            <option value="read" <?php echo $contact['status'] == 'read' ? 'selected' : ''; ?>>Đã đọc</option>
                                                            <option value="replied" <?php echo $contact['status'] == 'replied' ? 'selected' : ''; ?>>Đã trả lời</option>
                                                        </select>
                                                        <input type="hidden" name="update_status" value="1">
                                                    </form>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($contact['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-info" 
                                                                data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $contact['id']; ?>">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <a href="mailto:<?php echo $contact['email']; ?>?subject=Re: <?php echo urlencode($contact['subject']); ?>" 
                                                           class="btn btn-sm btn-success">
                                                            <i class="fas fa-reply"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $contact['id']; ?>" class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa tin nhắn này?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Modal xem chi tiết -->
                                            <div class="modal fade" id="viewModal<?php echo $contact['id']; ?>" tabindex="-1">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Chi Tiết Tin Nhắn</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Tên:</strong> <?php echo htmlspecialchars($contact['name']); ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>Email:</strong> <?php echo htmlspecialchars($contact['email']); ?>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Tiêu đề:</strong> <?php echo htmlspecialchars($contact['subject'] ?: 'Không có tiêu đề'); ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Nội dung:</strong>
                                                                <div class="border rounded p-3 mt-2">
                                                                    <?php echo nl2br(htmlspecialchars($contact['message'])); ?>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Ngày gửi:</strong> <?php echo date('d/m/Y H:i:s', strtotime($contact['created_at'])); ?>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                                                            <a href="mailto:<?php echo $contact['email']; ?>?subject=Re: <?php echo urlencode($contact['subject']); ?>" 
                                                               class="btn btn-success">
                                                                <i class="fas fa-reply"></i> Trả Lời
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                <h5>Không có tin nhắn nào</h5>
                                <p class="text-muted">Chưa có tin nhắn liên hệ nào hoặc không tìm thấy kết quả phù hợp.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
