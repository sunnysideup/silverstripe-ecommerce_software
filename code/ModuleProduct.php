<?php

/**
 * @author nicolaas [at] sunny side up . co . nz
 * this extension of product is for software products (modules)
 *
 *
 **/


class ModuleProduct extends Product {

	public static $icon = "ecommerce_software/images/treeicons/ModuleProduct";

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

	function getCMSFields(){
		$fields = new FieldSet();
		$fields = parent::getCMSFields();
		$manyManyCTF = new ManyManyComplexTableField(
			$controller = $this,
			$name = "Authors",
			$sourceClass = "Member",
			$fieldList = null,
			$detailFormFields = null,
			$sourceFilter = "",
			$sourceSort = "",
			$sourceJoin = ""
		);
		$fields->addFieldToTab('Root.Content.Software', new TextField('Code','Code (this should be the same as the recommended folder name)'));
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



}


class ModuleProduct_Controller extends Product_Controller {

	function init(){
		parent::init();
		Requirements::themedCSS("ModuleProduct");
	}

	function Form () {
		if($this->canEdit()) {
			return new AddingModuleProduct_Form($this, "Form",$this->ID);
		}
	}

}
