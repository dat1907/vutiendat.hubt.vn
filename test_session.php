<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Test</title>
</head>
<body>
    <h2>Session Debug Information</h2>
    <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
    <p><strong>Session Status:</strong> <?php echo session_status(); ?></p>
    <p><strong>User ID:</strong> <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'; ?></p>
    <p><strong>Username:</strong> <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Not set'; ?></p>
    <p><strong>Full Name:</strong> <?php echo isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'Not set'; ?></p>
    <p><strong>Role:</strong> <?php echo isset($_SESSION['role']) ? $_SESSION['role'] : 'Not set'; ?></p>
    
    <h3>All Session Data:</h3>
    <pre><?php print_r($_SESSION); ?></pre>
    
    <h3>Test Add to Cart AJAX:</h3>
    <button onclick="testAddToCart()">Test Add Product ID 1 to Cart</button>
    <div id="result"></div>
    
    <script>
    function testAddToCart() {
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=1&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('result').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('result').innerHTML = '<div style="color: red;">Error: ' + error + '</div>';
        });
    }
    </script>
</body>
</html>
