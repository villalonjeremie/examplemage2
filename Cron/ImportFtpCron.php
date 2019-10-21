<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Cron;

use Videoscm\MailChimpImport\Model\Files\Importer;
use Psr\Log\LoggerInterface;
use Videoscm\MailChimpImport\Helper\Data;


Class ImportFtpCron {

    /**
     * @var Importer
     */
    protected $importer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Data
     */
    protected $helper;


    /**
     * ImportFtpCron constructor.
     * @param Importer $importer
     * @param LoggerInterface $logger
     * @param Data $helper
     */
    public function __construct(Importer $importer,LoggerInterface $logger,Data $helper) {
        $this->importer = $importer;
        $this->logger = $logger;
        $this->helper = $helper;
    }

    /**
     *
     */
    public function execute()
    {
        try{
            if($this->helper->getEnabled())
                $this->importer->process();
        } catch (\Exception $e){
            $this->logger->critical($e->getMessage());
        }
    }

}
