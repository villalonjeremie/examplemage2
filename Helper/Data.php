<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Videoscm\MailChimpImport\Model\Logger\Logger;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Data constructor.
     * @param ScopeConfigInterface $config
     * @param Logger $logger
     */
    public function __construct(ScopeConfigInterface $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param $arrayListInfo
     * @return mixed|null
     * @throws \Exception
     */
    public function getIdTableSuperclub($arrayListInfo){
        $tableName = $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_tables/videoscm_mailchimpimport_tables_superclub');

        if (is_null($this->getIdInArray($tableName, $arrayListInfo))){
            throw new \Exception('Wrong Table Name Superclub in Configuration');
        }

        return $this->getIdInArray($tableName, $arrayListInfo);
    }

    /**
     * @param $arrayListInfo
     * @return mixed|null
     * @throws \Exception
     */
    public function getIdTableMicroplay($arrayListInfo){
        $tableName = $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_tables/videoscm_mailchimpimport_tables_microplay');

        if (is_null($this->getIdInArray($tableName, $arrayListInfo))){
            throw new \Exception('Wrong Table Name Microplay in Configuration');
        }

        return $this->getIdInArray($tableName, $arrayListInfo);
    }

    /**
     * @param $needle
     * @param $array
     * @return bool|mixed
     */
    protected function getIdInArray($needle,$array){
        if (is_array($array)) {
            $index = 0;
            foreach ($array as $subarray) {
               if(is_array($subarray) && $subarray['name']==$needle){
                   return $subarray['id'];
               }
            }
        }

        return false;
     }

    /**
     * @return mixed
     */
     public function getSuperClubMap()
     {
         $superclubMap = $this->config->getValue('datas/fields_mailchimp_superclub');

         if (is_null($superclubMap)){
             $this->logger->info('Not existing Field Superclub in Configuration');
         }

         return $superclubMap;
     }

    /**
     * @return mixed
     */
    public function getMicroPlayMap()
    {
        $microPlayMap = $this->config->getValue('datas/fields_mailchimp_microplay');

        if (is_null($microPlayMap)){
            $this->logger->info('Not existing Field Microplay in Configuration');
        }

        return $microPlayMap;
    }

    /**
     * @return mixed
     */
    public function getSuperClubMapField()
    {
        $superclubMapField = $this->config->getValue('datas/fields_mailchimp_superclub_field');

        if (is_null($superclubMapField))
        {
            $this->logger->info('Not existing Field Superclub Mailchimp in Configuration');
        }

        return $superclubMapField;
    }

    /**
     * @return mixed
     */
    public function getMicroPlayMapField()
    {
        $microPlayMapField = $this->config->getValue('datas/fields_mailchimp_microplay_field');

        if (is_null($microPlayMapField)){
            $this->logger->info('Not existing Field Microplay Mailchimp in Configuration');
        }

        return $microPlayMapField;
    }

    /**
     * @return array
     */
    public function getConnectionConfig() : array {
        return array(
            'host'     => $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_sftp_access/videoscm_mailchimpimport_host'),
            'username'     => $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_sftp_access/videoscm_mailchimpimport_user'),
            'password' => $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_sftp_access/videoscm_mailchimpimport_password'),
        );
    }

    /**
     * @return mixed
     */
    public function getLoginMailChimp(){
       return  $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_access/videoscm_mailchimpimport_access_login');
    }

    /**
     * @return mixed
     */
    public function getKeyMailChimp(){
        return  $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_access/videoscm_mailchimpimport_access_key');
    }

    /**
     * @return mixed
     */
    public function getUrlMailChimp(){
        return  $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_access/videoscm_mailchimpimport_access_url');
    }

    /**
     * @return mixed
     */
    public function getDataSeparator()
    {
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_csv/videoscm_mailchimpimport_csv_separator');
    }

    /**
     * @return mixed
     */
    public function getFilePrefix(){
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_csv/videoscm_mailchimpimport_csv_prefix');
    }

    /**
     * @return mixed
     */
    public function getStatusIfNew(){
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_status_members/videoscm_mailchimpimport_status_if_new');
    }

    /**
     * @return mixed
     */
    public function getStatus(){
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_status_members/videoscm_mailchimpimport_status');
    }

    /**
     * @return mixed
     */
    public function getCountColunmCsv(){
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_csv/videoscm_mailchimpimport_csv_count_column');
    }

    /**
     * @return mixed
     */
    public function getArrayMailTo(){
        return explode(',',$this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_mail_information/videoscm_mailchimpimport_mail_to'));
    }

    /**
     * @return mixed
     */
    public function getMailFrom(){
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_mail_information/videoscm_mailchimpimport_mail_from');
    }

    /**
     * @return mixed
     */
    public function getEnabled(){
        return $this->config->getValue('mailchimp_import_conf/videoscm_mailchimpimport_sftp_access/videoscm_mailchimpimport_enabled');
    }
}
