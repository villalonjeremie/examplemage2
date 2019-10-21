<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model\Logger;

use Monolog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/import-ftp-to-mailchimp.log';
}