<?php
session_start();   
if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit();
}   
// User is logged in, proceed to show the upload content here       
$displayName = htmlspecialchars($_SESSION['user']['fullname'], ENT_QUOTES, 'UTF-8');    

if($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle the file upload
    $targetDir = "uploadfile/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true); // Create uploads directory if it doesn't exist
    }
    $targetFile = $targetDir . basename($_FILES["fileUploading"]["name"]);
    if (move_uploaded_file($_FILES["fileUploading"]["tmp_name"], $targetFile)) {
        echo "The file ". htmlspecialchars(basename($_FILES["fileUploading"]["name"])). " has been uploaded.";
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>      
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Page</title>
</head>
<body>
    <h1>Upload Profile picture</h1>
    <p>Welcome, <?php echo $displayName; ?>! Use the form below to upload your profile picture.</p>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        <label for="fileUpload">Choose a file to upload:</label>
        <input type="file" name="fileUploading" id="fileUpload" required>
        <button type="submit">Upload</button>
    </form>
</body>
</html> 


