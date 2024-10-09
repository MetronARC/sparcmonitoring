<?php

// Koneksi ke database
include "../Dashboard/koneksi.php";

// Baca data
$API_KEY = $_GET['apiKey'];
$State = $_GET['State'];
$MachineID = $_GET['MachineID'];

// Validate API key
if ($API_KEY === "19403054") {
    // Validate state
    if ($State === "heartBeat") {
        // Set timezone to Asia/Jakarta
        date_default_timezone_set('Asia/Jakarta');

        // Get current time and datetime
        $currentTime = date("H:i:s");
        $currentDateTime = date("Y-m-d H:i:s");

        // Sanitize MachineID to prevent SQL injection
        $MachineID = mysqli_real_escape_string($konek, $MachineID);

        // Update lastSeen column with current DateTime
        $updateQuery = "UPDATE machine SET lastSeen = '$currentDateTime' WHERE MachineID = '$MachineID'";
        if (mysqli_query($konek, $updateQuery)) {
            echo "lastSeen updated successfully.";
        } else {
            echo "Error updating lastSeen: " . mysqli_error($konek);
        }
    } else {
        echo "State is not heartbeat";
    }
} else {
    // Invalid API key
    echo "API key invalid.";
}

// Close the database connection
mysqli_close($konek);
?>
