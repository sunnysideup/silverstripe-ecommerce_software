<?php

/**
 * extends Member with fields specific to the Software Author
 * @author nicolaas [at] sunnysideup.co.nz
 *
 *
 **/

class SoftwareAuthorMemberDOD extends DataExtension
{
    private static $register_group_title = "Software Authors";
    public function set_register_group_title($s)
    {
        self::$register_group_title = $s;
    }
    public function get_register_group_title()
    {
        return self::$register_group_title;
    }

    private static $register_group_code = "softwareauthors";
    public function set_register_group_code($s)
    {
        self::$register_group_code = $s;
    }
    public function get_register_group_code()
    {
        return self::$register_group_code;
    }

    private static $register_group_access_key = "SOFTWAREAUTHORS";
    public function set_register_group_access_key($s)
    {
        self::$register_group_access_key = $s;
    }
    public function get_register_group_access_key()
    {
        return self::$register_group_access_key;
    }

    private static $db = array(
        "ScreenName" => "Varchar(255)",
        "GithubURL" => "Varchar(255)",
        "SilverstripeDotOrgURL" => "Varchar(255)",
        "CompanyName" => "Varchar(255)",
        "CompanyURL" => "Varchar(255)",
        "AreYouHappyForPeopleToContactYou" => "Boolean",
        "ContactDetailURL" => "Varchar(255)",
        "OtherURL" => "Varchar(255)",
        "AreYouAvailableForPaidSupport" => "Boolean",
        "Rate15Mins" => "Currency",
        "Rate120Mins" => "Currency",
        "Rate480Mins" => "Currency"
    );

    private static $belongs_many_many = array(
        "ModuleProducts" => "ModuleProduct"
    );

    private static $defaults= array(
        "Rate15Mins" => 0,
        "Rate120Mins" => 0,
        "Rate480Mins" => 0
    );

    private static $api_acces = array(
        "view" => array("ModuleProducts", "ScreenName")
    );

    /**
     * Returns the currency used on the site.
     * @return String
     */
    public function Currency()
    {
        $currency = EcommercePayment::site_currency();
        return $currency;
    }

    public function updateMemberFormFields(&$fields)
    {
        $currency = $this->Currency();
        $fields->RemoveByName("PreferredCurrencyID");
        $field = $fields->fieldByName("ScreenName");
        $field->setTitle("Screen Name / Alias");
        $field = $fields->fieldByName("GithubURL");
        $field->setTitle("Github URL - e.g. https://github.com/mynamehere");
        $field = $fields->fieldByName("SilverstripeDotOrgURL");
        $field->setTitle("www.silverstripe.org URL - e.g. http://www.silverstripe.org/ForumMemberProfile/show/1");
        $field = $fields->fieldByName("CompanyName");
        $field->setTitle("Company Name (if any)");
        $field = $fields->fieldByName("CompanyURL");
        $field->setTitle("Company Link - e.g. http://www.the-company-i-work-for.co.nz/");
        $field = $fields->fieldByName("AreYouHappyForPeopleToContactYou");
        $field->setTitle("Are you happy to answer private questions about your code?");
        $field = $fields->fieldByName("ContactDetailURL");
        $field->setTitle("Contact Details URL - e.g. http://www.mysite.com/contact/");
        $field = $fields->fieldByName("OtherURL");
        $field->setTitle("other URL - e.g.  - e.g. http://www.mysite.com/about-me/");
        $field = $fields->fieldByName("AreYouAvailableForPaidSupport");
        $field->setTitle("Are you available for paid support?");
        $field = $fields->fieldByName("Rate15Mins");
        $field->setTitle("If applicable, approximate charge (in $currency) for a fifteen minute skype chat?");
        $field = $fields->fieldByName("Rate120Mins");
        $field->setTitle("If applicable, approximate charge (in $currency) for a two hour support block?");
        $field = $fields->fieldByName("Rate480Mins");
        $field->setTitle("If applicable, approximate charge (in $currency) for a development day (eight hours)?");
        if ($modules = $this->owner->ModuleProducts()) {
            if ($modules->count()) {
                $html = "<h3 id=\"ModuleListHeading\" class=\"clear\"><a href=\"".$this->ListOfModulesLink()."\">Currently Listed Modules ...</a></h3><ul>";
                foreach ($modules as $module) {
                    if ($module->ShowInSearch) {
                        $html .= "<li><a href=\"".$module->Link()."\">".$module->Title."</a></li>";
                    }
                }
                $html .= "</ul>";
                $fields->push(new LiteralField("ModuleList", $html));
            }
        }
        Requirements::javascript("ecommerce_software/javascript/SoftwareAuthorMemberDOD.js");
    }


    public function onBeforeWrite()
    {
        $id = intval($this->owner->ID);
        if (!$id) {
            $id = 0;
        }
        $i = 0;
        $startScreenName = $this->owner->ScreenName;
        $this->owner->ScreenName = preg_replace("[^A-Za-z0-9]", "", $this->owner->ScreenName);
        $className = $this->owner->ClassName;
        while ($className::get()
            ->filter(array("ScreenName" => $this->owner->ScreenName))
            ->exclude(array("ID" => $id ))
            ->first()
            && $i < 10
        ) {
            $i++;
            $this->ScreenName = $startScreenName."_".$i;
        }
        $this->owner->Locale = "en_GB";
    }

    public function ListOfModulesLink()
    {
        $page = ModuleProductGroup::get()
            ->filter(array("LevelOfProductsToShow" => 1))
            ->first();
        if ($page) {
            return $page->Link()."#author_".$this->owner->ScreenName;
        }
    }
}
