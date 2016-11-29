<?php



class ImportModulesTask extends BuildTask
{
    private static $parent_url_segment = "new-modules";
    public function get_parent_url_segment()
    {
        return self::$parent_url_segment;
    }
    public function set_parent_url_segment($s)
    {
        self::$parent_url_segment = $s;
    }

    private static $data_source = "/mysite/data/modules.csv";
    public function get_data_source()
    {
        return self::$data_source;
    }
    public function set_data_source($s)
    {
        self::$data_source = $s;
    }

    public function getTitle()
    {
        return "Import modules from csv";
    }

    public function getDescription()
    {
        return "Opens the local csv file and imports all the modules";
    }

    public function run($request)
    {
        $this->createAuthorGroup();
        $this->importmodules();
        $this->sortPagesAlphabetically();
        return "BBBBBBBBBBBBBBB";
    }

    public function cleantags()
    {
        DB::query("DELETE FROM \"EcommerceProductTag\" WHERE TRIM(Title) = '' OR TRIM(Code) = '' OR Title IS NULL or Code IS NULL;");
    }

    public function importmodules()
    {
        $rowPosition = 1;
        $fields = array(
            0 => "ImportID",
            1 => "ScreenName",
            2 => "Email",
            3 => "GithubURL",
            4 => "SilverstripeDotOrgURL",
            5 => "CompanyName",
            6 => "CompanyURL",
            7 => "Code",
            8 => "Title",
            9=> "MainURL",
            10=> "ReadMeURL",
            11=> "DemoURL",
            12=> "SvnURL",
            13=> "GitURL",
            14=> "OtherURL",
            15=> "Tags",
            16=> "Description"
        );
        $fullArray = array();
        $file = Director::baseFolder().self::get_data_source();
        if (($handle = fopen($file, "r")) !== false) {
            while (($row = fgetcsv($handle)) !== false) {
                $numberOfFields = count($row);
                //echo "<p> $num fields in line $rowPosition: <br /></p>\n";
                $rowPosition++;
                for ($fieldPosition = 0; $fieldPosition < $numberOfFields; $fieldPosition++) {
                    $fullArray[$rowPosition][$fields[$fieldPosition]] = $row[$fieldPosition];
                    //echo $fields[$fieldPosition]." = ".$row[$fieldPosition] . "<br />\n";
                }
            }
            fclose($handle);
        }
        $this->makeModules($fullArray);
        return $fullArray;
    }

    private function createAuthorGroup()
    {
        if (!$group = Group::get()
            ->filter(array("Code" => Config::inst()->get("SoftwareAuthorMemberDOD", "register_group_code")))
            ->first()
        ) {
            $group = new Group();
            $group->Code = SoftwareAuthorMemberDOD::get_register_group_code();
            $group->Title = SoftwareAuthorMemberDOD::get_register_group_title();
            $group->write();
            Permission::grant($group->ID, SoftwareAuthorMemberDOD::get_register_group_access_key());
            DB::alteration_message("GROUP: ".SoftwareAuthorMemberDOD::get_register_group_code().' ('.SoftwareAuthorMemberDOD::get_register_group_title().')', "created");
        } elseif (DB::query("SELECT * FROM Permission WHERE GroupID = ".$group->ID." AND Code = '".SoftwareAuthorMemberDOD::get_register_group_access_key()."'")->numRecords() == 0) {
            Permission::grant($group->ID, SoftwareAuthorMemberDOD::get_register_group_access_key());
        }
    }

    private function makeModules($rows)
    {
        increase_time_limit_to(600);
        DB::query("DELETE FROM \"EcommerceProductTag\" WHERE TRIM(Title) = '';");
        $parent = ModuleProductGroup::get()
            ->filter(array("URLSegment" => self::$parent_url_segment))
            ->first();
        if ($parent) {
            $parentID = $parent->ID;
            unset($parent);
        } else {
            $parentID = 0;
        }
        $group = Group::get()
            ->filter(array("Code" => SoftwareAuthorMemberDOD::get_register_group_code()))
            ->first();
        if (!$group) {
            user_error("Group for authors could not be found!");
        }
        if ($parentID) {
            if ($rows) {
                foreach ($rows as $row) {
                    $new = null;
                    $ImportID = intval($row["ImportID"]);
                    if ($ImportID) {
                        $ScreenName = Convert::raw2sql($row["ScreenName"]);
                        $Email = Convert::raw2sql($row["Email"]);
                        $GithubURL = Convert::raw2sql($row["GithubURL"]);
                        $SilverstripeDotOrgURL = Convert::raw2sql($row["SilverstripeDotOrgURL"]);
                        $CompanyName = Convert::raw2sql($row["CompanyName"]);
                        $CompanyURL = Convert::raw2sql($row["CompanyURL"]);
                        $Code = Convert::raw2sql($row["Code"]);
                        $Title = Convert::raw2sql($row["Title"]);
                        $MainURL = Convert::raw2sql($row["MainURL"]);
                        $ReadMeURL = Convert::raw2sql($row["ReadMeURL"]);
                        $DemoURL = Convert::raw2sql($row["DemoURL"]);
                        $SvnURL = Convert::raw2sql($row["SvnURL"]);
                        $GitURL = Convert::raw2sql($row["GitURL"]);
                        $OtherURL = Convert::raw2sql($row["OtherURL"]);
                        $Tags =  Convert::raw2sql($row["Tags"]);
                        $Description =  Convert::raw2sql($row["Description"]);
                        $page = ModuleProduct::get()
                            ->filter(array("ImportID" => $ImportID))
                            ->first();
                        if (!$page) {
                            $new = true;
                            $page = new ModuleProduct();
                        } else {
                            $new = false;
                        }
                        if ($new == false && isset($page->ParentID) && $page->ParentID  && $page->ParentID > 0 && $page->ParentID != $parentID) {
                            //do nothing
                        } else {
                            if (!$new) {
                                DB::query("DELETE FROM \"EcommerceProductTag_Products\" WHERE ProductID = ".$page->ID);
                            }
                            if ($Title && $Code) {
                                $member = null;
                                //member
                                if ($ScreenName) {
                                    $member = Member::get()
                                        ->filter(array("ScreenName" => $ScreenName))
                                        ->first();
                                }
                                $identifierField = Member::get_unique_identifier_field();
                                if (!$member) {
                                    $member = Member::get()
                                        ->filter(array($identifierField, $Email));
                                }
                                if ($member) {
                                    $i = 0;
                                    while ($replaceMember = Member::get()
                                    ->filter(array($identifierField => $Email))
                                    ->exclude("ID", $member->ID)
                                    ->first()
                                    && $i < 100000
                                ) {
                                        if ($replaceMember) {
                                            $i++;
                                            $member = $replaceMember;
                                            $Email = $Email."_DOUBLE_$i";
                                        }
                                    }
                                }
                                if (!$member) {
                                    $member = new Member();
                                }
                                if ($ScreenName) {
                                    $member->ScreenName = $ScreenName;
                                    $member->Email = $Email;
                                    $member->GithubURL = $GithubURL;
                                    $member->SilverstripeDotOrgURL = $SilverstripeDotOrgURL;
                                    $member->CompanyName = $CompanyName;
                                    $member->CompanyURL = $CompanyURL;
                                    if (!$member->Password) {
                                        $member->Password = Member::create_new_password();
                                    }
                                    $member->write();
                                    $member->Groups()->add($group);
                                } else {
                                    DB::alteration_message("no screen name provided for <b>$Title</b>", "deleted");
                                }
                                //page
                                $page->ImportID = $ImportID;
                                $page->ParentID = $parentID;
                                $page->ShowInSearch = 1;
                                $page->ShowInMenus = 1;
                                $page->Title = $Title;
                                $page->MenuTitle = $Title;
                                $page->MetaDescription = strip_tags($Description);
                                $page->Code = $Code;
                                $page->InternalItemID = $Code;
                                $page->URLSegment = $Code;
                                $page->ProvideComments = true;

                                $page->MainURL = $MainURL;
                                $page->ReadMeURL = $ReadMeURL;
                                $page->DemoURL= $DemoURL;
                                $page->SvnURL = $SvnURL;
                                $page->GitURL = $GitURL;
                                $page->OtherURL = $OtherURL;

                                $page->writeToStage('Stage');
                                $page->Publish('Stage', 'Live');
                                $page->Status = "Published";
                                $tagsArray = explode(",", $Tags);
                                if ($tagsArray && count($tagsArray)) {
                                    foreach ($tagsArray as $tag) {
                                        $tag = Convert::raw2sql(trim($tag));
                                        if ($tag) {
                                            $tagObject = EcommerceProductTag::get()
                                                ->filter(array("Title" => $tag))
                                                ->First();
                                            if (!$tagObject) {
                                                $tagObject = EcommerceProductTag::get()
                                                    ->filter(array("Synonyms:PartialMatch" => $tag))
                                                    ->first();
                                            }
                                            if (!$tagObject) {
                                                $tagObject = new EcommerceProductTag();
                                                $tagObject->Title = $tag;
                                                $tagObject->write();
                                            }
                                            $existingTags = $page->EcommerceProductTags();
                                            $existingTags->add($tagObject);
                                        }
                                    }
                                }
                                if ($member) {
                                    DB::query("DELETE FROM \"ModuleProduct_Authors\" WHERE \"ModuleProductID\" = ".$page->ID." AND MemberID <> ".$member->ID);
                                    $existingAuthors = $page->Authors();
                                    $existingAuthors->add($member);
                                } else {
                                    DB::alteration_message("no member for  <b>$Title</b>", "deleted");
                                }
                                if ($new === true) {
                                    DB::alteration_message("added <b>$Title</b>", "created");
                                } elseif ($new === false) {
                                    DB::alteration_message("updated <b>$Title</b>", "edited");
                                } elseif ($new === null) {
                                    DB::alteration_message("error updating <b>$Title</b>", "deleted");
                                } else {
                                    DB::alteration_message("BIG error updating <b>$Title</b>", "deleted");
                                }
                            } else {
                                DB::alteration_message("row found without title or code", "deleted");
                            }
                        }
                    } else {
                        DB::alteration_message("row found without import id", "deleted");
                    }
                }
            } else {
                DB::alteration_message("no data found", "deleted");
            }
        } else {
            DB::alteration_message("no parent group page found (a ModuleProductGroup with new-modules as URL Segment", "deleted");
        }
    }

    private function sortPagesAlphabetically()
    {
        $parent = ModuleProductGroup::get()
            ->filter(array("URLSegment" => self::$parent_url_segment))
            ->first();
        if ($parent) {
            $pages = ModuleProduct::get()
                ->filter(array("ParentID" => $parent->ID))
                ->sort("Title", "ASC");
            $i = 0;
            foreach ($pages as $page) {
                $i++;
                DB::query("Update \"SiteTree\"      SET \"Sort\" = $i WHERE \"ID\" = ".$page->ID);
                DB::query("Update \"SiteTree_Live\" SET \"Sort\" = $i WHERE \"ID\" = ".$page->ID);
            }
        }
    }

    public function deleteobsoletemoduleowners($request = null)
    {
        $group = Group::get()
            ->filter(array("Code" => SoftwareAuthorMemberDOD::get_register_group_code()))
            ->first();
        if ($group) {
            $members = $group->Members();
            if ($members) {
                foreach ($members as $member) {
                    if ($member->ModuleProducts() && $member->ModuleProducts()->count()) {
                        DB::alteration_message("The following member own modules so will not be deleted ...".$member->Email.": ".$member->Title, "created");
                    } else {
                        DB::alteration_message("The following member does not seem to have any module products ...".$member->Email.": ".$member->Title, "deleted");
                        if (!$member->inGroup("ADMIN")) {
                            $member->delete();
                        } else {
                            DB::alteration_message("The following member is an Admin so will not be deleted ...".$member->Email.": ".$member->Title, "created");
                        }
                    }
                }
            } else {
                DB::alteration_message("could not find members for group with code = ".self::get_register_group_code(), "deleted");
            }
        } else {
            DB::alteration_message("could not find group with code = ".self::get_register_group_code(), "deleted");
        }
    }
}







class ImportModulesTask_AdminDecorator extends Extension
{
    private static $allowed_actions = array(
        "importmodulestask" => true,
        "deleteobsoletemoduleowners" => true
    );

    public function updateEcommerceDevMenuMigrations($buildTasks)
    {
        $buildTasks[] = "importmodulestask";
        //$buildTasks[] = "deleteobsoletemoduleowners";
        return $buildTasks;
    }


    /**
     * executes build task: ImportModulesTask
     *
     */
    public function importmodulestask($request)
    {
        $buildTask = new ImportModulesTask($request);
        $buildTask->run($request);
        $this->owner->displayCompletionMessage($buildTask);
    }


    public function deleteobsoletemoduleowners()
    {
        $this->runTask("ImportModulesTask", $request);
    }
}
