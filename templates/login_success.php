<?php
session_start();
if(!session_is_registered(loginId)){
    header("Location: templates/login.php");
} else {
    header("Location: /");
}
?>
<html>
<body>
Login Successful
</body>
</html>