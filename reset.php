<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

// Print out on webpage current SESSION, POST and GET array values
function dbgSess()
{
    echo '<br> SESSION : <br>';
    var_dump($_SESSION);
    echo '<br> POST : <br>';
    var_dump($_POST);
    echo '<br> GET : <br>';
    var_dump($_GET);
    echo '<br><br>';
} // End of dbgSess()
 
// Include config file
require_once "config.php";
 
// Define variables and initialize with empty values
$new_password = $confirm_password = "";
$new_password_err = $confirm_password_err = "";
 
// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Validate new password
    if(empty(trim($_POST["new_password"]))){
        $new_password_err = "Please enter the new password.";     
    } elseif(strlen(trim($_POST["new_password"])) < 6){
        $new_password_err = "Password must have atleast 6 characters.";
    } else{
        $new_password = trim($_POST["new_password"]);
    }
    
    // Validate confirm password
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Please confirm the password.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($new_password_err) && ($new_password != $confirm_password)){
            $confirm_password_err = "Password did not match.";
        }
    }
        
    // Check input errors before updating the database
    if(empty($new_password_err) && empty($confirm_password_err)){
        // Prepare an update statement
        $sql = "UPDATE user SET password = ? WHERE userid = ?";
        
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "si", $param_password, $param_userid);
            
            // Set parameters
            $param_password = password_hash($new_password, PASSWORD_DEFAULT);
            $param_userid = $_SESSION["userid"];
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Password updated successfully. Destroy the session, and redirect to login page
                session_destroy();
                header("location: index.php");
                exit();
            } else{
                echo "Oops1! Something went wrong. Please try again later.";
            }

            // Close statement
            mysqli_stmt_close($stmt);
        }
    }
    
    // Close connection
    mysqli_close($link);
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <style>
	body {
		background-image: url('paper.jpg');
        	font: 16px sans-serif;
	}
	.wrapper{
		margin: 0px 5px 0px 5px;
	}
	table > tbody > tr > td {
		vertical-align: middle;
		text-align: left;
		padding: 5px;
	}
	ol > li {
		font-size: 18px;
	}
	.table > tbody > tr > td {
		vertical-align: middle;
		text-align: center;
		padding: 5px;
	}
    </style>
</head>
<body>
     <br>
    <div class="wrapper">
    <div class="container">
	<p>
	<table class="table-success table-bordered table-responsive" style="background-color: #FBEEE6;">
	<tbody>
	<tr><td>
		Hi, <b><?php echo htmlspecialchars($_SESSION["fname"]) . " " .  htmlspecialchars($_SESSION["lname"]) .
		" (" . ( htmlspecialchars($_SESSION["email"]) ) . ") "; ?></b>.
	</td></tr>
	<tr><td> Please fill out this form to reset your password.
	</td></tr>
	</tbody>
	</table>
	</p>
    </div>
    </div>


    <div class="wrapper">
    <div class="container">
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"> 
	<table class="table-success table-bordered table-responsive" style="background-color: #FBEEE6;">
	<tbody>
	<tr><td>
                <label>New Password</label><br>
		<input type="password" name="new_password" class="form-select
			<?php echo (!empty($new_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $new_password; ?>">
                <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
	</td></tr>
	<tr><td>
                <label>Confirm Password</label><br>
                <input type="password" name="confirm_password" class="form-select <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
	</td></tr>
	<tr><td>
                <input type="submit" class="btn btn-primary" value="Submit" style>
                <a class="btn btn-link ml-2" href="index.php">Cancel</a>
	</td></tr>
	</tbody>
	</table>
        </form>
    </div>    
    </div>    
</body>
</html>
