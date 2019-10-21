<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Api;

/**
* Interface ReaderInterface
* @package Videoscm\MailChimpImport\Api
*/
interface ReaderSftpInterface
{

    /**
    * @param string $resource
    * @return array
    */
    public function read($resource);

    /**
     * @return mixed
     */
    public function rawls();
}

