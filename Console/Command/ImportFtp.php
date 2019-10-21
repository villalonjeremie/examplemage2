<?php
/**
 * @author   Jérémie Villalon <jvillalon@o2web.ca≥
 * copyright Copyright (c) 2017 o2Web Inc (http://www.o2web.ca)
 * @link     http://www.o2web.ca
 */
namespace Videoscm\MailChimpImport\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;
use Videoscm\MailChimpImport\Cron\ImportFtpCron;
use Videoscm\MailChimpImport\Model\Files\Importer;

class ImportFtp extends Command
{
    /**
     * @var ImportFtpCron
     */
    protected $importFtpCron;


    /**
     * @var Importer
     *
     */
    protected $importer;

    /**
     * ImportFtp constructor.
     * @param ImportFtpCron $importFtpCron
     * @param Importer $importer
     */
    public function __construct(ImportFtpCron $importFtpCron,Importer $importer)
    {
        parent::__construct();
        $this->importFtpCron = $importFtpCron;
        $this->importer = $importer;
    }

    /**
     *
     */
    protected function configure()
    {
        $this->setName('videoscm:import_ftp')->setDescription('Import Ftp Files from Stores\' Videotron');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $time_start = microtime(true);
        $output->writeln('Start import FTP file command.');
        $this->importer->setCliProcess(true);
        $this->importFtpCron->execute();
        $difference_ms = round( (microtime(true) - $time_start), 3);
        $output->writeln('Import of FTP\' file  completed (execution time: '.$difference_ms.' ms)');
    }
}