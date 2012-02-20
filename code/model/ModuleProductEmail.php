<?php

/**
 * @author nicolaas [at] sunny side up . co . nz
 * this extension of product is for software products (modules)
 *
 *
 **/


class ModuleProductEmail extends DataObject {

	public static $icon = "ecommerce_software/images/treeicons/ModuleProduct";

	public static $db = array(
		"Subject" => "Varchar",
		"Body" => "HTMLText",
		"To" => "Varchar(255)",
		"Sent" => "Boolean"
	);

	public static $has_one = array(
		"ModuleProduct" => "ModuleProduct"
	);

	public static $singular_name = "Module Email";
		function i18n_singular_name() { return _t("ModuleProductEmail.MODULEPRODUCTEMAIL", "Module Email");}

	public static $plural_name = "Module Emails";
		function i18n_plural_name() { return _t("ModuleProductEmail.MODULEPRODUCTEMAILS", "Module Emails");}

	function canDelete($member = null) {
		return false;
	}

	function canEdit($member = null) {
		return false;
	}

	function onAfterWrite() {
		parent::onAfterWrite();
		if(!$this->Sent) {
			$email = new Email(
				$from = Email::getAdminEmail(),
				$to = $this->To,
				$subject = $this->Subject,
				$body = $this->Body,
				$bounceHandlerURL = null,
				$cc = null,
				$bcc = Email::getAdminEmail()
			);
			$email->send();
			$this->Sent = 1;
			$this->write();
		}
	}
}


class ModuleProductEmail_Form extends Form  {

	function __construct($controller, $name, $moduleProduct) {
		$fields = new FieldSet();
		$fields->push(new HeaderField('SendEmail',_t("ModuleProductEmail.SENDEMAILTOAUTHORS", "Send Email to Authors")));
		$fields->push(new TextField('To','To', $to));
		$fields->push(new TextField('Subject','Subject', $subject));
		$fields->push(new HiddenField('ModuleProductID', $moduleProduct->ID));
		$fields->push(new HTMLEditorField('Body','Body', $body));
		$actions = new FieldSet(new FormAction("submit", "submit"));
		$validator = new ModuleProductEmail_RequiredFields();
		parent::__construct($controller, $name, $fields, $actions, $validator);
		$this->loadDataFrom($moduleProduct->emailDefaults());
		return $this;
	}

	function submit($data, $form) {
		$member = Member::currentMember();
		if(!$member && !$member->IsAdmin()) {
			$form->setMessage("You need to be logged as an admin to send this email.", "bad");
			Director::redirectBack();
			return;
		}
		$data = Convert::raw2sql($data);
		$page = null;
		if(isset($data["ModuleProductID"])) {
			$page = DataObject::get_by_id("ModuleProduct", intval($data["ModuleProductID"]));
		}
		if(!$page) {
			$form->setMessage("Can not find the right page for saving this email.", "bad");
			Director::redirectBack();
			return;
		}
		$email = new ModuleProductEmail();
		$form->saveInto($email);
		$email->write();
		Director::redirect($page->Link());
	}
}

class ModuleProductEmail_RequiredFields extends RequiredFields {

	protected $currentID = 0;

	function __construct($currentID, $array) {
		$this->currentID = $currentID;
		parent::__construct($array);
	}

	function javascript() {
		$codes = DB::query("SELECT \"Code\" FROM ModuleProduct WHERE ModuleProduct.ID <> ".($this->currentID - 0))->column();
		if($codes) {
			$js = '
				jQuery(document).ready(
					function() {
						var AddingModuleProductCodes = new Array(\''.implode("','", $codes).'\');
						jQuery("#Code input").change(
							function(){
								var val = jQuery("#Code input").val();
								jQuery("#Code input").css("color", "green");
								for(i = 0; i < AddingModuleProductCodes.length; i++) {
									if(AddingModuleProductCodes[i] == val) {
										i = 999999999;
										alert("Your code \'"+val+"\' is already in use - please use an alternative code.");
										jQuery("#Code input").focus().css("color", "red");
									}
								}
							}
						);
					}
				);
			';
			Requirements::customScript($js, "AddingModuleProductCodes");
		}
		return parent::javascript();
	}


	/**
	* Allows validation of fields via specification of a php function for validation which is executed after
	* the form is submitted
	*/
	function php($data) {
		$valid = true;
		if(isset($data["Code"])) {
			$type = Convert::raw2sql($data["Code"]);
			$extension = '';
			if(Versioned::current_stage() == "Live") {
				$extension = "_Live";
			}
			if(DataObject::get_one("ModuleProduct", "\"Code\" = '$type' AND ModuleProduct{$extension}.ID <>".($this->currentID - 0))) {
				$errorMessage = sprintf(_t('Form.CODEALREADYINUSE', "Your code %s is already in use - please check if your code is listed already or use an alternative code."), $type);
				$this->validationError(
					$fieldName = "Code",
					$errorMessage,
					"required"
				);
				$valid = false;
			}
		}
		if(!$valid) {
			return false;
		}
		return parent::php($data);
	}

}
