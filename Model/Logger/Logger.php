<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model\Logger;

use Magento\Framework\Mail\Template\TransportBuilder;
use Monolog\Handler\HandlerInterface;

class Logger extends \Monolog\Logger
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var string
     */
    protected $name;

    /**
     * The handler stack
     *
     * @var HandlerInterface[]
     */
    protected $handlers;

    /**
     * Processors that will process all log records
     *
     * To process records of a single handler instead, add the processor on that specific handler
     *
     * @var callable[]
     */
    protected $processors;

    /**
     * @var string
     */
    protected $messageMail;

    /**
     * Logger constructor.
     * @param $name
     * @param array $handlers
     * @param array $processors
     * @param TransportBuilder $transportBuilder
     */
    public function __construct($name, array $handlers = array(), array $processors = array(), TransportBuilder $transportBuilder)
    {
        parent::__construct($name, $handlers, $processors);
        $this->transportBuilder = $transportBuilder;
        $this->messageMail = '';
    }

    /**
     * @param string $message
     * @param array $context
     * @param bool $isMessageMail
     * @return bool
     */
    public function info($message, array $context = array(),$isMessageMail = true)
    {
        if($isMessageMail) {
            $this->buildMessageMail($message);
        }
        return $this->addRecord(static::INFO, $message, $context);
    }

    /**
     * @param $message
     */
    public function buildMessageMail($message){
        $this->messageMail .= $message.'<br/>';
    }

    /**
     * @return array
     */
    public function getMessageMail(){
       return $this->messageMail;
    }
}