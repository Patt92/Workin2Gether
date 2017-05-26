var color = '008887';
var fontcolor = 'ffffff';

$(document).ready(function(){

   lockstate = t('workin2gether','Locked');
	
   if (typeof FileActions !== 'undefined' && $('#dir').length>0) {
		        
		OCA.Files.fileActions.registerAction({
			name:'getstate_w2g',
			displayName: '',
			mime: 'all',
			permissions: OC.PERMISSION_ALL,
			type: OCA.Files.FileActions.TYPE_INLINE,
			icon: function(){ return OC.imagePath('workin2gether','lock.png')},
			actionHandler: function(filename,context)
			{
				getStateSingle(context.$file.attr('data-id'),filename,context.$file.attr('data-share-owner'),"false");
			} 
		});

		var _files = [];		

		//Walk through all files in the active Filelist
		$('#content').delegate('#fileList', 'fileActionsReady',function(ev){
                	var $fileList = ev.fileList.$fileList;		
			$fileList.find('tr').each(function(){
				_files.push( [ $(this).attr('data-id') , $(this).attr('data-file') , $(this).attr('data-share-owner') , '' ] );
			});
				
			getStateAll(_files,"true");
		});
   }
	
	//Get the Background-color from the database
	$.ajax({
	url: OC.filePath('workin2gether','ajax','getcolor.php'),
	type: "post",
	data: { type: 'color'},
	async: false,
	success: function(data){if(data!=""){color = data;}},
	});
	
	//Get the Fontcolor from the database
	$.ajax({
	url: OC.filePath('workin2gether','ajax','getcolor.php'),
	type: "post",
	data: { type: 'fontcolor'},
	async: false,
	success: function(data){if(data!=""){fontcolor = data;}},
	});

	//Add dynamic CSS code
	var cssrules =  $("<style type='text/css'> </style>").appendTo("head");
	cssrules.append(".statelock{ background-color:#"+color+";color:#"+fontcolor+" !important;}"+
	".statelock span.modified{color:#"+fontcolor+" !important;}"+
	"a.w2g_active{color:#"+fontcolor+" !important;display:inline !important;opacity:1.0 !important;}"+
	"a.w2g_active:hover{color:#fff !important;}"+
	"a.namelock,a.namelock span.extension {color:#"+fontcolor+";opacity:1.0!important;padding: 0 !important;}");	
});

//Switch the Lockstate
function toggle_control(filename)
{
	$(".ignore-click").unbind("click");

	//Walk through the Filelists
	$('#fileList tr').each(function() {
		var $tr = $(this);
		var $_tr = $tr.html().replace(/^\s+|\s+$/g, '').replace('<span class="extension">','').split('</span>').join('');
		var actionname = 'getstate_w2g';
		if($_tr.indexOf(filename)!=-1)
		{
		    if($_tr.indexOf(lockstate)==-1)
		    {		//unlock
					$tr.find('a.action[data-action!='+actionname+']').removeClass('locked');
					$tr.find('a.action[data-action!='+actionname+']').addClass('permanent');
					$tr.find('a.action[data-action='+actionname+']').removeClass('w2g_active');
					$tr.find('a.namelock').addClass('name').removeClass('namelock').removeClass('ignore-click');
					$tr.find('td.filesize').removeClass('statelock');
					$tr.find('td.date').removeClass('statelock');
					$tr.find('td').removeClass('statelock');
					$tr.find('a.statelock').addClass('name');
		    }
		    else if($_tr.indexOf(lockstate)!=-1)
		    {		//lock	
					$tr.find('a.permanent[data-action!='+actionname+']').removeClass('permanent');
					$tr.find('a.action[data-action='+actionname+']').addClass('w2g_active');
					$tr.find('a.action[data-action!='+actionname+']:not([class*=favorite])').addClass('locked');
					$tr.find('a.name').addClass('namelock').removeClass('name').addClass('ignore-click');
					$tr.find('td.filesize').addClass('statelock');
					$tr.find('td.date').addClass('statelock');
					$tr.find('td').addClass('statelock');
		    }
		}
	});

        $(".ignore-click").click(function(){
                return false;
        });

}

//Get the current state
function getStateSingle(_id, _filename, _owner, _safe)
{
        oc_dir = $('#dir').val();
	oc_path = oc_dir +'/'+_filename;
	
	$.ajax({
        url: OC.filePath('workin2gether','ajax','workin2gether.php'),
        type: "post",
        data: { path: escapeHTML(oc_path), safe: _safe, owner: _owner, id: _id},
        success: function(data){postmode(_filename,data)},
    	});
}

function getStateAll(_array, _safe)
{
	oc_dir = $('#dir').val();
	if (oc_dir !== '/') oc_dir += '/';

	$.ajax({
        	url: OC.filePath('workin2gether','ajax','workin2gether.php'),
        	type: "post",
        	data: { batch: "true", path: JSON.stringify(_array), safe: _safe , folder: escapeHTML(oc_dir)},
        	success: function(data){PushAll(data)},
        });

}

function PushAll( data )
{
	data = JSON.parse(data);
	for ( var i = 0; i < data.length ; i++ )
        {
		postmode(data[i][1],data[i][3]);	
	}
}

function postmode(filename,data)
{
		filename = filename.replace(/%20/g,' ');
		var html = '<img class="svg" src="'+OC.imagePath('workin2gether','lock.png')+'"></img>'+'<span>'+escapeHTML(data)+'</span>';

		$('tr').filterAttr('data-file',filename).find('td.filename').find('a.name').find('span.fileactions').find("a.action").filterAttr('data-action','getstate_w2g').html(html);
		$('tr').filterAttr('data-file',filename).find('td.filename').find('a.namelock').find('span.fileactions').find("a.action").filterAttr('data-action','getstate_w2g').html(html);
		
		if(!data.includes(t('workin2gether','No permission')))	
			toggle_control(filename);
}
