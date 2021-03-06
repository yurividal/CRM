<?php

namespace ChurchCRM\Tasks;

use ChurchCRM\dto\SystemConfig;
use ChurchCRM\dto\SystemURLs;
use ChurchCRM\PersonQuery;

class PersonClassificationDataCheck implements iTask
{
    private $count;

    public function __construct()
    {
        $personQuery = PersonQuery::create()->filterByClsId(0)->find();
        $this->count = $personQuery->count();
    }

    public function isActive()
    {
        return $this->count > 0;
    }

    public function isAdmin()
    {
        return false;
    }

    public function getLink()
    {
        return SystemURLs::getRootPath() . '/SelectList.php?mode=person&Classification=0&PersonColumn3=Classification';
    }

    public function getTitle()
    {
        return gettext('Falta informação sobre status de Membresia') . " (" . $this->count . ")";
    }

    public function getDesc()
    {
        return gettext("Falta informação sobre status de Membresia de algumas pessoas");
    }
    
       

}

$sSQL = "UPDATE person_per
SET per_cls_ID = 4
WHERE per_cls_ID = 2 AND (DATE_FORMAT(FROM_DAYS(TO_DAYS(NOW())-TO_DAYS(CONCAT(per_BirthYear,'-',per_BirthMonth,'-',per_BirthDay))),'%Y')+0) > 18";

	  RunQuery($sSQL);
