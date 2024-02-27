<!--HUSNAIN MEHDI/342322-->

<!-- PHP Code for creating database, table, and inserting values -->
<?php

        // Defining the server variables
        $servername = "localhost";
        $username = "username";
        $password = "password";
        $dbState = false;

        $connection = mysqli_connect($servername, $username, $password);

        //Checking if database already exists
        $sql = "SHOW DATABASES;";
        $res = mysqli_query($connection, $sql);
        if (mysqli_num_rows($res)>0){
            while($entry = mysqli_fetch_assoc($res)){
                if ($entry["Database"] == "dictionarydb"){
                    $dbState = true;
                }
            }
        }
        mysqli_close($connection);

        //Database will only be created if it does not exist
        if (!$dbState){

            // Establishing the connection with the database
            $con = mysqli_connect($servername, $username, $password);

            if (!$con){
                die("Connection failed: " . mysqli_connect_error());
            }
            
            //SQL query for creating the database
            $query = "CREATE DATABASE dictionaryDB";
            if (!mysqli_query($con, $query)){
                echo "<script>alert('Error Creating Database');</script>";
            }
            
            //Closing the database connection
            mysqli_close($con);

            //Defining the name of the database
            $dbName = "dictionaryDB";

            //Establishing another connection for creating table and inserting values
            $con1 = mysqli_connect($servername, $username, $password, $dbName);

            //SQL query for creating table
            $query1 = "CREATE TABLE dictionary (
                words varchar(255),
                meanings varchar(255)
            ); ";
            mysqli_query($con1, $query1);

            //Converting the JSON file into an associative array
            $data = file_get_contents('dictionary.json');
            $data_array = json_decode($data, true);

            //Looping through the array and inserting the data into the database
            foreach ($data_array as $key => $value){
                $escaped_key = mysqli_real_escape_string($con1, $key);
                $escaped_value = mysqli_real_escape_string($con1, $value);
                $query2 = "INSERT INTO `dictionary` (`words`, `meanings`) VALUES ('$escaped_key', '$escaped_value'); ";
                mysqli_query($con1, $query2);
            }
            
            //Closing the connection
            mysqli_close($con1);
        }
        
    ?>

<!-- HTML Code -->
<!DOCTYPE html>
<html>
<head>
    <title>Webster's Dictionary Search</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    
    <h1>My Dictionary Search</h1>
    <!-- Creating the from -->
    <form method="post">
        <label for="searchTerm">Search a Word:</label>
        <input type="text" name="searchTerm" id="searchTerm" placeholder="Search...">
        <button type="submit">Go</button>
    </form>
   
    <!-- PHP Code for comparing user inputs with the data in our database -->
    <?php

        //Establishing the connection with the database
        $dbName = "dictionaryDB";
        $con2 = mysqli_connect($servername, $username, $password, $dbName);

        //Fetching user input through the server
        if ($_SERVER["REQUEST_METHOD"] === "POST"){

            //Getting rid of all the whitespaces
            $searchItem = preg_replace('/\s+/', '', $_POST['searchTerm']);

            //Checking if the user input is empty
            if (!empty($searchItem)){

                //SQL query for comparing the input with our data
                $query3 = "SELECT * FROM `dictionary` WHERE `words` LIKE '$searchItem'";
                $result = mysqli_query($con2, $query3);
                $row = mysqli_fetch_assoc($result);

                //Checking whether the input matches with our data in the database if not sending an alert to the user
                if (!empty($row)){
                    $word = $row["words"];
                    $meaning = $row["meanings"];
                    echo "<h2>Search Results:</h2>";
                    echo "<p id='message'><strong>{$word}:</strong> {$meaning}</p>";
                }
                else {
                    echo "<script>alert('Word not found! Search some other word.');</script>";
                }
            }

            //Sending alert to the user if the search bar input is empty
            elseif (empty($searchItem)){
                echo "<script>alert('Please enter a word to search.');</script>";
            }
            
        }
        //Closing the connection
        mysqli_close($con2);
    ?>
</body>
</html>
