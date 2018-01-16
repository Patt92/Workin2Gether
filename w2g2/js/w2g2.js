var color = '008887';
var fontcolor = 'ffffff';

$(document).ready(function () {
    var directoryLock = '';

    getBackgroundColor();
    getFontColor();
    getDirectoryLockStatus();

    lockstate = t('w2g2', 'Locked');

    if (typeof FileActions !== 'undefined' && $('#dir').length > 0) {
        OCA.Files.fileActions.registerAction({
            name: 'getstate_w2g',
            displayName: '',
            mime: 'all',
            permissions: OC.PERMISSION_ALL,
            type: OCA.Files.FileActions.TYPE_INLINE,
            icon: function () {
                return OC.imagePath('w2g2', 'lock.png')
            },
            actionHandler: function (filename, context) {
                var id = context.$file.attr('data-id');
                var fileName = filename;
                var owner = context.$file.attr('data-share-owner');
                var safe =  "false";
                var mountType = context.$file.attr('data-mounttype');
                var fileType = context.$file.attr('data-type');

                getStateSingle(id, fileName, owner, safe, mountType, fileType, directoryLock);
            }
        });

        var _files = [];

        //Walk through all files in the active Filelist
        $('#content').delegate('#fileList', 'fileActionsReady', function (event) {
            var $fileList = event.fileList.$fileList;

            $fileList.find('tr').each(function () {
                _files.push([
                    $(this).attr('data-id'),
                    $(this).attr('data-file'),
                    $(this).attr('data-share-owner'),
                    '',
                    $(this).attr('data-mounttype'),
                    $(this).attr('data-type')
                ]);
            });

            getStateForAllFiles(_files, "true", directoryLock);
        });
    }

    buildCSS();

    // Internal
    function getBackgroundColor() {
        $.ajax({
            url: OC.filePath('w2g2', 'ajax', 'getcolor.php'),
            type: "post",
            data: {type: 'color'},
            async: false,
            success: function (data) {
                if (data != "") {
                    color = data;
                }
            },
        });
    }

    function getFontColor() {
        $.ajax({
            url: OC.filePath('w2g2', 'ajax', 'getcolor.php'),
            type: "post",
            data: {type: 'fontcolor'},
            async: false,
            success: function (data) {
                if (data != "") {
                    fontcolor = data;
                }
            },
        });
    }

    function getDirectoryLockStatus() {
        $.ajax({
            url: OC.filePath('w2g2', 'ajax', 'directoryLock.php'),
            type: "post",
            data: {},
            async: false,
            success: function (data) {
                if (data !== "") {
                    directoryLock = data;
                }
            },
        });
    }

    function buildCSS() {
        var cssrules = $("<style type='text/css'> </style>").appendTo("head");

        cssrules.append(".statelock{ background-color:#" + color + ";color:#" + fontcolor + " !important;}" +
            ".statelock span.modified{color:#" + fontcolor + " !important;}" +
            "a.w2g_active{color:#" + fontcolor + " !important;display:inline !important;opacity:1.0 !important;}" +
            "a.w2g_active:hover{color:#fff !important;}" +
            "a.namelock,a.namelock span.extension {color:#" + fontcolor + ";opacity:1.0!important;padding: 0 !important;}");
    }
});

//Switch the Lockstate
function toggle_control(filename) {
    $(".ignore-click").unbind("click");

    //Walk through the Filelists
    $('#fileList tr').each(function () {
        var $tr = $(this);
        var $_tr = $tr.html().replace(/^\s+|\s+$/g, '').replace('<span class="extension">', '').split('</span>').join('');
        var actionname = 'getstate_w2g';

        if ($_tr.indexOf(filename) != -1) {
            if ($_tr.indexOf(lockstate) == -1) {
                unlock($tr, actionname);
            }
            else if ($_tr.indexOf(lockstate) != -1) {
                lock($tr, actionname);
            }
        }
    });

    $(".ignore-click").click(function (event) {
        event.preventDefault();

        return false;
    });

    removeLinksFromLockedDirectories();
}

//Get the current state
function getStateSingle(_id, _filename, _owner, _safe, _mountType, _fileType, _directoryLock) {
    if (_fileType === 'dir' && _directoryLock === 'directory_locking_none') {
        console.log('cannot lock directory');

        return;
    }

    oc_dir = $('#dir').val();
    oc_path = oc_dir + '/' + _filename;

    var data = {
        path: escapeHTML(oc_path),
        safe: _safe,
        owner: _owner ? _owner : '',
        id: _id,
        mountType: _mountType ? _mountType : '',
        fileType: _fileType
    };
    
    $.ajax({
        url: OC.filePath('w2g2', 'ajax', 'w2g2.php'),
        type: "post",
        data: data,
        success: function (data) {
            postmode(_filename, data)
        },
    });
}

function getStateForAllFiles(files, _safe, directoryLock) {
    oc_dir = $('#dir').val();

    if (oc_dir !== '/') {
        oc_dir += '/'
    };

    var data = {
        batch: "true",
        path: JSON.stringify(files),
        safe: _safe,
        folder: escapeHTML(oc_dir)
    };

    $.ajax({
        url: OC.filePath('w2g2', 'ajax', 'w2g2.php'),
        type: "post",
        data: data,
        success: function (data) {
            PushAll(data, directoryLock);
        },
    });

}

function PushAll(files, directoryLock) {
    files = JSON.parse(files);

    for (var i = 0; i < files.length; i++) {
        var fileName = files[i][1];
        var message = files[i][3];
        var fileType = files[i][5];

        // if (fileType === 'dir' && directoryLock === 'directory_locking_none') {
        //     return;
        // }

        postmode(fileName, message);
    }
}

function postmode(fileName, message) {
    fileName = fileName.replace(/%20/g, ' ');

    var html = '<img class="svg" src="' + OC.imagePath('w2g2', 'lock.png') + '"></img>' + '<span>' + escapeHTML(message) + '</span>';

    $('tr').filterAttr('data-file', fileName)
        .find('td.filename')
        .find('a.name')
        .find('span.fileactions')
        .find("a.action")
        .filterAttr('data-action', 'getstate_w2g')
        .html(html);

    $('tr').filterAttr('data-file', fileName)
        .find('td.filename')
        .find('a.namelock')
        .find('span.fileactions')
        .find("a.action")
        .filterAttr('data-action', 'getstate_w2g')
        .html(html);

    if ( ! message.includes(t('w2g2', 'No permission'))) {
        toggle_control(fileName);
    }
}

function unlock($tr, actionname) {
    $tr.find('a.action[data-action!=' + actionname + ']').removeClass('locked');
    $tr.find('a.action[data-action!=' + actionname + ']').addClass('permanent');
    $tr.find('a.action[data-action=' + actionname + ']').removeClass('w2g_active');
    $tr.find('a.namelock').addClass('name').removeClass('namelock').removeClass('ignore-click');

    var $fileSize = $tr.find('td.filesize');
    var $date = $tr.find('td.date');

    $fileSize.removeClass('statelock');
    $date.removeClass('statelock');

    $fileSize.unbind('click');
    $date.unbind('click');

    $tr.find('td').removeClass('statelock');
    $tr.find('a.statelock').addClass('name');
}

function lock($tr, actionname) {
    $tr.find('a.permanent[data-action!=' + actionname + ']').removeClass('permanent');

    $tr.find('a.action[data-action=' + actionname + ']').addClass('w2g_active');

    // $tr.find('a.action[data-action!=' + actionname + ']:not([class*=action-menu])').addClass('locked');
    $tr.find('a.action[data-action!=' + actionname + ']:not([class*=favorite])').addClass('locked');

    $tr.find('a.name').addClass('namelock').removeClass('name').addClass('ignore-click');

    var $fileSize = $tr.find('td.filesize');
    var $date = $tr.find('td.date');

    $fileSize.addClass('statelock');
    $date.addClass('statelock');

    $fileSize.click(function() {
        return false;
    });

    $date.click(function() {
        return false;
    });

    $tr.find('td').addClass('statelock');
}

function removeLinksFromLockedDirectories() {
    var $namelock = $("a.namelock");

    $namelock.removeAttr('href');
}
