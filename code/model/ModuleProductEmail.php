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
		$defaults = $moduleProduct->EmailDefaults();
		$fields = new FieldSet();
		$fields->push(new TextField('To','To', $defaults->To));
		$fields->push(new TextField('Subject','Subject', $defaults->Subject));
		$fields->push(new HiddenField('ModuleProductID','ModuleProductID', $moduleProduct->ID));
		$fields->push(new HTMLEditorField('Body','Body', $defaults->Body));
		$actions = new FieldSet(new FormAction("submit", "submit"));
		$validator = new ModuleProductEmail_RequiredFields(array("Subject"));
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
		if(Director::is_ajax()) {
			return "mail sent!";
		}
		else {
			Director::redirect($page->Link());
		}
	}
}

class ModuleProductEmail_RequiredFields extends RequiredFields {

	function __construct($array) {
		parent::__construct($array);
	}

	function javascript() {
		$js = '';
		Requirements::customScript($js, "ModuleProductEmail");
		return parent::javascript();
	}


	/**
	* Allows validation of fields via specification of a php function for validation which is executed after
	* the form is submitted
	*/
	function php($data) {
		$valid = true;
		if(!isset($data["Subject"]) || (isset($data["Subject"]) && strlen($data["Subject"])) < 3) {
			$errorMessage = _t("Form.PLEASEENTERASUBJECT", "Please enter a subject");
			$this->validationError(
				$fieldName = "Subject",
				$errorMessage,
				"required"
			);
			$valid = false;
		}
		if(!isset($data["To"]) || (isset($data["To"]) && strlen($data["To"]) < 3)) {
			$errorMessage = _t("Form.PLEASEENTERANEMAIL", "Please enter an e=mail");
			$this->validationError(
				$fieldName = "To",
				$errorMessage,
				"required"
			);
		}
		if(!$valid) {
			return false;
		}
		return parent::php($data);
	}

}
