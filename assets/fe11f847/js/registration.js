$(document).ready(function(){
   $(".subject_close").on("click", function() {
       var dataId = $(this).data('id');
       var dataDepend = $(this).data('depend');
       var currentItem = $(this);
       var parentTD = $(this).parents("td");

       $.ajax({
           url: urlDelete,
           data: {"dataId":dataId, "dataDepend":dataDepend},
           type: 'POST',
           success: function(data) {
               if(data) {
                   currentItem.parent(".label").remove();
                   if(parentTD.find("span").length == 0) {
                       parentTD.parents("tr").remove();
                   }
               }
           }
       });
   });


    if($(".filestyle").length) {
        $(".filestyle").filestyle({
            icon:false,
            buttonText: "Browse",
            buttonName: "btn-default"
        });
    }

    if($("#editAPST .styleapsts").length) {
        $("#editAPST .styleapsts").filestyle({
            icon:false,
            buttonText: "Browse",
            buttonName: "btn-default"
        });
    }
    
    $(".file_edit").on("click", function() {
        var name = $(this).data('name');
        var id = $(this).data('id');
        var input = "<input name='apsts_id' value='"+id+"' hidden>";
        var type = $(this).data('type');
            console.log(type);
        $("#editAPST .append-teacher-type").html(type);
        $("#editAPST .bootstrap-filestyle input[type=text]").val(name);
        $(".APSTS_id").empty();
        $(".APSTS_id").append(input);
        $("#editAPST").modal('show');
    });

    $(".apst-clear").on("click", function() {
        $("#editAPST form")[0].reset();
        return false;
    });

    $(".apsts-update-modal").on("click", function() {
        var data = new FormData($("#editAPST form")[0]);
        $.ajax({
            url: urlUpdateAPSTS,
            processData: false,
            contentType: false,
            data: data,
            type: 'POST',
            success: function(data) {
                if(data == 1) {
                   window.location.href = "";
                } else {
                    console.log(1234);
                    $("#editAPST .errorBlock").html("Browse file");
                }
            }
        });
    });
});