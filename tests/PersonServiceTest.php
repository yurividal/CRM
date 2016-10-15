<?php

$sSERVERNAME = 'localhost';
$sUSER = "churchcrm";
$sPASSWORD = "churchcrm";
$sDATABASE = "churchcrm";

$sRootPath = "../src";


require_once "../src/Include/LoadConfigs.php";
$_SESSION = array();
$_SESSION['sRootPath'] = $sRootPath;
$_SESSION['iUserID']  = 1;


require_once dirname(__FILE__) . '/../src/vendor/autoload.php';
require_once dirname(__FILE__) . '/../src/orm/conf/config.php';

use ChurchCRM\Service\PersonService;

class PersonServiceTest extends PHPUnit_Framework_TestCase {
  protected $backupGlobals = FALSE;

  public function testSearch() {
    $_SESSION['bAdmin'] = true;
    $personService = new PersonService();
    $results = $personService->search("admin");
    $this->assertNotEmpty($results);
  }



}
