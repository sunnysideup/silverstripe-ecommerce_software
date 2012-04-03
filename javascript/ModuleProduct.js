/**
	* @description: update Cart using AJAX (JSON data source)
	* as well as making any "add to cart" and "remove from cart" links
	* work with AJAX (if setup correctly)
	* @author nicolaas @ sunny side up . co . nz
	**/

(function(jQuery){
	jQuery(document).ready(
		function() {
			ModuleProduct.init();
			ModuleProduct.mailForm();
			ModuleProduct.editForm();
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
		);
		//ModuleProduct.forms();
	},

	editForm: function() {
		var options = {
			target: '#ModuleProductInnerHolder',   // target element(s) to be updated with server response
			beforeSubmit:  function(){jQuery("#LayoutHolder").html("saving ...");}  // pre-submit callback
		};

		// bind to the form's submit event
		jQuery("#AddingModuleProduct_Form_Form").submit(
			function() {
				// inside event callbacks 'this' is the DOM element so we first
				// wrap it in a jQuery object and then invoke ajaxSubmit
				jQuery(this).ajaxSubmit(options);

				// !!! Important !!!
				// always return false to prevent standard browser submit and page navigation
				return false;
			}
		);
	},

	mailForm: function() {
		var options = {
			target: '#EmailFormHolder',   // target element(s) to be updated with server response
			beforeSubmit:  function(){jQuery("#EmailFormHolder").html("mailing ...");}  // pre-submit callback
		};

		// bind to the form's submit event
		jQuery("#EmailFormHolder form").submit(
			function() {
				// inside event callbacks 'this' is the DOM element so we first
				// wrap it in a jQuery object and then invoke ajaxSubmit
				jQuery(this).ajaxSubmit(options);

				// !!! Important !!!
				// always return false to prevent standard browser submit and page navigation
				return false;
			}
		);
	}



}





