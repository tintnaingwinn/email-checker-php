<?php

namespace Tintnaingwin\EmailCheckerPHP;

interface EmailCheckerInterface {

    /**
     * To verify an email address exist.
     */
    public function check($email);
}
