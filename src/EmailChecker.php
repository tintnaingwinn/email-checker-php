<?php

namespace Tintnaingwin\EmailCheckerPHP;

class EmailChecker implements EmailCheckerInterface
{
    /**
     * PHP Socket
     * @var  $socket
     */
    protected $socket;

    /**
     * @var $user
     */
    protected $user;

    /**
     * @var $domain
     */
    protected $domain;

    /**
     * @var $from_email
     */
    protected $from_email = 'me@example.com';

    /**
     * SMTP Port
     * @var $port 25
     */
    protected $port = 25;

    /**
     * Maximum Connection Time to an MTA
     */
    protected $max_conn_time = 30;

    /**
     * Maximum Read Time from socket
     */
    protected $max_read_time = 5;

    /**
     * @var $nameServers
     */
    protected $nameServers = ['192.168.0.1'];

    /*
     * Requested mail action okay, completed.
     */
    const CONNECT_SUCCESS_CODE = "220";

    /*
     * Requested mail action okay, completed.
     */
    const HELO_SUCCESS_CODE = "250";

    /*
     * Requested mail action okay, completed.
     */
    const MAIL_SUCCESS_CODE = "250";

    /**
     * Rcpt success codes.
     */
    protected $responseSuccessCode = [
        "250", // Requested mail action okay, completed
        "251", // User not local; will forward to <forward-path>
        "451", // Rejected by the remote server because of anti-spam measures
        "452", // Too many emails sent or too many recipients
    ];

    /**
     * To verify an email address exist.
     * @return boolean
     */
    public function check($email)
    {
        $this->parseEmail($email);

        if ($this->isDisposableEmail()) {
            return false;
        }

        $mxRecords = $this->getMxRecords($this->domain);

        if (empty($mxRecords)) {
            return false;
        }

        $timeout = $this->max_conn_time/count($mxRecords);

        foreach ($mxRecords as $host => $weight)
        {
            if ($this->socket = @fsockopen($host, $this->port, $errno, $errstr, (float) $timeout))
            {
                stream_set_timeout($this->socket, $this->max_read_time);

                if(!$this->connectServer()) {
                    continue;
                }

                if(!$this->sayHelo()) {
                    continue;
                }

                if(!$this->fromMail()) {
                    continue;
                }

                return $this->rcpt($email);

                break;
            }
        }

        return false;
    }

    /**
     * Parse the mail.
     */
    protected function parseEmail($email)
    {
        $parts = explode('@', $email);
        $this->domain = array_pop($parts);
        $this->user= implode('@', $parts);
    }

    /**
     * Check the disposable email.
     */
    protected function isDisposableEmail()
    {
        $disposable = json_decode(file_get_contents(__DIR__.'/json/list.json'),true);

        return in_array($this->domain, $disposable);
    }

    /**
     * Get the mx records.
     */
    protected function getMxRecords($domain)
    {
        if (function_exists('getmxrr')) {

            getmxrr($domain, $mxhosts, $weight);

        } else {
            // windows, we need Net_DNS
            require_once 'Net/DNS.php';

            $resolver = new Net_DNS_Resolver();

            // nameservers to query
            $resolver->nameServers = $this->nameServers;
            $resp = $resolver->query($domain, 'MX');

            if ($resp) {
                foreach($resp->answer as $answer) {
                    $mxhosts[] = $answer->exchange;
                    $weight[] = $answer->preference;
                }
            }
        }

        $mxRecords = array_combine($mxhosts, $weight);

        asort($mxRecords);

        return $mxRecords;
    }

    /**
     * Connect the server.
     */
    protected function connectServer()
    {
        $reply = fread($this->socket, 2082);

        $code = $this->parseReplyMessage($reply);

        return $code === self::CONNECT_SUCCESS_CODE;
    }

    /**
     * Send the say helo command to the connected server.
     */
    protected function sayHelo()
    {
        $reply =  $this->send("helo hi");

        $code = $this->parseReplyMessage($reply);

        return $code === self::HELO_SUCCESS_CODE;
    }

    /**
     * Send the from mail command to the connected server.
     */
    protected function fromMail()
    {
        $reply = $this->send("MAIL FROM: <".$this->from_email.">");

        $code = $this->parseReplyMessage($reply);

        return $code === self::MAIL_SUCCESS_CODE;
    }

    /**
     * Send the command rcpt email to the connected server.
     */
    protected function rcpt($email)
    {
        $reply = $this->send("RCPT TO: <".$email.">");

        $code = $this->parseReplyMessage($reply);

        $this->quit();

        return in_array($code, $this->responseSuccessCode);
    }

    /**
     * Close the socket.
     */
    protected function quit()
    {
        $this->send("quit");

        fclose($this->socket);
    }

    /**
     * Send the command message to the connected server.
     */
    protected function send($message)
    {
        fwrite($this->socket, $message."\r\n");

        $reply = fread($this->socket, 2082);

        return $reply;
    }

    /**
     * Parse the replied message.
     */
    protected function parseReplyMessage($message): string
    {
        preg_match('/^([0-9]{3})/ims', $message, $matches);

        return isset($matches[1]) ? $matches[1] : '';
    }

}

