
<!DOCTYPE html>

<html>
	<head>
		<link href="<?= base_url() ?>/css/template.css" rel="stylesheet">
		<style>
			input {
				display: block;
			}
		</style>

	</head> 
<body>  
	<h1>Password Recovery</h1>
	
	<p>Please check your email for your new password.
	</p>
	
	
	
<?php 
	if (isset($errorMsg)) {
		echo "<p>" . $errorMsg . "</p>";
	}

	echo "<p>" . anchor('account/index','Login') . "</p>";
?>	
</body>

</html>

