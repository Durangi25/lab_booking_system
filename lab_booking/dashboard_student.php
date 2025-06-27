<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] !== 'student') {
    header("Location: index.php");
    exit;
}

$student_id = $_SESSION['id'];

// Get student's profile information
$profile_query = $conn->prepare("SELECT Stu_Name, semester FROM student WHERE Stu_ID = ?");
$profile_query->bind_param("i", $student_id);
$profile_query->execute();
$profile_result = $profile_query->get_result();
$profile = $profile_result->fetch_assoc();

$student_semester = $profile['semester'] ?? 0;

// Get statistics for this student
$upcoming_labs = $conn->prepare("SELECT COUNT(*) as count FROM Lab_Shedule S JOIN Lab L ON S.Lab_ID = L.Lab_ID WHERE S.Semester = ? AND S.Status = 'Approved'");
$upcoming_labs->bind_param("i", $student_semester);
$upcoming_labs->execute();
$upcoming_count = $upcoming_labs->get_result()->fetch_assoc()['count'];

// $total_equipment = $conn->query("SELECT COUNT(*) as count FROM Lab_Equipment")->fetch_assoc()['count']; // Removed
$available_labs = $conn->query("SELECT COUNT(*) as count FROM Lab")->fetch_assoc()['count'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - Lab Booking System</title>
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin: 40px;
            padding: 0 20px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 30px;
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
        
        .stat-card.upcoming {
            --accent-color: #28a745;
            --accent-color-light: #34ce57;
        }
        
        .stat-card.equipment {
            --accent-color: #17a2b8;
            --accent-color-light: #20c997;
        }
        
        .stat-card.labs {
            --accent-color: #ffc107;
            --accent-color-light: #ffdb4d;
        }
        
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, var(--accent-color), var(--accent-color-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .stat-label {
            color: #666;
            font-size: 16px;
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
        
        .quantity-badge {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
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
        
        .status-approved {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #c3e6cb;
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
                font-size: 36px;
            }
            
            .section-title {
                margin: 30px 20px 15px 20px;
                font-size: 20px;
            }
            
            table {
                width: calc(100% - 40px);
                margin: 15px 20px 30px 20px;
            }
            
            th, td {
                padding: 12px 10px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="login.php" style="display:inline-block;margin:30px 0 10px 40px;padding:10px 24px;border-radius:20px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;font-weight:600;text-decoration:none;box-shadow:0 2px 8px rgba(102,126,234,0.15);transition:background 0.2s;">← Back to Login</a>
        <!-- 👤 Student's Own Profile Section -->
        <div class="profile-section">
            <h2>👤 My Student Profile</h2>
            <div class="profile-details">
                <div class="profile-item">
                    <div class="profile-label">Name</div>
                    <div class="profile-value"><?php echo htmlspecialchars($profile['Stu_Name'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Student ID</div>
                    <div class="profile-value"><?php echo htmlspecialchars($student_id); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Semester</div>
                    <div class="profile-value"><?php echo htmlspecialchars($student_semester); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Role</div>
                    <div class="profile-value">Student</div>
                </div>
            </div>
        </div>

        <!-- 📊 Statistics Section -->
        <div class="stats-section">
            <div class="stat-card upcoming">
                <div class="stat-number"><?php echo $upcoming_count; ?></div>
                <div class="stat-label">Upcoming Labs</div>
            </div>
        </div>

        <!-- 📅 Upcoming Labs Section -->
        <h3 class="section-title">📅 My Upcoming Labs (Semester <?php echo $student_semester; ?>)</h3>
        <table>
            <tr>
                <th>Laboratory</th>
                <th>Lab Type</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Instructor</th>
                <th>Lecturer</th>
            </tr>
            <?php
            $query = "
                SELECT 
                    L.Lab_Name,
                    L.Lab_Type,
                    S.Date, 
                    S.Start_Time, 
                    S.End_Time, 
                    S.Status,
                    I.Instructor_Name,
                    Lec.Lect_Name
                FROM Lab_Shedule S
                JOIN Lab L ON S.Lab_ID = L.Lab_ID
                JOIN Instructor I ON S.Instructor_ID = I.Instructor_ID
                JOIN lecture Lec ON S.Lect_ID = Lec.Lect_ID
                WHERE S.Semester = ? AND S.Status = 'Approved'
                ORDER BY S.Date ASC, S.Start_Time ASC
            ";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $student_semester);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $lab_type_class = 'type-' . strtolower($row['Lab_Type']);
                    echo "<tr>
                        <td><span class='lab-name'>{$row['Lab_Name']}</span></td>
                        <td><span class='lab-type $lab_type_class'>" . ucfirst($row['Lab_Type']) . "</span></td>
                        <td>" . date('M d, Y', strtotime($row['Date'])) . "</td>
                        <td>{$row['Start_Time']} - {$row['End_Time']}</td>
                        <td>{$row['Instructor_Name']}</td>
                        <td>{$row['Lect_Name']}</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='6' class='no-data'>No upcoming labs scheduled for your semester</td></tr>";
            }
            ?>
        </table>

        <!-- 🔧 Lab Equipment Section -->
        <h3 class="section-title">🔧 Lab Equipment Information</h3>
        <table>
            <tr>
                <th>Laboratory</th>
                <th>Equipment</th>
            </tr>
            <?php
            $query = "
                SELECT L.Lab_Name, E.Equipment_Name
                FROM Lab_Equipment E
                JOIN Lab L ON E.Lab_ID = L.Lab_ID
                ORDER BY L.Lab_Name, E.Equipment_Name
            ";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td><span class='lab-name'>{$row['Lab_Name']}</span></td>
                        <td><span class='equipment-name'>{$row['Equipment_Name']}</span></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='2' class='no-data'>No equipment information available</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
