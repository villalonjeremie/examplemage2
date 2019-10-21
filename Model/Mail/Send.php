<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model\Mail;

use Magento\Framework\Mail\Template\TransportBuilder;
use Videoscm\MailChimpImport\Helper\Data;
use Videoscm\MailChimpImport\Model\Logger\Logger;

class Send
{
    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Send constructor.
     * @param TransportBuilder $transportBuilder
     * @param Data $helper
     * @param Logger $logger
     */
    public function __construct(TransportBuilder $transportBuilder, Data $helper, Logger $logger)
    {
        $this->transportBuilder = $transportBuilder;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    public function sendMail(){
        $mailTo = $this->helper->getArrayMailTo();
        $mailFrom = $this->helper->getMailFrom();

        $report = [
            'report_date' => date("j F Y"),
            'message' => $this->logger->getMessageMail()
        ];

        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($report);

        $transport = $this->transportBuilder
            ->setTemplateIdentifier('template_mailchimp_import')
            ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
            ->setTemplateVars(['data' => $postObject])
            ->setFrom(['name' => 'Report Importing','email' => $mailFrom])
            ->addTo($mailTo)
            ->getTransport();

        try{
            $transport->sendMessage();
        } catch(\Exception $e){
            $this->logger->info('Error importation :'.$e,[],true);
        }
    }

}