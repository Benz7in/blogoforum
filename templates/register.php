<?php require('header.php')?>

<?php
    if (isset($_SESSION['ERRMSG_ARR']) && is_array($_SESSION['ERRMSG_ARR']) && count($_SESSION['ERRMSG_ARR']) > 0) {
        echo '<ul class="err">';
        foreach($_SESSION['ERRMSG_ARR'] as $msg) {
            echo '<li>',$msg,'</li>';
        }
        echo '</ul>';
        unset($_SESSION['ERRMSG_ARR']);
    }
?>

<form action="?act=do-register" id="loginForm" name="loginForm" method="post">
    <table width="300" border="0" align="center" cellpadding="2" cellspacing="0">
        <tr>
            <th>First Name </th>
            <td><input name="fname" type="text" class="textfield" id="fname" /></td>
        </tr>
        <tr>
            <th>Last Name </th>
            <td><input name="lname" type="text" class="textfield" id="lname" /></td>
        </tr>
        <tr>
            <th width="124">E-mail</th>
            <td width="168"><input name="email" type="text" class="textfield" id="email" /></td>
        </tr>
        <tr>
            <th width="124">Login</th>
            <td width="168"><input name="login" type="text" class="textfield" id="login" /></td>
        </tr>
        <tr>
            <th>Password</th>
            <td><input name="password" type="password" class="textfield" id="password" /></td>
        </tr>
        <tr>
            <th>Confirm Password </th>
            <td><input name="cpassword" type="password" class="textfield" id="cpassword" /></td>
        </tr>
        <tr>
            <td>&nbsp;</td>
            <td><input type="submit" name="Submit" value="Register" /></td>
        </tr>
    </table>
</form>

<?php require('footer.php')?>