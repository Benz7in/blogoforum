<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My first PHP site!</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <link href="bootstrap/css/bootstrap.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px; /* 60px to make the container go all the way to the bottom of the topbar */
        }
    </style>
    <link href="bootstrap/css/bootstrap-responsive.css" rel="stylesheet">

    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <script src="jquery/jquery-1.8.0.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="js/delete_entry.js" type="text/JavaScript">
    </script>

</head>

<body>
<div class="navbar navbar-inverse navbar-fixed-top">
    <div class="navbar-inner">
        <div class="container">
            <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </a>
            <a class="brand" href="/">Blogoforum</a>
            <div class="nav-collapse collapse">
                <ul class="nav">
                    <li class="active"><a href="/">Home</a></li>
<!--                    <li><a href="">--><?//=$_SESSION['loginId']?><!-- (123fuckyou)</a></li>-->
                    <?php if (isset($_SESSION['loginId']) && ($_SESSION['loginId'] != '')): ?>
                        <li><a href="?act=logout"><?=$_SESSION['userName']?> (Logout)</a></li>
                    <?php else: ?>
                        <li><a href="?act=login">Login</a></li>
                    <?php endif ?>
                    <li><a href="?act=register">Registration</a></li>
                </ul>
            </div><!--/.nav-collapse -->
        </div>
    </div>
</div>

<div class="container">


