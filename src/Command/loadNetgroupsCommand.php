<?php

namespace App\Command;

use App\Service\LoadNetgroups;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class loadNetgroupsCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:load-netgroups';

    private $ng_service;

    public function __construct(LoadNetgroups $ng_service)
    {
        $this->ng_service = $ng_service;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Load netgroups and hosts from accessfiles directory')
            ->setHelp('tbc');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Loading Netgroups');
        $output->writeln('=================');

        $something = $this->ng_service->loadAll();
        $output->writeln($something);
    }
}