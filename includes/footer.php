    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Đạt Apple</h5>
                    <p>Website bán hàng trực tuyến uy tín, chất lượng cao.</p>
                </div>
                <div class="col-md-4">
                    <h5>Liên Kết Nhanh</h5>
                    <ul class="list-unstyled">
                        <li><a href="index.php" class="text-white-50">Trang Chủ</a></li>
                        <li><a href="products.php" class="text-white-50">Sản Phẩm</a></li>
                        <li><a href="contact.php" class="text-white-50">Liên Hệ</a></li>
                        <li><a href="about.php" class="text-white-50">Giới Thiệu</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Thông Tin Liên Hệ</h5>
                    <p><i class="fas fa-phone"></i> 0123-456-789</p>
                    <p><i class="fas fa-envelope"></i> info@webcban.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, TP.HCM</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2024 Đạt Apple. Tất cả quyền được bảo lưu.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <?php if (isset($_SESSION['user_id'])): ?>
    <script>
    // Load cart count on page load
    document.addEventListener('DOMContentLoaded', function() {
        loadCartCount();
    });

    function loadCartCount() {
        fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('cart-count').textContent = data.count;
                }
            })
            .catch(error => {
                console.log('Error loading cart count:', error);
            });
    }
    </script>
    <?php endif; ?>
</body>
</html>
