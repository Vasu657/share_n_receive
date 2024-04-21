<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page
    header("Location: login.php");
    exit;
}

// Database configuration
$host = 'localhost';
$dbname = 'share1';
$username = 'vasu@123';
$password = 'Vasu@123';

try {
    // Connect to the database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Logout logic
if (isset($_POST['logout'])) {
    // Unset all session variables
    $_SESSION = array();

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: login.php");
    exit;
}


// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $key = $_POST['key'];
    $emails = $_POST['email'];

    // Check if a file was uploaded
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Create the uploads directory if it doesn't exist
        $uploads_dir = 'uploads/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0755, true);
        }

        // Move the uploaded file to the uploads directory
        $target_file = $uploads_dir . basename($file['name']);
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            // Store file details in the database
            $stmt = $pdo->prepare("INSERT INTO file_uploads (file_key, file_name, file_path, shared_with) VALUES (?, ?, ?, ?)");
            $stmt->execute([$key, $file['name'], $target_file, $emails]);

            echo "<p class='success-message'>File uploaded successfully.</p>";
        } else {
            echo "<p class='error-message'>Error uploading file.</p>";
        }
    } else {
        echo "<p class='error-message'>Error uploading file: {$file['error']}</p>";
    }
}







// Handle upload history request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_email'])) {
    $user_email = $_POST['user_email'];
    
    // Determine if search query is provided
    if (isset($_POST['search_query'])) {
        $search_query = $_POST['search_query'];
        // Retrieve filtered upload history from the database based on search query
        $stmt = $pdo->prepare("SELECT * FROM file_uploads WHERE FIND_IN_SET(?, shared_with) AND (file_name LIKE ? OR file_key = ?)");
        $stmt->execute([$user_email, "%$search_query%", $search_query]);
    } else {
        // Retrieve all upload history
        $stmt = $pdo->prepare("SELECT * FROM file_uploads WHERE FIND_IN_SET(?, shared_with)");
        $stmt->execute([$user_email]);
    }
    
    // Fetch and return upload history as JSON response
    $upload_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($upload_history);
    exit; // Terminate script after sending JSON response
}












// Handle shared files request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email_shared'])) {
    $email_shared = $_POST['email_shared'];

    // Retrieve shared files from the database
    $stmt = $pdo->prepare("SELECT * FROM file_uploads WHERE FIND_IN_SET(?, shared_with)");
    $stmt->execute([$email_shared]);
    $shared_files = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($shared_files);
    exit; // Terminate script after sending JSON response
}




















    // Handle file request
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_email']) && isset($_POST['requested_file'])) {
    $request_email = $_POST['request_email'];
    $requested_file = $_POST['requested_file'];

    // Store file request details in the database
    $stmt = $pdo->prepare("INSERT INTO file_requests (request_email, requested_file) VALUES (?, ?)");
    $stmt->execute([$request_email, $requested_file]);

    echo "<p class='success-message'>File request sent successfully.</p>";
}



} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share 'n' Receive</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX" crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/127f9bd251.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <main>

    <form action="" method="post" onsubmit="return confirmLogout(event)">
    <button type="submit" name="logout" id="logoutButton">
        <img src="logout.png" alt="Logout">
    </button>
</form>



    
        <section class="hero animate__animated animate__fadeIn">
            <div class="social-icons">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-github"></i></a>
            </div>


            <h1>Welcome to Share 'n' Receive</h1>
            <p>Share and receive files and folders with your friends.</p>
            <div class="buttons">
                <a href="#upload-section" class="btn">Upload File</a>
                <a href="#receive-section" class="btn">Receive File</a>
                <a href="#request-section" class="btn">Request file</a>
                <a href="#history-section" class="btn">View History</a>
            </div>




        </section>








        <section id="upload-section" class="upload-section animate__animated animate__bounceIn">
            <h2>Upload File</h2>
            <form action="index.php" method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
                <div class="drag-drop-area" id="dragDropArea">
                    <div class="drag-drop-text">Drag & Drop files here or click to select</div>
                    <input type="file" name="file" id="fileInput" required class="file-input">
                </div>
                <input type="text" name="key" placeholder="Enter a key" required class="key-input">
                <input type="email" name="email" placeholder="Enter email(s) to share with (comma-separated)" required class="email-input">
                <button type="submit" class="btn upload-btn">Upload</button>
            </form>

            <div id="upload-history-section" class="upload-history-section animate__animated animate__fadeIn">
        <h2>Upload History</h2>
        <form id="uploadHistoryForm">
            <input type="email" name="user_email" placeholder="Enter your email" required class="email-input">
            <button type="submit" class="btn upload-history-btn">View Upload History</button>
        </form>
        <div class="search-container">
            <input type="text" id="uploadHistorySearchInput" placeholder="Search upload history...">
            <button onclick="searchUploadHistory()" class="btn search-btn">Search</button>
        </div>
        <div class="upload-history-list">
            <!-- Upload history will be displayed here -->
        </div>
    </div>


        </section>


        <section id="receive-section" class="receive-section animate__animated animate__bounceIn">
            <h2>Receive File</h2>
            <form action="index.php" method="post" class="receive-form">
                <input type="text" name="key" placeholder="Enter the key" required class="key-input">
                <button type="submit" class="btn receive-btn">Receive</button>
            </form>
        </section>


        <section id="shared-files-section" class="shared-files-section animate__animated animate__fadeIn">
            <h2>Shared Files</h2>
            <form action="index.php" method="post" class="shared-files-form">
                <input type="email" name="email_shared" placeholder="Enter your email" required class="email-input">
                <button type="submit" class="btn shared-files-btn">View Shared Files</button>
            </form>
            <div class="shared-files-list">
                <!-- Shared files will be displayed here -->
            </div>
        </section>





        <section id="request-section" class="request-section animate__animated animate__bounceIn">
            <h2>Request File</h2>
            <form action="index.php" method="post" class="request-form">
                <input type="email" name="request_email" placeholder="Enter your email" required class="email-input">
                <input type="text" name="requested_file" placeholder="Enter the file key" required class="file-key-input">
                <button type="submit" class="btn request-btn">Request</button>
            </form>
        </section>




          <!-- Cookie consent banner -->
  <div id="cookieConsent">
    We use cookies to ensure you get the best experience on our website. By clicking "Accept Cookies", you agree to our use of cookies.
    <a href="#" id="learnMoreLink">Learn more</a>
    <button id="manageCookiesBtn">Manage Cookies</button>
    <button id="acceptCookiesBtn">Accept Cookies</button>
  </div>

<!-- Cookie settings modal -->
<div id="cookieSettingsModal" style="display: none;">
  <div class="modal-content">
    <span class="close">&times;</span>
    <h2>Cookie Settings</h2>
    <p>Here you can manage your cookie preferences:</p>
    <div class="cookie-options">
      <label class="checkbox-container">
        <input type="checkbox" name="essential" checked disabled> Essential Cookies
        <span class="checkmark"></span>
      </label>
      <label class="checkbox-container">
        <input type="checkbox" name="analytics"> Analytics Cookies
        <span class="checkmark"></span>
      </label>
      <label class="checkbox-container">
        <input type="checkbox" name="advertising"> Advertising Cookies
        <span class="checkmark"></span>
      </label>
    </div>
    <button id="saveCookieSettings">Save Settings</button>
  </div>
</div>






        
    </main>

    <script src="script.js"></script>
</body>
</html>