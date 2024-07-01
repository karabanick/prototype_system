<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
    <div class="top-text stylish-text">Danvam Management System</div>
    <div class="login-container">
        <div class="login-box">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h2>Hello!</h2>
            <form action="login_process.php" method="post">
                <select name="role" required>
                    <option value="Property Owner">Property Owner</option>
                    <option value="Property Manager">Property Manager</option>
                </select>
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="error" id="error-message"></div>
        </div>
    </div>
    <div class="bottom-text stylish-text">Management at the tip of your fingers.</div>
    <div class="footer">
        <p>DANVAM CIRCLE | +254-000-000-00 | www.danvamcircle.co.ke | KENYA</p>
    </div>

    <script>
        // Display error message for 3 seconds
        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.style.display = 'block';
            setTimeout(() => {
                errorDiv.style.display = 'none';
            }, 3000);
        }

        // Check for error
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error')) {
            showError('Invalid Credentials');
        }
    </script>
</body>
</html>
