<?php
/**
 * AJAX API: Process Job Application & Resume Upload
 * - Validates file types (.pdf, .doc, .docx) & Max Size 5MB
 * - Stores resume in /uploads/resumes/
 * - Inserts record in `job_applications`
 * - Creates admin notification
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/notifications.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$jobId = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
$name = isset($_POST['candidate_name']) ? trim($_POST['candidate_name']) : '';
$email = isset($_POST['candidate_email']) ? trim($_POST['candidate_email']) : '';
$phone = isset($_POST['candidate_phone']) ? trim($_POST['candidate_phone']) : '';
$experience = isset($_POST['experience_years']) ? trim($_POST['experience_years']) : '';
$location = isset($_POST['current_location']) ? trim($_POST['current_location']) : '';
$noticePeriod = isset($_POST['notice_period']) ? trim($_POST['notice_period']) : '';
$portfolioUrl = isset($_POST['portfolio_url']) ? trim($_POST['portfolio_url']) : '';
$coverLetter = isset($_POST['cover_letter']) ? trim($_POST['cover_letter']) : '';

// Basic Validations
if ($jobId <= 0 || empty($name) || empty($email) || empty($phone) || empty($experience)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all required fields.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Please provide a valid email address.']);
    exit();
}

// Resume File Upload Validation
if (!isset($_FILES['resume_file']) || $_FILES['resume_file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'Please upload a valid resume file (PDF or DOCX).']);
    exit();
}

$file = $_FILES['resume_file'];
$fileSize = $file['size'];
$fileTmp = $file['tmp_name'];
$fileName = $file['name'];
$fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

// Allowed extensions & size (5MB max)
$allowedExts = ['pdf', 'doc', 'docx'];
if (!in_array($fileExt, $allowedExts)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Only PDF, DOC, and DOCX files are allowed.']);
    exit();
}

if ($fileSize > 5 * 1024 * 1024) {
    echo json_encode(['status' => 'error', 'message' => 'Resume file size exceeds the 5MB limit.']);
    exit();
}

// Upload directory
$uploadDir = __DIR__ . '/../uploads/resumes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Generate unique clean filename
$safeBaseName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($fileName, PATHINFO_FILENAME));
$uniqueFileName = 'resume_' . time() . '_' . substr(md5(uniqid()), 0, 6) . '.' . $fileExt;
$destination = $uploadDir . $uniqueFileName;

if (!move_uploaded_file($fileTmp, $destination)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save resume on server. Please try again.']);
    exit();
}

$relativeFilePath = 'uploads/resumes/' . $uniqueFileName;

$pdo = getDBConnection();
if (!$pdo) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit();
}

try {
    // 1. Fetch job title
    $stmtJob = $pdo->prepare("SELECT title FROM `jobs` WHERE `id` = ? LIMIT 1");
    $stmtJob->execute([$jobId]);
    $jobTitle = $stmtJob->fetchColumn() ?: 'General Opening';

    // 2. Insert application
    $stmt = $pdo->prepare("
        INSERT INTO `job_applications` 
        (`job_id`, `candidate_name`, `candidate_email`, `candidate_phone`, `experience_years`, `current_location`, `notice_period`, `portfolio_url`, `resume_file`, `cover_letter`, `status`)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([
        $jobId,
        $name,
        $email,
        $phone,
        $experience,
        $location,
        $noticePeriod,
        $portfolioUrl,
        $relativeFilePath,
        $coverLetter
    ]);

    // 3. Trigger Admin Notification (if notifications table exists)
    if (function_exists('addAdminNotification')) {
        addAdminNotification(
            'job_application',
            'New Job Application: ' . $name,
            "Received application for '{$jobTitle}' from {$name} ({$email}, {$experience}).",
            'admin/job-applications.php'
        );
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Your application for ' . htmlspecialchars($jobTitle) . ' has been submitted successfully! Our Talent Acquisition team will review your profile and get in touch.'
    ]);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving your application. Please try again.']);
}
