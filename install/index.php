<?php
	$actual_link = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$parts = explode('/', $actual_link);
	$root = $parts[1];

	include '../app/constants.php';
	include 'process.php';
	
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Install</title>
	<link href="../public/css/bootstrap.min.css" rel="stylesheet">
	<link href="../public/css/argon.min.css" rel="stylesheet">
	<link href="../public/css/bootstrap.dark.css" rel="stylesheet">
    <link href="../public/css/all.min.css" rel="stylesheet">
    <link href="../public/css/custom.css" rel="stylesheet">
</head>
<body>

    <div class="container" style="margin-top: 100px;">
        <div class="row">
        	<div class="col-sm-12">
				
				<?php if (!empty($errors)): ?>
				<div class="alert alert-danger">
					<p>One or more errors occured. Please correct them to proceed:</p>
					<ul>
						<?php foreach ($errors as $error): ?>
						<li><?= $error ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php endif; ?>

				<?php if (defined("site_title")) { ?>
				<div class="card shadow-sm border-success">
					<div class="card-body">
						<h3 class="text-success">Script Installed</h3>
						<p>Your website is configured and now ready to use. To login to the admin panel, use the credentials below.
							You can change the name and password via the Users page. To modify more options, 
							see <code>app/constants.php</code>.
						</p>

						<pre style="line-height:1.2em;" class="text-left">
Default Login Credentials

Username: <span class="text-primary">King Fox</span>
Password: <span class="text-primary">asd-123</span>
						</pre>

						<p class="text-danger">
							DELETE THE INSTALL FOLDER.
						</p>

						<p style="margin-top:20px;">
							<a href="../" class="btn btn-success">Home</a>
							<a href="../admin" class="btn btn-success">Admin</a>
						</p>
					</div>
				</div>
				<?php } else { ?>
				<div class="alert alert-info">
					Please make sure that all fields have been filled out properly before submitting the form. Incorrect data could lead to the script not functioning
				</div>

				<form action="index.php" method="post" style="margin-top: 30px;">
					<div class="card border-0 shadow-sm mb-3">
						<div class="card-body">
						<h5 class="text-primary">General Options</h5>
							<div class="form-group">
								<div>Website Title</div>
								<small class="grey-text">The title of your website/server. Shown in the header and in the tab.</small>
								<input type="text" name="site_title" class="form-control" required>
							</div>

							<div class="form-group">
								<div>Root Directory</div>
								<small class="grey-text">
									The path this script is located.
								</small>
								<input class="form-control" type="text" name="web_root" value="/<?php echo $root; ?>/">
							</div>
						</div>
					</div>

					<div class="card border-0 shadow-sm mb-3">
						<div class="card-body">
							<h5 class="text-primary">Database Setup</h5>
							<div class="form-group">
								<div>MySQL Host</div>
								<small class="grey-text">
									The database server IP. This is typically "localhost" unless your database server is hosted remotely.
								</small>
								<input class="form-control" type="text" name="MYSQL_HOST" value="localhost" required>
							</div>

							<div class="form-group">
								<div>MySQL Database</div>
								<small class="grey-text">
									The name of the database that's going to be used.
								</small>
								<input class="form-control" type="text" name="MYSQL_DATABASE" required>
							</div>

							<div class="form-group">
								<div>MySQL Username</div>
								<small class="grey-text">
									The username that's attached to the database you entered above.
								</small>
								<input class="form-control" type="text" name="MYSQL_USERNAME" required>
							</div>

							<div class="form-group">
								<div>MySQL Password</div>
								<small class="grey-text">
									The passwords that's attached to the username you entered above.
								</small>
								<input class="form-control" type="text" name="MYSQL_PASSWORD">
							</div>
						</div>
					</div>

					<div class="card border-0 shadow-sm mb-3">
						<div class="card-body">
						<h5 class="text-primary">User Configuration</h5>
							<div class="alert alert-primary">
								This user is disabled by default. DO NOT use unless you absolutely have to.
							</div>

							<div class="form-group">
								<div>Root Username</div>
								<small class="grey-text">
									Used for "root" access to the script. Disabled by default. 
								</small>
								<input class="form-control" type="text" name="admin_username">
							</div>

							<div class="form-group">
								<div>Root Password</div>
								<small class="grey-text">
									Used for "root" access to the admin panel. Disabled by default.
								</small>
								<input class="form-control" type="text" name="admin_password">
							</div>
							<div class="form-group">
								<button type="submit" class="btn btn-primary">Install</button>
							</div>
						</div>
					</div>
				</form>
				<?php } ?>

	        </div>
        </div>
    </div>

    <script src="../public/js/jquery-3.1.1.min.js"></script>
    <script src="../public/js/materialize.min.js"></script>
    <script src="../public/js/custom.js"></script>
</body>
