<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['imageFile'])) {
    // Directory to save uploaded frames
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Save the uploaded frame
    $target_file = $target_dir . "current_frame.jpg";
    if (move_uploaded_file($_FILES['imageFile']['tmp_name'], $target_file)) {
        echo "Frame received.";
    } else {
        echo "Error uploading frame.";
    }
} else {
    echo "No image uploaded.";
}
?>
