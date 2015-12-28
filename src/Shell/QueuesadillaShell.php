<?php
namespace App\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Log\Log;
use Exception;

class QueuesadillaShell extends Shell
{
    /**
     * Override main() to handle action
     * Starts a Queuesadilla worker
     *
     * @return void
     */
    public function main()
    {
        $engine = $this->params['engine'];
        $worker = $this->params['worker'];
        $EngineClass = "josegonzalez\\Queuesadilla\\Engine\\" . $engine . 'Engine';
        $WorkerClass = "josegonzalez\\Queuesadilla\\Worker\\" . $worker . "Worker";

        $config = $this->getEngineConfig();
        $loggerName = $this->getLoggerName();

        $logger = Log::engine($loggerName);
        $engine = new $EngineClass($logger, $config);

        $worker = new $WorkerClass($engine, $logger);
        $worker->work();
    }

    /**
     * Retrieves default configuration for the engine
     *
     * @return array
     */
    protected function getEngineConfig()
    {
        $config = Configure::read('Queuesadilla.engine');
        if (empty($config)) {
            throw new Exception('Invalid Queuesadilla.engine config');
        }

        if (!empty($this->params['queue'])) {
            $config['queue'] = $this->params['queue'];
        }
        return $config;
    }

    /**
     * Retrieves a name of a logger engine to use
     *
     * @return string
     */
    protected function getLoggerName()
    {
        $loggerName = Configure::read('Queuesadilla.logger');
        if (empty($loggerName)) {
            $loggerName = $this->params['logger'];
        }
        return $loggerName;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addOption('engine', [
            'choices' => [
                'Beanstalk',
                'Iron',
                'Memory',
                'Mysql',
                'Null',
                'Redis',
                'Synchronous',
            ],
            'default' => 'Mysql',
            'help' => 'Name of engine',
            'short' => 'e',
        ]);
        $parser->addOption('queue', [
            'help' => 'Name of a queue',
            'short' => 'q',
        ]);
        $parser->addOption('logger', [
            'help' => 'Name of a configured logger',
            'default' => 'stdout',
            'short' => 'l',
        ]);
        $parser->addOption('worker', [
            'choices' => [
                'Sequential',
                'Test',
            ],
            'default' => 'Sequential',
            'help' => 'Name of worker class',
            'short' => 'w',
        ])->description(__('Runs a Queuesadilla worker.'));
        return $parser;
    }
}
