<?php
/*
 * This file is part of the prooph/humus-amqp-producer.
 * (c) 2016 prooph software GmbH <contact@prooph.de>
 * (c) 2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types=1);

namespace Prooph\ServiceBus\Message\HumusAmqp\Container;

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Config\RequiresConfigId;
use Interop\Container\ContainerInterface;
use Prooph\Common\Messaging\FQCNMessageFactory;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\Exception;
use Prooph\ServiceBus\Message\HumusAmqp\AmqpEventConsumerCallback;

/**
 * Class AmqpEventConsumerCallbackFactory
 * @package Prooph\ServiceBus\Message\HumusAmqp\Container
 */
final class AmqpEventConsumerCallbackFactory implements ProvidesDefaultOptions, RequiresConfigId
{
    use ConfigurationTrait;

    /**
     * @var string
     */
    private $amqpEventConsumerCallbackName;

    /**
     * Creates a new instance from a specified config, specifically meant to be used as static factory.
     *
     * In case you want to use another config key than provided by the factories, you can add the following factory to
     * your config:
     *
     * <code>
     * <?php
     * return [
     *     'your_amqp_event_consumer_callback' => [AmqpEventConsumerCallbackFactory::class, 'your_amqp_event_consumer_callback_name'],
     * ];
     * </code>
     *
     * @param string $amqpEventConsumerCallbackName
     * @param array $arguments
     * @return AmqpEventConsumerCallback
     * @throws Exception\InvalidArgumentException
     */
    public static function __callStatic(string $amqpEventConsumerCallbackName, array $arguments) : AmqpEventConsumerCallback
    {
        if (!isset($arguments[0]) || !$arguments[0] instanceof ContainerInterface) {
            throw new Exception\InvalidArgumentException(
                sprintf('The first argument must be of type %s', ContainerInterface::class)
            );
        }
        return (new static($amqpEventConsumerCallbackName))->__invoke($arguments[0]);
    }

    /**
     * AmqpEventConsumerCallbackFactory constructor.
     * @param string $amqpEventConsumerCallbackName
     */
    public function __construct(string $amqpEventConsumerCallbackName)
    {
        $this->amqpEventConsumerCallbackName = $amqpEventConsumerCallbackName;
    }

    /**
     * @param ContainerInterface $container
     * @return AmqpEventConsumerCallback
     */
    public function __invoke(ContainerInterface $container) : AmqpEventConsumerCallback
    {
        $options = $this->options($container->get('config'), $this->amqpEventConsumerCallbackName);

        return new AmqpEventConsumerCallback(
            $container->get($options['event_bus']),
            $container->get($options['message_factory'])
        );
    }

    /**
     * @return array
     */
    public function dimensions()
    {
        return ['prooph', 'humus-amqp-producer', 'event_consumer_callback'];
    }

    /**
     * @return array
     */
    public function defaultOptions()
    {
        return [
            'event_bus' => EventBus::class,
            'message_factory' => FQCNMessageFactory::class
        ];
    }
}
