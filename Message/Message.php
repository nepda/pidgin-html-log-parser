<?php
/**
 * nepda Internetdienstleistungen
 * Nepomuk Fraedrich
 * http://nepda.eu/
 *
 * PHP Version >= 7.0
 *
 * @author    Nepomuk Fraedrich <info@nepda.eu>
 * @copyright 2017 Nepomuk Fraedrich
 */

namespace Message;

/**
 * Class Message
 */
class Message
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var string
     */
    protected $message;

    /**
     * Returns the from
     *
     * @return string
     * @see setFrom
     * @see $from
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Sets the from
     *
     * @param string $from
     * @return Message
     * @see getFrom
     * @see $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Returns the to
     *
     * @return string
     * @see setTo
     * @see $to
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Sets the to
     *
     * @param string $to
     * @return Message
     * @see getTo
     * @see $to
     */
    public function setTo($to)
    {
        $this->to = $to;
        return $this;
    }

    /**
     * Returns the date
     *
     * @return \DateTime
     * @see setDate
     * @see $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Sets the date
     *
     * @param \DateTime $date
     * @return Message
     * @see getDate
     * @see $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Returns the message
     *
     * @return string
     * @see setMessage
     * @see $message
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the message
     *
     * @param string $message
     * @return Message
     * @see getMessage
     * @see $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Returns the id
     *
     * @return string
     * @see setId
     * @see $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the id
     *
     * @param string $id
     * @return $this
     * @see getId
     * @see $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }
}