<?php

namespace ChurchCRM\Tasks;

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\Emails\TestEmail;

class EmailTask implements iTask
{

    private $testEmail;

    public function __construct()
    {
        $this->testEmail = new TestEmail(SystemConfig::getValue("sChurchEmail"));
    }

    public function isActive()
    {
        return $_SESSION['user']->isAdmin() &&
            (
                empty(SystemConfig::getValue('sSMTPHost')) ||
                empty(SystemConfig::getValue("sChurchEmail")) ||
                $this->testEmail->testConnection()
            );
    }

    public function isAdmin()
    {
        return true;
    }

    public function getLink()
    {
        return SystemURLs::getRootPath() . '/SystemSettings.php';
    }

    public function getTitle()
    {
        if (empty(SystemConfig::getValue('sSMTPHost'))) {
            return gettext('Set Email Settings');
        } elseif (empty(SystemConfig::getValue("sChurchEmail"))) {
            return gettext('Set Church Email');
        } else {
            return "SMTP Error: " + $this->testEmail->getError();
        }
    }

    public function getDesc()
    {
        return gettext("We are unable to send emails from the system.");
    }

}
