<?php
/**
 * @author   Jeremie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.com)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model\Link;

abstract class StoreSftp
{
    /**
     * @var Sftp
     */
    protected $sftp;

    /**
     * @var
     */
    protected $helper;

    protected function openConnection()
    {
        $this->sftp->open($this->helper->getConnectionConfig());
    }
}

