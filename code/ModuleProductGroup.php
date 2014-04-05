<?php

/**
 * extension of Product Group
 *
 *
 *
 **/


class ModuleProductGroup extends ProductGroupWithTags {

	/**
	 * Standard SS variable.
	 */
	private static $singular_name = "Module";
		function i18n_singular_name() { return _t("ProductGroup.MODULEPRODUCTGROUP", "Module");}

	/**
	 * Standard SS variable.
	 */
	private static $plural_name = "Modules";
		function i18n_plural_name() { return _t("ProductGroup.MODULEPRODUCTGROUPS", "Modules");}


	private static $default_child = 'ModuleProduct';

	private static $icon = "ecommerce_software/images/treeicons/ModuleProductGroup";


	/**
	 * standard SS method
	 */
	function getCMSFields() {
		$fields = parent::getCMSFields();
		$fields->removeByName("Tags");
		return $fields;
	}

	/**
	 * returns the inital (all) products, based on the all the eligile products
	 * for the page.
	 *
	 * This is THE pivotal method that probably changes for classes that
	 * extend ProductGroup as here you can determine what products or other buyables are shown.
	 *
	 * The return from this method will then be sorted and filtered to product the final product list
	 *
	 * @param string $extraFilter Additional SQL filters to apply to the Product retrieval
	 * @param boolean $recursive
	 * @return DataObjectSet | Null
	 **/
	protected function currentInitialProducts($extraFilter = ''){
		$this->allProducts = parent::currentInitialProducts();
		if($extraFilter) {
			if($extraFilter instanceOf DataObjectSet) {
				$tags = $extraFilter;
				//do nothing
			}
			elseif($extraFilter instanceOf DataObject) {
				$tags = new ArrayList(array($extraFilter));
			}
			elseif(is_array($extraFilter) || intval($extraFilter) == $extraFilter) {
				$tags = EcommerceProductTag::get()
					->filter(array("ID" => $extraFilter));
			}
			else {
				user_error("Error in tags", E_USER_NOTICE);
			}
			$idArray = array();
			if($tags->count()) {
				$stage = '';
				if(Versioned::current_stage() == "Live") {
					$stage = "_Live";
				}
				if($tags->count()) {
					foreach($tags as $tag) {
						$rows = DB::query("
							SELECT \"ProductID\"
							FROM \"EcommerceProductTag_Products\"
								INNER JOIN \"ModuleProduct{$stage}\"
									ON \"ModuleProduct{$stage}\".\"ID\" = \"EcommerceProductTag_Products\".\"ProductID\"
							WHERE \"EcommerceProductTag_Products\".\"EcommerceProductTagID\" IN (".implode(",",$tags->column("ID")).")
						");
						if($rows) {
							foreach($rows as $row) {
								$idArray[$row["ProductID"]] = $row["ProductID"];
							}
						}
					}
					if(count($idArray)) {
						$this->allProducts = $this->allProducts->filter(array("ID" => $idArray));
					}
				}
			}
		}
		return $this->allProducts;
	}

	/**
	 * Returns the class we are working with for the initial product selection
	 * @return String
	 */
	protected function getClassNameSQL(){
		return "ModuleProduct";
	}



	/**
	 * returns the CLASSNAME part of the final selection of products.
	 * @return String
	 */
	protected function currentClassNameSQL() {
		return "ModuleProduct";
	}


	/**
	 * @param String $tagCode - code of the current tag.
	 * @return Object - DataObjectSet - Tags that are related to ModuleProducts
	 */
	function DefaultEcommerceProductTags($tagCode = ""){
		$stage = '';
		if(Versioned::current_stage() == "Live") {
			$stage = "_Live";
		}
		$idArray = array();
		$productIDs[0] = 0;
		$rows = DB::query("
			SELECT \"EcommerceProductTagID\"
			FROM \"EcommerceProductTag_Products\"
				INNER JOIN \"ModuleProduct{$stage}\"
					ON \"ModuleProduct{$stage}\".\"ID\" = \"EcommerceProductTag_Products\".\"ProductID\"
				INNER JOIN \"SiteTree{$stage}\"
					ON \"ModuleProduct{$stage}\".\"ID\" = \"SiteTree{$stage}\".\"ID\"
			WHERE \"SiteTree{$stage}\".ShowInSearch = 1
		");
		if($rows) {
			foreach($rows as $row) {
				$idArray[$row["EcommerceProductTagID"]] = $row["EcommerceProductTagID"];
			}
		}
		if(count($idArray)) {
			$tags = EcommerceProductTag::get()
				->filter(array("ID" => $idArray));
			if($tags->count()) {
				foreach($tags as $tag) {
					$tag->Link = $this->Link("show")."/".$tag->Code."/";
					if($tag->Code == $tagCode) {
						$tag->LinkingMode = "current";
					}
					else {
						$tag->LinkingMode = "link";
					}
				}
			}
			return $tags;
		}
	}


}


class ModuleProductGroup_Controller extends ProductGroupWithTags_Controller {

	function init(){
		parent::init();
		Requirements::javascript("ecommerce_software/javascript/ModuleProductGroup.js");
		Requirements::themedCSS("ModuleProduct", "ecommerce_software");
	}


	/**
	 * Return the products for this group.
	 *
	 * @return DataObjectSet(Products)
	 **/
	public function Products(){
		if($this->tag) {
			$toShow = $this->tag;
			Requirements::customScript("ModuleProductGroup.set_urlFiltered(true)", "set_urlFiltered");
		}
		else {
			$toShow = null;
		}
		return $this->ProductsShowable($toShow);
	}


	/**
	 * Tags available in the template
	 */
	function Tags() {
		$tagCode = "";
		if($this->tag) {
			$tagCode = $this->tag->Code;
		}
		return $this->DefaultEcommerceProductTags($tagCode);
	}



	/**
	 * Site search form
	 */
	function ModuleSearchForm() {
		$searchText =  _t('ModuleProductGroup.KEYWORDS', 'keywords');

		if($this->request) {
			$searchText = $this->request->getVar('Search');
		}

		$fields = new FieldList(
			new TextField('Search', _t('ModuleProductGroup.KEYWORDS', 'keywords'), $searchText)
		);
		$actions = new FieldList(
			new FormAction('modulesearchformresults',  _t('ModuleSearchForm.FILTER', 'Filter'))
		);
		$form = new SearchForm($this, 'ModuleSearchForm', $fields, $actions);
		$form->classesToSearch(array("SiteTree"));
		return $form;
	}

	/**
	 * Process and render search results.
	 *
	 * @param array $data The raw request data submitted by user
	 * @param SearchForm $form The form instance that was submitted
	 * @param SS_HTTPRequest $request Request generated for this action
	 */
	function modulesearchformresults($data, $form, $request) {
		$data = array(
			'Results' => $form->getResults(),
			'Query' => $form->getSearchQuery(),
			'Title' => _t('SearchForm.SearchResults', 'Search Results')
		);
		//search tags
		//search authors
		if($data["Results"]) {
			foreach($data["Results"] as $key => $resultItem) {
				if(!($resultItem instanceOf ModuleProduct)) {
					($data["Results"]->remove($resultItem));
				}
			}
		}
		else {
			$data["Results"] = new ArrayList();
		}
		$search = Convert::raw2sql($data["Query"]);
		if(strlen($search) > 2) {
			$additionalProducts = ModuleProduct::get()->filterAny(array("Code:PartialMatch" => $search, "MenuTitle:PartialMatch" => $search));
			if($additionalProducts) {
				foreach($additionalProducts as $moduleProduct) {
					$data["Results"]->push($moduleProduct);
				}
			}
			$tags = EcommerceProductTag::get()->filterAny(array("Title:PartialMatch" => $search, "Synonyms:PartialMatch" => $search, "Explanation:PartialMatch" => $search));
			if($tags->count()) {
				foreach($tags as $tag) {
					$rows = DB::query("SELECT ProductID FROM EcommerceProductTag_Products WHERE EcommerceProductTagID = ".$tag->ID);
					if($rows) {
						foreach($rows as $row) {
							$item = ModuleProduct::get()->byID($row["ProductID"]);
							if($item) {
								$data["Results"]->push($item);
							}
						}
					}
				}
			}
			$authors = Member::get()
				->filterAny(
					array(
						"ScreenName:PartialMatch" => $search,
						"FirstName:PartialMatch" => $search,
						"Surname:PartialMatch" => $search)
				);
			if($authors->count()) {
				foreach($authors as $author) {
					$rows = DB::query("SELECT \"ModuleProductID\" FROM \"ModuleProduct_Authors\" WHERE \"MemberID\" = ".$author->ID);
					if($rows) {
						foreach($rows as $row) {
							$item = ModuleProduct::get()->byID( $row["ModuleProductID"]);
							if($item) {
								$data["Results"]->push($item);
							}
						}
					}
				}
			}
		}
		if($data["Results"] && $data["Results"] instanceOf DataObjectSet) {
			$data["Results"]->removeDuplicates();
		}
		if(Director::is_ajax()) {
			return Convert::array2json(array("ModuleProducts" => $data["Results"]->column("ID")));
		}
		return $this->customise(array("Products" => $data["Results"]));
	}

	/**
	 * Admin ONLY action
	 * to view a list of all developers
	 * that have not been contacted yet
	 */
	function introemails(){
		$i = 0;
		$member = Member::currentUser();
		$dos = new ArrayList();
		if($member && $member->inGroup("ADMIN")) {
			$modules = ModuleProduct::get()
				->filter(array("ShowInSearch" => 1, "ShowInMenus" => 1));
			foreach($modules as $module) {
				if(!$module->HasEmail() && !$module->HasMemberContact()) {
					$i++;
					if($i < 10) {
						$dos->push($module);
					}
					else {
						break;
					}
				}
			}
			return $this->customise(array("Products" => $dos));
		}
		Security::permissionFailure($this, "You need to log in as an Administrator.");
	}

}
