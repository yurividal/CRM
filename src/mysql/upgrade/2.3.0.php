<?php
use ChurchCRM\GroupQuery;
use ChurchCRM\Group;
use Propel\Runtime;
# Migrate the data from each groupprop_ table into  `person2group2role_p2g2r`.`p2g2r_membershipData`
$connection = Runtime\Propel::getConnection();
#Find all of the groups in the databse that have special properties
$groups = GroupQuery::create()->filterByHasSpecialProps(true)->find();
file_put_contents(__DIR__."/upgradelog.log", "Found :" . count($groups). " GRroups");
foreach ($groups as $group)
{
  $groupProperties = array();
  $query = "SELECT groupprop_master.*, list_lst.lst_OptionName FROM groupprop_master 
    INNER JOIN list_lst ON
    list_lst.lst_ID = 4 and
    list_lst.lst_OptionID = groupprop_master.type_ID
    where grp_ID =".$group->getId();
  file_put_contents(__DIR__."/upgradelog.log", $query);
  $statement = $connection->prepare($query);
  $resultset = $statement->execute();
  $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
  foreach ($results as $groupProperty)
  {
    $property = new \stdClass();
    $property->Field = $groupProperty['prop_Field'];
    $property->Name = $groupProperty['prop_Name'];
    $property->Description = $groupProperty['prop_Description'];
    $property->Type = $groupProperty['lst_OptionName'];
    array_push($groupProperties, $property);
  }
  
  foreach ($group->getPerson2group2roleP2g2rs() as $personMembership) 
  {
    $membershipData = array();
    $query = "SELECT * FROM groupprop_".$group->getId();
    $statement = $connection->prepare($query);
    $resultset = $statement->execute();
    $results = $statement->fetchAll(\PDO::FETCH_ASSOC);
    foreach ($groupProperties as $groupProperty)
    {
      $membershipProperty = new \stdClass();
      $membershipProperty->{$groupProperty->Name} = $results[0][$groupProperty->Field];
      array_push($membershipData,$membershipProperty);
    }
    $personMembership->setMembershipData(json_encode($membershipData));
    $personMembership->save();
  }
  $group->setHasSpecialProps(true);
  $group->setSpecialProps(json_encode($groupProperties));
  $group->save();
}
/*


rsort($results);
return $results[0]['ver_version'];
 */



?>