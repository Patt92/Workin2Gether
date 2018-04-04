$(document).ready(function(){

    var $lockUrl = OC.generateUrl('/apps/w2g2/lock');
    var $updateUrl = OC.generateUrl('/apps/w2g2/update');

    $('#submitColor').click(function(){
        $.ajax({
            url: $updateUrl,
            type: "post",
            data: { type: 'color', value: $('#multicolor').val()},
            async: false,
            success: function(data){text = data;},
        });
        alert(text);
    });

    $('#submitfontcolor').click(function(){
        $.ajax({
            url: $updateUrl,
            type: "post",
            data: { type: 'fontcolor', value: $('#multifontcolor').val()},
            async: false,
            success: function(data){text = data;},
        });
        alert(text);
    });

    $('input:radio[name="suffix"]').change(function(){
        $.ajax({
            url: $updateUrl,
            type: "post",
            data: { type: 'suffix', value: $("input:radio[name='suffix']:checked").attr('id')},
            async: false,
            success: function(data){},
        });
    });

    $('#clearall').click(function(){
        var data = {
            action: 'all'
        };

        $.ajax({
            url: $lockUrl,
            type: "delete",
            data: data,
            async: false,
            success: function(data) {
                $('#lockfield').html(t("w2g2", "There are no locked files at the moment"));
            }
        });
    });

    $('#clearthis').click(function(){
        var lockFile = $('#select_lock option:selected').val();

        if ( ! lockFile) {
            return;
        }
        
        var data =  {
            action: 'one',
            lockedFileId: lockFile
        };

        $.ajax({
            url: $lockUrl,
            type: "delete",
            data: data,
            async: false,
            success: function(data){
                $('#select_lock option:selected').remove();

                if ($.trim($('#select_lock').html()) == "") {
                    $('#lockfield').html(t("w2g2", "There are no locked files at the moment"));
                }
            },
        });
    });

    $('#w2g_lpml').click(function(){
        $('#w2g_lock_permission_extended').click();
    });

    $('#w2g_lock_permission_extended').click(function(){
        var extended_checked = $('#w2g_lock_permission_extended').attr('checked') ? 1 : 0;

        $.ajax({
            url: $updateUrl,
            type: "post",
            data: { mode: 'extended',value: extended_checked },
            async: false,
            success: function(){},
        });
    });

    // Directory locking
    $('input:radio[name="directory_locking"]').change(function() {
        $.ajax({
            url: $updateUrl,
            type: "post",
            data: { type: 'directory_locking', value: $("input:radio[name='directory_locking']:checked").attr('id')},
            async: false,
            success: function(data){},
        });
    });
});
