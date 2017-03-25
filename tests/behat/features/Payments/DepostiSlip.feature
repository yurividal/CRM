Feature: Deposit Slip
  In order to manage Finances
  As a user
  I am able to create a deposit slip

  Scenario: Create a Deposit Slip
    Given I am authenticated as "admin" using "changeme"
    And I am on "/FindDepositSlip.php"
    When I fill in "Deposit Comment" with "BEHAT TEST" 
    And I press "addNewDeposit"
    Then I should see "Deposit Type"
    And I should see "BEHAT TEST"
  
  Scenario: Create a Payment
    Given I am authenticated as "admin" using "changeme"
    And I am on "/DepositSlipEditor.php?DepositSlipID=67"
    Then I should see "Bank Deposit Slip Number: 67"
    When I Press "AddPayment"
    Then I should see "Payment Details"
    And I fill in "Family" with "Crossan"
    And I fill in "Method" with "Check"
    And I fill in "CheckNo" with "100"
    And I fill in "1_Amount" with "1.00"
    And I press "Save"
    Then I should see "Payments on this deposit slip"