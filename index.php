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
// To protect MySQL injection (more detail about MySQL injection)
    $login = mysql_real_escape_string($login);
    $password = mysql_real_escape_string($password);

    $result = $mysqli->query(
        "SELECT * FROM Members WHERE login='$login' and password='$password'");
    if($result) {
        if($result->num_rows == 1) {
            $row = $result->fetch_assoc();
        } else {
            //Login failed
            die("Login failed! Please check your credentials!");
            exit();
        }
    } else {
        die("Query failed");
    }
    return $row;
}

function getEntriesList($limit, $offset) {
    global $mysqli;
    $records = array();
    $cursor = $mysqli->query(
        "select e.*, count(c.id) as comments_cnt
         from Entry e
         left join Comment c on e.id = c.entry_id
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

function addEntry($author, $header, $content) {
//echo $author, " ", $header, " ", $content;
    global $mysqli;
    $statement = $mysqli->prepare(
        "insert into Entry (date, author, header, content)
         values(UNIX_TIMESTAMP(), ?, ?, ?)");
    $statement->bind_param('sss', $author, $header, $content);
    return $statement->execute();
}

function editEntry($id, $author, $header, $content) {
//echo $id, " ", $author, " ", $header, " ", $content;
    global $mysqli;
    $statement = $mysqli->prepare(
        "update Entry set author = ?, header = ?, content = ?
        where id = ?");
    $statement->bind_param('sssi', $author, $header, $content, $id);
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
        "select c.*,
                (
                 select count(*)
                 from Comment c1
                 where c1.entry_id = c.entry_id
        		) as comments_cnt
        from Comment c
        where c.entry_id = $id
        order by date");
    while ($row = $cursor->fetch_assoc()) {
        $comments[] = $row;
    }
    return $comments;
}

function addComment($entry_id, $author, $content) {
//echo $entry_id, " ", $author, " ", $content;
    global $mysqli;
    $statement = $mysqli->prepare(
        "insert into Comment (entry_id, date, author, content)
         values(?, UNIX_TIMESTAMP(), ?, ?)");
    $statement->bind_param('iss', intval($entry_id), $author, $content);
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

$act = isset($_GET['act']) ? $_GET['act'] : 'list';
$records = array();
$comments = array();

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
    case 'view-entry':
        if(!isset($_GET['id'])) die ('Missing id parameter!');
        $id = intval($_GET['id']);
        $row = getEntry($id);
        if (!$row) die ("No such entry!");
        $row = formatRowData($row, 5000);

        $comments = getEntryComments($id);
        $comments = formatCommentsData($comments);

        require('templates/entry.php');
        break;
    case 'edit-entry':
        if ($_SESSION['IS_ADMIN'] == 0) die ("You must be authorized to edit entry!");
        $id = intval($_GET['id']);
        $row = getEntry($id);
        if (!$row) die ("No such entry!");
        //$row = formatRowData($row, 5000);
        require('templates/entry_edit.php');
        break;
    case 'login':
        require('templates/login.php');
        break;
    case 'do-login':
        // username and password sent from form
        $login = $_POST['login'];
        $password = $_POST['password'];
        $result = getUserCredits($login, $password);

        $loginId = $result['id'];
        $userName = $result['firstname']. " ". $result['lastname'];
        session_register("loginId");
        session_register("login");
        session_register("password");
        session_register("userName");
        header("Location: /");
        break;
    case 'logout':
        session_destroy();
        header('Location: /');
        break;
    case 'do-new-entry':
        if (!session_is_registered(loginId)) die ("You must be authorized to add entry!");
        $res = addEntry($_POST['author'], $_POST['header'], $_POST['content']);
        if (!$res) die ("Can't insert the entry!");
        header('Location: /');
        break;
    case 'delete-entry':
        if ($_SESSION['IS_ADMIN'] == 0) die ("You must be authorized to delete entry!");
        $res = deleteEntry($_GET['id']);
        if (!$res) die ("Can't delete the entry!");
        header('Location: /');
        break;
    case 'do-edit-entry':
        if ($_SESSION['IS_ADMIN'] == 0) die ("You must be authorized to edit entry!");
        $res = editEntry($_POST['id'],
            htmlspecialchars_decode($_POST['author']),
            htmlspecialchars_decode($_POST['header']),
            htmlspecialchars_decode($_POST['content'])
        );
        if (!$res) die ("Can't edit the entry!");
        header('Location: /');
        break;
    case 'do-new-comment':
        if (!session_is_registered(loginId)) die ("You must be authorized to add comment!");
        $entry_id = $_POST['entry_id'];
        $res = addComment($entry_id, $_POST['author'], $_POST['content']);
        if (!$res) die ("Can't insert the comment!");
        header("Location: ?act=view-entry&id=". intval($entry_id));
        break;
    default:
        die("No such action");
}

