<div class="admin-sidebar">
    <div class="p-3">
        <h5 class="text-white mb-3">Menu Quản Trị</h5>
        <nav class="nav flex-column">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>" href="products.php">
                <i class="fas fa-box"></i> Quản Lý Sản Phẩm
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>" href="categories.php">
                <i class="fas fa-tags"></i> Quản Lý Danh Mục
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>" href="customers.php">
                <i class="fas fa-users"></i> Quản Lý Khách Hàng
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>" href="orders.php">
                <i class="fas fa-shopping-cart"></i> Quản Lý Đơn Hàng
            </a>
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>" href="contacts.php">
                <i class="fas fa-envelope"></i> Tin Nhắn Liên Hệ
            </a>
        </nav>
    </div>
</div>
