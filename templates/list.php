<?php require("header.php") ?>

<style type="text/css">
    .entry {
        padding-left: 20px;
    }
    h3 {
        margin-bottom: 10px;
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
    .pages {
        margin-bottom: 20px;
    }
</style>

<h1>Header of the blog</h1>

<?php foreach($records as $row): ?>
    <div class="entry">
        <h2>
            <a href="?act=view-entry&id=<?=$row['id']?>"><?=$row['header']?></a>
            <?php if ($_SESSION['IS_ADMIN'] == 1) : ?>
            <a href="?act=edit-entry&id=<?=$row['id']?>"><i class="icon-edit"></i></a>
            <a href="?act=delete-entry&id=<?=$row['id']?>"><i class="icon-trash"></i></a>
            <?php endif ?>
        </h2>
        <p class="content"><?=$row['content']?></p>
        <div class="comments">
            <!--<small><small>-->
            <span class="date"><?=$row['date']?></span>
            <span class="author"><?=$row['author']?></span>
            <!--</small></small>-->
            </br>
            <a href="?act=view-entry&id=<?=$row['id']?>">
                <?php
                    $comments_cnt = intval($row['comments_cnt']);
                    if ($comments_cnt > 0) {
                        if ($comments_cnt > 1) {
                            echo $comments_cnt.' comments';
                        } else {
                            echo $comments_cnt.' comment';
                        }
                    }
                ?>
            </a>
        </div>
    </div>
<?php endforeach ?>

<div class="pages">
<strong>Pages:</strong>
    <?php for($i = 1; $i <= $pagesCount; $i++) { ?>
        <?php if ($i == $page) { ?><b><?=$i?></b>
        <?php } else { ?><a href="?page=<?=$i?>"><?=$i?></a>
        <?php } ?>
    <?php } ?>
</div>

<?php if(isset($_SESSION['IS_ADMIN'])) { ?>
    <h1>Add new entry</h1>
    <?php require('entry_add.php') ?>
<?php } ?>


<?php require("footer.php") ?>
