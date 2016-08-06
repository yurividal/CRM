<?php
// Routes


$app->group('/families', function () {

  $this->get('/search/{query}', function ($request, $response, $args) {
    $query = $args['query'];
    $q= \ChurchCRM\FamilyQuery::create();
    $q ->filterByName("%$query%",  Propel\Runtime\ActiveQuery\Criteria::LIKE) 
         ->limit(5);
    echo ($q->find()->toJSON());
  });

  $this->get('/lastedited', function ($request, $response, $args) {
    $this->FamilyService->lastEdited();
  });

  $this->get('/byCheckNumber/{scanString}', function ($request, $response, $args) {
    global $bUseScannedChecks;
    if ($bUseScannedChecks) {
        require "../Include/MICRFunctions.php";
         $micrObj = new MICRReader(); // Instantiate the MICR class
        $routeAndAccount = $micrObj->FindRouteAndAccount($args['scanString']);
        if ($routeAndAccount) {
            $q= \ChurchCRM\FamilyQuery::create();
            $q ->filterByScanCheck($routeAndAccount);
            $q->find();
            return '{"ScanString": "' . $tScanString . '" , "RouteAndAccount": "' . $routeAndAccount . '" , "CheckNumber": "' . $iCheckNo . '" ,"fam_ID": "' . $fam_ID . '" , "fam_Name": "' . $fam_Name . '"}';

        }
    }
   
  });

  $this->get('/byEnvelopeNumber/{envelopeNumber:[0-9]+}', function ($request, $response, $args) {
    $envelopeNumber = $args['envelopeNumber'];
    echo $this->FamilyService->getFamilyStringByEnvelope($envelopeNumber);
  });
});
