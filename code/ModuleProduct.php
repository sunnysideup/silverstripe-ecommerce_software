<?php

/**
 * @author nicolaas [at] sunny side up . co . nz
 * this extension of product is for software products (modules)
 *
 *
 **/


class ModuleProduct extends Product {

	public static $icon = "ecommerce_software/images/treeicons/ModuleProduct";

	public static $api_access = array(
		'view' => array(
				"ModuleTitle",
				"Code",
				"MainURL",
				"ReadMeURL",
				"DemoURL",
				"SvnURL",
				"GitURL",
				"OtherURL",
				"EcommerceProductTags",
				"Authors"
			)
	 );

	public static $db = array(
		"Code" => "Varchar",
		"MainURL" => "Varchar(255)",
		"ReadMeURL" => "Varchar(255)",
		"DemoURL" => "Varchar(255)",
		"SvnURL" => "Varchar(255)",
		"GitURL" => "Varchar(255)",
		"OtherURL" => "Varchar(255)",
		"ImportID" => "Int"
	);

	public static $has_many = array(
		"ModuleProductEmails" => "ModuleProductEmail"
	);

	public static $casting = array(
		"ModuleTitle" => "Varchar"
	);

	function ModuleTitle(){return $this->getModuleTitle();}
	function getModuleTitle() {
		return $this->getField("MenuTitle");
	}


	public static $many_many = array(
		"Authors" => "Member"
	);

	public static $singular_name = "Module";
		function i18n_singular_name() { return _t("Order.MODULE", "Module");}

	public static $plural_name = "Modules";
		function i18n_plural_name() { return _t("Order.Modules", "Modules");}

	function canEdit($member = null){
		if($member = Member::currentMember()) {
			if($member->IsShopAdmin()) {
				return true;
			}
			if($authors = $this->Authors()) {
				foreach($authors as $author) {
					if($author->ID == $member->ID) {
						return true;
					}
				}
			}
		}
		return false;
	}

	public static $searchable_fields = array(
		'Title' => "PartialMatchFilter",
		'InternalItemID' => "PartialMatchFilter",
		'ImportID',
		'ShowInSearch',
		'AllowPurchase',
		'FeaturedProduct',
		'Price'
	);


	function getCMSFields(){
		$fields = new FieldSet();
		$fields = parent::getCMSFields();
		$authors = $this->Authors();
		$sortString = "";
		if($authors) {
			$authorsArray = $authors->map("ID", "ScreenName");
			$sortString = "";
			$sortStringEnd = "";
			if(is_array($authorsArray) && count($authorsArray)) {
				foreach($authorsArray as $ID => $ScreenName) {
					$sortString .= "IF(Member.ID = $ID, 1, ";
					$sortStringEnd .= ")";
				}
				$sortString .= " 0".$sortStringEnd." DESC";
			}
		}
		$manyManyCTF = new ManyManyComplexTableField(
			$controller = $this,
			$name = "Authors",
			$sourceClass = "Member",
			$fieldList = null,
			$detailFormFields = null,
			$sourceFilter = "",
			$sourceSort = $sortString ,
			$sourceJoin = ""
		);
		$fields->addFieldToTab('Root.Content.Software', new TextField('Code','Code (this should be the same as the recommended folder name)'));
		$fields->addFieldToTab('Root.Content.Software', new TextareaField('MetaDescription','Three sentence introduction', 3));
		$fields->addFieldToTab('Root.Content.Software', new TextField('MainURL','Link to home page for the module - e.g. http://www.mymodule.com/'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('ReadMeURL','Link to read me file - e.g. http://www.mymodule.com/readme.md'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('DemoURL','Link to a demo - e.g. http://demo.mymodule.com/'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('SvnURL','Link to the SVN URL - allowing you to checkout trunk or latest version directly - e.g. http://svn.mymodule.com/svn/trunk/'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('GitURL','Link to the GIT URL - e.g. https://github.com/my-git-username/silverstripe-my-module'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('OtherURL','Link to other repository or download URL - e.g. http://www.mymodule.com/downloads/'));
		$fields->addFieldToTab('Root.Content.Software', new ReadonlyField('ImportID','Import Identifier'));
		if($this->ID) {
			$fields->addFieldToTab('Root.Content.Authors', $manyManyCTF);
		}
		return $fields;
	}

	/**
	 * Has an email been sent?
	 * @return Boolean
	 *
	 */
	public function HasEmail(){
		if($this->EmailObject()) {return true;}
		return false;
	}

	/**
	 * Return the ModuleProductEmail
	 * @return Object (ModuleProductEmail)
	 *
	 */
	public function EmailObject(){
		return DataObject::get_one("ModuleProductEmail", "\"ModuleProductID\" = ".$this->ID);
	}

	public function EmailDefaults(){
		$to = "";
		$authorEmailArray = array();
		if($authors = $this->Authors()) {
			foreach($authors as $author) {
				$authorEmailArray[$author->ScreenName] = $author->Email;
			}
		}
		$to = implode(", ", $authorEmailArray);
		$subject = _t("ModuleProduct.SUBJECT", "Check your module:").$this->Title;
		$body = $this->createBodyAppendix(implode(", ", array_flip($authorEmailArray)));
		return new ArrayData (
			array(
				"To" => $to,
				"Subject" => $subject,
				"Body" => $body
			)
		);
	}

	protected function createBodyAppendix($screenName){
		$pageLink = Director::absoluteURL($this->Link());
		$passwordResetLink = Director::absoluteURL("Security/lostpassword");
		$logInLink = Director::absoluteURL("Security/login");
		$customisationArray = array(
			"PageLink" => $pageLink,
			"PasswordResetLink" => $passwordResetLink,
			"LogInLink" => $logInLink,
			"Title" => $this->Title,
			"ScreenName" => $screenName
		);
		$body = $this->customise($customisationArray)->renderWith("ModuleProductEmailBody");
		return $body;
	}

}



class ModuleProduct_Controller extends Product_Controller {

	function init(){
		parent::init();
		Requirements::javascript("ecommerce_software/javascript/Markdown.Converter.js");
		Requirements::javascript("ecommerce_software/javascript/ModuleProduct.js");
		Requirements::themedCSS("ModuleProduct");
	}

	function Form () {
		if($this->canEdit()) {
			return new AddingModuleProduct_Form($this, "Form",$this->ID);
		}
	}

	function ReadMeContent() {

		if($this->ReadMeURL){
			$this->ReadMeURL = str_replace("http://raw.github", "https://raw.github", $this->ReadMeURL);
			if($this->url_exists($this->ReadMeURL)) {
				return file_get_contents($this->ReadMeURL);
			}
		}
	}

	function url_exists($url) {
		// Version 4.x supported
		$handle   = curl_init($url);
		if (false === $handle)
		{
				return false;
		}
		curl_setopt($handle, CURLOPT_HEADER, false);
		curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
		curl_setopt($handle, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15") ); // request as if Firefox
		curl_setopt($handle, CURLOPT_NOBODY, true);
		curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
		$connectable = curl_exec($handle);
		curl_close($handle);
		return $connectable;
	}


	/**
	 *
	 * @return Object Product
	 */
	function PreviousProduct(){
		$products = DataObject::get("ModuleProduct", "\"Sort\" < ".$this->Sort." AND ParentID = ".$this->ParentID, "\"Sort\" DESC", "", 1);
		if($products) {
			foreach($products as $product) {
				return $product;
			}
		}
	}

	/**
	 *
	 * @return Object Product
	 */
	function NextProduct(){
		$products = DataObject::get("ModuleProduct", "\"Sort\" > ".$this->Sort." AND ParentID = ".$this->ParentID, "\"Sort\" ASC", "", 1);
		if($products) {
			foreach($products as $product) {
				return $product;
			}
		}
	}


	function HasPreviousOrNextProduct(){
		if($this->NextProduct()) {
			return true;
		}
		if($this->PreviousProduct()) {
			return true;
		}
		return true;
	}

	function EmailForm(){
		if($this->canEdit()) {
			if(!$this->HasEmail()){
				return new ModuleProductEmail_Form($this, "EmailForm", $this->dataRecord);
			}
		}
	}




}



