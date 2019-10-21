<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model;

class MCAPI extends \Ebizmarts\MageMonkey\Model\MCAPI
{
    /**
     * @param string $use
     * @param null $method
     * @param null $params
     * @param null $fields
     * @return mixed
     * @throws \Exception
     */
    protected function callServer($use = 'GET', $method = null, $params = null, $fields = null)
    {
        $dc = '';
        $key = '';
        list($host,$key) = $this->getHost($method, $params);

        $curl = clone $this->_curl;
        $curl->addOption(CURLOPT_POST, false);
        if ($fields) {
            if ($use != 'GET') {
                $curl->addOption(CURLOPT_POSTFIELDS, $fields);
            } else {
                $host .= $this->addGetParams($fields);
            }
        }
        switch ($use) {
            case 'POST':
                $curl->addOption(CURLOPT_POST, true);
                break;
            case 'GET':
                break;
            case 'DELETE':
                $curl->addOption(CURLOPT_POST, false);
                $curl->addOption(CURLOPT_CUSTOMREQUEST, 'DELETE');
                break;
            case 'PATCH':
                $curl->addOption(CURLOPT_POST, true);
                $curl->addOption(CURLOPT_CUSTOMREQUEST, 'PATCH');
                break;
            case 'PUT':
                $curl->addOption(CURLOPT_POST, true);
                $curl->addOption(CURLOPT_PUT, true);
                break;
        }
        $curl->addOption(CURLOPT_USERPWD, "lesuperclub:0d473ea9ad1ff411162b5f1f83efb1a5-us16");

        $curl->addOption(CURLOPT_URL, $host);
        $curl->addOption(CURLOPT_USERAGENT, 'MageMonkey/');
        $curl->addOption(CURLOPT_HEADER, true);
        $curl->addOption(CURLOPT_HTTPHEADER, ['Content-Type: application/json','Authorization: apikey '.$key,'Cache-Control: no-cache']);
        $curl->addOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->addOption(CURLOPT_CONNECTTIMEOUT, 30);
        $curl->addOption(CURLOPT_TIMEOUT, $this->_timeout);
        $curl->addOption(CURLOPT_FOLLOWLOCATION, 1);
        $curl->connect($host);

        $response = $curl->read();

        $body = preg_split('/^\r?$/m', $response);
        $responseCode = $curl->getInfo(CURLINFO_HTTP_CODE);
        $curl->close();
        $data = json_decode($body[count($body)-1]);
        $dataType = (isset($data->type)) ? $data->type : '';
        $dataTitle = (isset($data->title)) ? $data->title : '';
        $dataStatus = (isset($data->status)) ? $data->status : '';
        $dataDetail = (isset($data->detail)) ? $data->detail : 'Wrong API Key';
        switch ($use) {
            case 'DELETE':
                if ($responseCode!=204) {
                    throw new \Exception('Type: '.$dataType.' Title: '.$dataTitle.' Status: '.$dataStatus.' Detail: '.$dataDetail);
                }
                break;
            case 'PUT':
                if ($responseCode!=200) {
                    throw new \Exception('Type: '.$dataType.' Title: '.$dataTitle.' Status: '.$dataStatus.' Detail: '.$dataDetail);
                }
                break;
            case 'POST':
                if ($responseCode!=200) {
                    throw new \Exception('Type: '.$dataType.' Title: '.$dataTitle.' Status: '.$dataStatus.' Detail: '.$dataDetail);
                }
                break;
            case 'PATCH':
                if ($responseCode!=200) {
                    throw new \Exception('Type: '.$dataType.' Title: '.$dataTitle.' Status: '.$dataStatus.' Detail: '.$dataDetail);
                }
                break;
        }
        return $data;
    }
}
