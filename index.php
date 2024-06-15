<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container" style="background-image: url('images/background.jpg');">
        <div class="login-box">
            <img src="images/logo.png" alt="Logo" class="logo">
            <h2>Admin Login</h2>
            <form action="login_process.php" method="post">
                <input type="hidden" name="role" value="Admin">
                <input type="text" name="username" placeholder="Username" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit">Login</button>
            </form>
            <div class="error" id="error-message"></div>
        </div>
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

        // Check URL params for error
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('error')) {
            showError('Invalid Credentials');
        }
    </script>
</body>
</html>