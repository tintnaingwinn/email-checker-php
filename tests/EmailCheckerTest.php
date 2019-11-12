<?php

use Tintnaingwin\EmailCheckerPHP\EmailChecker;
use PHPUnit\Framework\TestCase;

class EmailCheckerTest extends TestCase
{
    public $email_checker;

    /**
     * EmailCheckerTest constructor.
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->email_checker = new EmailChecker();
    }

    /** @test */
    public function email_checker_is_true()
    {
        $this->assertTrue($this->email_checker->check('amigo.k8@gmail.com'));
    }

    /** @test */
    public function email_checker_is_false()
    {
        $this->assertFalse($this->email_checker->check('example@example.com'));
    }

    /** @test */
    public function disposable_mail_is_false()
    {
        $this->assertFalse($this->email_checker->check('amigo.k8@0-mail.com'));
    }

}
