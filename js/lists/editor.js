$(document).ready(
	function()
	{
		CKEDITOR.replace( 'thankyou_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '350px',
			extraPlugins: 'codemirror,dragresize'
			
		});
		CKEDITOR.replace( 'goodbye_message', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '350px',
			extraPlugins: 'codemirror'
		});
		CKEDITOR.replace( 'confirmation_email', {
			fullPage: true,
			allowedContent: true,
			filebrowserUploadUrl: 'includes/create/upload.php',
			height: '350px',
			extraPlugins: 'codemirror'
		});
	}
);