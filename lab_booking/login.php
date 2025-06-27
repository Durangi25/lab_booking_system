<?php
session_start();
include 'db_connect.php';

$error_message = "";
$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    $table = "";
    $id_field = "";
    $email_field = "";
    $password_field = "password"; // Use correct field name

    switch ($role) {
        case 'student':
            $table = 'student';
            $id_field = 'Stu_ID';
            $email_field = 'Stu_Email';
            break;
        case 'instructor':
            $table = 'instructor';
            $id_field = 'Instructor_ID';
            $email_field = 'Instructor_Email';
            break;
        case 'lecture':
            $table = 'lecture';
            $id_field = 'Lect_ID';
            $email_field = 'Lect_Email';
            break;
        case 'to':
            $table = 'to';
            $id_field = 'To_ID';
            $email_field = 'To_Email';
            break;
        default:
            $error_message = "Invalid role specified.";
    }

    if (!empty($table)) {
        $sql = "SELECT * FROM `$table` WHERE `$email_field` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            // Check if password is hashed (new users) or plain text (old users)
            if (password_verify($password, $row[$password_field]) || $password == $row[$password_field]) {
                $_SESSION['role'] = $role;
                $_SESSION['id'] = $row[$id_field];
                $success_message = "Login successful! Redirecting...";
                // Redirect after a short delay to show success message
                header("refresh:2;url=dashboard_$role.php");
            } else {
                $error_message = "Invalid password. Please try again.";
            }
        } else {
            $error_message = "Email not found. Please check your email address.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab Booking Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(102,126,234,0.15);
            padding: 40px 32px 32px 32px;
            max-width: 400px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .header {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .login-logo {
            font-size: 48px;
            margin-bottom: 10px;
            color: #667eea;
            text-shadow: 0 2px 8px rgba(102,126,234,0.15);
        }
        .login-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 24px;
            letter-spacing: 1px;
        }
        .message {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
        }
        .error-message {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border-left: 4px solid #d63031;
            box-shadow: 0 4px 15px rgba(255,107,107,0.2);
        }
        .success-message {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            border-left: 4px solid #00a085;
            box-shadow: 0 4px 15px rgba(0,184,148,0.2);
        }
        form {
            width: 100%;
            display: flex;
            flex-direction: column;
        }
        label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            margin-bottom: 6px;
            margin-top: 12px;
            letter-spacing: 0.5px;
        }
        input[type="email"],
        input[type="text"],
        input[type="password"],
        select {
            padding: 12px 14px;
            border-radius: 10px;
            border: 2px solid #e9ecef;
            font-size: 15px;
            margin-bottom: 8px;
            background: #f8f9fa;
            transition: border 0.2s, box-shadow 0.2s;
        }
        input[type="email"]:focus,
        input[type="text"]:focus,
        input[type="password"]:focus,
        select:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102,126,234,0.08);
        }
        .login-btn {
            margin-top: 18px;
            padding: 14px 0;
            border: none;
            border-radius: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102,126,234,0.15);
            transition: background 0.2s, transform 0.2s;
        }
        .login-btn:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px) scale(1.03);
        }
        .back-btn {
            margin-top: 12px;
            padding: 10px 20px;
            border: 2px solid #667eea;
            border-radius: 20px;
            background: transparent;
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .back-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
        }
        .login-footer {
            margin-top: 18px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
            margin-left: 10px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        @media (max-width: 500px) {
            .login-container {
                padding: 24px 8px 16px 8px;
            }
            .login-title {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="header">Lab Booking System</div>
        
        <div class="login-logo">🔒</div>
        <div class="login-title">Welcome Back</div>
        
        <?php if (!empty($error_message)): ?>
            <div class="message error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="message success-message">
                ✅ <?php echo htmlspecialchars($success_message); ?>
                <span class="loading"></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="login.php">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" required autocomplete="username" 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required autocomplete="current-password">

            <label for="role">Role</label>
            <select name="role" id="role" required>
                <option value="">Select your role</option>
                <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="instructor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                <option value="lecture" <?php echo (isset($_POST['role']) && $_POST['role'] == 'lecture') ? 'selected' : ''; ?>>Lecturer</option>
                <option value="to" <?php echo (isset($_POST['role']) && $_POST['role'] == 'to') ? 'selected' : ''; ?>>Technical Officer</option>
            </select>

            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <a href="index.php" class="back-btn">← Back to Home</a>
        
        <div class="login-footer">
            &copy; <?php echo date('Y'); ?> Lab Booking System
        </div>
    </div>
</body>
</html>
