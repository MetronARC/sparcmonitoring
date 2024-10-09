<?php
// Include database connection
include "../Dashboard/koneksi.php";

// Read input parameters
$Area = $_GET['Area'];
$UID = $_GET['UID'];
$Status = $_GET['Status'];
$Mode = $_GET['Mode'];
$MachineID = $_GET['MachineID'];
$WeldID = $_GET['weldID'];

$Name = getEmployeeName($konek, $UID);

// Function to get employee name by UID
function getEmployeeName($konek, $UID)
{
    $query = "SELECT Name FROM employee WHERE cardUID = '$UID'";
    $result = mysqli_query($konek, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $data = mysqli_fetch_array($result);
        return $data['Name'];
    }
    
    return ''; // Return empty string if no data found or query fails
}

// Function to execute query and handle errors
function executeQuery($konek, $query)
{
    if (mysqli_query($konek, $query)) {
        echo "Query executed successfully: $query\n";
    } else {
        echo "Error executing query: " . mysqli_error($konek) . "\n";
    }
}

// Set timezone and get current date and time
date_default_timezone_set('Asia/Jakarta');
$Date = date('Y-m-d');
$Time = date('H:i:s');

// Debugging: Print received parameters
echo "Area: $Area, UID: $UID, Status: $Status, Mode: $Mode, MachineID: $MachineID, WeldID: $WeldID\n";

// Check if the area is valid
if (in_array($Area, ["1", "2", "3", "4", "5"])) {

    // Get maximum ID for the specified area
    $sql = "SELECT MAX(ID) as max_id FROM area$Area";
    $result = mysqli_query($konek, $sql);

    // Handle different statuses
    if ($Status == "Inactive") {
        // Check if a row with the same MachineID exists
        $checkQuery = "SELECT * FROM area$Area WHERE MachineID = '$MachineID'";
        $checkResult = mysqli_query($konek, $checkQuery);

        if (mysqli_num_rows($checkResult) > 0) {
            // Update the row if the MachineID exists
            $updateQuery = "UPDATE area$Area SET WeldID = '$WeldID', Date = '$Date' WHERE MachineID = '$MachineID'";
            executeQuery($konek, $updateQuery);
        } else {
            // Insert a new row if the MachineID does not exist
            $insertQuery = "INSERT INTO area$Area (Area, UID, Status, Mode, MachineID, WeldID, Date, Name) 
                            VALUES ('$Area', '$UID', '$Status', '$Mode', '$MachineID', '$WeldID', '$Date', '$Name')";
            executeQuery($konek, $insertQuery);
        }
    } elseif ($Status == "Active") {
        // Update area for 'Active' status
        $updateQuery = "UPDATE area$Area 
                        SET Name = '$Name', UID = '$UID', Status = '$Status', Mode = '$Mode', MachineID = '$MachineID', WeldID = '$WeldID', Date = '$Date', State = 'IDLE' 
                        WHERE MachineID = '$MachineID'";
        executeQuery($konek, $updateQuery);

        // Handle login and logout modes
        if ($Mode == "Login") {
            $loginQuery = "UPDATE area$Area SET Login = '$Time' WHERE MachineID = '$MachineID'";
            executeQuery($konek, $loginQuery);
        } elseif ($Mode == "Logout") {
            $logoutQuery = "UPDATE area$Area SET Logout = '$Time' WHERE MachineID = '$MachineID'";
            executeQuery($konek, $logoutQuery);
        }
    } elseif ($Status == "Done") {
        // Handle 'Done' status

        // Retrieve WeldID from the area
        $queryWeldID = "SELECT WeldID FROM area$Area WHERE MachineID = '$MachineID'";
        $resultWeldID = mysqli_query($konek, $queryWeldID);

        if ($resultWeldID && mysqli_num_rows($resultWeldID) > 0) {
            $dataWeldID = mysqli_fetch_array($resultWeldID);
            $WeldID = $dataWeldID['WeldID'];

            // Delete data from the area
            $deleteQuery = "DELETE FROM area$Area WHERE MachineID = '$MachineID'";
            executeQuery($konek, $deleteQuery);

            // Increment WeldID in the machine table
            $incrementWeldIDQuery = "UPDATE machine SET WeldID = WeldID + 1 WHERE MachineID = '$MachineID'";
            executeQuery($konek, $incrementWeldIDQuery);

            // Calculate the total ArcTime from machinehistory1
            $arcTotalQuery = "SELECT SEC_TO_TIME(SUM(TIME_TO_SEC(ArcTotal))) AS totalArcTime FROM machinehistory1 WHERE WeldID = '$WeldID'";
            $resultArcTotal = mysqli_query($konek, $arcTotalQuery);

            if ($resultArcTotal && mysqli_num_rows($resultArcTotal) > 0) {
                $totalArcTime = mysqli_fetch_array($resultArcTotal)['totalArcTime'];
                
                // Update the corresponding row with the calculated totalArcTime (optional depending on use case)
                $updateTotalTimeQuery = "UPDATE area$Area SET upTime = '$totalArcTime' WHERE WeldID = '$WeldID'";
                executeQuery($konek, $updateTotalTimeQuery);
            } else {
                echo "Error calculating total ArcTime: " . mysqli_error($konek) . "\n";
            }
        } else {
            echo "Error retrieving WeldID: " . mysqli_error($konek) . "\n";
        }
    }
} else {
    echo "Invalid area!";
}
?>
