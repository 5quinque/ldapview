<?php

namespace App\Command;

use App\Service\LdapService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class loadEntitiesCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:load-entity';

    private $ldapService;

    private $peopleOption;
    private $netgroupOption;
    private $sudoOption;

    public function __construct(LdapService $ldapService)
    {
        $this->ldapService = $ldapService;

        parent::__construct();
    }

    protected function configure()
    {
        set_time_limit(0);

        $this
            ->setDescription('')
            ->setHelp('tbc');

        $this
            ->addOption('people', 'p')
            ->addOption('netgroup', 'g')
            ->addOption('sudo', 's')
            ;    
    }

    protected function getInput(InputInterface $input)
    {
        $this->peopleOption = $input->getOption('people');
        $this->netgroupOption = $input->getOption('netgroup');
        $this->sudoOption = $input->getOption('sudo');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getInput($input);

        
        $output->writeln('Loading Entity: ');

        if ($this->peopleOption) {
            $output->writeln('People');
            $this->ldapService->findAll(["ou" => "people", "objectClass" => "posixAccount"]);
        }
        if ($this->netgroupOption) {
            $output->writeln('Netgroup');
            $netgroups = $this->ldapService->findAll(["ou" => "netgroup", "objectClass" => "nisNetgroup"]);
        }
        if ($this->sudoOption) {
            $output->writeln('Sudo');
            $sudoGroups = $this->ldapService->findAll(["ou" => "SUDOers", "objectClass" => "sudoRole"]);
        }

        $output->writeln('=================');
    }
}
