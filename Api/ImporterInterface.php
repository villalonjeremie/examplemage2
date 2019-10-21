<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Api;

/**
 * Interface ImporetInterface
 * @package Videoscm\MailChimpImport\Api
 */
interface ImporterInterface
{

    /**
     * @return mixed
     */
    function process();

    /**
     * @return mixed
     */
    function getResourceName();

    /**
     * @param $type
     * @param $target
     * @param bool $data
     * @return mixed
     */
    function mailChimpApi($type, $target, $data = false);

    /**
     * @param boolean $bool
     * @return boolean
     */
    function setCliProcess($bool=false);

    /**
     * @return bool
     */
    function getCliProcess();
}

