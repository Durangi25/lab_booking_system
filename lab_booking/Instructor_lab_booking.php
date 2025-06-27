<?php
session_start();
include 'db_connect.php';

if ($_SESSION['role'] !== 'instructor') {
    header("Location: index.php");
    exit;
}

$instructor_id = $_SESSION['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $lab_id = $_POST['lab_id'];
    $date = $_POST['date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    $semester = $_POST['semester'];
    $lecturer_id = $_POST['lecturer_in_charge'];

    // Check for overlapping booking
    $overlap = $conn->prepare("SELECT * FROM Lab_Shedule WHERE Lab_ID = ? AND Date = ? AND (Start_Time < ? AND End_Time > ?)");
    $overlap->bind_param("isss", $lab_id, $date, $end_time, $start_time);
    $overlap->execute();
    $conflicts = $overlap->get_result();

    if ($conflicts->num_rows > 0) {
        $message = "<p style='color:red;'>⛔ Conflict: Another booking already exists for this time.</p>";
    } else {
        // Insert new booking
        $stmt = $conn->prepare("INSERT INTO Lab_Shedule (Date, Start_Time, End_Time, Semester, Status, Instructor_ID, Lab_ID, Lect_ID)
            VALUES (?, ?, ?, ?, 'Pending', ?, ?, ?)");
        $stmt->bind_param("sssiiii", $date, $start_time, $end_time, $semester, $instructor_id, $lab_id, $lecturer_id);
        $stmt->execute();

        // Fetch lecturer info for confirmation
        $get_lecturer = $conn->query("SELECT Lect_Name FROM lecture WHERE Lect_ID = $lecturer_id");
        $lect_row = $get_lecturer->fetch_assoc();
        $lec_name = $lect_row['Lect_Name'];

        $message = "<p style='color:green;'>✅ Booking submitted. Waiting for lecturer <strong>$lec_name</strong> to approve.</p>";
    }
}

// Fetch labs and lecturers for dropdowns
$labs = $conn->query("SELECT Lab_ID, Lab_Name FROM Lab");
$lecturers = $conn->query("SELECT Lect_ID, Lect_Name FROM lecture");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Instructor Lab Booking</title>
</head>
<body>
    <h2>Welcome Instructor ID: <?php echo $instructor_id; ?></h2>
    <h3>📅 Lab Booking Form</h3>

    <?php if (isset($message)) echo $message; ?>

    <form method="POST">
        <label>Choose Lab:</label><br>
        <select name="lab_id" required>
            <option value="">Select Lab</option>
            <?php while ($lab = $labs->fetch_assoc()): ?>
                <option value="<?php echo $lab['Lab_ID']; ?>"><?php echo $lab['Lab_Name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Lecturer In-Charge:</label><br>
        <select name="lecturer_in_charge" required>
            <option value="">Select Lecturer</option>
            <?php while ($lec = $lecturers->fetch_assoc()): ?>
                <option value="<?php echo $lec['Lect_ID']; ?>"><?php echo $lec['Lect_Name']; ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Date:</label><br>
        <input type="date" name="date" required><br><br>

        <label>Start Time:</label><br>
        <input type="time" name="start_time" required><br><br>

        <label>End Time:</label><br>
        <input type="time" name="end_time" required><br><br>

        <label>Semester:</label><br>
        <input type="number" name="semester" min="1" max="8" required><br><br>

        <input type="submit" value="Submit Booking">
    </form>
</body>
</html>
