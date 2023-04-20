CKEDITOR.editorConfig = function (config) {
    config.language = 'zh-cn';
    config.image_previewText = ' ';
    config.height = 800;
    config.width = 'auto';
    config.toolbarGroups = [
        { name: 'document', groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'clipboard', groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing', groups: [ 'find', 'selection', 'spellchecker', 'editing' ] },
		{ name: 'forms', groups: [ 'forms' ] },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi', 'paragraph' ] },
		{ name: 'links', groups: [ 'links' ] },
		{ name: 'insert', groups: [ 'insert' ] },
		'/',
		{ name: 'styles', groups: [ 'styles' ] },
		{ name: 'colors', groups: [ 'colors' ] },
		{ name: 'tools', groups: [ 'tools' ] },
		{ name: 'others', groups: [ 'others' ] },
    ];
    config.filebrowserImageUploadUrl = config.filebrowserImageUploadUrl || "/admin/ajax/uploadEditor";

    config.removeButtons = 'Save,NewPage,ExportPdf,Preview,Print,Templates,Scayt';

    config.format_tags = 'p;h1;h2;h3;pre';

    config.removeDialogTabs = 'image:advanced;link:advanced';

    config.font_names = '微软雅黑/Microsoft YaHei;宋体/SimSun;新宋体/NSimSun;仿宋/FangSong;楷体/KaiTi;黑体/SimHei;' + config.font_names;
};
