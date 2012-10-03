<?php require('header.php')?>

<?php
    if (isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) > 0) {
        echo '<ul class="err">';
        foreach($_SESSION['ERRMSG_ARR'] as $msg) {
            echo '<li>',$msg,'</li>';
        }
        echo '</ul>';
        session_destroy();
    }
?>

<form action="?act=do-login" method="POST" class="well">
    <label>Login</label>
    <input name="login" type="text">
    <label>Password</label>
    <input name="password" type="password">
    <div style="padding-top: 10px">
        <button type="submit" class="btn">
            Login
        </button>
    </div>
</form>

<?php require('footer.php')?>