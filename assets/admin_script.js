jQuery(document).ready(function ($) {
	window.send_to_editor_default = window.send_to_editor;
	jQuery('#wpss_upload_image_button').click(function() {
		formfield = jQuery('#wpss_upload_image').attr('name');
		tb_show('', 'media-upload.php?type=image&TB_iframe=true');
		return false;
	});
	window.send_to_editor = function(html) {
		/*imgurl = jQuery('img',html).attr('src');
		jQuery('#wpss_upload_image').val(imgurl);
		tb_remove();*/
		$('body').append('<div id="temp_image">' + html + '</div>');
		var img = $('#temp_image').find('img');
		imgurl   = img.attr('src');
		imgclass = img.attr('class');
		imgid    = parseInt(imgclass.replace(/\D/g, ''), 10);
		var base_url = window.location.origin+'/wp-content/';
		var myimage = imgurl.replace(base_url, "");
		jQuery('#wpss_upload_image').val(myimage);
		$('#temp_image').remove();
		tb_remove();
		window.send_to_editor = window.send_to_editor_default;
	}
});