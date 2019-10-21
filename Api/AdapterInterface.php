<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Api;

/**
 * Interface AdapterInterface
 * @package Videoscm\MailChimpImport\Api
 */
interface AdapterInterface
{
    /**
     * @param $rawData
     * @param $fileName
     * @return array
     */
    public function format($rawData,$fileName) : array;

    /**
     * @return array
     */
    function getMappingFromHeader() : array;

    /**
     * @param $row
     * @param $fileName
     * @return mixed
     */
    function getMappedItem($row,$fileName);

    /**
     * @param $arrays
     * @return array
     */
    function formatMailChimp($arrays) : array;

    /**
     * @param $subArray
     * @param $idTable
     * @return string
     */
    function getSubBatchJson($subArray,$idTable) : string;

    /**
     * @return array
     */
    function getListsInfo() : array;
}

