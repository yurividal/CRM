Feature: Deposit Slip Listing
  In order to manage bank deposits
  As a User
  I am able to visit the deposit listing

  Scenario: Open the Deposit Listing
    Given I am authenticated as "admin" using "changeme"
    And  I am on "/FindDepositSlip.php"
    Then I should see "Add New Deposit"

  Scenario: Create a new Deposit Slip
    Given I am authenticated as "admin" using "changeme"
    And I am on "/FindDepositSlip.php"
    And I press "Add New Deposit"

  Scenario: Open the Deposit Slip Editor
    Given I am authenticated as "admin" using "changeme"
    And  I am on "/DepositSlipEditor.php?DepositSlipID=1"
    Then I should see "Bank Deposit Slip Number: 1"
    And I should see "Payments on this deposit slip"