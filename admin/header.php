<!DOCTYPE html>
<html>
<head>
	<title>Admin</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- CSS link -->
	<link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx"
      crossorigin="anonymous"
    />
	<link rel="stylesheet" href="css/style.css">
	<link rel="stylesheet" href="css/media.css">
</head>
<body>
	<section class="header" id="header">
		<i class="fas fa-bars fixed" onclick="openside()"></i>
		<div class="line-fixed">Admin Panel</div>
		
		<a href="adminlogout.php">(logout)</a>
	</section>

	<div class="sidenav" id="sidenav">
		<ul class="navbar-nav">
		   <li class="nav-item">
				<a class="nav-link d" href="admin_home.php">List of Agents</a>
			</li>
			<li class="nav-item">
				<a class="nav-link d" href="createAgent.php">Create New Agent</a>
			</li>
			<li class="nav-item">
				<a class="nav-link d" href="report.php">Report Analytics</a>
			</li>
		</ul>
	</div>
	
	<!-- JS links -->
	<script
      src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.5/dist/umd/popper.min.js"
      integrity="sha384-Xe+8cL9oJa6tN/veChSP7q+mnSPaj5Bcu9mPX5F5xIGE0DVittaqT5lorf0EI7Vk"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.min.js"
      integrity="sha384-ODmDIVzN+pFdexxHEHFBQH3/9/vQ9uori45z4JjnFsRydbmQbmL5t1tQ0culUzyK"
      crossorigin="anonymous"
    ></script>
	<script src="js/script.js"></script>
	<script src="https://kit.fontawesome.com/3b83a3096d.js" crossorigin="anonymous"></script>
</body>
</html>
