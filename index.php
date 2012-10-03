<?php
/**
 * Created by JetBrains PhpStorm.
 * User: v.pumpur
 * Date: 23/08/12
 * Time: 07:01
 * To change this template use File | Settings | File Templates.
 */
header('Content-type: text/html; charset=UTF-8');
mb_internal_encoding('UTF-8');

//model
$mysqli = new mysqli('localhost','root','') or die("Can't connect to the DB!");
$mysqli->set_charset('utf8');
$mysqli->select_db('bf') or die("Can't select the DB!");

function getUserCredits($login, $password) {
    global $mysqli;
    $result = $mysqli->query(
        "SELECT * FROM Members
        WHERE login='$login'
        and password='".md5($password)."'");

    if ($result) {
        if ($result->num_rows == 1) {
//echo " 26 ";
            $row = $result->fetch_assoc();
        } else {
//echo " 29 ";
            return null;
        }
        @mysql_free_result($result);
    } else {
        die("Query failed. Please try again");
    }
//echo " 36 ";
    return $row;
}

function getUserCreditsByLoginOrEmail($login, $email) {
    global $mysqli;
    $result = $mysqli->query(
        "SELECT * FROM Members
        WHERE login='$login'
        OR email='$email'");
    if ($result) {
        $row = $result->fetch_assoc();
    } else {
        die("Query failed. Please try again");
    }
    return $row;
}

function registerMember($fname, $lname, $email, $login, $password) {
    global $mysqli;
    $statement = $mysqli->prepare(
        "INSERT INTO members(firstname, lastname, email, login, password)
        VALUES(?,?,?,?,?)"
    );
    $statement->bind_param('sssss', $fname, $lname, $email, $login, md5($password));
    return $statement->execute();
}

function getEntriesList($limit, $offset) {
    global $mysqli;
    $records = array();
    $cursor = $mysqli->query(
        "select e.*, m.login as author, count(c.id) as comments_cnt
         from Entry e
         left join Comment c on e.id = c.entry_id
         join Members m on e.author_id = m.id
         group by e.id
         order by date desc
         limit $offset, $limit");
//    $records = $cursor->fetch_array(MYSQLI_ASSOC);
    while ($row = $cursor->fetch_assoc()) {
        $records[] = $row;
    }
    return $records;
}

function getEntriesCount() {
    global $mysqli;
    $cursor = $mysqli->query("select count(e.id) cnt from Entry e");
    $row = $cursor->fetch_assoc();
    return $row['cnt'];
}

function getEntry($id) {
    global $mysqli;
    $cursor = $mysqli->query(
        'select e.* from Entry e
         where e.id = '.$id);
    $row = $cursor->fetch_assoc();
    return $row;
}

function addEntry($author_id, $header, $content) {
//echo $author_id, " ", $header, " ", $content;
    global $mysqli;
    $statement = $mysqli->prepare(
        "insert into Entry (date, author_id, header, content)
         values(UNIX_TIMESTAMP(), ?, ?, ?)");
    $statement->bind_param('iss', $author_id, $header, $content);
    return $statement->execute();
}

function editEntry($id, $author_id, $header, $content) {
//echo $id, " ", $author_id, " ", $header, " ", $content;
    global $mysqli;
    $statement = $mysqli->prepare(
        "update Entry set author_id = ?, header = ?, content = ?
        where id = ?");
    $statement->bind_param('issi', $author_id, $header, $content, $id);
    return $statement->execute();
}

function deleteEntry($id) {
//echo $id;
    global $mysqli;
    $statement = $mysqli->prepare(
        "delete from Comment where entry_id = ?");
    $statement->bind_param('i', $id);
    if (!$statement->execute()) return false;

    $statement = $mysqli->prepare(
        "delete from Entry where id = ?");
    $statement->bind_param('i', $id);
    return $statement->execute();
}

function getEntryComments($id) {
    global $mysqli;
    $cursor = $mysqli->query(
        "select c.*, m.login as author,
                (
                 select count(*)
                 from Comment c1
                 where c1.entry_id = c.entry_id
        		) as comments_cnt
        from Comment c
        join Members m on c.author_id = m.id
        where c.entry_id = $id
        order by date");
    while ($row = $cursor->fetch_assoc()) {
        $comments[] = $row;
    }
    return $comments;
}

function addComment($entry_id, $author_id, $content) {
//echo $entry_id, " ", $author_id, " ", $content;
    global $mysqli;
    $statement = $mysqli->prepare(
        "insert into Comment (entry_id, date, author_id, content)
         values(?, UNIX_TIMESTAMP(), ?, ?)");
    $statement->bind_param('iis', intval($entry_id), $author_id, $content);
    return $statement->execute();
}

//controller
function formatRecordsData($records) {
    $recordsNew = array();
    foreach($records as $row) {
        $recordsNew []= formatRowData($row, 100);
    }
    return $recordsNew;
}

function formatRowData($row, $maxLen) {
    $row['header'] = htmlspecialchars($row['header']);
    $row['author'] = htmlspecialchars($row['author']);
    $row['date'] = date('Y.m.d H:i:s', $row['date']);
    $row['content'] = nl2br(htmlspecialchars($row['content']));
    if (mb_strlen($row['content']) > $maxLen) {
        $row['content'] = mb_substr(strip_tags($row['content']), 0, $maxLen-3).'...';
    }
    return $row;
}

function formatCommentsData($comments) {
    $commentsNew = array();
    foreach($comments as $row) {
        $row['date'] = date('Y.m.d H:i:s', $row['date']);
        $row['author'] = htmlspecialchars($row['author']);
        $row['content'] = nl2br(htmlspecialchars($row['content']));
        $commentsNew []= $row;
    }
    return $commentsNew;
}

//define('IS_ADMIN', isset($_SESSION['IS_ADMIN']));
session_start();

$act = isset($_GET['act']) ? $_GET['act'] : 'list';
$records = array();
$comments = array();

//Function to sanitize values received from the form. Prevents SQL injection
function clean($str) {
    $str = @trim($str);
    if(get_magic_quotes_gpc()) {
        $str = stripslashes($str);
    }
    return mysql_real_escape_string($str);
}

switch ($act) {
    case 'list':
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 3;
        $offset = ($page-1)*$limit;

        if (getEntriesCount()%$limit == 0) {
            $pagesCount = intval(getEntriesCount()/$limit);
        } else {
            $pagesCount = intval(getEntriesCount()/$limit) + 1;
        }

        $records = getEntriesList($limit, $offset);
        $records = formatRecordsData($records);
        require('templates/list.php');
        break;
    case 'register':
        require('templates/register.php');
        break;
    case 'do-register':
        // Users data sent from registration form
        $email = clean($_POST['email']);
        $login = clean($_POST['login']);
        $password = clean($_POST['password']);
        $cpassword = clean($_POST['cpassword']);
        $fname = clean($_POST['fname']);
        $lname = clean($_POST['lname']);

        // Input Validations
        if ($fname == '') {
            $errmsg_arr[] = 'First name is missed' ;
            $errflag = true;
        }
        if ($lname == '') {
            $errmsg_arr[] = 'Last name is missed';
            $errflag = true;
        }
        if ($email == '') {
            $errmsg_arr[] = 'E-mail is missed';
            $errflag = true;
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errmsg_arr[] = 'E-mail is wrong';
                $errflag = true;
            }
        }
        if ($login == '') {
            $errmsg_arr[] = 'Login is missed';
            $errflag = true;
        }
        if ($password == '') {
            $errmsg_arr[] = 'Password is missed';
            $errflag = true;
        } else {
            if (sizeof($password) < 5) {
                $errmsg_arr[] = 'Password is too short. It should have 5 symbols at least';
                $errflag = true;
            }
        }

        if ($cpassword == '') {
            $errmsg_arr[] = 'Confirm password is missed ';
            $errflag = true;
        }

        if (strcmp($password, $cpassword) != 0) {
            $errmsg_arr[] = 'Passwords do not match ';
            $errflag = true;
        }


        //Check for duplicate login ID and e-mail
        if (($login != '') or ($email != '')) {
            $result = getUserCreditsByLoginOrEmail($login, $email);
            if (isset($result)) {
                if (strcasecmp($login, $result['login']) == 0) {
                    $errmsg_arr[] = 'Login is already in use';
                }
                if (strcasecmp($email, $result['email']) == 0) {
                    $errmsg_arr[] = 'E-mail is already in use';
                }
            }
        }

        //If there are input validations, redirect back to the registration form
        if($errflag) {
            $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
            header("location: ?act=register");
            break;
        }

        $regResult = registerMember($fname, $lname, $email, $login, $_POST['password']);

        //Check whether the query was successful or not
        if (!$regResult) {
            $errmsg_arr[] = 'Registration was failed. Please try again.';
            $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
            header("location: ?act=register");
            break;
        }

        $result = getUserCredits($login, $password);
        $loginId = $result['id'];
        $userName = $result['firstname']. " ". $result['lastname'];

        $_SESSION['login'] = $login;
        $_SESSION['password'] = $password;
        $_SESSION['loginId'] = $loginId;
        $_SESSION['userName'] = $userName;

        header("Location: /");
        break;
    case 'login':
        if(isset($_SESSION['loginId']) && (trim($_SESSION['loginId']) != '')) {
            header('Location: /');
            break;
        }
        require('templates/login.php');
        break;
    case 'do-login':
        if(isset($_SESSION['loginId']) && (trim($_SESSION['loginId']) != '')) {
            header('Location: /');
            break;
        }

        // Users data and password sent from form
        $login = clean($_POST['login']);
        $password = clean($_POST['password']);

        // Input validation
        if ($login == '') {
            $errmsg_arr[] = "Login is missed";
            $errflag = true;
        }
        if ($password == '') {
            $errmsg_arr[] = "Password is missed";
            $errflag = true;
        }

        if ($errflag) {
//            $errmsg_arr[] = "Please try again";
            $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
            header("location: ?act=login");
            break;
        }

        $password = $_POST['password'];
        $result = getUserCredits($login, $password);

        //Check whether the query was successful or not
//echo " 344 " . isset($result);
//echo " 345 " . sizeof($result);
        if (!isset($result)) {
            $errmsg_arr[] = "Login failed. Please check your credentials and try again.";
            $_SESSION['ERRMSG_ARR'] = $errmsg_arr;
            header("location: ?act=login");
            break;
        }

        $loginId = $result['id'];
        $userName = $result['firstname']. " ". $result['lastname'];
        $_SESSION['login'] = $login;
        $_SESSION['password'] = $password;
        $_SESSION['loginId'] = $loginId;
        $_SESSION['userName'] = $userName;

        header("Location: /");
        break;
    case 'logout':
        session_destroy();
        header('Location: /');
        break;
    case 'view-entry':
        if(!isset($_GET['id'])) die ('Missing id parameter!');
        $id = intval($_GET['id']);
        $row = getEntry($id);
        if (!$row) die ("No such entry!");
        $row = formatRowData($row, 5000);

        $comments = getEntryComments($id);
        if (isset($comments) && ($comments != null)) {
            $comments = formatCommentsData($comments);
        }
        require('templates/entry.php');
        break;
    case 'do-new-entry':
        if (!isset($_SESSION['loginId']) || (trim($_SESSION['loginId']) == '')) {
            die ("You must be authorized to add entry!");
        }
        $res = addEntry($_POST['author_id'], $_POST['header'], $_POST['content']);
        if (!$res) die ("Can't insert the entry!");
        header('Location: /');
        break;
    case 'edit-entry':
        if (!isset($_SESSION['loginId']) || (trim($_SESSION['loginId']) == '')) {
            die ("You must be authorized to edit entry!");
        }
        $id = intval($_GET['id']);
        $row = getEntry($id);
        if (!$row) die ("No such entry!");

        if ($_SESSION['loginId'] != $row['author_id']) {
            die ("You must be an author of entry to edit it!");
        }
        require('templates/entry_edit.php');
        break;
    case 'do-edit-entry':
        if (!isset($_SESSION['loginId']) || (trim($_SESSION['loginId']) == '')) {
            die ("You must be authorized to edit entry!");
        }
        if ($_SESSION['loginId'] != $_POST['author_id']) {
            die ("You must be an author of entry to edit it!");
        }
        $res = editEntry($_POST['id'],
            htmlspecialchars_decode($_POST['author_id']),
            htmlspecialchars_decode($_POST['header']),
            htmlspecialchars_decode($_POST['content'])
        );
        if (!$res) die ("Can't edit the entry!");
        header('Location: /');
        break;
    case 'delete-entry':
        if (!isset($_SESSION['loginId']) || (trim($_SESSION['loginId']) == '')) {
            die ("You must be authorized to delete entry!");
        }

        $id = intval($_GET['id']);
        $row = getEntry($id);
        if (!$row) die ("No such entry!");
        if ($_SESSION['loginId'] != $row['author_id']) {
            die ("You must be an author of entry to delete it!");
        }

        $res = deleteEntry($_GET['id']);
        if (!$res) die ("Can't delete the entry!");
        header('Location: /');
        break;
    case 'do-new-comment':
        if (!isset($_SESSION['loginId']) || (trim($_SESSION['loginId']) == '')) {
            die ("You must be authorized to add comment!");
        }
        $entry_id = $_POST['entry_id'];
        $res = addComment($entry_id, $_POST['author_id'], $_POST['content']);
        if (!$res) die ("Can't insert the comment!");
        header("Location: ?act=view-entry&id=". intval($entry_id));
        break;
    default:
        die("No such action");
}

