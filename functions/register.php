<html>
<title>New War Game</title>
<head>
	<link rel="stylesheet" type="text/css" href="mainDesign.css">
	<script type="text/javascript">
		function validateForm()
			{
				var a=document.forms["reg"]["username"].value;
				var b=document.forms["reg"]["password"].value;
				if ((a==null || a=="") && (b==null || b==""))
				{
					alert("All Field must be filled out");
					return false;
				}
				if (a==null || a=="")
				{
					alert("Username must be filled out");
					return false;
				}
				if (b==null || b=="")
				{
					alert("Password must be filled out");
					return false;
				}
			}
</script>
</head>
<body bgcolor="#1A0000">
	<div class="Container">
	
		<div class="header">
		<h1> DeadFall Register</h1>
		</div>
		
		<div class="login">
		<b>Register Details</b>
			
			<div class="login-top">
			
				<form action="signup.php" name="reg" method="post" onsubmit="return validateform()">
					<input type="text" value="Username" name="username" onfocus="if (this.value == 'Username') {this.value='';}" onblur="if (this.value == '') {this.value = 'Username';}"></br>
					<input type="password" id="pass" value="password" name="password" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'password';}"></br>
					<input type="password" id="conf-pass" value="password" name="conf-password" onfocus="this.value = '';" onblur="if (this.value == '') {this.value = 'password';}"></br>
					<a href="login.php" class="forgot">Already Signed Up?</a></br>
					<input type="submit" name="submit" value="Register Now!">
				</form>
				
			</div>
			
		</div>
	
	</div>
</body>
</html>

