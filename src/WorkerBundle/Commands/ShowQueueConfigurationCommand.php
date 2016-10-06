<?php

namespace Alchemy\WorkerBundle\Commands;

use Alchemy\Queue\MessageQueueRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ShowQueueConfigurationCommand extends Command
{
    /**
     * @var MessageQueueRegistry
     */
    private $queueRegistry;

    /**
     * @param MessageQueueRegistry $queueRegistry
     */
    public function __construct(MessageQueueRegistry $queueRegistry)
    {
        parent::__construct();

        $this->queueRegistry = $queueRegistry;
    }

    protected function configure()
    {
        $this->setName('workers:show-configuration');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([ '', 'Configured queues: ' ]);

        foreach ($this->queueRegistry->getConfigurations() as $name => $configuration) {
            $output->writeln([ '  ' . $name . ': ' . Yaml::dump($configuration, 0), '' ]);
        }
    }
}
