<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$database = "reminder";
$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Sorry, we failed to connect.");
}

// Reset flags on page load
if (!isset($_SESSION['insert']) || !isset($_SESSION['delete'])) {
    $_SESSION['insert'] = false;
    $_SESSION['delete'] = false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST["message"];
    $time = $_POST["time"];

    $sql = "INSERT INTO `task` (`message`, `time`) VALUES ('$message', '$time')";
    $result = mysqli_query($conn, $sql);
    if ($result) {
        $_SESSION['insert'] = true;
        header("Location: /mp/index.php?inserted=true");
        exit;
    } else {
        echo "The record was not inserted successfully because of: " . mysqli_error($conn);
    }
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM task WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id);
        $delete = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        echo "Error preparing the deletion statement: " . mysqli_error($conn);
    }

    if ($delete) {
        $_SESSION['delete'] = true;
        header("Location: /mp/index.php?deleted=true");
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="favicon.png"sizes="20x">
    <link rel="stylesheet" href="style.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    
    <title>Reminder</title>
    <style>
        /* Style for the modal */
        
    </style>
</head>
<body>
    <?php
    if ($_SESSION['insert']) {
        echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
        <strong>Success!</strong> Your record has been inserted successfully
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }

    if ($_SESSION['delete']) {
        echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
        <strong>Success!</strong> Your record has been deleted successfully
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>";
    }
    $_SESSION['insert'] = false;
    $_SESSION['delete'] = false;
    ?>

    <div class="loader"></div> 

    <div class="container">
        <div class="reminder-box">
            <h1>Create a Message Reminder</h1>
            <form id="reminderForm" action="/mp/index.php" method="post">
                <div class="input-group">
                    <label for="message">Message:</label>
                    <input type="text" id="message" name="message" required>
                </div>
                <div class="input-group">
                    <label for="datetime">Date and Time:</label>
                    <input type="datetime-local" id="time" name="time" required>
                </div>
                <div class="button-group">
                    <button type="submit" id="createReminder">Create Reminder</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal js-loading-bar" id="loadingBarModal" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="progress progress-popup">
                        <div class="progress-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Sno </th>
                    <th scope="col">Message </th>
                    <th scope="col">Time</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM `task`";
                $result = mysqli_query($conn, $sql);
                $num = 0;
                while ($row = mysqli_fetch_assoc($result)) {
                    $num += 1;
                    echo " <tr>
                        <th scope='row'>" . $num . "</th>
                        <td><b>" . $row['message'] . "</b></td>
                        <td>" . $row['time'] . "</td>
                        <td>
        <form method='GET' action='/mp/index.php'>
            <input type='hidden' name='id' value='" . $row['id'] . "'>
            <button type='submit' class='btn btn-danger'>Delete</button>
        </form>
    </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
   
</body>
</html>

<?php
date_default_timezone_set('Asia/Kolkata'); // Set the timezone to Indian Standard Time (IST)

// Database connection information
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "reminder";

function sendReminders() {
    // Create a database connection
    global $servername, $username, $password, $dbname;
    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Fetch reminders from the database
    $sql = "SELECT * FROM task WHERE sent = 0";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reminderTime = strtotime($row["time"]);
            $currentTime = time();
            $reminderTimeIST = $reminderTime + 19800; // Convert to IST (5 hours and 30 minutes)
            $currentTimeIST = $currentTime + 19800; // Convert to IST (5 hours and 30 minutes)

            if ($reminderTimeIST <= $currentTimeIST) {
                $reminderId = $row["id"];
                $message = $row["message"];

                // Send the message using Twilio
                // Replace with your Twilio configuration
                $accountSid = "ACb81e372d0fd7c1ea03f90118816c9509";
                $authToken =  "46db0289a79e21a714ea94a1057fd45a";

                // Initialize Twilio client
                require_once 'C:\xampp\htdocs\reminder\twilio-php-main\twilio-php-main\src/Twilio/autoload.php';

                $client = new Twilio\Rest\Client($accountSid, $authToken);

                // Replace with your WhatsApp numbers
                $from = "whatsapp:+14155238886";
                $to = "whatsapp:+917489887741";  // Your recipient's WhatsApp number

                $message = $client->messages->create($to, [
                    "from" => $from,
                    "body" => $message,
                ]);

                // Mark the reminder as "sent" in the database
                $updateSql = "UPDATE task SET sent = 1 WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("i", $reminderId);
                $updateStmt->execute();

                echo '
                <div class="modal-wrapper">
                <div class="modal-box">
                    <img src="gf.gif" alt="Checkmark" class="checkmark-img">
                    <h4 class="modal-title">Success!</h4>
                    <p>Your message has been sent successfully.</p>
                    <button type="button" class="close-btn">Close</button>
                </div>
            </div>
        
            <!-- Include jQuery and Bootstrap scripts -->
            <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
            <script>
                $(document).ready(function(){
                    $(".modal-wrapper").show();
        
                    // Close the modal when clicking the close button
                    $(".close-btn").on("click", function() {
                        $(".modal-wrapper").hide();
                    });
                });
            </script>';
            }
        }
    }

    // Close the database connection
    $conn->close();
}

// Call the function to start the process
sendReminders();
?>
<script> function reloadAtNextMinute() {
            var now = new Date();
            var secondsToNextMinute = 60 - now.getSeconds();
            setTimeout(function() {
                location.reload();
            }, secondsToNextMinute * 1000);
        }

        $(document).ready(function() {
            // Call the reload function initially
            reloadAtNextMinute();

            // Set interval to reload at the beginning of every minute
            setInterval(function() {
                reloadAtNextMinute();
            }, 60000); // 60000 milliseconds = 1 minute
        });

</script>