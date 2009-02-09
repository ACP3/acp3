jQuery(function($) {
	function switchContainer() {
		var mode = $('#mode').val();
		var page = $('#page-type');
		var module = $('#module-container');
		var hints = $('#link-hints');
		var link = $('#link-container');

		if (mode == '1') {
			page.show();
			module.show();
			hints.hide();
			link.hide();
		} else if (mode == '2') {
			page.show();
			module.hide();
			hints.show();
			link.show();
		} else if (mode == '3') {
			page.show();
			module.hide();
			hints.hide();
			link.show();
		} else {
			page.hide();
			module.hide();
			hints.hide();
			link.hide();
		}
	}
	// Seitentyp
	switchContainer();
	$('#mode').change(function() {
		switchContainer();
	});
})