<?php

namespace Illuminate\Queue\Capsule;

use Illuminate\Queue\QueueManager;
use Illuminate\Container\Container;
use Illuminate\Queue\QueueServiceProvider;
use Illuminate\Support\Traits\CapsuleManagerTrait;

/**
 * @mixin \Illuminate\Queue\QueueManager
 * @mixin \Illuminate\Contracts\Queue\Queue
 */
class Manager
{
    //引入trait
    use CapsuleManagerTrait;

    /**
     * The queue manager instance.
     *
     * @var \Illuminate\Queue\QueueManager
     */
    protected $manager;

    /**
     * Create a new queue capsule manager.
     *
     * @param  \Illuminate\Container\Container  $container
     * @return void
     */
    public function __construct(Container $container = null)
    {
        //保存容器
        $this->setupContainer($container ?: new Container);

        // Once we have the container setup, we will setup the default configuration
        // options in the container "config" bindings. This just makes this queue
        // manager behave correctly since all the correct binding are in place.
        //设置默认列队
        $this->setupDefaultConfiguration();

        //保存QueueManager 对象
        $this->setupManager();

        //注册链接器[注册各种连接驱动]
        //列队服务提供器已经完成了
        //主要是将各种链接如redis,database,file,sync这些注册并保存在
        //$this->connectors[$driver] = $resolver; QueueManager
        $this->registerConnectors();
    }

    /**
     * Setup the default queue configuration options.
     *
     * @return void
     */
    protected function setupDefaultConfiguration()
    {
        $this->container['config']['queue.default'] = 'default';
    }

    /**
     * Build the queue manager instance.
     *
     * @return void
     */
    protected function setupManager()
    {
        $this->manager = new QueueManager($this->container);
    }

    /**
     * Register the default connectors that the component ships with.
     *
     * @return void
     */
    protected function registerConnectors()
    {
        //列队服务提供器 基类需要容器对象即Application
        $provider = new QueueServiceProvider($this->container);

        //注册链接器 QueueManager
        $provider->registerConnectors($this->manager);
    }

    /**
     * Get a connection instance from the global manager.
     *
     * @param  string  $connection
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public static function connection($connection = null)
    {
        //得到驱动实例【连接实例】对象
        return static::$instance->getConnection($connection);
    }

    /**
     * Push a new job onto the queue.
     *
     * @param  string  $job  任务
     * @param  mixed   $data  任务数据
     * @param  string  $queue  队列
     * @param  string  $connection 连接
     * @return mixed
     */
    public static function push($job, $data = '', $queue = null, $connection = null)
    {
        //static::$instance 自己
        //connection($connection) 根据连接名称取得对应的连接实例【连接对象】
        //$connection 要用哪个连接驱动
        //队列管理器已经封闭了redis,database,sync这些玩意，传递时就会选择并实例化连接返回如redis
        

        return static::$instance->connection($connection)->push($job, $data, $queue);
    }

    /**
     * Push a new an array of jobs onto the queue.
     *
     * @param  array   $jobs
     * @param  mixed   $data
     * @param  string  $queue
     * @param  string  $connection
     * @return mixed
     */
    public static function bulk($jobs, $data = '', $queue = null, $connection = null)
    {
        return static::$instance->connection($connection)->bulk($jobs, $data, $queue);
    }

    /**
     * Push a new job onto the queue after a delay.
     *
     * @param  \DateTimeInterface|\DateInterval|int  $delay
     * @param  string  $job
     * @param  mixed   $data
     * @param  string  $queue
     * @param  string  $connection
     * @return mixed
     */
    public static function later($delay, $job, $data = '', $queue = null, $connection = null)
    {
        return static::$instance->connection($connection)->later($delay, $job, $data, $queue);
    }

    /**
     * Get a registered connection instance.
     *得到连接的驱动实例【对象】
     * @param  string  $name
     * @return \Illuminate\Contracts\Queue\Queue
     */
    public function getConnection($name = null)
    {
        return $this->manager->connection($name);
    }

    /**
     * Register a connection with the manager.
     *给指定的队列连接设置连接参数
     * @param  array   $config
     * @param  string  $name
     * @return void
     */
    public function addConnection(array $config, $name = 'default')
    {
        //往指定的队列链接配置参数 设置值
        /**
        'connections' => [

        'sync' => [
        'driver' => 'sync',
        ],

        'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        ],

        'beanstalkd' => [
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        ],
         **/
        $this->container['config']["queue.connections.{$name}"] = $config;
    }

    /**
     * Get the queue manager instance.
     *
     * @return \Illuminate\Queue\QueueManager
     */
    public function getQueueManager()
    {
        return $this->manager;
    }

    /**
     * Pass dynamic instance methods to the manager.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->manager->$method(...$parameters);
    }

    /**
     * Dynamically pass methods to the default connection.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::connection()->$method(...$parameters);
    }
}
