jQuery(document).ready( function () { 
	jQuery("#dt-additional-info").addClass("mceEditor"); 
	if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
		jQuery("#dt-additional-info").wrap( "<div id='editorcontainer'></div>" ); 
		tinyMCE.execCommand("mceAddControl", false, "dt-additional-info");
	}
});