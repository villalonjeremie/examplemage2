<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model\Files;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Videoscm\MailChimpImport\Model\{Link\ReaderSftp,Files\Adapter as Adapter,Logger\Logger};
use Magento\Framework\App\State;
use Videoscm\MailChimpImport\Helper\Data;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Videoscm\MailChimpImport\Model\Mail\Send;
use Magento\Store\Model\StoreManagerInterface;

class Importer
{
    CONST PATH_RELATIVE_CSV = 'history_import_mailchimp';

    /**
     * @var ReaderSftp
     */
    protected $reader;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var
     */
    protected $mailChimpApi;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var
     */
    protected $listsInfo;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var boolean
     */
    protected $cliProcess;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var
     */
    protected $directoryList;

    /**
     * @var File
     */
    protected $file;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var Send
     */
    protected $send;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Importer constructor.
     * @param ReaderSftp $reader
     * @param ScopeConfigInterface $config
     * @param \Videoscm\MailChimpImport\Model\Files\Adapter $adapter
     * @param State $state
     * @param Data $helper
     * @param Logger $logger
     * @param DirectoryList $directoryList
     * @param File $file
     * @param TimezoneInterface $timezone
     * @param Send $send
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(ReaderSftp $reader,
                                ScopeConfigInterface $config,
                                Adapter $adapter,
                                State $state,
                                Data $helper,
                                Logger $logger,
                                DirectoryList $directoryList,
                                File $file,
                                TimezoneInterface $timezone,
                                Send $send,
                                StoreManagerInterface $storeManager)
    {
        $this->reader = $reader;
        $this->config = $config;
        $this->adapter = $adapter;
        $this->state = $state;
        $this->helper= $helper;
        $this->logger = $logger;
        $this->directoryList = $directoryList;
        $this->file = $file;
        $this->timezone = $timezone;
        $this->send = $send;
        $this->storeManager = $storeManager;
    }

    /**
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function process()
    {
        if($this->cliProcess) {
            $this->state->setAreaCode('adminhtml');
        }
        $arrayFilesInfo = $this->reader->rawls();

        foreach($arrayFilesInfo as $fileName => $data){

            $posImport = strpos($fileName, $this->helper->getFilePrefix());
            $posDone = strpos($fileName, '_done_');

            if($posImport !== false && $posDone === false) {
                $this->logger->info('Name File : '.$fileName.' at '.$this->timezone->formatDate().' on '.$this->storeManager->getStore()->getBaseUrl());
                $csvString = $this->reader->read($fileName);
                $this->writeToCsv($fileName, self::PATH_RELATIVE_CSV, $csvString);
                $results = $this->adapter->format($csvString,$fileName);
                try {
                    $this->mailChimpApi('POST','batches', $results['list_superclub']);
                    $this->mailChimpApi('POST','batches', $results['list_microplay']);
                    $this->send->sendMail();
                } catch (\Exception $e) {
                    $this->logger->info('Exception :'.$e);
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getResourceName()
    {
        return $this->config->getValue('datas/resource_name');
    }

    /**
     * @param $type
     * @param $target
     * @param bool $data
     * @return mixed
     */
    public function mailChimpApi($type, $target, $data = false)
    {
        $api = [
            'login' => $this->helper->getLoginMailChimp(),
            'key'   => $this->helper->getKeyMailChimp(),
            'url'   => $this->helper->getUrlMailChimp()
        ];

        $ch = curl_init($api['url'] . $target);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: ' . $api['login'] . ' ' . $api['key']
        ));

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'MailChimpImport-Magento2');

        if ($data)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = curl_exec($ch);

        $this->logger->info('Curl Report :'.$response,[],false);
        if ($response === false) {
            echo curl_error($ch);
            $this->logger->info('Curl Error :'.curl_error($ch));
        }
        curl_close($ch);

        return json_decode($response, true);
    }

    /**
     * @param $bool
     */
    public function setCliProcess($bool=false)
    {
        $this->cliProcess = $bool;
    }

    /**
     * @return bool
     */
    public function getCliProcess()
    {
        if (!isset($this->cliProcess)){
            $this->cliProcess = false;
        }

        return $this->cliProcess;
    }

    /**
     * @param $fileName
     * @param $filePath
     * @param $contentCsv
     */
    protected function writeToCsv($fileName, $filePath, $contentCsv)
    {
        try {
            $csvPath = $this->directoryList->getPath('var').DIRECTORY_SEPARATOR.$filePath;
            if (!is_dir($csvPath)) {
                $this->file->mkdir($csvPath, 0775);
            }
            $this->file->open(array('path'=>$csvPath));
            $this->file->write($fileName, $contentCsv, 0644);
        } catch (\Exception $e){
            $this->logger->info('Get Directory Writte Error : '.$e);
        } finally {
            $this->file->close();
        }
    }
}

