<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
    <title>Lab Booking System - Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            min-height: 100vh;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .welcome-container {
            background: rgba(255,255,255,0.97);
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(102,126,234,0.15);
            padding: 40px 32px 32px 32px;
            max-width: 450px;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }
        .welcome-logo {
            font-size: 64px;
            margin-bottom: 20px;
            color: #667eea;
            text-shadow: 0 2px 8px rgba(102,126,234,0.15);
        }
        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 16px;
            letter-spacing: 1px;
        }
        .welcome-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 32px;
            line-height: 1.5;
        }
        .button-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .signin-btn {
            padding: 16px 0;
            border: none;
            border-radius: 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(102,126,234,0.15);
            transition: all 0.2s;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        .signin-btn:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 6px 20px rgba(102,126,234,0.25);
        }
        .signup-btn {
            padding: 16px 0;
            border: 2px solid #667eea;
            border-radius: 25px;
            background: transparent;
            color: #667eea;
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            text-align: center;
            display: block;
        }
        .signup-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(102,126,234,0.15);
        }
        .features {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e9ecef;
            width: 100%;
        }
        .features h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 16px;
            font-weight: 600;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            text-align: left;
        }
        .feature-list li {
            padding: 8px 0;
            color: #666;
            font-size: 14px;
            position: relative;
            padding-left: 24px;
        }
        .feature-list li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #28a745;
            font-weight: bold;
        }
        .welcome-footer {
            margin-top: 24px;
            font-size: 13px;
            color: #888;
            text-align: center;
        }
        .heading {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .heading h1 {
            font-size: 36px;
            font-weight: 700;
            margin: 0 0 10px 0;
            letter-spacing: 1px;
        }
        .heading p {
            font-size: 18px;
            margin: 0;
            opacity: 0.9;
            font-weight: 400;
        }
        @media (max-width: 500px) {
            .welcome-container {
                padding: 24px 16px 16px 16px;
                margin: 0 16px;
            }
            .welcome-title {
                font-size: 28px;
            }
            .heading h1 {
                font-size: 28px;
            }
            .heading p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="heading">
        <h1>Lab Booking System</h1>
    </div>

    <div class="welcome-container">
        <div class="welcome-logo">🏢</div>
        <div class="welcome-title">Welcome to Lab Booking</div>
        <div class="welcome-subtitle">
            Access your lab booking dashboard and manage your reservations efficiently
        </div>
        
        <div class="button-group">
            <a href="login.php" class="signin-btn">Sign In</a>
            <a href="register.php" class="signup-btn">Create New Account</a>
        </div>
        
        <div class="features">
            <h3>System Features:</h3>
            <ul class="feature-list">
                <li>Easy lab booking and reservation management</li>
                <li>Role-based access for Students, Instructors, Lecturers, and Technical Officers</li>
                <li>Real-time availability tracking</li>
                <li>Secure authentication system</li>
                <li>User-friendly dashboard interface</li>
            </ul>
        </div>
        
        <div class="welcome-footer">
            &copy; <?php echo date('Y'); ?> Lab Booking System
        </div>
    </div>
</body>
</html>
