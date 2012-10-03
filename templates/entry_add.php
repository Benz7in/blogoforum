<form action="?act=do-new-entry" method="POST" class="well">
<!--    <label>Author</label>-->
<!--    <input name="author" type="text"/>-->
    <input name="author_id" type="hidden" value="<?=$_SESSION['loginId']?>"/>
    <label>Header</label>
    <input name="header" type="text"/>
    <label>Content</label>
    <textarea name="content"></textarea>
    <div style="padding-top: 10px">
        <button type="submit" class="btn">
            Add
        </button>
    </div>
</form>
