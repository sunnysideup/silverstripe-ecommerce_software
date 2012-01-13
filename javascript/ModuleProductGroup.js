/**
  * @description: update Cart using AJAX (JSON data source)
  * as well as making any "add to cart" and "remove from cart" links
  * work with AJAX (if setup correctly)
  * @author nicolaas @ sunny side up . co . nz
  **/

(function($){
	$(document).ready(
		function() {
			ModuleProductGroup.init();
		}
	);
})(jQuery);

ModuleProductGroup = {

	startFilter: "",
		set_startFilter:function(s) {this.startFilter = s;},

	currentFilter: "",
		set_currentFilter:function(s) {this.currentFilter = s;},

	urlFiltered: false,
		set_urlFiltered:function(b) {this.urlFiltered = b;},

	/**
	 * initialises all the ajax functionality
	 */
	init: function () {
		this.setupExplanations();

		if(!this.urlFiltered) {
			this.setupKeywordSearch();
			this.setupTagClicks();
			if(this.startFilter) {
				this.filterFor(this.startFilter);
			}
			else {
				jQuery("#SidebarModuleProductGroupTags li.showAll").hide();
			}
		}
	},

	setupExplanations: function(){
		jQuery(".explanation").hide();
		jQuery("a.moreInfoLink").click(
			function(event) {
				event.preventDefault();
				var moreInfoSelector = "#"+jQuery(this).attr("rel");
				jQuery(moreInfoSelector).slideToggle();
			}
		);
	},

	setupTagClicks: function(){
		if(ModuleProductGroup.urlFiltered) {
			return true;
		}
		jQuery(".tags a, #SidebarModuleProductGroupTags a").click(
			function(event) {
				event.preventDefault();
				var tag = jQuery(this).attr("rel");
				ModuleProductGroup.filterFor(tag);
			}
		);
	},

	setupKeywordSearch: function(){
		jQuery("#ModuleSearchForm #Search input").keyup(
			function(){
				phrase = escape(jQuery(this).val());
				if(phrase.length > 2) {
					jQuery(this).addClass("readyToSearch");
					jQuery(this).removeClass("notReadyToSearch");
				}
				else {
					jQuery(this).addClass("notReadyToSearch");
					jQuery(this).removeClass("readyToSearch");
				}
			}
		);
		jQuery("#ModuleSearchForm #Search input").keyup();
		jQuery("#ModuleSearchForm #Search input").change(
			function() {
				phrase = jQuery(this).val();
				if(phrase.length > 2) {
					jQuery("#ModuleSearchForm input.action").hide();
					url = jQuery(this).parents("form").attr('action');
					jQuery("#Search").addClass("loading");
					jQuery.getJSON(
						url,
						{
							Search: escape(phrase),
							action_modulesearchformresults: "Filter"
						},
						function(data){
							jQuery("#ModuleSearchForm").removeClass("loading");
							if(data.ModuleProducts && data.ModuleProducts.length > 0) {
								jQuery(".productList > li").hide();
								for(i = 0; i < data.ModuleProducts.length; i++) {
									var selector= "li#ModuleProductID" + data.ModuleProducts[i];
									jQuery(selector).show();
									jQuery("#SidebarModuleProductGroupTags li.showAll").show();
								}
							}
							else {
								alert("no modules found");
								jQuery("#ModuleSearchForm #Search input").focus();
								jQuery(".productList > li").show();
							}
						}
					);
				}
				else {
					jQuery("#ModuleSearchForm #Search input").focus();
					jQuery(".productList > li").show();
				}
			}
		);
	},

	filterFor: function(tag) {
		jQuery("#ModuleSearchForm #Search input").val("");
		jQuery(".tags a, #SidebarModuleProductGroupTags a").removeClass("current");
		if(ModuleProductGroup.currentFilter == tag || tag == "") {
			jQuery(".productList > li").show();
			ModuleProductGroup.currentFilter = "";
			jQuery("#SidebarModuleProductGroupTags li.showAll").hide();
		}
		else {
			ModuleProductGroup.currentFilter = tag;
			jQuery(".productList > li").hide();
			jQuery(".productList > li."+tag).show();
			jQuery(".productList > li."+tag+" a[rel='"+tag+"'], #SidebarModuleProductGroupTags a[rel='"+tag+"']").addClass("current");
			jQuery("#SidebarModuleProductGroupTags li.showAll").show();
		}
		this.scrollTo("Products");
	},

	scrollTo: function(id){
		jQuery('html,body').animate({scrollTop: jQuery("#"+id).offset().top},'slow');
	}


}





