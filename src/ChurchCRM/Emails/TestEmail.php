<?php
namespace ChurchCRM\Emails;

class TestEmail extends BaseEmail {

    public function __construct($email) {
        parent::__construct($email);
        $this->mail->setFrom($email);

    }

    public function testConnection() {
        $testPassed = false;
        if( $this->mail->smtpConnect()) {
            $testPassed = true;
            $this->mail->smtpClose();
        }
        return $testPassed;
    }

}