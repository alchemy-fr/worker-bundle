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

use Alchemy\Worker\WorkerResolver;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InvokeWorkerCommand extends Command
{

    /**
     * @var WorkerResolver
     */
    private $workerResolver;

    /**
     * @param WorkerResolver $workerResolver
     */
    public function __construct(WorkerResolver $workerResolver)
    {
        parent::__construct();

        $this->workerResolver = $workerResolver;
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('workers:run-worker')
            ->addArgument('type')
            ->addArgument('body');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $type = $input->getArgument('type');
        $body = file_get_contents($input->getArgument('body'));

        if ($body === false) {
            $output->writeln('Unable to read payload file');

            return;
        }

        $body = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $output->writeln('<error>Invalid message body</error>');

            return;
        }

        $worker = $this->workerResolver->getWorker($type, $body);

        $worker->process($body);

        unlink($input->getArgument('body'));
    }
}
