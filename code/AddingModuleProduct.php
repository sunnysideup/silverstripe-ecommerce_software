<?php

/**
 *
 *
 *
 *
 **/ 


class AddingModuleProduct extends Page {

	
	public static $icon = "ecommerce_software/images/treeicons/AddingModuleProduct";


}


class AddingModuleProduct_Controller extends Page_Controller {


	function init(){
		parent::init();
		if(!Member::currentMember()) {
			RegisterAndEditDetailsPage::link_for_going_to_page_via_making_user($this->Link());
		}
	}

	function Form () {
		return new AddingModuleProduct_Form($this, "Form");
	}

}

class AddingModuleProduct_Form extends Form  {

	function __construct($controller, $name, $moduleProductID = 0) {
		$moduleProductGroup = DataObject::get("ModuleProductGroup", "ParentID > 0");
		if($moduleProductGroup) {
			$types = $moduleProductGroup->toDropDownMap($index = 'ID', $titleField = 'MenuTitle', $emptyString = "-- please select --", $sort = false) ;
		}
		else {
			$types = array();
		}
		$fields = new FieldSet();
		if($moduleProductID) {
			$fields->push(new HeaderField('AddEditModule','Edit '.$controller->dataRecord->Title, 2));
			$fields->push(new HiddenField('moduleProductID',$moduleProductID));
		}
		else {
			$fields->push(new HeaderField('AddEditModule','Add to '.$controller->dataRecord->Title, 2));
		}
		$fields->push(new TextField('Code','Code (folder name)'));
		$fields->push(new DropdownField('ParentID','Type', $types, $controller->dataRecord->ID));
		$fields->push(new TextField('Title','Title'));
		$fields->push(new TextField('MainURL','Main URL'));
		$fields->push(new TextField('RepositoryURL','Repository URL'));
		$fields->push(new TextareaField('MetaDescription','Short Description (~12 words)', 3));
		$fields->push(new HTMLEditorField('Content','Long Description (optional)', 3));
		$fields->push(new CheckboxSetField('EcommerceProductTags','Tags', DataObject::get("EcommerceProductTag")));
		$fields->push(new TextField('AddATag','Additional Keyword(s), comma separated'));
		$fields->push(new HiddenField('AuthorID', Member::currentUserID()));
		$actions = new FieldSet(new FormAction("submit", "submit"));
		$validator = new AddingModuleProduct_RequiredFields($moduleProductID, array('Code', 'Name', 'ParentID'));
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
		$data = Convert::raw2sql($data);
		$page = null;
		if(isset($data["moduleProductID"])) {
			$page = DataObject::get_by_id("ModuleProduct", intval($data["moduleProductID"]));
		}
		if(!$page) {
			$page = new ModuleProduct();
		}
		$form->saveInto($page);
		$page->MetaTitle = $data["Title"];
		$page->MenuTitle = $data["Title"];
		$page->writeToStage('Stage');
		$page->Publish('Stage', 'Live');
		$page->Status = "Published";
		$page->flushCache();
		if(!isset( $data["EcommerceProductTags"]) || ! is_array( $data["EcommerceProductTags"]) || !count( $data["EcommerceProductTags"])) {
			 $data["EcommerceProductTags"] = array(-1 => -1);
		}
		if(isset($data["AddATag"]) && $data["AddATag"]) {
			$extraTagsArray = explode(",", $data["AddATag"]);
			foreach($extraTagsArray as $tag) {
				if(!DataObject::get("EcommerceProductTag", "\"Title\" = '$tag'")) {
					$obj = new EcommerceProductTag();
					$obj->Title = $tag;
					$obj->write;
					$data["EcommerceProductTags"][$obj->ID] = $obj->ID;
				}
			}
		}
		DB::query("Delete FROM EcommerceProductTag_Products WHERE ProductID = ".$page->ID. " AND EcommerceProductTagID NOT IN (".implode(",", $data["EcommerceProductTags"]).")");
		if(is_array($data["EcommerceProductTags"]) && count($data["EcommerceProductTags"])) {
			$page->EcommerceProductTags()->addMany($data["EcommerceProductTags"]);
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
		$codes = DB::query("SELECT \"Code\" FROM ModuleProduct WHERE ModuleProduct.ID <> ".$this->currentID)->column();
		if($codes) {
			$js = '
				jQuery(document).ready(
					function() {
						var AddingModuleProductCodes = new Array('.implode(",", $codes).');
						jQuery("#Code input").changed(
							function(){
								var val = jQuery("#Code input").val();
								if(jQuery.inArray(val, AddingModuleProductCodes)) {
									jQuery("#Code input").focus();
									alert("Your code "+val+" is already in use - please check if your code is listed already or use an alternative code.");
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
			if(DataObject::get_one("ModuleProduct", "\"Code\" = '$type' AND ModuleProduct{$extension}.ID <>".$this->currentID)) {
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
