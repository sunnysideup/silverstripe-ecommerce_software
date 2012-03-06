/**
  * @description: update Cart using AJAX (JSON data source)
  * as well as making any "add to cart" and "remove from cart" links
  * work with AJAX (if setup correctly)
  * @author nicolaas @ sunny side up . co . nz
  **/

(function($){
	$(document).ready(
		function() {
			ModuleProduct.init();
		}
	);
})(jQuery);

ModuleProduct = {

	/**
	 * initialises all the ajax functionality
	 */
	init: function () {
		jQuery(".md2html").each(
			function() {
				var id = jQuery(this).attr("rel");
				jQuery("#"+id).hide();
			}
		)
		jQuery(".md2html").click(
			function(e) {
				var id = jQuery(this).attr("rel");
				var converter = new Markdown.Converter();
				var text = jQuery("#" + id+ " pre").text();
				var html = converter.makeHtml(text);
				jQuery("#" + id).html(html).slideDown();
				jQuery(this).unbind('click');
				jQuery(this).click(
					function(e) {
						e.preventDefault();
						var id = jQuery(this).attr("rel");
						jQuery("#" + id).slideToggle();
					}
				);
			}
		)
	}


}





