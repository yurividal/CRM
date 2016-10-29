<?php
use ChurchCRM\GroupQuery;
use ChurchCRM\Group;
use Propel\Runtime;
# Migrate the data from each groupprop_ table into  `person2group2role_p2g2r`.`p2g2r_membershipData`
$connection = Runtime\Propel::getConnection(); # doesn't make sense to use ORM here since this is legacy model, and is going away.
#Find all of the groups in the databse that have special properties
$groups = GroupQuery::create()->filterByHasSpecialProps(true)->find();
file_put_contents(__DIR__."/upgradelog.log", "Found :" . count($groups). " GRroups");
foreach ($groups as $group)  # Iterate through each group
{
  $groupProperties = array(); # Create an array to contain the list of group specific properties for this group
  $query = "SELECT groupprop_master.*, list_lst.lst_OptionName FROM groupprop_master 
    INNER JOIN list_lst ON
    list_lst.lst_ID = 4 and
    list_lst.lst_OptionID = groupprop_master.type_ID
    where grp_ID =".$group->getId();
  file_put_contents(__DIR__."/upgradelog.log", $query);
  $statement = $connection->prepare($query);
  $resultset = $statement->execute();
  $results = $statement->fetchAll(\PDO::FETCH_ASSOC); # doesn't make sense to use ORM here since this is legacy model, and is going away.
  foreach ($results as $groupProperty)  # Iterate through each group specific property for this group
  {
    $property = new \stdClass(); # create a new temp class for this property
    $property->Field = $groupProperty['prop_Field'];
    $property->Name = $groupProperty['prop_Name'];
    $property->Description = $groupProperty['prop_Description'];
    $property->Type = $groupProperty['lst_OptionName'];
    array_push($groupProperties, $property); #add the propert to the array for all of this group's properties
  }
  $group->setSpecialProps(json_encode($groupProperties)); # Write this group's group specific properties to the group_grp table.
  $group->save();
  
  foreach ($group->getPerson2group2roleP2g2rs() as $personMembership) # iterate through each member of this group
  {
    $membershipData = array(); # create an array to contain all of the values for this member's group specific properties 
    $query = "SELECT * FROM groupprop_".$group->getId(); 
    $statement = $connection->prepare($query);
    $resultset = $statement->execute();
    $results = $statement->fetchAll(\PDO::FETCH_ASSOC); # doesn't make sense to use ORM here since this is legacy model, and is going away.
    foreach ($groupProperties as $groupProperty) #iterate through all of the group specific properties in order to obtain this member's value
    {
      $membershipProperty = new \stdClass();
      $membershipProperty->{$groupProperty->Name} = $results[0][$groupProperty->Field];
      array_push($membershipData,$membershipProperty);
    }
    $personMembership->setMembershipData(json_encode($membershipData)); #write the values of this member's group specific properties to the person 2 group 2 role table.
    $personMembership->save();
  }

}

?>