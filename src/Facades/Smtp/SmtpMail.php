<?php

namespace Magpie\Facades\Smtp;

use Magpie\Exceptions\InvalidStateException;
use Magpie\Exceptions\SafetyCommonException;
use Magpie\General\Concepts\BinaryDataProvidable;
use Magpie\Objects\ReleasableCollection;

/**
 * SMTP mail
 */
abstract class SmtpMail
{
    /**
     * If the mail is already sent
     */
    protected bool $isSent = false;
    /**
     * @var ReleasableCollection Resources to be released upon sent
     */
    protected ReleasableCollection $releasedAfterSent;


    /**
     * Constructor
     */
    protected function __construct()
    {
        $this->releasedAfterSent = new ReleasableCollection();
    }


    /**
     * Specify sender
     * @param string $email
     * @param string|null $name
     * @return $this
     * @throws SafetyCommonException
     */
    public abstract function withSender(string $email, ?string $name = null) : static;


    /**
     * Add recipient
     * @param string $email
     * @param string|null $name
     * @param SmtpRecipientType $type
     * @return $this
     * @throws SafetyCommonException
     */
    public abstract function withRecipient(string $email, ?string $name = null, SmtpRecipientType $type = SmtpRecipientType::TO) : static;


    /**
     * Add recipient (carbon copy)
     * @param string $email
     * @param string|null $name
     * @return $this
     * @throws SafetyCommonException
     */
    public function withCC(string $email, ?string $name = null) : static
    {
        return $this->withRecipient($email, $name, SmtpRecipientType::CC);
    }


    /**
     * Add recipient (blind carbon-copy)
     * @param string $email
     * @param string|null $name
     * @return $this
     * @throws SafetyCommonException
     */
    public function withBCC(string $email, ?string $name = null) : static
    {
        return $this->withRecipient($email, $name, SmtpRecipientType::BCC);
    }


    /**
     * Specify mail subject
     * @param string $subject
     * @return $this
     * @throws SafetyCommonException
     */
    public abstract function withSubject(string $subject) : static;


    /**
     * Specify mail content body
     * @param MailBody $body
     * @return $this
     * @throws SafetyCommonException
     */
    public abstract function withBody(MailBody $body) : static;


    /**
     * Add attachment to the mail
     * @param BinaryDataProvidable $content
     * @return $this
     * @throws SafetyCommonException
     */
    public abstract function withAttachment(BinaryDataProvidable $content) : static;


    /**
     * Send the mail
     * @return SmtpSentMail
     * @throws SafetyCommonException
     */
    public function send() : SmtpSentMail
    {
        if ($this->isSent) throw new InvalidStateException();

        try {
            $ret = $this->onSend();
            $this->isSent = true;
            return $ret;
        } finally {
            $this->releasedAfterSent->release();
        }
    }


    /**
     * Handle sending the mail
     * @return SmtpSentMail
     * @throws SafetyCommonException
     */
    protected abstract function onSend() : SmtpSentMail;
}
