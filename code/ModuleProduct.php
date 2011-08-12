<?php

/**
 *
 *
 *
 *
 **/ 


class ModuleProduct extends Product {

	public static $hide_ancester = "Product";

	public static $db = array(
		"Code" => "Varchar",
		"Name" => "Varchar",
		"MoreInfoURL" => "Varchar(255)",
		"RepositoryURL" => "Varchar(255)"
	);

	public static $has_one = array(
		"Member" => "Member"
	);

}


class ModuleProduct_Controller extends Product_Controller {


}
