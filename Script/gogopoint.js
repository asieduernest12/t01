$(document).ready(function() {
    $("input[type='checkbox']").change(function () {
        var current_progress = ($(":checkbox:checked").length/8)*100;

        $(".textbox").val(current_progress+"%");
        $("#dynamic")
        .css("width", current_progress + "%")
        .attr("aria-valuenow", current_progress)
        .text(current_progress + "% complete");
    });
});