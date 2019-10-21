<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Model\Link;

use Magento\Framework\Filesystem\Io\Sftp;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Videoscm\MailChimpImport\{Api\ReaderSftpInterface,Helper\Data as Helper};
use Magento\Framework\File\Csv;

/**
 * Class ReaderSftp
 * @package Videoscm\MailChimpImport\Link
 *
 * Open Sftp connection to Store VideoTron and fetch a resource
 *
 */
class ReaderSftp extends StoreSftp implements ReaderSftpInterface
{
    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var Sftp
     */
    protected $sftp;

    /**
     * @var ScopeConfigInterface
     */
    protected $config;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * ReaderSftp constructor.
     * @param Sftp $sftp
     * @param ScopeConfigInterface $config
     * @param Csv $csv
     * @param Helper $helper
     */
    public function __construct(Sftp $sftp, ScopeConfigInterface $config, Csv $csv, Helper $helper)
    {
        $this->sftp = $sftp;
        $this->config = $config;
        $this->csv = $csv;
        $this->helper = $helper;
    }

    /**
     * @param string $resource
     * @return array|false|string
     * @throws \Exception
     */
    public function read($resource)
    {
        try {
            $this->openConnection();
            $listData = $this->sftp->read($resource);
            $this->renameFile($resource);
        }catch(\Exception $e){
            throw new \Exception("Unable to make a Ftp connection to the source file .", 0, $e);
        } finally {
            $this->sftp->close();
        }

        return $listData;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function rawls() : array
    {
        try {
            $this->openConnection();
            $listFileInfo = $this->sftp->rawls();
        }catch(\Exception $e){
            throw new \Exception("Unable to make a Ftp connection to the source file .", 0, $e);
        } finally {
            $this->sftp->close();
        }

        return $listFileInfo;
    }

    /**
     * @param $resource
     */
    protected function renameFile($resource) {
        $this->sftp->mv($resource,substr_replace($resource, '_done', 6, 0));
    }
}

