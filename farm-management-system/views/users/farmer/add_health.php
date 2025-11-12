<?php
session_start();
require_once '../../auth/check_role.php';
check_role('farmer');

// Database connection
$host = 'localhost';
$db_user = 'root';
$db_pass = '1234';
$db_name = 'farm';
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $animal_id = $_POST['animal_id'] ?? '';
    $check_date = $_POST['check_date'] ?? date('Y-m-d');
    $record_date = $_POST['record_date'] ?? date('Y-m-d');
    $health_status = $_POST['health_status'] ?? '';
    $treatment_status = $_POST['treatment_status'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $next_vaccination = $_POST['next_vaccination'] ?? null;

    $stmt = $conn->prepare("INSERT INTO health_records (animal_id, check_date, record_date, health_status, treatment_status, notes, next_vaccination) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('issssss', $animal_id, $check_date, $record_date, $health_status, $treatment_status, $notes, $next_vaccination);
    if ($stmt->execute()) {
        header('Location: health.php?success=1');
        exit;
    } else {
        $error = 'Failed to add health record.';
    }
}

// Fetch animals for dropdown
$animals = $conn->query("SELECT id, type, breed FROM animals");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Health Record</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f4ecd8 0%, #eaffea 100%);
            color: #4e3b1f;
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
        }
        .container {
            background: linear-gradient(135deg, #a67c52 0%, #eaffea 100%);
            border-radius: 16px;
            box-shadow: 0 6px 32px rgba(67,234,94,0.12);
            padding: 40px 32px;
            margin: 60px auto 0 auto;
            max-width: 700px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #388e3c;
            font-size: 2rem;
            margin-bottom: 22px;
            font-family: 'Poppins', serif;
            letter-spacing: 1px;
            text-align: center;
            text-shadow: 0 2px 8px #eaffea, 0 1px 0 #a67c52;
        }
        .form-group {
            margin-bottom: 18px;
            width: 100%;
        }
        label {
            color: #fffbe6;
            font-weight: 700;
            font-size: 1.05rem;
            text-shadow: 0 1px 2px #4e3b1f;
        }
        input, select, textarea {
            background: #fffbe6;
            border: 1.5px solid #a67c52;
            border-radius: 6px;
            padding: 12px;
            font-size: 1.05rem;
            color: #4e3b1f;
            margin-bottom: 10px;
            width: 100%;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #43ea5e;
            outline: none;
        }
        .btn-primary {
            background: linear-gradient(90deg, #388e3c 0%, #a67c52 100%);
            color: #fffbe6;
            border: none;
            border-radius: 6px;
            padding: 12px 22px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(67,234,94,0.08);
            margin-top: 10px;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #a67c52 0%, #388e3c 100%);
        }
        .alert-error {
            background: #fbeee6;
            color: #8d5524;
            border-left: 5px solid #a67c52;
            padding: 12px;
            margin-bottom: 16px;
            border-radius: 5px;
            font-size: 1rem;
            width: 100%;
        }
        a.btn-primary {
            display: inline-block;
            margin-top: 18px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Health Record</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="animal_id">Animal</label>
                <select name="animal_id" id="animal_id" required>
                    <option value="">Select Animal</option>
                    <?php while ($row = $animals->fetch_assoc()): ?>
                        <option value="<?php echo $row['id']; ?>">
                            <?php echo htmlspecialchars($row['type'] . ' - ' . $row['breed']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="check_date">Check Date</label>
                <input type="date" name="check_date" id="check_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="record_date">Record Date</label>
                <input type="date" name="record_date" id="record_date" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="form-group">
                <label for="health_status">Health Status</label>
                <input type="text" name="health_status" id="health_status" required>
            </div>
            <div class="form-group">
                <label for="treatment_status">Treatment Status</label>
                <input type="text" name="treatment_status" id="treatment_status">
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label for="next_vaccination">Next Vaccination Date</label>
                <input type="date" name="next_vaccination" id="next_vaccination">
            </div>
            <button type="submit" class="btn-primary">Add Record</button>
        </form>
        <br>
        <a href="health.php" class="btn-primary">Back to Health Records</a>
    </div>
</body>
</html>
