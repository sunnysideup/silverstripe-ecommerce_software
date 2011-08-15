<?php

/**
 *
 *
 *
 *
 **/ 


class ModuleProduct extends Product {

	public static $icon = "ecommerce_software/images/treeicons/ModuleProduct";

	public static $db = array(
		"Code" => "Varchar",
		"MainURL" => "Varchar(255)",
		"RepositoryURL" => "Varchar(255)"
	);

	public static $has_one = array(
		"Author" => "Member"
	);

	
	function getCMSFields(){
		$fields = parent::getCMSFields();
		$fields->addFieldToTab('Root.Content.Software', new TextField('Code','Code (folder name)'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('MainURL','Main URL'));
		$fields->addFieldToTab('Root.Content.Software', new TextField('RepositoryURL','Repository URL'));
		$fields->addFieldToTab('Root.Content.Software', new DropdownField('AuthorID','Author', DataObject::get("Member")->toDropdownMap('ID', 'Title')));
		return $fields;
	}
	

}


class ModuleProduct_Controller extends Product_Controller {


	function Form () {
		$id = 0;
		$memberID = Member::currentUserID();
		if($memberID && $memberID == $this->AuthorID) {
			$id = $this->ID;
		}
		return new AddingModuleProduct_Form($this, "Form", $id);
	}

}
