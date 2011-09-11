<?php

/**
 *
 *
 *
 *
 **/


class SoftwareAuthorMemberDOD extends DataObjectDecorator {

	function extraStatics () {
		return array(
			"db" => array(
				"AreYouHappyForPeopleToContactYou" => "Boolean",
				"ContactDetailURL" => "Varchar(255)",
				"AreYouAvailableForPaidSupport" => "Boolean",
				"Rate15Mins" => "Currency",
				"Rate120Mins" => "Currency",
				"Rate480Mins" => "Currency"
			)
		);
	}

	function updateMemberFormFields(&$fields) {
		$currency = Payment::site_currency();
		$field = $fields->fieldByName("AreYouHappyForPeopleToContactYou"); $field->setTitle("Are you happy to answer questions about your code?");
		$field = $fields->fieldByName("ContactDetailURL"); $field->setTitle("Contact Details URL");
		$field = $fields->fieldByName("AreYouAvailableForPaidSupport"); $field->setTitle("Are you available for paid support?");
		$field = $fields->fieldByName("Rate15Mins"); $field->setTitle("If applicable, how much do you charge (in $currency) for a fifteen minute skype chat?");
		$field = $fields->fieldByName("Rate120Mins"); $field->setTitle("If applicable, how much do you charge (in $currency) for a two hour support block?");
		$field = $fields->fieldByName("Rate480Mins"); $field->setTitle("If applicable, how much do you charge (in $currency) for a development day (eight hours)?");
			$js = '
				jQuery(document).ready(
					function() {
						jQuery("#AreYouHappyForPeopleToContactYou input").live("change",
							function(){
								if(jQuery("#AreYouHappyForPeopleToContactYou input").is(":checked")) {
									jQuery("#ContactDetailURL").slideDown();
									jQuery("#AreYouAvailableForPaidSupport").show();
								}
								else {
									jQuery("#ContactDetailURL, #AreYouAvailableForPaidSupport, #Rate15Mins, #Rate120Mins, #Rate480Mins").slideUp();
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
			';
		Requirements::customScript($js, "SoftwareAuthorMemberDOD");
	}

	function onBeforeWrite() {
		if($this->owner->AreYouHappyForPeopleToContactYou) {
			if(!$this->owner->AreYouHappyForPeopleToContactYou) {
				$this->owner->Rate15Mins = 0;
				$this->owner->Rate120Mins = 0;
				$this->owner->Rate480Mins = 0;
			}
		}
		else {
			$this->owner->ContactDetailURL = '';
			$this->owner->AreYouAvailableForPaidSupport = FALSE;
			$this->owner->Rate15Mins = 0;
			$this->owner->Rate120Mins = 0;
			$this->owner->Rate480Mins = 0;
		}
	}


}


