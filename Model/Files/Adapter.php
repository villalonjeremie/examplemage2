<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */

namespace Videoscm\MailChimpImport\Model\Files;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Setup\Exception;
use Videoscm\MailChimpImport\{Api\AdapterInterface,Helper\Data,Model\Logger\Logger};

class Adapter implements AdapterInterface
{
    const LIMIT_COUNT_FIELD = 21;
    const METHOD_UPDATE_CREATE_MEMBERS_MAILCHIMP = 'PUT';
    const STATUS_IF_NEW = 'pending';
    const STATUS = 'subscribed';

    /**
     * @var
     */
    protected $rows;

    /**
     * @var
     */
    protected $mapping;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var int
     */
    protected $linesArrays;

    /**
     * @var int
     */
    protected $linesSuperClub;

    /**
     * @var int
     */
    protected $linesMicroPlay;

    /**
     * @var int
     */
    protected $linesConsentement;

    /**
     * @var int
     */
    protected $linesErrors;

    /**
     * @var string
     */
    protected $batchesSuperClub;

    /**
     * @var string
     */
    protected $batchesMicroPlay;

    /**
     * @var
     */
    protected $mailChimpApi;

    /**
     * Adapter constructor.
     * @param ScopeConfigInterface $config
     * @param Data $helper
     * @param Logger $logger
     */
    public function __construct(ScopeConfigInterface $config, Data $helper, Logger $logger)
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param $rawData
     * @param $fileName
     * @return array
     * @throws Exception
     * @throws \Exception
     * @throws \Zend_Validate_Exception
     */
    public function format($rawData,$fileName) : array
    {
        $this->rows = explode("\n", $rawData);
        $this->mapping = $this->getMappingFromHeader();
        $arrayFormat = [];

        foreach ($this->rows as $key => $row) {
            if ($key === 0) continue;
            $item = $this->getMappedItem($row,$fileName);
            if (!is_null($item)) {
                $arrayFormat[] = $item;
            }
        }

        return $this->formatMailChimp($arrayFormat);
    }

    /**
     * @return array
     */
    public function getMappingFromHeader() : array
    {
        $header = explode($this->helper->getDataSeparator(), reset($this->rows));
        $appMap = $this->config->getValue('datas/fields');
        $mapping = [];

        foreach ($appMap as $field => $extMap) {
            $key = array_search('"' . $extMap . '"', $header);
            if (false !== $key) {
                $mapping[$field] = $key;
            } elseif ($field == "email" && false == $key) {
                $this->logger->info('WARNING : Field Name "' . $field . '" in config.xml does not match with Header FTP\'s file, CRON process die');
                exit;
            } else {
                $this->logger->info('Field Name "' . $field . '" in config.xml does not match with Header FTP\'s file');
            }
        }

        return $mapping;
    }

    /**
     * @param $row
     * @param $filename
     * @return array|null
     */
    public function getMappedItem($row,$filename)
    {
        $fields = explode($this->helper->getDataSeparator(), trim($row));
        $arrayItemMapped = [];
        $countField = $this->helper->getCountColunmCsv() ?? self::LIMIT_COUNT_FIELD;

        if (empty($fields) || count($fields) !== intval($countField) ) {
            if($row !="") {
                $this->logger->info('Count Field is wrong from File CSV ' . $filename . 'row : ' . $row);
            }
            return null;
        }

        $appMap = $this->config->getValue('datas/fields');

        foreach ($appMap as $field => $extMap) {
            if (array_key_exists($field, $this->mapping)) {
                $arrayItemMapped[$field] = trim($fields[$this->mapping[$field]], '"');
                if (($field == "category_name") && (strpos($arrayItemMapped[$field], 'Rented') !== false)) {
                    $arrayItemMapped[$field] = "Rent";
                }
            } else {
                continue;
            }
        }

        return $arrayItemMapped;
    }

    /**
     * @param $arrays
     * @return array
     * @throws \Exception
     * @throws \Zend_Validate_Exception
     */
    public function formatMailChimp($arrays) : array
    {
        $superClubMap = $this->helper->getSuperClubMap();
        $microPlayMap = $this->helper->getMicroPlayMap();

        $superClubMapField = $this->helper->getSuperClubMapField();
        $microPlayMapField = $this->helper->getMicroPlayMapField();

        $idTableSuperClub = $this->helper->getIdTableSuperclub($this->getListsInfo());
        $idTableMicroPlay = $this->helper->getIdTableMicroplay($this->getListsInfo());

        if (empty($idTableSuperClub)) {
            $this->logger->info('Wrong Name Table SuperClub Mailchimp');
            exit;
        }

        if (empty($idTableMicroPlay)) {
            $this->logger->info('Wrong Name Table Microplay Mailchimp');
            exit;
        }

        $this->linesArrays = 0;
        $this->linesSuperClub = 0;
        $this->linesMicroPlay = 0;
        $this->linesErrors = 0;
        $this->linesConsentement = 0;


        foreach ($arrays as $array) {

            $subArraySuperClubToExported = null;
            $subArrayMicroPlayToExported = null;
            $noFoundSuperClub = null;
            $noFoundSuperClubField = null;
            $noFoundMicroPlay = null;
            $noFoundMicroPlayField = null;
            $isSuperClubData = null;
            $isMicroPlayData = null;
            $this->linesArrays++;

            if ($array['need_newsletter'] == "0" || $array['need_newsletter'] == 0){
                $this->linesConsentement++;
                continue;
            }

            if (!\Zend_Validate::is($array['email'], 'EmailAddress')) {
                $this->linesErrors++;
                $this->logger->info('Email : '.$array['email'].' is not validate line => ' . $this->linesArrays);
            } else {
                foreach ($array as $field => $extMap) {
                    if ($field != 'category_name' && array_key_exists($field, $superClubMap))
                        $subArraySuperClubToExported[$superClubMapField[$field]] = utf8_encode($array[$field]);

                    if ($field != 'category_name' && array_key_exists($field, $microPlayMap))
                        $subArrayMicroPlayToExported[$microPlayMapField[$field]] = utf8_encode($array[$field]);

                    if ($field == 'category_name') {
                        if (in_array($extMap, $superClubMap)) {
                            $subArraySuperClubToExported[$superClubMapField[array_search($extMap, $superClubMap)]] = 1;
                            $isSuperClubData = true;
                            $isMicroPlayData = false;
                        } elseif(in_array($extMap, $microPlayMap)){
                            $subArrayMicroPlayToExported[$microPlayMapField[array_search($extMap, $microPlayMap)]] = 1;
                            $isSuperClubData = false;
                            $isMicroPlayData = true;
                        } else {
                            $noFoundSuperClub = true;
                            $noFoundMicroPlay = true;
                            $noFoundSuperClubField = $extMap;
                        }
                   }
                }

                if ($noFoundMicroPlay && $noFoundSuperClub && ($noFoundSuperClubField == $noFoundSuperClubField)) {
                    $this->linesErrors++;
                    $this->logger->info('Field incorrect in config.xml or mailchimp : ' . $noFoundSuperClubField . ' lines ' . $this->linesArrays);
                }

                if($isSuperClubData) {
                    $this->linesSuperClub++;
                    $this->batchesSuperClub .= $this->getSubBatchJson($subArraySuperClubToExported,$idTableSuperClub);
                }

                if($isMicroPlayData) {
                    $this->linesMicroPlay++;
                    $this->batchesMicroPlay .= $this->getSubBatchJson($subArrayMicroPlayToExported,$idTableMicroPlay);
                }
            }
        }

        $this->logger->info($this->linesArrays.' lines processed , ('.$this->linesMicroPlay.' Microplay), ('.$this->linesSuperClub.' SuperClub), ('.$this->linesErrors.' lines not processed), ('.$this->linesConsentement.' non-consentements)');

        return [
            'list_superclub' => '{"operations" : [' . rtrim($this->batchesSuperClub, ",") . ']}',
            'list_microplay' => '{"operations" : [' . rtrim($this->batchesMicroPlay, ",") . ']}'
        ];
    }

    /**
     * @param $subArray
     * @param $idTable
     * @return string
     */
    public function getSubBatchJson($subArray,$idTable) : string {
        $statusIfNew = $this->helper->getStatusIfNew() ?? self::STATUS_IF_NEW;
        $status = $this->helper->getStatus() ?? self::STATUS;
        $language = strtolower($subArray['language']);
            if($language == 'fr'){
                $language = 'fr_CA';
            }
        
        $arrayToExported = [
            "email_address" => $subArray['EMAIL'],
            "status_if_new" => $statusIfNew,
            "status" => $status,
            "language" => $language,
            "merge_fields" => $subArray
        ];

        $jsonTempMicroPlay['method'] = self::METHOD_UPDATE_CREATE_MEMBERS_MAILCHIMP;
        $jsonTempMicroPlay['path'] = "lists/" . $idTable . "/members/" . md5(trim($arrayToExported['email_address']));
        $jsonTempMicroPlay['body'] = json_encode($arrayToExported, JSON_FORCE_OBJECT);

        return json_encode($jsonTempMicroPlay, JSON_FORCE_OBJECT) . ',';
    }

    /**
     * @return array
     */
    public function getListsInfo() : array {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mailChimpApi = $objectManager->create('Videoscm\MailChimpImport\Model\MCAPI');
        $arrayListInfo = [];
        if(!isset($this->listsInfo)) {
            $listsObject = $this->mailChimpApi->lists();
            foreach ($listsObject->lists as $list) {
                $arrayListInfo[] = [
                    "id" => $list->id,
                    "name" => $list->name
                ];
            }

            $this->listsInfo = $arrayListInfo;
        }

        return $this->listsInfo;
    }
}

