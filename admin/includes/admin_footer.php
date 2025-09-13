    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        // Admin specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-refresh dashboard every 5 minutes
            if (window.location.pathname.includes('admin/index.php')) {
                setInterval(function() {
                    location.reload();
                }, 300000);
            }
        });
    </script>
</body>
</html>
