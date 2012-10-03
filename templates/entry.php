<?php require("header.php") ?>

<style type="text/css">
    h2 {
        margin-bottom: 10px;
    }
    h3 {
        margin-top: 10px;
    }
    .content {
        padding-top: 5px;
        padding-left: 15px;
    }
    .date {
        /*color: gray;*/
        margin-right: 10px;
    }
    .author {
        color:#ff4500;
    }
    .comments {
        font-size: 0.87em;
        margin-bottom: 20px;
    }

    .comment-header {
        font-size: 0.8em;
        margin-bottom: 20px;
    }
    .comment-content {
        padding-left: 10px;
        margin-bottom: 10px;
    }
</style>

<h2>
    <a href="?act=view-entry&id=<?=$row['id']?>"><?=$row['header']?></a>
    <?php if (isset($_SESSION['loginId']) && (trim($_SESSION['loginId']) != '') &&
             (trim($_SESSION['loginId'])==$row['author_id'])) : ?>
        <a href="?act=edit-entry&id=<?=$row['id']?>"><i class="icon-edit"></i></a>
        <a onClick="return confirmDelete()" href="?act=delete-entry&id=<?=$row['id']?>"><i class="icon-trash"></i></a>
    <?php endif ?>
</h2>
<p class="content"><?=$row['content']?></p>
<div class="comments">
    <!--<small><small>-->
    <span class="date"><?=$row['date']?></span>
    <span class="author"><?=$row['author']?></span>
    <!--</small></small>-->
    </br>
    <!--<a href="?act=view-entry&id=<?=$row['id']?>">comments</a>-->
</div>

<?php if (isset($comments) && ($comments != null)): ?>
    <h4>Comments</h4>

    <?php foreach($comments as $row): ?>
        <div class="entry">
            <div class="comment-header">
                <!--<small><small>-->
                <span class="date"><?=$row['date']?></span>
                <span class="author"><b><?=$row['author']?></b></span>
                <!--</small></small>-->
                <p class="comment-content"><?=$row['content']?></p>
            </div>
        </div>
    <?php endforeach ?>
<?php endif ?>

    <?php if (isset($_SESSION['loginId']) && (trim($_SESSION['loginId']) != '')) : ?>
    <h4>Add new comment</h4>
    <?php require('comment_add.php') ?>
<?php endif?>

<?php require("footer.php") ?>

