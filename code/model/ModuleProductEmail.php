<?php

/**
 * @author nicolaas [at] sunny side up . co . nz
 * this extension of product is for software products (modules)
 *
 *
 **/


class ModuleProductEmail extends DataObject
{

    private static $icon = "ecommerce_software/images/treeicons/ModuleProduct";

    private static $db = array(
        "Subject" => "Varchar",
        "Body" => "HTMLText",
        "To" => "Varchar(255)",
        "Sent" => "Boolean"
    );

    private static $has_one = array(
        "ModuleProduct" => "ModuleProduct",
        "Member" => "Member"
    );

    private static $singular_name = "Module Email";
    public function i18n_singular_name()
    {
        return _t("ModuleProductEmail.MODULEPRODUCTEMAIL", "Module Email");
    }

    private static $plural_name = "Module Emails";
    public function i18n_plural_name()
    {
        return _t("ModuleProductEmail.MODULEPRODUCTEMAILS", "Module Emails");
    }

    public function canDelete($member = null)
    {
        return false;
    }

    public function canEdit($member = null)
    {
        return false;
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if (!$this->Sent) {
            $email = new Email(
                $from = Email::getAdminEmail(),
                $to = $this->To,
                $subject = $this->Subject,
                $body = $this->Body,
                $bounceHandlerURL = null,
                $cc = null,
                $bcc = Email::getAdminEmail()
            );
            $email->send();
            $this->Sent = 1;
            $this->write();
        }
    }
}


class ModuleProductEmail_Form extends Form
{

    public function __construct($controller, $name, $moduleProduct)
    {
        $defaults = $moduleProduct->EmailDefaults();
        $fields = new FieldList();
        $fields->push(new TextField('To', 'To', $defaults->To));
        $fields->push(new TextField('Subject', 'Subject', $defaults->Subject));
        $fields->push(new HiddenField('ModuleProductID', 'ModuleProductID', $moduleProduct->ID));
        $fields->push(new HiddenField('MemberID', 'memberID', $moduleProduct->DefaultMemberID()));
        $fields->push(new HtmlEditorField('Body', 'Body', $defaults->Body));
        $actions = new FieldList(new FormAction("submit", "submit"));
        $validator = new ModuleProductEmail_RequiredFields(array("Subject"));
        parent::__construct($controller, $name, $fields, $actions, $validator);
        $this->loadDataFrom($moduleProduct->emailDefaults());
        return $this;
    }

    public function submit($data, $form)
    {
        $member = Member::currentUser();
        if (!$member || !$member->inGroup("ADMIN")) {
            $form->setMessage("You need to be logged as an admin to send this email.", "bad");
            return Controller::curr()->redirectBack();
        }
        $data = Convert::raw2sql($data);
        $page = null;
        if (isset($data["ModuleProductID"])) {
            $page = ModuleProduct::get()->byID(intval($data["ModuleProductID"]));
        }
        if (!$page) {
            $form->setMessage("Can not find the right page for saving this email.", "bad");
            return Controller::curr()->redirectBack();
        }
        $email = new ModuleProductEmail();
        $form->saveInto($email);
        $email->write();
        if (Director::is_ajax()) {
            return "mail sent!";
        } else {
            return Controller::curr()->redirect($page->Link());
        }
    }
}

class ModuleProductEmail_RequiredFields extends RequiredFields
{

    public function __construct($array)
    {
        parent::__construct($array);
    }

    public function javascript()
    {
        $js = '';
        Requirements::customScript($js, "ModuleProductEmail");
        return parent::javascript();
    }


    /**
    * Allows validation of fields via specification of a php function for validation which is executed after
    * the form is submitted
    */
    public function php($data)
    {
        $valid = true;
        if (!isset($data["Subject"]) || (isset($data["Subject"]) && strlen($data["Subject"])) < 3) {
            $errorMessage = _t("Form.PLEASEENTERASUBJECT", "Please enter a subject");
            $this->validationError(
                $fieldName = "Subject",
                $errorMessage,
                "required"
            );
            $valid = false;
        }
        if (!isset($data["To"]) || (isset($data["To"]) && strlen($data["To"]) < 3)) {
            $errorMessage = _t("Form.PLEASEENTERANEMAIL", "Please enter an e=mail");
            $this->validationError(
                $fieldName = "To",
                $errorMessage,
                "required"
            );
        }
        if (!$valid) {
            return false;
        }
        return parent::php($data);
    }
}
