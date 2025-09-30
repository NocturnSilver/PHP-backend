<?php
    /*
        Sources:

        ----------------------FOR Session functions ----------------------------
        session code - COMP519 Lecture 25: PHP (PART 7) Handouts 

        ----------------------FOR Forms ---------------------------------------
        drop-down menu - COMP519 Practical 16 (php16A) - drop down menu

        ----------------------FOR DATE SORTING ------------------------
        usort() - https://www.php.net/manual/en/function.usort.php
        DateTime::createFromFormat() - https://www.php.net/manual/en/datetime.createfromformat.php

        ----------------------FOR Printing characters -----------------
        filter_input() - https://www.php.net/manual/en/function.filter-input.php and 
                       - https://www.w3schools.com/Php/func_filter_input.asp
        htmlspecialchars() - https://www.php.net/manual/en/function.htmlspecialchars.php

        --------------------- DataBase locking ------------------------
        GET_LOCK() - https://dev.mysql.com/doc/refman/8.4/en/locking-functions.html

    */

    // start a session
    session_start();

    if (isset($_SESSION['LAST_ACTIVITY']) &&
        (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        // last request was more than 30 minutes ago
        session_destroy(); // destroy session data in storage
        $_SESSION = array(); // unset session variables
    if (session_id() != "" || isset($_COOKIE[session_name()]))
        setcookie(session_name(), session_id(), time()-2592000 , '/');
    } else {
        // update last activity time stamp
        $_SESSION['LAST_ACTIVITY'] = time();
    }

    // If there is no existing session set start time
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        // session started more than 30 minutes ago
        session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
        $_SESSION['CREATED'] = time();  // update creation time
    }

    // Check if the topic has been posted previously
    if (isset($_POST['topic']) && $_POST['topic'] !== 'None') {
        $_SESSION['selected_topic'] = $_POST['topic'];
    }

    //sanitize the value with the assumption that it may be used by malicious users
    $topic_sanitized =  filter_input(INPUT_POST, "topic", FILTER_SANITIZE_SPECIAL_CHARS);

    // Retrieve stored topic from session (or default to 'None')
    $selected_topic = isset($_SESSION['selected_topic']) ? $topic_sanitized : 'None';

?>

<!DOCTYPE html>
<html lang='en-GB'>
    <head>
        <title>training</title>
    </head>
    <body>
        <h1>Training</h1>
        <?php

            // database information ----------------------------------------------------------------------------------------------------------------
            $db_hostname = "studdb.csc.liv.ac.uk";
            $db_database = "sgnsee";
            $db_username = "sgnsee";
            $db_password = "Assignment3";
            $db_charset = "utf8";
            $dsn = "mysql:host=$db_hostname;dbname=$db_database;charset=$db_charset";
            $opt = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
                );

            // catch errors and handle so that error message does not leak information -----------------------------------------------------------------
            try {

                // Establish connection with database
                $pdo = new PDO($dsn,$db_username,$db_password,$opt);

                // Retain form values after submission - sanitize time
                $time_sanitized =  filter_input(INPUT_POST, "time", FILTER_SANITIZE_SPECIAL_CHARS);
                $time =  isset($_POST['time'])  ? $time_sanitized : 'null';

                // Should not over sanitize until regexp because it will affect the regexp check
                // especially for apostrophe and other special characters that regexp might handle wrongly instead
                $name =  isset($_POST['name'])  ? $_POST['name'] : 'null';
                $email = isset($_POST['email']) ? $_POST['email'] : 'null';

                
// HTML Forms --------------------------------------------------------------------------------------------------------------------------

            // FUNCTION FOR THE 1ST DROP DOWN MENU ---------------------------------------------------------------
        
            function first_check_selected($selected_topic) {
                // This checks if there is a selected topic carried over - allows for simulating
                // the effect that the page did not change due ot first option retention
                if ($selected_topic != 'None' || $selected_topic != '')
                    // option was previously sanitized using filter therefore no htmlspecialchars() was used
                    echo "<option value='",$selected_topic,"'>",$selected_topic,"</option>";
                else{
                    // echo the default option if there was no previously selected value
                    echo "<option value='None'>Select a name</option>";
                }
            }

            function first_options($pdo, $selected_topic) {
                // This function prints out the options for the first drop down menu based on the DB values

                // option values queries from database table: train_capacity 
                $query = "select topic from train_capacity where capacity > 0 order by topic;";
                $stmt = $pdo->query($query);                         

                // Create option for the drop-down menu from the database values
                while ($row = $stmt->fetch()) {
                    if ($selected_topic != $row["topic"]) {
                        // makes sure that inputted values to be turned into html are sanitized
                        echo "<option value='", htmlspecialchars($row["topic"]),"'>",htmlspecialchars($row["topic"]),"</option>";
                    }
                } 
            }   

            function first_drop_down($pdo, $selected_topic) {
                // This generates the base for the first drop down menu
                echo "<label for='topic_label'>Topic:</label> <br>
                    <select name='topic' onChange='document.form1.submit()' id='topic_label' required='required'>
                    ";   
                
                // Check if the first-drop down menu already has a value from previous session
                first_check_selected($selected_topic);
                // Create option for the first drop-down menu
                first_options($pdo, $selected_topic);
                // echo the closing tag for the drop down menu
                echo "</select> <br>";         
            }

            // FUNCTION FOR THE 2ND OPTION ---------------------------------------------------------------------------------------            
            function convertToDateTime($time) {
                // convert the string into date time format for sorting
                return DateTime::createFromFormat("l, h:i", $time);
            }

            function second_options($pdo) {
                if (isset($_POST['topic'])) {
                    // Retrieve the topic name selected from the previous drop down menu
                    $query = "select time from train_time where topic = ? order by time;";
                    $stmt = $pdo->prepare($query);

                    // sanitize the selected topic
                    $topic_sanitized =  filter_input(INPUT_POST, "topic", FILTER_SANITIZE_SPECIAL_CHARS);
                    $stmt->execute([$topic_sanitized]);
                    
                    // Convert into date time object for a selected topic and store in array
                    $data = [];
                    while ($row = $stmt->fetch()) {
                        $data[] = ['time' => $row['time'], 'datetime' => convertToDateTime($row['time'])];
                    }

                    // sort the array chronologically once it is in date time format
                    usort($data, function ($a, $b) {return $a['datetime'] <=> $b['datetime'];});

                    // print out based on array order
                    foreach ($data as $row) {
                        echo "<option value='", htmlspecialchars($row['time']), "'>", htmlspecialchars($row['time']), "</option>";
                    }
                } 
            }

            function second_drop_down($pdo) {
                // Create the options for the second drop down menu
                echo "
                    <label for='time_label'>Schedule:</label> <br>
                    <select name='time' view='select time' id='time_label' required='required'>
                        <option value='null'>Select a time</option>";

                    // option values queries from database table: train_time 
                    second_options($pdo);

                // end tag for the second drop down menu
                echo "</select> <br>";
            }

            // Name text field functions ---------------------------------------------------------------------
            function name_text() {
                // create text field for name
                echo "<label for='name'>Name:</label>
                    <input type='text' id='name' name='name' required='required'> <br>";
            }

            // email text field functions --------------------------------------------------------------------
            function email_text() {
                // create a text field for email and end the form and fieldset
                echo "<label for='email'>Email:</label>
                    <input type='text' id='email' name='email' required='required'> <br>
                    <input type='submit' name='insert' value='submit'>
                    ";
            }


// Transaction handling functions -------------------------------------------------------------------------------------------------------------------------------

            // function to append data into a DB
            function insert_into_db($pdo, $email, $name, $selected_topic, $time) {
                // Prepare, bind values, and execute to insert into DB
                $query = "insert into bookings (email,name,topic,time) values(?,?,?,?)";
                $stmt = $pdo->prepare($query);
                $success = $stmt->execute(array($email, $name, $selected_topic, $time));
                return $success;
            }

            // Function to decrease capacity -- if successful
            function decrease_capacity($pdo,$selected_topic) {
                // Prepare statement to decremenet capacity value 
                $query = "update train_capacity set capacity = capacity - 1 where topic = ? AND capacity > 0";
                $stmt = $pdo->prepare($query);
                $success = $stmt->execute([$selected_topic]);
            }

            // function to execute on a successful booking
            function booking_success($pdo) {
                echo "Booking was successful.<br>";
                // create a static query to the bookings table
                $query = "select * from bookings";
                $stmt = $pdo->query($query); 
                // create table to show the bookings 
                echo "<table border='solid'>";
                echo "<tr><th>name</th><th>email</th><th>topic</th><th>time</th></tr>";
                // Make sure inputted data doesn't output unsanitized user input retrieved from DB
                foreach($stmt as $row) {
                    echo "<tr><td>", htmlspecialchars($row["name"]),
                    "</td><td>", htmlspecialchars($row["email"]),
                    "</td><td>", htmlspecialchars($row["topic"]),
                    "</td><td>",  htmlspecialchars($row["time"]),
                    "</td></tr>";
                } echo "</table>";
            }



            


// Database check function ---------------------------------------------------------------------------------------------------------------------


function check_all_capacity($pdo) {
    // Check table train_capacity for the topic if capacity is still greater than 0

    // Prepared statement to query for capacity
    $query = "select capacity from train_capacity";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $capacity = 0;

    // get the capacity data and sum
    foreach($stmt as $row) {
        $capacity += $row['capacity'];
    }
    
    // Check if capacity has depleted
    if ($capacity == 0) {
        return "invalid";
    } else {
        return "valid";
    }
 }


function check_session_capacity($pdo,$selected_topic) {
    // Function Check train_capacity for the topic if capacity is still greater than 0

    // Prepare statement for query
    $query = "SELECT capacity FROM train_capacity WHERE topic = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$selected_topic]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if the topic's capacity is less than or equal to 0
    if (!$row || $row['capacity'] <= 0) {
        return "invalid";
    }
    return "valid";
 }

// Security Functions ---------------------------------------------------------------------------------------------------------------------------




function check_topic($selected_topic) {
    // checks for allowed topic values from the Forms
    
    // return invalid for the following topic values ($Selected_topic has been previously sanitized)
    if ($selected_topic == "" || $selected_topic == "None" || $selected_topic == "null") {
        echo "Invalid topic value: check whether you picked a valid topic value<br>";
        return "invalid";
    } else {
        return "valid";
    }
 }


function check_time($time) {
    // checks for allowed time values from the Forms

    // return invalid for the following time values
    if ($time == "" || $time == "None" || $time == "null") {
        echo "Invalid time value: check whether you picked a valid time value<br>";
        return "invalid";
    } else {
        return "valid";
    }
 }

function validate_name($name){
    // The function validates inputted name using regular expressions 
    $two_apos_or_hyphen = "/(\'(?=[a-zA-Z\ ]{0,}[\'])|\-(?=[a-zA-Z\ ]{0,}[\-]))/";
    $name_match = "/^[a-zA-Z']([a-zA-Z\-\'\ ]*[a-zA-Z'])?$/";

    // Runs through test cases and ret
    if ($name == "") {
        echo "No Username was entered<br>"; 
        return "invalid";
    } else if (strlen($name)>50) {
        // avoid database leaking from exceeding max value
        echo "Name Exceeds max storable length for database<br>";
        return "invalid";
    } else if (preg_match($two_apos_or_hyphen, $name)) {
        echo "Two hyphens or two apostrophes in a name is not allowed.<br>"
        ."Allowed values: alphabet, one apostrophe, one hyphen, and spaces<br>";
        return "invalid";
    } else if (!preg_match($name_match, $name)) {
        echo "Name can only contain letters, hyphens, apostrophes, and spaces.<br>
              It must start with a letter or apostrophe, and cannot end with a hyphen or space.<br>";
        return "invalid";
    } else {
        return "valid";
    }
}


// function validates email
function validate_email($email) {
    // check if the inputted email satisfied the conditions and returns valid if it does
    $email_regexp = "/[a-z\.\_\-]*[a-z\_]@{1,1}[a-z\.\_\-]*[a-z\_]$/";
    if ($email == "") {
        echo "No Email was entered<br>";
        return "invalid";
    } else if (strlen($email)>320) {
        // avoid leaking database information from storing greater than allowed value
        echo "Email Exceeds max storable length for database<br>";
        return "invalid";
    }else if (!preg_match($email_regexp, $email)) {
        echo "The Email address is invalid<br>";
        return "invalid";
    } return "valid";
}

// Transaction function -----------------------------------------------------------------------------------------------------------------------

function transaction($pdo, $email, $name, $selected_topic, $time) {
    try{
        // Begin the transaction once submit button has been pressed
        $pdo->beginTransaction();

            // validation section for insertion
            if (check_topic($selected_topic) != 'invalid' 
            && check_time($time) != 'invalid'
            && validate_name($name) != 'invalid' 
            && validate_email($email) != 'invalid') {

                // Lock variables - "Other sessions cannot acquire a lock with that name until the acquiring session releases all its locks for the name."
                $lockName = "lockName" . $selected_topic;
                $timeout = 10; 

                // prepare and execute the DB locks
                $stmt = $pdo->prepare("SELECT GET_LOCK(?, ?)");
                $stmt->execute([$lockName, $timeout]);
                $lock_acquired = $stmt->fetchColumn();

                // check session capacity if for the selected topic, the capacity > 0 
                if (check_session_capacity($pdo,$selected_topic) == "valid") {
                    $success = insert_into_db($pdo, $email, $name, $selected_topic, $time);
                    // if the insertion is successful perform the following:
                    if ($success) {
                        // Deduct count from train_capacity
                        decrease_capacity($pdo,$selected_topic);
                        // Query and Print Tables
                        booking_success($pdo);
                        // Commit the changes
                        $pdo->commit();
                    // if the insertion has failed 
                    } else {
                        echo "Transaction failed while insertion: Please try again";
                    }
                // if check session is not valid
                } else {
                    echo "There are no more sessions available";
                }   
                
                // Release DB locks after transaction
                $stmt = $pdo->prepare("SELECT RELEASE_LOCK(?)");
                $stmt->execute([$lockName]);

            // if at least one input validation fails
            } else {
                "The submitted values have failed the validation test";
            } 
    } catch (PDOException $e) {
        // Rollback transaction if there is an error and do not output database information
        $pdo->rollback();
        echo "Transaction failed: Please recheck the inputted values";
    }
    
}


            
// FORM FUNCTION EXECUTION ----------------------------------------------------------------------------------------------------------------------

            // Check if there are enough sessions before making forms avaiilable
            if (check_all_capacity($pdo) == "valid") {

                echo "<form name='form1' method='post'>
                        <fieldset>";

                // First Drop Down menu for topic 
                first_drop_down($pdo, $selected_topic);
                // Second Drop Down menu for time 
                second_drop_down($pdo);
                // Name text field generating function    
                name_text();
                // Email text Field generating function
                email_text();

                echo "</fieldset>
                    </form>";

                // Check if submit button was pressed
                if (isset($_POST['insert'])) {
                    // Proceed with the transaction function 
                    transaction($pdo, $email, $name, $selected_topic, $time);
                } 
            // If there are no more available sessions do not generate forms
            } else {
                echo "All sessions are filled. There are no more sessions available.";
            }
                $pdo = NULL;
            } catch (PDOException $e) {
                // Output errors, but do not leak database informations
                echo "There was an error during the transaction";
                exit("Error: Something was wrong when displaying forms <br>");
            }
        ?>
    </body>
</html>