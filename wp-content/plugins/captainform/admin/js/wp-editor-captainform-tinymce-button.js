(function () {
	tinymce.create('tinymce.plugins.captainform', {
		init: function (editor, url) {

			var root_url = url.replace("/captainform/admin/js", "/captainform");
			editor.addButton('captainform', {
				image: root_url + "/admin/images/captainform-18.png",
				tooltip: 'Insert Form',
				cmd: 'captainform_insert'
			});

			editor.addCommand('captainform_insert', function () {
				// Calls the pop-up modal
				editor.windowManager.open({
					action: 'captainform_insert_dialog',
					title: 'Insert Form',
					width: (jQuery(window).width() * 0.7) < 600 ? jQuery(window).width() * 0.7 : 600,
					// minus head and foot of dialog box
					height: ((jQuery(window).height() - 36 - 50) * 0.7) < 365 ? (jQuery(window).height() - 36 - 50) * 0.7 : 365,
					resizable: true,
					inline: 1,
					scrollbars: 'x',
					id: 'captainform-insert-dialog',
					buttons: [{
						text: 'Insert form',
						id: 'captainform-button-insert',
						class: 'captainform-insert',
						onclick: function (e) {
							insertShortcode();
							editor.windowManager.close();
						},
					},
						{
							text: 'Cancel',
							id: 'captainform-button-cancel',
							onclick: 'close'
						}],
				});
				appendInsertDialog(root_url);
			});
		},
		getInfo: function () {
			return {
				longname: 'CaptainForm for Wordpress plugin',
				author: 'Captain Form',
				authorurl: 'http://www.captainform.com',
				infourl: '',
				version: "2.0.0"
			};
		}

	});

	tinymce.PluginManager.add('captainform', tinymce.plugins.captainform);

	function insertShortcode() {
		var publish_code = document.getElementById('captainform_publish_code').value;
		var custom_vars = document.getElementById('captainform_custom_vars_code').value;
		code = publish_code.substring(0,publish_code.length -1 ) + custom_vars + ']';
		top.window.tinyMCE.execCommand('mceInsertContent', false, code);
	}

	function appendInsertDialog(root_url) {
		var captainformDialogBody;
		captainformDialogBody = jQuery('#captainform-insert-dialog-body');
		captainformDialogBody.append('<link rel="stylesheet" type="text/css" href="' + root_url + '/admin/css/chosen/chosen.css" />');
		captainformDialogBody.append('<link rel="stylesheet" type="text/css" href="' + root_url + '/admin/css/widget.css" />');
		captainformDialogBody.append('<link rel="stylesheet" type="text/css" href="' + root_url + '/admin/css/publish_lightbox_posts.css" />');

		captainformDialogBody.append('<span class="captainform_spinner">Loading, please wait...</span>');

		// Get the form template from WordPress
		var ajax_url = captainform_get_absolute_path() + 'admin-ajax.php';
		jQuery.post(ajax_url, {
			action: 'captainform_insert_dialog'
		}, function (response) {
			window.captainform_is_widget_page = false;
			captainformDialogBody.children('.captainform_spinner').remove();
			captainformDialogBody.append(response);
		}).done(function () {
			if (typeof jscolor != 'undefined') {
				jscolor.dir = root_url + "/admin/js/jscolor/";
				captainform_bind_widget();
			}
		});
	}
})();

function captainform_get_absolute_path() {
	var loc      = window.location;
	var pathName = loc.pathname.substring(0, loc.pathname.lastIndexOf('/') + 1);
	return loc.href.substring(0, loc.href.length - ((loc.pathname + loc.search + loc.hash).length - pathName.length));
}