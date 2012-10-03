<?php require("header.php") ?>
<h1>Edit entry</h1>
<form action="?act=do-edit-entry" method="POST" class="well">
    <input name="id" type="hidden" value="<?=$id?>"/>
<!--    <label>Author</label>-->
<!--    <input name="author" type="text" value="--><?//=$row['author']?><!--"/>-->
    <input name="author_id" type="hidden" value="<?=$row['author_id']?>"/>
    <label>Header</label>
    <input name="header" type="text" value="<?=$row['header']?>"/>
    <label>Content</label>
    <textarea name="content"><?=$row['content']?></textarea>
    <div style="padding-top: 10px">
        <button type="submit" class="btn">
            Edit
        </button>
    </div>
</form>
<?php require("footer.php") ?>
