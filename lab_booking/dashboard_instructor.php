<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] !== 'instructor') {
    header("Location: index.php");
    exit;
}

$instructor_id = $_SESSION['id'];
$edit_id = isset($_GET['edit']) ? intval($_GET['edit']) : 0;
$edit_data = null;

// Get instructor's profile information
$profile_query = $conn->query("SELECT Instructor_Name FROM instructor WHERE Instructor_ID = $instructor_id");
$profile = $profile_query->fetch_assoc();

// Get statistics for this instructor
$total_bookings = $conn->query("SELECT COUNT(*) as count FROM Lab_booking WHERE Instructor_ID = $instructor_id")->fetch_assoc()['count'];
$pending_bookings = $conn->query("SELECT COUNT(*) as count FROM Lab_booking WHERE Instructor_ID = $instructor_id AND Status = 'Pending'")->fetch_assoc()['count'];
$approved_bookings = $conn->query("SELECT COUNT(*) as count FROM Lab_booking WHERE Instructor_ID = $instructor_id AND Status = 'Approved'")->fetch_assoc()['count'];

// Fetch lecturers
$lecturers = $conn->query("SELECT Lect_ID, Lect_Name FROM lecture");

// Handle DELETE
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM Lab_booking WHERE Booking_ID = $delete_id AND Instructor_ID = $instructor_id");
    header("Location: dashboard_instructor.php?success=deleted");
    exit;
}

// ✅ Handle CREATE or UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_booking'])) {
    $booking_id = $_POST['booking_id'];
    $lab_id = $_POST['lab_id'];
    $lab_type = $_POST['lab_type'];
    $date = $_POST['date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $semester = $_POST['semester'];
    $lecturer = $_POST['lecturer_in_charge'];

    if ($booking_id) {
        // ✅ UPDATE existing booking
        $stmt = $conn->prepare("UPDATE Lab_booking 
            SET Lab_ID=?, Lab_Type=?, Request_Date=?, Start_Time=?, End_Time=?, Semester=?, Lect_ID=? 
            WHERE Booking_ID=? AND Instructor_ID=?");
        $stmt->bind_param("issssiiii", $lab_id, $lab_type, $date, $start, $end, $semester, $lecturer, $booking_id, $instructor_id);
    } else {
        // ✅ INSERT new booking
        $status = 'Pending';
        $stmt = $conn->prepare("INSERT INTO Lab_booking 
            (Lab_ID, Lab_Type, Request_Date, Start_Time, End_Time, Semester, Lect_ID, Instructor_ID, Status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssiis", $lab_id, $lab_type, $date, $start, $end, $semester, $lecturer, $instructor_id, $status);
    }

    if ($stmt->execute()) {
        header("Location: dashboard_instructor.php?success=saved");
        exit;
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
}

// Handle FETCH for editing
if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM Lab_booking WHERE Booking_ID=? AND Instructor_ID=?");
    $stmt->bind_param("ii", $edit_id, $instructor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Dashboard - Lab Booking System</title>
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
        
        .stat-card.total {
            --accent-color: #667eea;
            --accent-color-light: #764ba2;
        }
        
        .stat-card.pending {
            --accent-color: #ffc107;
            --accent-color-light: #ffdb4d;
        }
        
        .stat-card.approved {
            --accent-color: #28a745;
            --accent-color-light: #34ce57;
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
        
        .booking-form {
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
        
        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
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
            text-decoration: none;
            display: inline-block;
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
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(108, 117, 125, 0.4);
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
        
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            text-decoration: none;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }
        
        .edit-btn {
            background: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        }
        
        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(23, 162, 184, 0.4);
        }
        
        .delete-btn {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        
        .delete-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(220, 53, 69, 0.4);
        }
        
        .status-pending {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            color: #856404;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #ffeaa7;
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
        
        .status-rejected {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid #f5c6cb;
        }
        
        .no-data {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 40px;
            font-size: 16px;
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
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                margin: 10px;
                border-radius: 15px;
            }
            
            .profile-section {
                padding: 30px 20px;
            }
            
            .back-btn {
                top: 15px;
                right: 15px;
                padding: 8px 16px;
                font-size: 12px;
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
            
            .booking-form {
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
            
            .success-message {
                margin: 15px 20px;
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- 👤 Instructor's Own Profile Section -->
        <div class="profile-section">
            <a href="index.php" class="back-btn">Back to login</a>
            <h2>👤 My Instructor Profile</h2>
            <div class="profile-details">
                <div class="profile-item">
                    <div class="profile-label">Name</div>
                    <div class="profile-value"><?php echo htmlspecialchars($profile['Instructor_Name'] ?? 'N/A'); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Instructor ID</div>
                    <div class="profile-value"><?php echo htmlspecialchars($instructor_id); ?></div>
                </div>
                <div class="profile-item">
                    <div class="profile-label">Role</div>
                    <div class="profile-value">Instructor</div>
                </div>
            </div>
        </div>

        <!-- 📊 Statistics Section -->
        <div class="stats-section">
            <div class="stat-card total">
                <div class="stat-number"><?php echo $total_bookings; ?></div>
                <div class="stat-label">Total Bookings</div>
            </div>
            <div class="stat-card pending">
                <div class="stat-number"><?php echo $pending_bookings; ?></div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-number"><?php echo $approved_bookings; ?></div>
                <div class="stat-label">Approved Requests</div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php if ($_GET['success'] == 'saved'): ?>
                    ✅ Lab booking has been successfully saved!
                <?php elseif ($_GET['success'] == 'deleted'): ?>
                    🗑️ Lab booking has been deleted.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- 📝 Lab Booking Form -->
        <h3 class="section-title">📝 <?php echo $edit_data ? 'Edit Lab Booking' : 'Create New Lab Booking'; ?></h3>
        <div class="booking-form">
            <form method="POST" action="dashboard_instructor.php">
                <input type="hidden" name="booking_id" value="<?php echo $edit_data['Booking_ID'] ?? ''; ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label>Choose Lab</label>
                        <select name="lab_id" required>
                            <option value="">-- Select Lab --</option>
                            <?php
                            $labs = $conn->query("SELECT Lab_ID, Lab_Name FROM Lab");
                            while ($lab = $labs->fetch_assoc()) {
                                $selected = ($edit_data && $edit_data['Lab_ID'] == $lab['Lab_ID']) ? "selected" : "";
                                echo "<option value='{$lab['Lab_ID']}' $selected>{$lab['Lab_Name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Lab Type</label>
                        <select name="lab_type" required>
                            <option value="">-- Select Type --</option>
                            <option value="software" <?php if ($edit_data && $edit_data['Lab_Type'] == 'software') echo 'selected'; ?>>Software</option>
                            <option value="hardware" <?php if ($edit_data && $edit_data['Lab_Type'] == 'hardware') echo 'selected'; ?>>Hardware</option>
                            <option value="network" <?php if ($edit_data && $edit_data['Lab_Type'] == 'network') echo 'selected'; ?>>Network</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" value="<?php echo $edit_data['Request_Date'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Start Time</label>
                        <input type="time" name="start_time" value="<?php echo $edit_data['Start_Time'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>End Time</label>
                        <input type="time" name="end_time" value="<?php echo $edit_data['End_Time'] ?? ''; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Semester</label>
                        <input type="number" name="semester" value="<?php echo $edit_data['Semester'] ?? ''; ?>" min="1" max="8" required>
                    </div>

                    <div class="form-group">
                        <label>Lecturer in Charge</label>
                        <select name="lecturer_in_charge" required>
                            <option value="">-- Select Lecturer --</option>
                            <?php
                            if ($lecturers) {
                                while ($lec = $lecturers->fetch_assoc()) {
                                    $selected = ($edit_data && $edit_data['Lect_ID'] == $lec['Lect_ID']) ? "selected" : "";
                                    echo "<option value='{$lec['Lect_ID']}' $selected>{$lec['Lect_Name']}</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" name="save_booking" class="btn btn-primary">
                        <?php echo $edit_data ? '💾 Update Booking' : '💾 Create Booking'; ?>
                    </button>
                    <?php if ($edit_data): ?>
                        <a href="dashboard_instructor.php" class="btn btn-secondary">❌ Cancel Edit</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- 📋 My Bookings Table -->
        <h3 class="section-title">📋 My Lab Bookings requests</h3>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Laboratory</th>
                <th>Lab Type</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $query = "
                SELECT B.Booking_ID, L.Lab_Name, L.Lab_Type, B.Request_Date, B.Start_Time, B.End_Time, B.Status
                FROM Lab_booking B
                JOIN Lab L ON B.Lab_ID = L.Lab_ID
                WHERE B.Instructor_ID = $instructor_id
                ORDER BY B.Request_Date DESC
            ";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = 'status-' . strtolower($row['Status']);
                    echo "<tr>
                        <td><strong>#{$row['Booking_ID']}</strong></td>
                        <td>{$row['Lab_Name']}</td>
                        <td>" . ucfirst($row['Lab_Type']) . "</td>
                        <td>" . date('M d, Y', strtotime($row['Request_Date'])) . "</td>
                        <td>{$row['Start_Time']} - {$row['End_Time']}</td>
                        <td><span class='$status_class'>{$row['Status']}</span></td>
                        <td>
                            <a href='dashboard_instructor.php?edit={$row['Booking_ID']}' class='action-btn edit-btn'>✏️ Edit</a>
                            <a href='dashboard_instructor.php?delete={$row['Booking_ID']}' onclick=\"return confirm('Are you sure you want to delete this booking?');\" class='action-btn delete-btn'>🗑️ Delete</a>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='no-data'>No bookings found. Create your first lab booking above!</td></tr>";
            }
            ?>
        </table>

        <!-- 📥 Lab Booking Requests from Lecturers -->
        <h3 class="section-title">📥 Lecturer approved or rejected status </h3>
        <table>
            <tr>
                <th>Booking ID</th>
                <th>Laboratory</th>
                <th>Lab Type</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Lecturer Name</th>
                <th>Status</th>
            </tr>
            <?php
            $query_lect = "
                SELECT B.Booking_ID, L.Lab_Name, L.Lab_Type, B.Request_Date, B.Start_Time, B.End_Time, Lec.Lect_Name, B.Status
                FROM Lab_booking B
                JOIN Lab L ON B.Lab_ID = L.Lab_ID
                JOIN lecture Lec ON B.Lect_ID = Lec.Lect_ID
                WHERE B.Instructor_ID = $instructor_id
                ORDER BY B.Request_Date DESC
            ";
            $result_lect = $conn->query($query_lect);
            if ($result_lect && $result_lect->num_rows > 0) {
                while ($row = $result_lect->fetch_assoc()) {
                    $status_class = 'status-' . strtolower($row['Status']);
                    echo "<tr>
                        <td><strong>#{$row['Booking_ID']}</strong></td>
                        <td>{$row['Lab_Name']}</td>
                        <td>" . ucfirst($row['Lab_Type']) . "</td>
                        <td>" . date('M d, Y', strtotime($row['Request_Date'])) . "</td>
                        <td>{$row['Start_Time']} - {$row['End_Time']}</td>
                        <td>{$row['Lect_Name']}</td>
                        <td><span class='$status_class'>{$row['Status']}</span></td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='7' class='no-data'>No lecturer booking requests found.</td></tr>";
            }
            ?>
        </table>
    </div>
</body>
</html> 