<?php
/**
 * developed by www.sunnysideup.co.nz
 * Nicolaas modules [at] sunnysideup.co.nz
 **/


//copy the lines between the START AND END line to your /mysite/_config.php file and choose the right settings
//===================---------------- START ecommerce_software MODULE ----------------===================
//MUST SET
//Object::add_extension('Member', 'SoftwareAuthorMemberDOD');
//Object::add_extension("EcommerceDatabaseAdmin", "ImportModulesTask_AdminDecorator");
//ImportModulesTask::set_data_source("/data/importme.csv");

//MAY SET
//ProductsAndGroupsModelAdmin::add_managed_model("ModuleProduct");
//ModuleProductGroup::remove_sort_option("default");
//ModuleProductGroup::add_sort_option( $key = "created", $title = "Most recently added", $sql = "\"Created\" DESC");
//ModuleProductGroup::add_sort_option( $key = "code", $title = "Code", $sql = "\"Code\" ASC");

//===================---------------- END ecommerce_software MODULE ----------------===================


