<?php

namespace Alchemy\WorkerProvider;

use Alchemy\Queue\MessageQueueRegistry;
use Alchemy\Worker\MessageDispatcher;
use Alchemy\Worker\ProcessPool;
use Alchemy\Worker\TypeBasedWorkerResolver;
use Alchemy\Worker\WorkerInvoker;
use Alchemy\WorkerBundle\Commands\DispatchingConsumerCommand;
use Alchemy\WorkerBundle\Commands\InvokeWorkerCommand;
use Psr\Log\LoggerAwareInterface;
use Silex\Application;
use Silex\ServiceProviderInterface;

class WorkerServiceProvider implements ServiceProviderInterface
{

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     */
    public function register(Application $app)
    {
        $this->registerDefaultParameters($app);

        $loggerSetter = function (LoggerAwareInterface $loggerAware) use ($app) {
            if (isset($app[$app['alchemy_worker.logger_service_name']])) {
                $loggerAware->setLogger($app[$app['alchemy_worker.logger_service_name']]);
            }

            return $loggerAware;
        };

        $app['alchemy_worker.process_pool'] = $app->share(function (Application $app) use ($loggerSetter) {
            return $loggerSetter(new ProcessPool($app['alchemy_worker.process_pool_size']));
        });

        $app['alchemy_worker.worker_invoker'] = $app->share(function (Application $app) use ($loggerSetter) {
            return $loggerSetter(new WorkerInvoker($app['alchemy_worker.process_pool'], 'prod'));
        });

        $app['alchemy_worker.queue_registry'] = $app->share(function () use ($loggerSetter) {
            return $loggerSetter(new MessageQueueRegistry());
        });

        $app['alchemy_worker.message_dispatcher'] = $app->share(function (Application $app) use ($loggerSetter) {
            return $loggerSetter(new MessageDispatcher(
                $app['alchemy_worker.worker_invoker'],
                $app['alchemy_worker.queue_registry'],
                $app['alchemy_worker.queue_name']
            ));
        });

        $app['alchemy_worker.type_based_worker_resolver'] = $app->share(function () {
            return new TypeBasedWorkerResolver();
        });

        $app['alchemy_worker.worker_resolver'] = $app->share(function (Application $app) {
            return $app['alchemy_worker.type_based_worker_resolver'];
        });

        $app['alchemy_worker.commands.run_dispatcher_command'] = $app->share(function (Application $app) {
            return new DispatchingConsumerCommand($app['alchemy_worker.message_dispatcher']);
        });

        $app['alchemy_worker.commands.run_worker_command'] = $app->share(function (Application $app) {
            return new InvokeWorkerCommand($app['alchemy_worker.worker_resolver']);
        });
    }

    /**
     * @param Application $app
     */
    protected function registerDefaultParameters(Application $app)
    {
        if (!isset($app['alchemy_worker.logger_service_name'])) {
            $app['alchemy_worker.logger_service_name'] = 'logger';
        }

        if (!isset($app['alchemy_worker.queue_name'])) {
            $app['alchemy_worker.queue_name'] = 'alchemy_worker';
        }

        if (!isset($app['alchemy_worker.process_pool_size'])) {
            $app['alchemy_worker.process_pool_size'] = 8;
        }
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
        // TODO: Implement boot() method.
    }
}
