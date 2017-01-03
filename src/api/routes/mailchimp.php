<?php
use ChurchCRM\dto\SystemConfig;
use DrewM\MailChimp\MailChimp;

$app->group('/mailchimp', function () {



  // generate x families and people in each family
  $this->get('/status', function ($request, $response, $args) {
    $email = $request->getQueryParam("email");

    if (!empty($email)) {
      $mailchimp = new MailChimp(SystemConfig::getValue("mailChimpApiKey"));
      $mcResponse = $mailchimp->get("/search-members?query=".urlencode($email));
      return $response->withJson($mcResponse);
    }

  });

});
