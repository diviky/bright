<?php

namespace Diviky\Bright\Database\Sharding;

use Diviky\Bright\Database\Sharding\Exceptions\ShardingException;
use Diviky\Bright\Database\Sharding\IdGenerators\IdGeneratorInterface;
use Diviky\Bright\Database\Sharding\ShardChoosers\ShardChooserInterface;

/**
 * Class MapManager.
 */
class MapManager
{
    /**
     * Full config map.
     *
     * @var array
     */
    protected $map = [];

    /**
     * Connections list for current service.
     *
     * @var array
     */
    protected $currentConnections = [];

    /**
     * Array of object per services
     * Example
     * [
     *  'auth' => [
     *      'chooser' => 'Diviky\Bright\Database\Sharding\ShardChoosers\ModuleDivision',
     *      'id_generator' => 'Diviky\Bright\Database\Sharding\IdGenerators\RedisSequence'
     *  ],
     * ].
     *
     * @var array
     */
    protected $container = [];

    /**
     * Current service name.
     *
     * @var string
     */
    protected $name;

    /**
     * MapManager constructor.
     *
     * @param array $map config
     */
    public function __construct($map)
    {
        $this->map = $map;
    }

    /**
     * Set the sharding service name.
     *
     * @param string $name
     *
     * @throws ShardingException
     */
    public function setService($name): void
    {
        $this->name = $name;
        if (!isset($this->map[$name]['connections'])) {
            throw new ShardingException('Connections are not configured for ' . $name . ' service');
        }
        $this->currentConnections = $this->map[$name]['connections'];

        if (!isset($this->map[$name]['shard_chooser'])) {
            throw new ShardingException('Shard chooser are not configured for ' . $name . ' service');
        }

        $chooserClass = $this->map[$name]['shard_chooser'];
        $relationKey  = (isset($this->map[$name]['relation_key']) ? $this->map[$name]['relation_key'] : null);
        $chooser      = new $chooserClass($this->currentConnections, $relationKey);

        if (!$chooser instanceof ShardChooserInterface) {
            throw new ShardingException('Shard chooser must be instanceof ShardChooserInterface');
        }

        if (!isset($this->container[$name]['shard_chooser'])) {
            $this->container[$name]['shard_chooser'] = $chooser;
        }

        if (!isset($this->map[$name]['id_generator'])) {
            throw new ShardingException('Id generator are not configured for ' . $name . ' service');
        }

        $generatorClass = $this->map[$name]['id_generator'];
        $sequenceKey    = $this->map[$name]['sequence_key'];
        $generator      = new $generatorClass($sequenceKey);

        if (!$generator instanceof IdGeneratorInterface) {
            throw new ShardingException('Id generator must be instanceof IdGeneratorInterface');
        }

        if (!isset($this->container[$name]['id_generator'])) {
            $this->container[$name]['id_generator'] = $generator;
        }
    }

    /**
     * Get sharding config.
     *
     * @return array
     */
    public function getServiceConfig()
    {
        return $this->map[$this->name];
    }

    /**
     * Get the shard chooser.
     *
     * @return ShardChooserInterface
     */
    public function getShardChooser()
    {
        return $this->container[$this->name]['shard_chooser'];
    }

    /**
     * Get the shard id generator.
     *
     * @return IdGeneratorInterface
     */
    public function getIdGenerator()
    {
        return $this->container[$this->name]['id_generator'];
    }
}
