$(document).ready(function(){
	
	$('#submitColor').click(function(){
			$.ajax({
			url: OC.filePath('files_w2g','ajax','update.php'),
			type: "post",
			data: { color: $('#multicolor').val()},
			async: false,
			success: function(data){text = data;},
			});
		alert(text);
	});
	
	$('#submitfontcolor').click(function(){
			$.ajax({
			url: OC.filePath('files_w2g','ajax','update.php'),
			type: "post",
			data: { fontcolor: $('#multifontcolor').val()},
			async: false,
			success: function(data){text = data;},
			});
		alert(text);
	});
	
	$('#clearall').click(function(){
		$.ajax({
			url: OC.filePath('files_w2g','ajax','admin_db.php'),
			type: "post",
			data: { action: 'clearall'},
			async: false,
			success: function(data){
			if(data=="clear") $('#lockfield').html(t("files_w2g","There are no locked files at the moment"));},
		});
	});
	
	$('#clearthis').click(function(){
		$.ajax({
			url: OC.filePath('files_w2g','ajax','admin_db.php'),
			type: "post",
			data: { action: 'clearthis',lock: $('#select_lock option:selected').val()},
			async: false,
			success: function(data){
				if(data=="clear") $('#select_lock option:selected').remove();
				
				if($.trim($('#select_lock').html())=="")
					$('#lockfield').html(t("files_w2g","There are no locked files at the moment"));

			},
			});
	});

});