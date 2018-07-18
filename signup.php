<?php
	session_start();
	Include('connect.php');
	$addusername=$_POST['username'];
	$addpassword=$_POST['password'];
	
	if(isset($_POST['username'])){
    if(empty($_POST['username']) || empty($_POST['password']))
	{
        echo '<b>Please fill out all fields.</b>';

    }
	/*elseif($_POST['password'] != $_POST['conf_pass']){
        echo '<b>Your Passwords do not match.</b>';
    }*/
	else
	{

        $dup = mysqli_query($con, "SELECT username FROM userstable WHERE username='".$_POST['username']."'");
        if(mysqli_num_rows($dup) >0){
            echo '<b>username Already Used.</b>';
        }
		elseif($_POST['password'] != $_POST['conf-password'])
		{
        echo '<b>Your Passwords do not match.</b>';
		}
		elseif($_POST['password'] == "password")
		{
			echo '<b>Must Designate a Password</b>';
		}
        else{
            
            $sql = mysqli_query($con, "INSERT INTO userstable VALUES(NULL, '$_POST[username]', '$_POST[password]')");     
            if($sql){
                 echo '<b>Congrats, You are now Registered.</b>';
            }
            else{
                echo '<b>Error Registeration.</b>';
            }
        }
    }
}

	
	
	/*mysqli_query($con, "INSERT INTO userstable (username, password) VALUES ('$addusername','$addpassword')");*/

?>