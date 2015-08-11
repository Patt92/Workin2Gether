var text = "";
var lockedtext = "";
var lockstate = "";
var color = '008887';
var fontcolor = 'ffffff';


$(document).ready(function(){

	text = translate("filelock");
	lockedtext = translate("File is locked");
	lockstate = translate("Status: locked");
	
    if (typeof FileActions !== 'undefined' && $('#dir').length>0) {
		
        
            FileActions.register('file',text,OC.PERMISSION_READ,function(){return OC.imagePath('files_w2g','w2g.svg')},function(filename){
                getState(filename,"false");
            });
            
            FileActions.register('dir',text,OC.PERMISSION_READ,function(){return OC.imagePath('files_w2g','w2g.svg')},function(filename){
                getState(filename,"false");
            });
			
			$('#fileList').on('fileActionsReady',function(filename){
				$('#fileList tr td.filename a.name span.nametext').each(function() {
					var $tr = $(this);
					getState($tr.html(),"true");
				});
			});
    }

	
	$.ajax({
	url: OC.filePath('files_w2g','ajax','getcolor.php'),
	type: "post",
	data: { type: 'color'},
	async: false,
	success: function(data){if(data!=""){color = data;}},
	});
	
	$.ajax({
	url: OC.filePath('files_w2g','ajax','getcolor.php'),
	type: "post",
	data: { type: 'fontcolor'},
	async: false,
	success: function(data){if(data!=""){fontcolor = data;}},
	});
	
	var cssrules =  $("<style type='text/css'> </style>").appendTo("head");
	cssrules.append(".statelock{ background-color:#"+color+";color:#"+fontcolor+" !important;}.statelock span.modified{color:#"+fontcolor+" !important;}");
	
});

function disable_control(filename)
{

	$('#fileList tr').each(function() {
	
		var $tr = $(this);
		
		var $_tr = $tr.html().replace(/^\s+|\s+$/g, '').replace('<span class="extension">','').replace('</span>','');
		
		    if($_tr.indexOf(lockedtext)!=-1||$_tr.indexOf(lockstate)!=-1)
		    {	
				if($_tr.indexOf(filename)!=-1)
					$tr.find('a.action[data-action!='+text+']').addClass('locked');
					$tr.find('a.name').addClass('statelock');
					$tr.find('td.filesize').addClass('statelock');
					$tr.find('td.date').addClass('statelock');
					
			}
	});
}

function enable_control(filename)
{
	$('#fileList tr').each(function() {
	
		var $tr = $(this);
		
		var $_tr = $tr.html().replace(/^\s+|\s+$/g, '').replace('<span class="extension">','').replace('</span>','');
		
		    if($_tr.indexOf(lockedtext)==-1 && $_tr.indexOf(lockstate)==-1)
		    {	
				if($_tr.indexOf(filename)!=-1)
					$tr.find('a.action[data-action!='+text+']').removeClass('locked');
					$tr.find('a.name').removeClass('statelock');
					$tr.find('td.filesize').removeClass('statelock');
					$tr.find('td.date').removeClass('statelock');
			}
	});
}

function translate(text)
{
	$.ajax({
        url: OC.filePath('files_w2g','ajax','l10n.php'),
        type: "post",
        data: { rawtext: text},
		async: false,
        success: function(data){text = data;},
    });
	return text;
}

function getState(filename,_safe)
{
	filename = filename.replace(/^\s+|\s+$/g, '').replace('<span class="extension">','').replace('</span>','');
	
    oc_dir = $('#dir').val();
	filename = filename.replace(/ /g, "%20");
	oc_path = oc_dir +'/'+filename;

	
	$.ajax({
        url: OC.filePath('files_w2g','ajax','workin2gether.php'),
        type: "post",
        data: { path: oc_path, safe: _safe},
        success: function(data){postmode(filename,data)},
    });

}

function postmode(filename,data)
{
		filename = filename.replace(/%20/g,' ');

		var html = '<img class="svg" src="'+OC.imagePath('files_w2g','w2g.svg')+'"></img> '+data;
		$('tr').filterAttr('data-file',filename).find('td.filename').find('a.name').find('span.fileactions').find("a.action").filterAttr('data-action',text).html(html);
		
		if(data==lockedtext||data==lockstate)
			disable_control(filename);
		else
			enable_control(filename);

}

