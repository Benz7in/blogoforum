<?php require('header.php')?>

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