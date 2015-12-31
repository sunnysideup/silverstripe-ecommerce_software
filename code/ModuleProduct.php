<?php

/**
 * @author nicolaas [at] sunny side up . co . nz
 * this extension of product is for software products (modules)
 *
 *
 **/


class ModuleProduct extends Product
{

    private static $icon = "ecommerce_software/images/treeicons/ModuleProduct";

    private static $api_access = array(
        'view' => array(
                "ModuleTitle",
                "Code",
                "MainURL",
                "ReadMeURL",
                "DemoURL",
                "SvnURL",
                "GitURL",
                "OtherURL",
                //"EcommerceProductTags",
                "Authors"
            )
     );

    private static $db = array(
        "Code" => "Varchar",
        "MainURL" => "Varchar(255)",
        "ReadMeURL" => "Varchar(255)",
        "DemoURL" => "Varchar(255)",
        "SvnURL" => "Varchar(255)",
        "GitURL" => "Varchar(255)",
        "OtherURL" => "Varchar(255)",
        "ImportID" => "Int"
    );

    private static $has_many = array(
        "ModuleProductEmails" => "ModuleProductEmail"
    );

    private static $casting = array(
        "ModuleTitle" => "Varchar"
    );

    public function ModuleTitle()
    {
        return $this->getModuleTitle();
    }
    public function getModuleTitle()
    {
        return $this->getField("MenuTitle");
    }

    private static $many_many = array(
        "Authors" => "Member"
    );

    private static $singular_name = "Module";
    public function i18n_singular_name()
    {
        return _t("Order.MODULE", "Module");
    }

    private static $plural_name = "Modules";
    public function i18n_plural_name()
    {
        return _t("Order.Modules", "Modules");
    }

    public function canEdit($member = null)
    {
        if ($member = Member::currentUser()) {
            if ($member->IsShopAdmin()) {
                return true;
            }
            if ($authors = $this->Authors()) {
                foreach ($authors as $author) {
                    if ($author->ID == $member->ID) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function canDelete($member = null)
    {
        if ($member = Member::currentUser()) {
            if ($member->IsShopAdmin()) {
                return true;
            }
        }
        return false;
    }

    public function canEmail($member = null)
    {
        return $this->canDelete($member);
    }

    private static $searchable_fields = array(
        'Title' => "PartialMatchFilter",
        'InternalItemID' => "PartialMatchFilter",
        'ImportID',
        'ShowInSearch',
        'AllowPurchase',
        'FeaturedProduct',
        'Price'
    );


    public function getCMSFields()
    {
        $fields = new FieldList();
        $fields = parent::getCMSFields();
        $authors = $this->Authors();
        $sortString = "";
        if ($authors) {
            $authorsArray = $authors->map("ID", "ScreenName")->toArray();
            $sortStringEnd = "";
            if (is_array($authorsArray) && count($authorsArray)) {
                foreach ($authorsArray as $ID => $ScreenName) {
                    $sortString .= "IF(Member.ID = $ID, 1, ";
                    $sortStringEnd .= ")";
                }
                $sortString .= " 0".$sortStringEnd." DESC, \"Email\"";
            }
        }
        $fields->addFieldToTab('Root.Software', new TextField('Code', 'Code (this should be the same as the recommended folder name)'));
        $fields->addFieldToTab('Root.Software', new TextareaField('MetaDescription', 'Three sentence introduction'));
        $fields->addFieldToTab('Root.Software', new TextField('MainURL', 'Link to home page for the module - e.g. http://www.mymodule.com/'));
        $fields->addFieldToTab('Root.Software', new TextField('ReadMeURL', 'Link to read me file - e.g. http://www.mymodule.com/readme.md'));
        $fields->addFieldToTab('Root.Software', new TextField('DemoURL', 'Link to a demo - e.g. http://demo.mymodule.com/'));
        $fields->addFieldToTab('Root.Software', new TextField('SvnURL', 'Link to the SVN URL - allowing you to checkout trunk or latest version directly - e.g. http://svn.mymodule.com/svn/trunk/'));
        $fields->addFieldToTab('Root.Software', new TextField('GitURL', 'Link to the GIT URL - e.g. https://github.com/my-git-username/silverstripe-my-module'));
        $fields->addFieldToTab('Root.Software', new TextField('OtherURL', 'Link to other repository or download URL - e.g. http://www.mymodule.com/downloads/'));
        $fields->addFieldToTab('Root.Software', new ReadonlyField('ImportID', 'Import Identifier'));
        $fields->addFieldToTab('Root.Software', new HeaderField("AuthorsHeading", "Authors"));
        return $fields;
    }


    /**
     * return the first Author
     * @return Int
     */
    public function DefaultMemberID()
    {
        $memberID = 0;
        $authors = $this->Authors();
        if ($authors && $authors->count()) {
            $memberID = $authors->First()->ID;
        }
        return $memberID;
    }


    /**
     * Has an email been sent?
     * @return Boolean
     *
     */
    public function HasMemberContact()
    {
        return ModuleProductEmail::get()
            ->filter(array("MemberID" => $this->DefaultMemberID()))
            ->count() ? true : false;
    }

    /**
     * Has an email been sent?
     * @return Boolean
     *
     */
    public function HasEmail()
    {
        if ($this->EmailObject()) {
            return true;
        }
        return false;
    }

    /**
     * Return the ModuleProductEmail
     * @return Object (ModuleProductEmail)
     *
     */
    public function EmailObject()
    {
        return ModuleProductEmail::get()
            ->filter(array("ModuleProductID" => $this->ID))
            ->first();
    }

    public function EmailDefaults()
    {
        $to = "";
        $authorEmailArray = array();
        $authorFirstNameArray = array();
        if ($authors = $this->Authors()) {
            foreach ($authors as $author) {
                $authorEmailArray[$author->ScreenName] = $author->Email;
                if ($author->FirstName) {
                    $authorFirstNameArray[$author->ScreenName] = $author->FirstName;
                } else {
                    $authorFirstNameArray[$author->ScreenName] = $author->ScreenName;
                }
            }
        }
        $to = implode(", ", $authorEmailArray);
        $subject = _t("ModuleProduct.SUBJECT", "Please check your module: ").$this->Title;
        $body = $this->createBodyAppendix(implode(", ", $authorFirstNameArray));
        return new ArrayData(
            array(
                "To" => $to,
                "Subject" => $subject,
                "Body" => $body
            )
        );
    }

    protected function createBodyAppendix($screenName)
    {
        $pageLink = Director::absoluteURL($this->Link());
        $passwordResetLink = Director::absoluteURL("Security/lostpassword");
        $logInLink = Director::absoluteURL("Security/login");
        $editYourDetailsLink = Director::absoluteURL(RegisterAndEditDetailsPage::get()->first()->Link());
        $customisationArray = array(
            "ID" => $this->ID,
            "PageLink" => $pageLink,
            "PasswordResetLink" => $passwordResetLink,
            "LogInLink" => $logInLink,
            "Title" => $this->Title,
            "ScreenName" => $screenName,
            "EditYourDetailsLink" => $editYourDetailsLink
        );
        $body = $this->customise($customisationArray)->renderWith("ModuleProductEmailBody");
        return $body;
    }


    public function ReadMeContent()
    {
        if ($this->ReadMeURL) {
            $this->ReadMeURL = str_replace("http://raw.github", "https://raw.github", $this->ReadMeURL);
            if ($this->checkIfExternalLinkWorks($this->ReadMeURL)) {
                return file_get_contents($this->ReadMeURL);
            }
        }
    }

    protected function checkIfExternalLinkWorks($url)
    {
        // Version 4.x supported
        $handle   = curl_init($url);
        if (false === $handle) {
            return false;
        }
        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_FAILONERROR, true);  // this works
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.15) Gecko/20080623 Firefox/2.0.0.15")); // request as if Firefox
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);
        $connectable = curl_exec($handle);
        curl_close($handle);
        return $connectable;
    }
}



class ModuleProduct_Controller extends Product_Controller
{

    public function init()
    {
        parent::init();
        Requirements::javascript(THIRDPARTY_DIR."/jquery-form/jquery.form.js");
        Requirements::javascript("ecommerce_software/javascript/Markdown.Converter.js");
        Requirements::javascript("ecommerce_software/javascript/ModuleProduct.js");
        Requirements::themedCSS("ModuleProduct", "ecommerce_software");
    }

    public function Form()
    {
        if ($this->canEdit()) {
            return new AddingModuleProduct_Form($this, "Form", $this->ID);
        }
    }

    /**
     *
     * @return Object Product
     */
    public function PreviousProduct()
    {
        return ModuleProduct::get()
            ->where("\"Sort\" < ".$this->Sort." AND ParentID = ".$this->ParentID)
            ->sort("Sort", "DESC")
            ->limit(1)
            ->first();
    }

    /**
     *
     * @return Object Product
     */
    public function NextProduct()
    {
        return ModuleProduct::get()
            ->where("\"Sort\" > ".$this->Sort." AND ParentID = ".$this->ParentID)
            ->sort("Sort", "ASC")
            ->limit(1)
            ->first();
    }


    public function HasPreviousOrNextProduct()
    {
        if ($this->NextProduct()) {
            return true;
        }
        if ($this->PreviousProduct()) {
            return true;
        }
        return true;
    }

    public function EmailForm()
    {
        if ($this->canEdit()) {
            if (!$this->HasEmail()) {
                return new ModuleProductEmail_Form($this, "EmailForm", $this->dataRecord);
            }
        }
    }
}
