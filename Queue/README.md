## Illuminate Queue

The Laravel Queue component provides a unified API across a variety of different queue services. Queues allow you to defer the processing of a time consuming task, such as sending an e-mail, until a later time, thus drastically speeding up the web requests to your application.

### Usage Instructions

First, create a new Queue `Capsule` manager instance. Similar to the "Capsule" provided for the Eloquent ORM, the queue Capsule aims to make configuring the library for usage outside of the Laravel framework as easy as possible.

```PHP
use Illuminate\Queue\Capsule\Manager as Queue;

//实例化队列管理器
//已经实现了各类队列的连接驱动
$queue = new Queue;

//给指定的连接驱动设置连接参数如redis
$queue->addConnection([
    'driver' => 'beanstalkd',
    'host' => 'localhost',
    'queue' => 'default',
]);

//将本身作为静态变量【整个程序运行周期均有效】
// Make this Capsule instance available globally via static methods... (optional)
$queue->setAsGlobal();
```

Once the Capsule instance has been registered. You may use it like so:

```PHP
// As an instance...
//得指定的连接驱动里添加任务
//如redis->rpush(default[任务名称],任务数据)
$queue->push('SendEmail', array('message' => $message));

// If setAsGlobal has been called...
Queue::push('SendEmail', array('message' => $message));
```
//当运行php artisan queue:work 时从队列里取出
For further documentation on using the queue, consult the [Laravel framework documentation](https://laravel.com/docs).
