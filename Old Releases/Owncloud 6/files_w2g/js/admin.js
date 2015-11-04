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
	
});