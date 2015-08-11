var color = '008887';
var fontcolor = 'ffffff';

$(document).ready(function(){
	//The default text, if no translation is available
	text = "filelock";
	//t('files_w2g','filelock');
	lockedtext = t('files_w2g','File is locked');
	lockstate = t('files_w2g','Status: locked');
	
    if (typeof FileActions !== 'undefined' && $('#dir').length>0) {
		        
        //Initiate the FileAction for file
		OCA.Files.fileActions.register(
			'file',
			'getstate_file',
			OC.PERMISSION_UPDATE,
			function(){ return OC.imagePath('files_w2g','w2g.svg') },
			function(filename,context) { getState(context.$file.attr('data-id'),filename,context.$file.attr('data-share-owner'),"false") }, t('files_w2g',text)
		);
            
		//Initiate the FileAction for dir
        OCA.Files.fileActions.register(
			'dir',
			'getstate_dir',
			OC.PERMISSION_UPDATE,
			function(){ return OC.imagePath('files_w2g','w2g.svg') },
			function(filename,context) { getState(context.$file.attr('data-id'),filename,context.$file.attr('data-share-owner'),"false") }, t('files_w2g',text)
		);
		
		//Walk through all files in the active Filelist
		$('#content').delegate('#fileList', 'fileActionsReady',function(ev){

                var $fileList = ev.fileList.$fileList;

				$fileList.find('tr').each(function(){
					$filename = $(this).attr('data-file');
					$owner = $(this).attr('data-share-owner');	
					$id = $(this).attr('data-id');
					getState($id,$filename,$owner,"true");			
				});
		});
    }
	
	//Get the Background-color from the database
	$.ajax({
	url: OC.filePath('files_w2g','ajax','getcolor.php'),
	type: "post",
	data: { type: 'color'},
	async: false,
	success: function(data){if(data!=""){color = data;}},
	});
	
	//Get the Fontcolor from the database
	$.ajax({
	url: OC.filePath('files_w2g','ajax','getcolor.php'),
	type: "post",
	data: { type: 'fontcolor'},
	async: false,
	success: function(data){if(data!=""){fontcolor = data;}},
	});
	
	//Add dynamic CSS code
	var cssrules =  $("<style type='text/css'> </style>").appendTo("head");
	cssrules.append(".statelock{ background-color:#"+color+";color:#"+fontcolor+" !important;}"+
	".statelock span.modified{color:#"+fontcolor+" !important;}"+
	"a.action-getstate_file.locked,a.action-getstate_dir.locked{color:#"+fontcolor+" !important;}"+
	"a.action-getstate_file.locked:hover,a.action-getstate_dir.locked:hover{color:#fff !important;}");	
});

//Switch the Lockstate
function toggle_control(filename)
{
	//Walk through the Filelists
	$('#fileList tr').each(function() {
	
		var $tr = $(this);
		var $_tr = $tr.html().replace(/^\s+|\s+$/g, '').replace('<span class="extension">','').replace('</span>','');
		
		if($_tr.indexOf(filename)!=-1)
		{
		    if($_tr.indexOf(lockedtext)==-1 && $_tr.indexOf(lockstate)==-1)
		    {		//unlock
					$tr.find('a.action[data-action!='+text+']').removeClass('locked');
					$tr.find('a.name').removeClass('statelock');
					$tr.find('td.filesize').removeClass('statelock');
					$tr.find('td.date').removeClass('statelock');
					$tr.find('td').removeClass('statelock');		
					$tr.find('td').draggable('enable');
			}
			else if($_tr.indexOf(lockedtext)!=-1||$_tr.indexOf(lockstate)!=-1)
			{		//lock	
					$tr.find('a.action[data-action!='+text+']').addClass('locked');
					$tr.find('a.name').addClass('statelock');
					$tr.find('td.filesize').addClass('statelock');
					$tr.find('td.date').addClass('statelock');
					$tr.find('td').addClass('statelock');
					$tr.find('td').draggable('disable');
			}
		}
	});
}

//Get the current state
function getState( _id, _filename, _owner, _safe)
{
    oc_dir = $('#dir').val();
	_filename = _filename.replace(/ /g, "%20");
	oc_path = oc_dir +'/'+_filename;
	
	$.ajax({
        url: OC.filePath('files_w2g','ajax','workin2gether.php'),
        type: "post",
        data: { path: oc_path, safe: _safe, owner: _owner, id: _id},
        success: function(data){postmode(_filename,data)},
    });
}

//Push the status
function postmode(filename,data)
{
		filename = filename.replace(/%20/g,' ');

		var html = '<img class="svg" src="'+OC.imagePath('files_w2g','w2g.svg')+'"></img> '+'<span>'+data+'</span>';

		//Push the status text to the file
		$('tr').filterAttr('data-file',filename).find('td.filename').find('a.name').find('span.fileactions').find("a.action").filterAttr('data-action','getstate_file').html(html);
		
		//Push the status text to the dir
		$('tr').filterAttr('data-file',filename).find('td.filename').find('a.name').find('span.fileactions').find("a.action").filterAttr('data-action','getstate_dir').html(html);

		toggle_control(filename);
}