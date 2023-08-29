/**
 * Return a rendered template
 *
 * @access  public
 * @since   1.0.0
 */
var tops_template = function( template, data ) {
  
  if( !tops_vars.templates[template] ) {
		return 'HTML template does not exist!';
	}
	
	var template_str = tops_vars.templates[template];
	
	jQuery.each(data, function( key, value ) {
	  var re = new RegExp('{{'+key+'}}', 'g');
	  template_str = template_str.replace(re, value);
	});
	
	return template_str;
};