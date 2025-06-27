<?php
session_start();
include 'db_connect.php';

$error_message = "";
$success_message = "";
$registered_role = "";
$registered_id = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle "Login Here" button after registration
    if (isset($_POST['action']) && $_POST['action'] === 'login_here') {
        if (!empty($_SESSION['registered_role']) && !empty($_SESSION['registered_id'])) {
            $role = $_SESSION['registered_role'];
            $_SESSION['role'] = $role;
            $_SESSION['id'] = $_SESSION['registered_id'];
            // Clean up
            unset($_SESSION['registered_role'], $_SESSION['registered_id']);
            header("Location: dashboard_{$role}.php");
            exit;
        }
    }

    // Normal registration flow
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';
    $name = $_POST['name'] ?? '';
    $semester = $_POST['semester'] ?? '';

    // Validation
    if (empty($email) || empty($password) || empty($confirm_password) || empty($role) || empty($name) || ($role == 'student' && empty($semester))) {
        $error_message = "All fields are required.";
    } elseif (strlen($password) < 4) {
        $error_message = "Password must be at least 4 characters long.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Determine table and field names based on role
        $table = "";
        $id_field = "";
        $email_field = "";
        $name_field = "";
        $password_field = "password";

        switch ($role) {
            case 'student':
                $table = 'student';
                $id_field = 'Stu_ID';
                $email_field = 'Stu_Email';
                $name_field = 'Stu_Name';
                break;
            case 'instructor':
                $table = 'instructor';
                $id_field = 'Instructor_ID';
                $email_field = 'Instructor_Email';
                $name_field = 'Instructor_Name';
                break;
            case 'lecture':
                $table = 'lecture';
                $id_field = 'Lect_ID';
                $email_field = 'Lect_Email';
                $name_field = 'Lect_Name';
                break;
            case 'to':
                $table = 'to';
                $id_field = 'To_ID';
                $email_field = 'To_Email';
                $name_field = 'To_Name';
                break;
            default:
                $error_message = "Invalid role specified.";
        }

        if (!empty($table)) {
            // Check if email already exists
            $check_sql = "SELECT * FROM `$table` WHERE `$email_field` = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows > 0) {
                $error_message = "Email already exists. Please use a different email or login.";
            } else {
                // Generate a random unique ID (between 10000 and 99999)
                $unique_id = rand(10000, 99999);

                // Insert new user (including the primary key)
                if ($role == 'student') {
                    $insert_sql = "INSERT INTO `$table` (`$id_field`, `$email_field`, `$name_field`, `Semester`, `$password_field`) VALUES (?, ?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("issss", $unique_id, $email, $name, $semester, $password);
                } else {
                    $insert_sql = "INSERT INTO `$table` (`$id_field`, `$email_field`, `$name_field`, `$password_field`) VALUES (?, ?, ?, ?)";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("isss", $unique_id, $email, $name, $password);
                }

                if ($insert_stmt->execute()) {
                    $success_message = "Registration successful! Click below to go to your dashboard.";
                    // Store info for login here
                    $_SESSION['registered_role'] = $role;
                    $_SESSION['registered_id'] = $unique_id;
                } else {
                    $error_message = "Registration failed. Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lab Booking Registration</title>
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
        .register-container {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(102,126,234,0.15);
            padding: 40px 32px 32px 32px;
            max-width: 450px;
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
        .register-logo {
            font-size: 48px;
            margin-bottom: 10px;
            color: #667eea;
            text-shadow: 0 2px 8px rgba(102,126,234,0.15);
        }
        .register-title {
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
        .register-btn {
            margin-top: 18px;
            padding: 14px 0;
            border: none;
            border-radius: 25px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(40,167,69,0.15);
            transition: background 0.2s, transform 0.2s;
        }
        .register-btn:hover {
            background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
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
        .register-footer {
            margin-top: 18px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }
        @media (max-width: 500px) {
            .register-container {
                padding: 24px 8px 16px 8px;
            }
            .register-title {
                font-size: 22px;
            }
        }
        .signin-btn {
            margin-top: 12px;
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
            text-decoration: none;
            text-align: center;
            display: block;
        }
        .signin-btn:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px) scale(1.03);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="header">Lab Booking System</div>
        
        <div class="register-logo">📝</div>
        <div class="register-title">Create Account</div>
        
        <?php if (!empty($error_message)): ?>
            <div class="message error-message">
                ❌ <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success_message)): ?>
            <div class="message success-message">
                ✅ <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="register.php">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" required autocomplete="name" 
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required autocomplete="username" 
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="password">Password (min 4 characters)</label>
            <input type="password" name="password" id="password" required minlength="4" placeholder="Enter password">

            <label for="confirm_password">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" required minlength="4" placeholder="Repeat password">

            <label for="role">Role</label>
            <select name="role" id="role" required onchange="toggleSemesterField()">
                <option value="">Select your role</option>
                <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                <option value="instructor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'instructor') ? 'selected' : ''; ?>>Instructor</option>
                <option value="lecture" <?php echo (isset($_POST['role']) && $_POST['role'] == 'lecture') ? 'selected' : ''; ?>>Lecturer</option>
                <option value="to" <?php echo (isset($_POST['role']) && $_POST['role'] == 'to') ? 'selected' : ''; ?>>Technical Officer</option>
            </select>

            <div id="semester-field" style="display:<?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'block' : 'none'; ?>">
                <label for="semester">Semester</label>
                <input type="text" name="semester" id="semester" value="<?php echo isset($_POST['semester']) ? htmlspecialchars($_POST['semester']) : ''; ?>">
            </div>

            <button type="submit" class="register-btn">Create Account</button>
        </form>
        
        <?php if (!empty($success_message)): ?>
            <form method="POST" action="register.php">
                <input type="hidden" name="action" value="login_here">
                <button type="submit" class="signin-btn">Login Here</button>
            </form>
        <?php endif; ?>
        <a href="login.php" class="back-btn">← Back to Login</a>
        
        <div class="register-footer">
            &copy; <?php echo date('Y'); ?> Lab Booking System
        </div>
    </div>

    <script>
    function toggleSemesterField() {
        var role = document.getElementById('role').value;
        document.getElementById('semester-field').style.display = (role === 'student') ? 'block' : 'none';
    }
    </script>
</body>
</html> 