<?php

/**
 *
 *
 *
 *
 **/ 


class ModuleProductGroup extends ProductGroup {


	public static $icon = "ecommerce_software/images/treeicons/ModuleProductGroup";


}


class ModuleProductGroup_Controller extends ProductGroup_Controller {


	function Form () {
		return new AddingModuleProduct_Form($this, "Form", 0);
	}

}
