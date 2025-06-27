<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'to') {
    header("Location: index.php");
    exit;
}

$to_id = $_SESSION['id'];

// Get TO's profile information
$profile_query = $conn->prepare("SELECT To_Name FROM `to` WHERE To_ID = ?");
$profile_query->bind_param("i", $to_id);
$profile_query->execute();
$profile_result = $profile_query->get_result();
$profile = $profile_result->fetch_assoc();

// Get statistics for this TO
$total_labs = $conn->query("SELECT COUNT(*) as count FROM Lab")->fetch_assoc()['count'];
$total_equipment = $conn->query("SELECT COUNT(*) as count FROM Lab_Equipment")->fetch_assoc()['count'];
$equipment_excellent = $conn->query("SELECT COUNT(*) as count FROM Lab_Equipment WHERE Equip_Condition = 'Excellent'")->fetch_assoc()['count'];
$equipment_good = $conn->query("SELECT COUNT(*) as count FROM Lab_Equipment WHERE Equip_Condition = 'Good'")->fetch_assoc()['count'];
$equipment_fair = $conn->query("SELECT COUNT(*) as count FROM Lab_Equipment WHERE Equip_Condition = 'Fair'")->fetch_assoc()['count'];
$equipment_poor = $conn->query("SELECT COUNT(*) as count FROM Lab_Equipment WHERE Equip_Condition = 'Poor'")->fetch_assoc()['count'];

// Handle equipment condition update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['equip_id']) && isset($_POST['new_condition'])) {
    $equip_id = $_POST['equip_id'];
    $new_condition = $_POST['new_condition'];

    $stmt = $conn->prepare("UPDATE Lab_Equipment SET Equip_Condition = ? WHERE Equipment_ID = ?");
    $stmt->bind_param("si", $new_condition, $equip_id);

    if ($stmt->execute()) {
        header("Location: dashboard_to.php?success=updated");
        exit;
    } else {
        header("Location: dashboard_to.php?error=failed");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Technical Officer Dashboard - Lab Booking System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .profile-section h2 {
            margin: 0 0 20px 0;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }
        
        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
            position: relative;
            z-index: 1;
        }
        
        .profile-item {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }
        
        .profile-item:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.25);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        .profile-label {
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            opacity: 0.9;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        
        .profile-value {
            font-size: 18px;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 40px;
            padding: 0 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 25px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent-color), var(--accent-color-light));
        }
        
        .stat-card.labs {
            --accent-color: #667eea;
            --accent-color-light: #764ba2;
        }
        
        .stat-card.equipment {
            --accent-color: #17a2b8;
            --accent-color-light: #20c997;
        }
        
        .stat-card.excellent {
            --accent-color: #28a745;
            --accent-color-light: #34ce57;
        }
        
        .stat-card.good {
            --accent-color: #ffc107;
            --accent-color-light: #ffdb4d;
        }
        
        .stat-card.fair {
            --accent-color: #fd7e14;
            --accent-color-light: #ff8c42;
        }
        
        .stat-card.poor {
            --accent-color: #dc3545;
            --accent-color-light: #e74c3c;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 36px;
            font-weight: 800;
            margin-bottom: 8px;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-color-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .section-title {
            color: #2c3e50;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin: 40px 40px 20px 40px;
            font-size: 24px;
            font-weight: 700;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        table {
            width: calc(100% - 80px);
            margin: 20px 40px 40px 40px;
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 18px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        td {
            padding: 18px 15px;
            border-bottom: 1px solid #f1f3f4;
            font-size: 14px;
        }
        
        tr:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            transform: scale(1.01);
            transition: all 0.2s ease;
        }
        
        .equipment-condition {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .condition-excellent {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .condition-good {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .condition-fair {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .condition-poor {
            background: linear-gradient(135deg, #f8d7da 0%, #e74c3c 100%);
            color: #721c24;
            border: 1px solid #e74c3c;
        }
        
        .lab-type {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .type-software {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1565c0;
            border: 1px solid #bbdefb;
        }
        
        .type-hardware {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            color: #7b1fa2;
            border: 1px solid #e1bee7;
        }
        
        .type-network {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .capacity-badge {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .quantity-badge {
            background: linear-gradient(135deg, #6f42c1 0%, #8e44ad 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .update-form {
            background: white;
            margin: 20px 40px;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            border: 1px solid rgba(0,0,0,0.05);
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .form-group select,
        .form-group input {
            padding: 12px 15px;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }
        
        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .success-message {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 40px;
            border-left: 5px solid #28a745;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.2);
        }
        
        .error-message {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 40px;
            border-left: 5px solid #dc3545;
            font-weight: 600;
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.2);
        }
        
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px;
            font-size: 16px;
        }
        
        .lab-name {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .equipment-name {
            font-weight: 500;
            color: #495057;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .profile-section {
                padding: 30px 20px;
            }
            
            .profile-section h2 {
                font-size: 24px;
            }
            
            .stats-section {
                margin: 20px;
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 28px;
            }
            
            .section-title {
                margin: 30px 20px 15px 20px;
                font-size: 20px;
            }
            
            .update-form {
                margin: 20px;
                padding: 20px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            table {
                width: calc(100% - 40px);
                margin: 15px 20px 30px 20px;
            }
            
            th, td {
                padding: 12px 10px;
                font-size: 13px;
            }
            
            .success-message,
            .error-message {
                margin: 15px 20px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 👤 Technical Officer's Profile Section -->
        <div class="profile-section">
            <a href="index.php" id="backToLoginBtn" style="display:inline-block;margin-bottom:20px;padding:10px 24px;border-radius:20px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(102,126,234,0.15);transition:background 0.2s;">← Back to Login</a>
            <script>
                document.getElementById('backToLoginBtn').addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = 'index.php';
                });
            </script>
            <h2>👤 My Technical Officer Profile</h2>
            <div class="profile-details">
                <div class="profile-item">
                    <div class="profile-label">Name</div>
                    <div class="profile-value"><?php echo htmlspecialchars($profile['To_Name'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Technical Officer ID</div>
                    <div class="profile-value"><?php echo htmlspecialchars($to_id); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Role</div>
                    <div class="profile-value">Technical Officer</div>
                </div>
            </div>
        </div>

        <!-- Lab Usage Log Button -->
        <div style="margin: 40px 40px 0 40px;">
            <form method="get" style="display:inline;">
                <button type="submit" name="show_usage" value="1" class="btn btn-primary">📋 Show Lab Usage Log</button>
            </form>
        </div>

        <?php if (isset($_GET['show_usage']) && $_GET['show_usage'] == '1'): ?>
            <h3 class="section-title">📋 Lab Usage Log</h3>
            <table>
                <tr>
                    <th>Log ID</th>
                    <th>Semester</th>
                    <th>Date</th>
                    <th>Entry Time</th>
                    <th>Exit Time</th>
                    <th>Booking ID</th>
                </tr>
                <?php
                $usage_query = "
                    SELECT Log_ID, Semester, Date, Entry_Time, Exit_Time, Booking_ID
                    FROM lab_usage_log
                    ORDER BY Date DESC, Entry_Time DESC
                ";
                $usage_result = $conn->query($usage_query);
                if ($usage_result && $usage_result->num_rows > 0) {
                    while ($row = $usage_result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['Log_ID']}</td>
                            <td>{$row['Semester']}</td>
                            <td>" . date('M d, Y', strtotime($row['Date'])) . "</td>
                            <td>{$row['Entry_Time']}</td>
                            <td>{$row['Exit_Time']}</td>
                            <td>{$row['Booking_ID']}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' class='no-data'>No lab usage logs found.</td></tr>";
                }
                ?>
            </table>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                ✅ Equipment condition updated successfully!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                ❌ Failed to update equipment condition. Please try again.
            </div>
        <?php endif; ?>

        <!-- 🔧 Equipment Management Section -->
        <h3 class="section-title">🔧 Equipment Management</h3>
        <table>
            <tr>
                <th>Equipment ID</th>
                <th>Equipment Name</th>
                <th>Laboratory</th>
                <th>Quantity</th>
                <th>Condition</th>
            </tr>
            <?php
            $equipment = $conn->query("
                SELECT E.Equipment_ID, E.Equipment_Name, L.Lab_Name, E.Quantity, E.Equip_Condition
                FROM Lab_Equipment E
                JOIN Lab L ON E.Lab_ID = L.Lab_ID
                ORDER BY L.Lab_Name, E.Equipment_Name
            ");
            if ($equipment && $equipment->num_rows > 0) {
                while ($row = $equipment->fetch_assoc()) {
                    $condition_class = 'condition-' . strtolower($row['Equip_Condition']);
                    echo "<tr>
                        <td><strong>#{$row['Equipment_ID']}</strong></td>
                        <td><span class='equipment-name'>{$row['Equipment_Name']}</span></td>
                        <td><span class='lab-name'>{$row['Lab_Name']}</span></td>
                        <td><span class='quantity-badge'>{$row['Quantity']}</span></td>
                        <td><span class='equipment-condition $condition_class'>{$row['Equip_Condition']}</span></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='no-data'>No equipment found</td></tr>";
            }
            ?>
        </table>

        <!-- 🔄 Update Equipment Condition Section -->
        <h3 class="section-title">🔄 Update Equipment Condition</h3>
        <div class="update-form">
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Equipment ID</label>
                        <input type="number" name="equip_id" required placeholder="Enter Equipment ID">
                    </div>
                    <div class="form-group">
                        <label>New Condition</label>
                        <select name="new_condition" required>
                            <option value="">-- Select Condition --</option>
                            <option value="Excellent">Excellent</option>
                            <option value="Good">Good</option>
                            <option value="Fair">Fair</option>
                            <option value="Poor">Poor</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">🔄 Update Equipment Condition</button>
            </form>
        </div>
    </div>
</body>
</html>
