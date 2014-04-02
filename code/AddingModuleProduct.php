<?php

/**
 *
 *
 *
 *
 **/


class AddingModuleProduct extends Page {


	private static $icon = "ecommerce_software/images/treeicons/AddingModuleProduct";


}


class AddingModuleProduct_Controller extends Page_Controller {

	function init(){
		parent::init();
		if(!Member::currentUser()) {
			$link = RegisterAndEditDetailsPage::link_for_going_to_page_via_making_user($this->Link());
			$this->redirect($link);
		}
		if(isset($_REQUEST["ModuleProductID"])) {
			$this->moduleProductID = intval($_REQUEST["ModuleProductID"]);
		}
	}

	function Form () {
		if(Member::currentUser()) {
			return new AddingModuleProduct_Form($this, "Form",$this->moduleProductID);
		}
	}


}

class AddingModuleProduct_Form extends Form  {

	function __construct($controller, $name, $moduleProductID = 0) {

		$fields = new FieldList();
		$moduleProduct = null;
		if($moduleProductID) {
			$fields->push(new HeaderField('AddEditModule','Edit '.$controller->dataRecord->Title, 2));
			$fields->push(new HiddenField('ModuleProductID',$moduleProductID, $moduleProductID));
			$moduleProduct = ModuleProduct::get()->byID($moduleProductID);
		}
		else {
			$fields->push(new HeaderField('AddEditModule',$controller->dataRecord->Title, 2));
			$fields->push(new HiddenField('ModuleProductID',0, 0));
		}
		$fields->push(new TextField('Code','Code (folder name)'));
		$moduleProductGroups = ModuleProductGroup::get()
			->filter(array("ParentID:GreaterThan" => 0));
		if($moduleProductGroups->count()) {
			$types = array("" => " --- please select ---";
			$types += $moduleProductGroups->map($index = 'ID', $titleField = 'MenuTitle'))->toArray(); ;
		}
		else {
			$types = array();
		}
		//$fields->push(new DropdownField('ParentID','Type', $types, $controller->dataRecord->ID));
		$fields->push(new TextField('Title','Title'));
		$fields->push(new TextareaField('MetaDescription','Three sentence Introduction'));
		$fields->push(new HtmlEditorField('Content','Long Description'));
		$fields->push(new TextField('AdditionalTags','Additional Keyword(s), comma separated'));
		$fields->push(new HeaderField('LinkHeader','Links', 4));
		$fields->push(new TextField('MainURL','Home page'));
		$fields->push(new TextField('ReadMeURL','Read me file - e.g. http://www.mymodule.com/readme.md'));
		$fields->push(new TextField('DemoURL','Demo - e.g. http://demo.mymodule.com/'));
		$fields->push(new TextField('SvnURL','SVN repository - allowing you to checkout trunk or latest version - e.g. http://svn.mymodule.com/svn/trunk/'));
		$fields->push(new TextField('GitURL','GIT repository - e.g. https://github.com/my-github-username/silverstripe-my-module'));
		$fields->push(new TextField('OtherURL','Link to other repository or download URL - e.g. http://www.mymodule.com/downloads/'));
		$fields->push(new CheckboxSetField('EcommerceProductTags','Tags', EcommerceProductTag::get()->map()->toArray()));
		$member = Member::currentUser();
		if($member->inGroup("ADMIN")) {
			$fields->push(new CheckboxSetField('Authors','Author(s)', Member::get()->exclude("Email", "")->map("ID", "Email")->toArray()));
			$fields->push(new DropdownField('ParentID','Move to', ProductGroup::get()->map()->toArray()));
			$fields->push(new CheckboxField('ShowInMenus','Show in menus (unticking both boxes here will hide the module)'));
			$fields->push(new CheckboxField('ShowInSearch','Show in search (unticking both boxes here will hide the module)'));

		}
		else {
			$fields->push(new HiddenField('ShowInMenus', '', 0));
			$fields->push(new HiddenField('ShowInSearch', '', 0));
			$fields->push(new HiddenField('ParentID', '', $controller->dataRecord->ID));
			if($moduleProduct) {
				$moduleProduct->ParentID = $controller->dataRecord->ID;
				$moduleProduct->ShowInSearch = 0;
				$moduleProduct->ShowInMenus = 0;
			}
		}
		if($moduleProduct && $moduleProduct->canEdit()) {
			if($authors = $moduleProduct->Authors()) {
				$authorsIDArray = $authors->map("ID","ID");
				$authorsIDArray[0] = 0;
				$fields->push($this->ManyManyComplexTableFieldAuthorsField($controller, $authorsIDArray));
				//$controller, $name, $sourceClass, $fieldList = null, $detailFormFields = null, $sourceFilter = "", $sourceSort = "", $sourceJoin = ""
			}
		}
		$actions = new FieldList(new FormAction("submit", "submit"));
		$validator = new AddingModuleProduct_RequiredFields($moduleProductID, array('Code', 'Name', 'ParentID', 'MainURL'));
		parent::__construct($controller, $name, $fields, $actions, $validator);
		if($moduleProduct) {
			$this->loadDataFrom($moduleProduct);
		}
		return $this;
	}

	function submit($data, $form) {

		$member = Member::currentUser();
		if(!$member) {
			$form->setMessage("You need to be logged in to edit this module.", "bad");
			$this->redirectBack();
			return;
		}
		$data = Convert::raw2sql($data);
		$page = null;
		if(isset($data["ModuleProductID"])) {
			$page = ModuleProduct::get()->byID(intval($data["ModuleProductID"]));
		}
		if(!$page) {
			$page = new ModuleProduct();
		}
		if(isset($page->ParentID)){
			$oldParentID = $page->ParentID;
		}
		$form->saveInto($page);
		$page->Title = $data["Title"];
		$page->MenuTitle = $data["Title"];
		if(!$member->inGroup("ADMIN")) {
			$page->ShowInMenus = 0;
			$page->ShowInMenus = 0;
			$parentPage = AddingModuleProduct::get()->First();
			if($parentPage) {
				$page->ParentID = $parentPage->ID;
			}
		}
		$page->writeToStage('Stage');
		$page->Publish('Stage', 'Live');
		$page->Status = "Published";
		$page->flushCache();
		if($page->Authors()->count() == 0 && $member) {
			$page->Authors()->addMany(array($member->ID => $member->ID));
		}
		if(!isset( $data["EcommerceProductTags"]) || ! is_array( $data["EcommerceProductTags"]) || !count( $data["EcommerceProductTags"])) {
			$data["EcommerceProductTags"] = array(-1 => -1);
		}
		if(isset($data["AdditionalTags"]) && $data["AdditionalTags"]) {
			$extraTagsArray = explode(",", $data["AdditionalTags"]);
			if(is_array($extraTagsArray) && count($extraTagsArray)) {
				foreach($extraTagsArray as $tag) {
					$tag = trim($tag);
					$obj = EcommerceProductTag::get()
						->filter(array("Title" => $tag))
						->first();
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
		if(Director::is_ajax()) {
			return $page->renderWith("ModuleProductInner");
		}
		else {
			$this->redirect($page->Link());
		}
	}


	protected function ManyManyComplexTableFieldAuthorsField($controller, $authorsIDArray) {
		$detailFields = new FieldList();
		$detailFields->push(new TextField("ScreenName"));
		$detailFields->push(new TextField("FirstName"));
		$detailFields->push(new TextField("Surname"));
		$detailFields->push(new TextField("Email"));
		$detailFields->push(new TextField("GithubURL", "Github URL"));
		$detailFields->push(new TextField("SilverstripeDotOrgURL", "www.silverstripe.org URL"));
		$detailFields->push(new TextField("CompanyName", "Company Name"));
		$detailFields->push(new TextField("CompanyURL", "Company URL"));
		$detailFields->push(new CheckboxField("AreYouHappyForPeopleToContactYou", "are you happy for people to contact you about your module?"));
		$detailFields->push(new TextField("ContactDetailURL", "Contact details URL"));
		$detailFields->push(new TextField("OtherURL", "Other URL"));
		$detailFields->push(new CheckboxField("AreYouAvailableForPaidSupport", "are you available for paid support?"));
		$detailFields->push(new NumericField("Rate15Mins", "Indicative rate for 15 minute skype chat"));
		$detailFields->push(new NumericField("Rate120Mins", "Indicative rate for two hour work slot"));
		$detailFields->push(new NumericField("Rate480Mins", "Indicative rate for one day of work"));
		$field = new GridField(
			'Authors', //name
			'Member', //sourceClass
			'Member', //sourceClass
			/*
			array(
				"ScreenName" => "Screen name",
				"FirstName" => "First name",
				"Surname" => "Surname",
				"Email" => "Email"
				),//fieldList
			$detailFields,//detailFormFields
			"\"Member\".\"ID\" IN (".implode(",", $authorsIDArray).")",//sourceFilter
			"",//sourceSort
			null//sourceJoin
			*/
		);
		$field->setPopupCaption("Edit Author");
		$field->setAddTitle("Author");
		return $field;
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
			if(ModuleProduct::get()
				->filter(array("Code" => $type))
				->exclude("ModuleProduct", $this->currentID - 0)
				->count()
			) {
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
