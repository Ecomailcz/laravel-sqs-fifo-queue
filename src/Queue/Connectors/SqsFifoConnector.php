<?php

namespace ShiftOneLabs\LaravelSqsFifoQueue\Queue\Connectors;

use Aws\Sqs\SqsClient;
use InvalidArgumentException;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Queue\Connectors\SqsConnector;
use ShiftOneLabs\LaravelSqsFifoQueue\SqsFifoQueue;

class SqsFifoConnector extends SqsConnector
{
    /**
     * Establish a queue connection.
     *
     * @param  array  $config
     *
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if (!Str::endsWith($config['queue'], '.fifo')) {
            throw new InvalidArgumentException('FIFO queue name must end in ".fifo"');
        }

        if (!empty($config['key']) && !empty($config['secret'])) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        $group = Arr::pull($config, 'group', 'default');
        $deduplicator = Arr::pull($config, 'deduplicator', 'unique');

        return new SqsFifoQueue(
            new SqsClient($config),
            $config['queue'],
            Arr::get($config, 'prefix', ''),
            $group,
            $deduplicator
        );
    }

    /**
     * Get the default configuration for SQS.
     *
     * @param  array  $config
     *
     * @return array
     */
    protected function getDefaultConfiguration(array $config)
    {
        // Laravel >= 5.1 has the "getDefaultConfiguration" method.
        if (method_exists(get_parent_class(), 'getDefaultConfiguration')) {
            return parent::getDefaultConfiguration($config);
        }

        return array_merge([
            'version' => 'latest',
            'http' => [
                'timeout' => 60,
                'connect_timeout' => 60,
            ],
        ], $config);
    }
}
