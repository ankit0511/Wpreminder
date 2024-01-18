<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminder</title>
   
    <style>
        /* Style for the modal */
        .modal-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5); /* Semi-transparent background */
            z-index: 9999;
        }

        .modal-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 20px 0px rgba(0,0,0,0.5); 
            text-align: center;
            margin-left: 500px;
            max-width: 400px; 
            width: 90%; 
            max-height: 70vh; 
            overflow-y: auto; 
        }

        .checkmark-img {
            width: 80px; 
            height: auto;
            margin-bottom: 20px;
        }


        .close-btn {
        background-color: green; 
        color: white; 
        padding: 8px 16px; 
        border-radius: 5px; 
        border: none; 
        box-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2); 
        cursor: pointer; 
    }

    
    .close-btn:hover {
        background-color: #6fd164; 
    }

    </style>
</head>
<body>
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
        $(".modal-wrapper").modal("show");

        // Close the modal when clicking the close button
        $(".close-btn").on("click", function() {
            $(".modal-wrapper").modal("hide");
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
</body>
</html>
