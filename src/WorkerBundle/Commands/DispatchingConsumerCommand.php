<?php

/*
 * This file is part of alchemy/worker-bundle.
 *
 * (c) Alchemy <info@alchemy.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Alchemy\WorkerBundle\Commands;

use Alchemy\Worker\MessageDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchingConsumerCommand extends Command
{
    /**
     * @var MessageDispatcher
     */
    private $messageDispatcher;

    /**
     * @param MessageDispatcher $messageDispatcher
     */
    public function __construct(MessageDispatcher $messageDispatcher)
    {
        parent::__construct();

        $this->messageDispatcher = $messageDispatcher;
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('workers:run-dispatcher');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        while (true) {
            $this->messageDispatcher->run();
        }
    }
}
