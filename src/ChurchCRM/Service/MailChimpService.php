<?php

namespace ChurchCRM\Service;

use ChurchCRM\dto\SystemConfig;
use DrewM\MailChimp\MailChimp;

class MailChimpService
{

  private $mailchimp;

  public function __construct()
  {

   if ($this->isActive()) {
      $this->;
    }
  }

  function isActive()
  {
    return !empty(SystemConfig::getValue("mailChimpApiKey"));
  }

  function isEmailInMailChimp($email)
  {

    if (!$this->isActive()) {
      return "MailChimp ". gettext("is not active");
    }

    if ($email == "") {
      return "No email";
    }

    try {
      $mcResponse = $this->mailchimp->get("/search-members?query=".$email);
      return print_r($mcResponse);
      $listNames = array();
      foreach ($mcResponse->members as $member) {
        array_push($listNames, $member->list_id);
      }
      return implode(",", $listNames);
    } catch (\Mailchimp_Invalid_ApiKey $e) {
      return "Invalid ApiKey";
    } catch (\Mailchimp_List_NotSubscribed $e) {
      return "";
    } catch (\Mailchimp_Email_NotExists $e) {
      return "";
    } catch (\Exception $e) {
      return $e;
    }

  }

  function getLists()
  {
    if (!$this->isActive) {
      return "Mailchimp is not active";
    }
    try {
      $result = $this->mailchimp->lists->getList();
      return $result["data"];
    } catch (\Mailchimp_Invalid_ApiKey $e) {
      return "Invalid ApiKey";
    } catch (\Exception $e) {
      return $e->getMessage();
    }
  }

}
