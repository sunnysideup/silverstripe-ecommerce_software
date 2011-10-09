jQuery(document).ready(
	function() {
		jQuery("#AreYouHappyForPeopleToContactYou input").live("change",
			function(){
				if(jQuery("#AreYouHappyForPeopleToContactYou input").is(":checked")) {
					jQuery("#ContactDetailURL").slideDown();
					jQuery("#AreYouAvailableForPaidSupport").show();
				}
				else {
					jQuery("#ContactDetailURL").slideUp();
					jQuery("#AreYouAvailableForPaidSupport input").attr("checked", "").change();
					jQuery("#AreYouAvailableForPaidSupport").hide();
				}
			}
		);
		jQuery("#AreYouAvailableForPaidSupport input").live("change",
			function(){
				if(jQuery("#AreYouAvailableForPaidSupport input").is(":checked")) {
					jQuery("#Rate15Mins, #Rate120Mins, #Rate480Mins").slideDown();
				}
				else {
					jQuery("#Rate15Mins, #Rate120Mins, #Rate480Mins").slideUp();
				}
			}
		);
		jQuery("#AreYouHappyForPeopleToContactYou input").change();
		jQuery("#AreYouAvailableForPaidSupport input").change();
	}
);
