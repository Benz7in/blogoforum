function confirmDelete() {
    var agree=confirm("Are you sure you want to delete this post?");
    if (agree)
        return true;
    else
        return false;
}

//$(function() {
//    $('#delete').click(function() {
//        $("#delete").con
//        $("#delete").fadeOut("slow");
//        $("#delete").fadeIn("slow");
//        return false;
//    });
//});