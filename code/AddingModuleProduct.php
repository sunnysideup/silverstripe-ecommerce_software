<?php

/**
 *
 *
 *
 *
 **/


class AddingModuleProduct extends Page {


	public static $icon = "ecommerce_software/images/treeicons/AddingModuleProduct";

	public function canView($member = null) {
		if(!$member) {
			$member = Member::currentMember();
		}
		return $member ? true : false;
	}


}


class AddingModuleProduct_Controller extends Page_Controller {

	function init(){
		parent::init();
		if(!Member::currentMember()) {
			RegisterAndEditDetailsPage::link_for_going_to_page_via_making_user($this->Link());
		}
		if(isset($_REQUEST["ModuleProductID"])) {
			$this->moduleProductID = intval($_REQUEST["ModuleProductID"]);
		}
	}

	function Form () {
		return new AddingModuleProduct_Form($this, "Form",$this->moduleProductID);
	}


}

class AddingModuleProduct_Form extends Form  {

	function __construct($controller, $name, $moduleProductID = 0) {
		$fields = new FieldSet();
		if($moduleProductID) {
			$fields->push(new HeaderField('AddEditModule','Edit '.$controller->dataRecord->Title, 2));
			$fields->push(new HiddenField('ModuleProductID',$moduleProductID, $moduleProductID));
		}
		else {
			$fields->push(new HeaderField('AddEditModule',$controller->dataRecord->Title, 2));
			$fields->push(new HiddenField('ModuleProductID',0, 0));
		}
		$fields->push(new TextField('Code','Code (folder name)'));
		$moduleProductGroup = DataObject::get("ModuleProductGroup", "ParentID > 0");
		if($moduleProductGroup) {
			$types = $moduleProductGroup->toDropDownMap($index = 'ID', $titleField = 'MenuTitle', $emptyString = "-- please select --", $sort = false) ;
		}
		else {
			$types = array();
		}
		//$fields->push(new DropdownField('ParentID','Type', $types, $controller->dataRecord->ID));
		$fields->push(new TextField('Title','Title'));
		$fields->push(new TextareaField('MetaDescription','Three sentence Introduction', 3));
		$fields->push(new HTMLEditorField('Content','Long Description', 3));
		$fields->push(new TextField('AdditionalTags','Additional Keyword(s), comma separated'));
		$fields->push(new HeaderField('LinkHeader','Links', 4));
		$fields->push(new TextField('MainURL','Home page'));
		$fields->push(new TextField('ReadMeURL','Read me file - e.g. http://www.mymodule.com/readme.md'));
		$fields->push(new TextField('DemoURL','Demo - e.g. http://demo.mymodule.com/'));
		$fields->push(new TextField('SvnURL','SVN repository - allowing you to checkout trunk or latest version - e.g. http://svn.mymodule.com/svn/trunk/'));
		$fields->push(new TextField('GitURL','GIT repository - e.g. https://github.com/my-git-username/silverstripe-my-module'));
		$fields->push(new TextField('OtherURL','Link to other repository or download URL - e.g. http://www.mymodule.com/downloads/'));
		$fields->push(new CheckboxSetField('EcommerceProductTags','Tags', DataObject::get("EcommerceProductTag")));
		$member = Member::currentMember();
		if($member->IsAdmin()) {
			$fields->push(new CheckboxSetField('Authors','Author(s)', DataObject::get("Member", "Email <> '' AND Email IS NOT NULL")->toDropDownMap('ID','Email')));
			$fields->push(new DropdownField('ParentID','Move to', DataObject::get("ProductGroup")->toDropDownMap('ID','MenuTitle')));
		}

		$actions = new FieldSet(new FormAction("submit", "submit"));
		$validator = new AddingModuleProduct_RequiredFields($moduleProductID, array('Code', 'Name', 'ParentID', 'MainURL'));
		parent::__construct($controller, $name, $fields, $actions, $validator);
		if($moduleProductID) {
			$moduleProduct = DataObject::get_by_id("ModuleProduct", $moduleProductID);
			if($moduleProduct) {
				$this->loadDataFrom($moduleProduct);
			}
		}
		return $this;
	}

	function submit($data, $form) {
		$member = Member::currentMember();
		if(!$member) {
			$form->setMessage("You need to be logged in to edit this module.", "bad");
			Director::redirectBack();
			return;
		}
		$data = Convert::raw2sql($data);
		$page = null;
		if(isset($data["ModuleProductID"])) {
			$page = DataObject::get_by_id("ModuleProduct", intval($data["ModuleProductID"]));
		}
		if(!$page) {
			$page = new ModuleProduct();
		}
		if(isset($page->ParentID)){
			$oldParentID = $page->ParentID;
		}
		$form->saveInto($page);
		$page->MetaTitle = $data["Title"];
		$page->MenuTitle = $data["Title"];
		$page->writeToStage('Stage');
		$page->Publish('Stage', 'Live');
		$page->Status = "Published";
		$page->flushCache();
		$page->Authors()->addMany(array($member->ID => $member->ID));
		if(!isset( $data["EcommerceProductTags"]) || ! is_array( $data["EcommerceProductTags"]) || !count( $data["EcommerceProductTags"])) {
			$data["EcommerceProductTags"] = array(-1 => -1);
		}
		if(isset($data["AdditionalTags"]) && $data["AdditionalTags"]) {
			$extraTagsArray = explode(",", $data["AdditionalTags"]);
			if(is_array($extraTagsArray) && count($extraTagsArray)) {
				foreach($extraTagsArray as $tag) {
					$tag = trim($tag);
					$obj = DataObject::get_one("EcommerceProductTag", "\"Title\" = '$tag'");
					if(!$obj) {
						$obj = new EcommerceProductTag();
						$obj->Title = $tag;
						$obj->write();
					}
					$data["EcommerceProductTags"][$obj->ID] = $obj->ID;
				}
			}
		}
		DB::query("DELETE FROM \"EcommerceProductTag_Products\" WHERE \"ProductID\" = ".$page->ID. " AND \"EcommerceProductTagID\" NOT IN (".implode(",", $data["EcommerceProductTags"]).")");
		if(is_array($data["EcommerceProductTags"]) && count($data["EcommerceProductTags"])) {
			$page->EcommerceProductTags()->addMany($data["EcommerceProductTags"]);
		}
		if($member->IsAdmin() && $page && $oldParentID){
			$pages = DataObject::get("ModuleProduct", "\"Sort\" > ".$page->Sort." AND \"ParentID\" = ".$oldParentID, "Sort ASC", null, 1);
			foreach($pages as $page){
				//no need to do anymore.
			}
		}
		Director::redirect($page->Link());
	}
}

class AddingModuleProduct_RequiredFields extends RequiredFields {

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
