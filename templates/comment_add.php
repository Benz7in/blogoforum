<form action="?act=do-new-comment" method="POST" class="well">
    <input name="entry_id" type="hidden" value="<?=$id?>"/>
    <input name="author_id" type="hidden" value="<?=$_SESSION['loginId']?>"/>
<!--    <label>Author</label>-->
<!--    <input name="author" type="text"/>-->
    <label>Content</label>
    <textarea name="content"></textarea>
    <div style="padding-top: 10px">
        <button type="submit" class="btn">
            Add
        </button>
    </div>
</form>
