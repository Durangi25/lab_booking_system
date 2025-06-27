<?php
session_start();
include 'db_connect.php';

// Ensure only lectures can access
if ($_SESSION['role'] !== 'lecture') {
    header("Location: index.php");
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$lect_id = $_SESSION['id'];

// === Handle Approval ===
if (isset($_GET['approve'])) {
    $booking_id = intval($_GET['approve']);
    
    // Get booking details
    $booking_query = $conn->prepare("SELECT Lab_ID, Request_Date, Start_Time, End_Time, Semester, Instructor_ID, Lect_ID FROM Lab_booking WHERE Booking_ID = ? AND Lect_ID = ?");
    $booking_query->bind_param("ii", $booking_id, $lect_id);
    $booking_query->execute();
    $booking_result = $booking_query->get_result();
    
    if ($booking_result->num_rows > 0) {
        $booking_data = $booking_result->fetch_assoc();
        
        // Check if this schedule already exists
        $check = $conn->prepare("SELECT 1 FROM Lab_Shedule WHERE Lab_ID = ? AND Date = ? AND Start_Time = ? AND End_Time = ?");
        $check->bind_param("isss", $booking_data['Lab_ID'], $booking_data['Request_Date'], $booking_data['Start_Time'], $booking_data['End_Time']);
        $check->execute();
        $check->store_result();
        
        if ($check->num_rows > 0) {
            // Schedule already exists
            header("Location: dashboard_lecture.php?error=duplicate");
            exit;
        }
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Update booking status to Approved
            $update_booking = $conn->prepare("UPDATE Lab_booking SET Status = 'Approved' WHERE Booking_ID = ? AND Lect_ID = ?");
            $update_booking->bind_param("ii", $booking_id, $lect_id);
            $update_booking->execute();
            
         
            $insert_schedule = $conn->prepare("
    INSERT INTO Lab_Shedule 
    (Lab_ID, Semester, Start_Time, End_Time, Date, Instructor_ID, Lect_ID, Status) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 'Approved')
");
$insert_schedule->bind_param("iisssii",
    $booking_data['Lab_ID'],
    $booking_data['Semester'],
    $booking_data['Start_Time'],
    $booking_data['End_Time'],
    $booking_data['Request_Date'],
    $booking_data['Instructor_ID'],
    $booking_data['Lect_ID']
);
            $insert_schedule->execute();
            
            // Commit transaction
            $conn->commit();
            
            header("Location: dashboard_lecture.php?success=approved");
            exit;
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            header("Location: dashboard_lecture.php?error=failed");
            exit;
        }
    } else {
        header("Location: dashboard_lecture.php?error=notfound");
        exit;
    }
}

// === Handle Rejection ===
if (isset($_GET['reject'])) {
    $booking_id = intval($_GET['reject']);
    $conn->query("UPDATE Lab_booking SET Status = 'Rejected' WHERE Booking_ID = $booking_id AND Lect_ID = $lect_id");
    header("Location: dashboard_lecture.php?success=rejected");
    exit;
}

$profile_query = $conn->query("SELECT Lect_Name FROM lecture WHERE Lect_ID = $lect_id");
$profile = $profile_query->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lecturer Dashboard - Lab Booking System</title>
    <style>
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
        
        .back-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            z-index: 10;
        }
        
        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        
        .back-btn::before {
            content: '← ';
            margin-right: 5px;
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
        .profile-header {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
        }
        .profile-header .icon {
            font-size: 38px;
            background: rgba(255,255,255,0.18);
            border-radius: 50%;
            padding: 10px;
            box-shadow: 0 2px 8px rgba(102,126,234,0.10);
        }
        .profile-header h2 {
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }
        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 10px;
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
        .section-title {
            display: flex;
            align-items: center;
            font-size: 22px;
            font-weight: 700;
            color: #764ba2;
            margin: 32px 0 16px 0;
            letter-spacing: 0.5px;
        }
        .section-title .icon {
            font-size: 22px;
            margin-right: 10px;
        }
        .section-title .line {
            flex: 1;
            height: 3px;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            margin-left: 16px;
            border-radius: 2px;
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
        .stat-card.pending {
            --accent-color: #ffc107;
            --accent-color-light: #ffdb4d;
        }
        .stat-card.approved {
            --accent-color: #28a745;
            --accent-color-light: #34ce57;
        }
        .stat-card.rejected {
            --accent-color: #dc3545;
            --accent-color-light: #e74c3c;
        }
        .stat-number {
            font-size: 40px;
            font-weight: 800;
            margin-bottom: 10px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .stat-label {
            font-size: 15px;
            color: #555;
            font-weight: 600;
        }
        .success-message {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 18px 0 24px 0;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,184,148,0.10);
        }
        .error-message {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: white;
            border-radius: 10px;
            padding: 14px 18px;
            margin: 18px 0 24px 0;
            font-size: 15px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(238,90,82,0.10);
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(102,126,234,0.08);
            margin-bottom: 32px;
        }
        th, td {
            padding: 14px 12px;
            font-size: 15px;
            text-align: left;
        }
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            font-weight: 700;
            border-bottom: 2px solid #e0e7ff;
        }
        tr:nth-child(even) td {
            background: #f3f6fd;
        }
        tr:hover td {
            background: #e0e7ff;
        }
        .no-data {
            text-align: center;
            color: #888;
            font-size: 16px;
            padding: 24px 0;
        }
        .action-btn {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 700;
            margin-right: 8px;
            text-decoration: none;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            box-shadow: 0 2px 8px rgba(102,126,234,0.10);
        }
        .approve-btn {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: #fff;
            border: none;
        }
        .approve-btn:hover {
            background: linear-gradient(135deg, #00a085 0%, #00b894 100%);
            color: #fff;
        }
        .reject-btn {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
            color: #fff;
            border: none;
        }
        .reject-btn:hover {
            background: linear-gradient(135deg, #ee5a52 0%, #ff6b6b 100%);
            color: #fff;
        }
        @media (max-width: 900px) {
            .container { padding: 12px; }
            .profile-details { grid-template-columns: 1fr; gap: 12px; }
            .stats-section { grid-template-columns: 1fr; gap: 12px; }
            
            .back-btn {
                top: 15px;
                right: 15px;
                padding: 8px 16px;
                font-size: 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-section">
            <div class="profile-header">
                <span class="icon">👨‍🏫</span>
                <h2>My Lecturer Profile - <?php echo htmlspecialchars($profile['Lect_Name'] ?? ''); ?></h2>
            </div>
            <div class="profile-details">
                <div class="profile-item">
                    <div class="profile-label">Name</div>
                    <div class="profile-value"><?php echo htmlspecialchars($profile['Lect_Name'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Lecturer ID</div>
                    <div class="profile-value"><?php echo htmlspecialchars($lect_id); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Role</div>
                    <div class="profile-value">Lecturer</div>
                </div>
            </div>
            <a href="index.php" class="back-btn">Back to login</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php if ($_GET['success'] == 'approved'): ?>
                    ✅ Lab booking approved and scheduled successfully!
                <?php elseif ($_GET['success'] == 'rejected'): ?>
                    ❌ Lab booking rejected.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error'])): ?>
            <div class="error-message">
                <?php if ($_GET['error'] == 'failed'): ?>
                    ❌ Failed to process booking. Please try again.
                <?php elseif ($_GET['error'] == 'notfound'): ?>
                    ❌ Booking not found or you don't have permission to approve it.
                <?php elseif ($_GET['error'] == 'duplicate'): ?>
                    ❌ This lab schedule already exists.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <h3 class="section-title"><span class="icon">📥</span> Instructor Lab Booking Requests <span class="line"></span></h3>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Laboratory</th>
                <th>Lab Type</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Instructor</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $query = "
                SELECT B.Booking_ID, L.Lab_Name, L.Lab_Type, B.Request_Date, B.Start_Time, B.End_Time, I.Instructor_Name, B.Status
                FROM Lab_booking B
                JOIN Lab L ON B.Lab_ID = L.Lab_ID
                JOIN Instructor I ON B.Instructor_ID = I.Instructor_ID
                WHERE B.Status = 'Pending' AND B.Lect_ID = $lect_id
                ORDER BY B.Request_Date DESC
            ";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['Booking_ID']}</td>
                        <td>{$row['Lab_Name']}</td>
                        <td>{$row['Lab_Type']}</td>
                        <td>" . date('M d, Y', strtotime($row['Request_Date'])) . "</td>
                        <td>{$row['Start_Time']} - {$row['End_Time']}</td>
                        <td>{$row['Instructor_Name']}</td>
                        <td>{$row['Status']}</td>
                        <td>
                            <a href='?approve={$row['Booking_ID']}' class='action-btn approve-btn'>✅ Approve</a>
                            <a href='?reject={$row['Booking_ID']}' class='action-btn reject-btn'>❌ Reject</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='no-data'>No pending instructor booking requests.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html>
