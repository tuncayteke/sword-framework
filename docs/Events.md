# Events Class - Complete Documentation

Modern, powerful and PSR-14 compatible event dispatching system for Sword Framework.

## Table of Contents

- [Overview](#overview)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Event Objects](#event-objects)
- [Event Listeners](#event-listeners)
- [Event Subscribers](#event-subscribers)
- [Wildcard Events](#wildcard-events)
- [Event Middleware](#event-middleware)
- [Queued Events](#queued-events)
- [Async Events](#async-events)
- [Event History](#event-history)
- [System Events](#system-events)
- [Advanced Features](#advanced-features)
- [Best Practices](#best-practices)
- [Performance](#performance)

---

## Overview

The Events class provides a robust event dispatching system with modern features:

- ✅ PSR-14 Event Dispatcher compatible
- ✅ Event objects with stopPropagation
- ✅ Event subscribers
- ✅ Wildcard event listeners
- ✅ Event middleware
- ✅ Queued events
- ✅ Async event support
- ✅ Event history & replay
- ✅ Priority-based execution
- ✅ Type-safe event objects

---

## Installation

```php
require_once 'sword/Events.php';
```

---

## Basic Usage

### Simple Event Listening

```php
// Listen to an event
Events::listen('user.registered', function($event) {
    $user = $event->get('user');
    echo "Welcome, " . $user->name;
});

// Dispatch event
Events::dispatch('user.registered', ['user' => $user]);
```

### With Priority

```php
// Lower priority number = runs first
Events::listen('user.registered', function($event) {
    echo "This runs first";
}, 10);

Events::listen('user.registered', function($event) {
    echo "This runs second";
}, 20);
```

### Multiple Listeners

```php
Events::listen('order.created', function($event) {
    // Send email
    Mailer::send($event->get('order'));
});

Events::listen('order.created', function($event) {
    // Update inventory
    Inventory::reduce($event->get('order'));
});

Events::listen('order.created', function($event) {
    // Log order
    Logger::info('Order created', $event->getData());
});
```

---

## Event Objects

### Creating Event Objects

```php
// Simple event
$event = new Event('user.registered', [
    'user' => $user,
    'ip' => $_SERVER['REMOTE_ADDR']
]);

Events::dispatch($event);
```

### Custom Event Classes

```php
class UserRegisteredEvent extends Event
{
    private $user;

    public function __construct($user)
    {
        parent::__construct('user.registered');
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }
}

// Usage
$event = new UserRegisteredEvent($user);
Events::dispatch($event);

// In listener
Events::listen('user.registered', function(UserRegisteredEvent $event) {
    $user = $event->getUser();
    // ...
});
```

### Stoppable Events

```php
Events::listen('payment.processing', function($event) {
    if ($event->get('amount') > 10000) {
        // Stop further processing
        $event->stopPropagation();
        return false;
    }
});

Events::listen('payment.processing', function($event) {
    // This won't run if amount > 10000
    processPayment($event->get('order'));
});
```

### Event Methods

```php
$event = new Event('user.action', ['action' => 'login']);

// Get event name
$name = $event->getName(); // 'user.action'

// Get all data
$data = $event->getData(); // ['action' => 'login']

// Get specific data
$action = $event->get('action'); // 'login'
$missing = $event->get('missing', 'default'); // 'default'

// Set data
$event->set('timestamp', time());

// Get timestamp
$timestamp = $event->getTimestamp();

// Stop propagation
$event->stopPropagation();

// Check if stopped
if ($event->isPropagationStopped()) {
    // Handle stopped event
}
```

---

## Event Listeners

### Basic Listener

```php
Events::listen('user.login', function($event) {
    $user = $event->get('user');
    Logger::info("User {$user->name} logged in");
});
```

### Class-based Listener

```php
class UserLoginListener
{
    public function handle($event)
    {
        $user = $event->get('user');
        // Handle login logic
    }
}

// Register with class@method notation
Events::listen('user.login', 'UserLoginListener@handle');
```

### Multiple Events, One Listener

```php
$logListener = function($event) {
    Logger::info($event->getName(), $event->getData());
};

Events::listen('user.login', $logListener);
Events::listen('user.logout', $logListener);
Events::listen('order.created', $logListener);
```

### Removing Listeners

```php
$listener = function($event) {
    echo "This will be removed";
};

Events::listen('test.event', $listener);

// Remove specific listener
Events::forget('test.event', $listener);

// Remove all listeners for an event
Events::forget('test.event');
```

---

## Event Subscribers

### Creating a Subscriber

```php
class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'user.registered' => 'onUserRegistered',
            'user.login' => ['onUserLogin', 10],
            'user.logout' => [
                ['onUserLogout', 10],
                ['logLogout', 20]
            ]
        ];
    }

    public function onUserRegistered($event)
    {
        $user = $event->get('user');
        // Send welcome email
        Mailer::send($user->email, 'Welcome!');
    }

    public function onUserLogin($event)
    {
        // Update last login
        $user = $event->get('user');
        $user->last_login = time();
        $user->save();
    }

    public function onUserLogout($event)
    {
        // Clear session data
        Session::clear();
    }

    public function logLogout($event)
    {
        // Log logout event
        Logger::info('User logged out');
    }
}

// Register subscriber
Events::subscribe(new UserEventSubscriber());

// Or with class name
Events::subscribe('UserEventSubscriber');
```

### Order Event Subscriber Example

```php
class OrderEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'order.created' => [
                ['sendConfirmation', 10],
                ['updateInventory', 20],
                ['notifyAdmin', 30]
            ],
            'order.cancelled' => 'onOrderCancelled',
            'order.shipped' => 'onOrderShipped'
        ];
    }

    public function sendConfirmation($event)
    {
        $order = $event->get('order');
        Mailer::send($order->email, 'Order Confirmed', $order);
    }

    public function updateInventory($event)
    {
        $order = $event->get('order');
        foreach ($order->items as $item) {
            Inventory::reduce($item->product_id, $item->quantity);
        }
    }

    public function notifyAdmin($event)
    {
        $order = $event->get('order');
        Mailer::send('admin@example.com', 'New Order', $order);
    }

    public function onOrderCancelled($event)
    {
        // Handle cancellation
    }

    public function onOrderShipped($event)
    {
        // Handle shipping
    }
}
```

---

## Wildcard Events

### Wildcard Patterns

```php
// Listen to all user events
Events::listen('user.*', function($event) {
    Logger::info('User event: ' . $event->getName());
});

// Triggers:
Events::dispatch('user.registered'); // ✓
Events::dispatch('user.login');      // ✓
Events::dispatch('user.logout');     // ✓
Events::dispatch('user.updated');    // ✓
```

### Suffix Wildcards

```php
// Listen to all "created" events
Events::listen('*.created', function($event) {
    Analytics::track('created', $event->getData());
});

// Triggers:
Events::dispatch('user.created');  // ✓
Events::dispatch('post.created');  // ✓
Events::dispatch('order.created'); // ✓
```

### Complex Wildcards

```php
// Listen to all model events
Events::listen('model.*', function($event) {
    Cache::flush('models');
});

// Listen to all admin actions
Events::listen('admin.*.action', function($event) {
    AuditLog::record($event);
});
```

### Wildcard Priority

```php
Events::listen('user.*', function($event) {
    echo "Low priority wildcard";
}, 50);

Events::listen('user.*', function($event) {
    echo "High priority wildcard";
}, 10);

Events::listen('user.login', function($event) {
    echo "Specific event listener";
}, 20);

// Order: High priority wildcard → Specific → Low priority wildcard
```

---

## Event Middleware

### Adding Middleware

```php
// Log all events
Events::middleware(function($event) {
    Logger::debug('Event: ' . $event->getName());
    return $event;
}, 10);

// Modify event data
Events::middleware(function($event) {
    $event->set('timestamp', microtime(true));
    $event->set('ip', $_SERVER['REMOTE_ADDR']);
    return $event;
}, 20);

// Validate event
Events::middleware(function($event) {
    if (!$event->get('user_id')) {
        throw new Exception('user_id required');
    }
    return $event;
}, 5);
```

### Authentication Middleware

```php
Events::middleware(function($event) {
    // Only allow events from authenticated users
    if (!Auth::check() && strpos($event->getName(), 'admin.') === 0) {
        throw new Exception('Unauthorized');
    }
    return $event;
});
```

### Rate Limiting Middleware

```php
Events::middleware(function($event) {
    $key = 'event:' . $event->getName();
    
    if (RateLimit::tooManyAttempts($key, 100)) {
        $event->stopPropagation();
    }
    
    RateLimit::hit($key);
    return $event;
});
```

---

## Queued Events

### Queue Events

```php
// Queue event instead of dispatching immediately
Events::queue('email.send', [
    'to' => 'user@example.com',
    'subject' => 'Welcome'
]);

Events::queue('report.generate', [
    'user_id' => 123
]);

// Flush queue (process all queued events)
Events::flush();
```

### Background Processing

```php
// In your application
Events::queue('order.created', ['order' => $order]);
Events::queue('inventory.update', ['items' => $items]);

// At the end of request or in cron job
register_shutdown_function(function() {
    Events::flush();
});
```

### Queued Event Use Cases

```php
// Heavy operations
Events::queue('image.resize', ['path' => $imagePath]);
Events::queue('video.transcode', ['video_id' => $id]);

// Batch operations
foreach ($users as $user) {
    Events::queue('email.send', ['user' => $user]);
}
Events::flush(); // Send all at once

// Non-critical notifications
Events::queue('notification.push', ['message' => $message]);
```

---

## Async Events

### Setting Up Async Handler

```php
// Define how async events should be handled
Events::asyncHandler('email.send', function($event, $payload) {
    // Option 1: Use exec to run in background
    $command = "php background-worker.php '{$event}' '" . 
               json_encode($payload) . "' > /dev/null 2>&1 &";
    exec($command);
});

Events::asyncHandler('report.generate', function($event, $payload) {
    // Option 2: Add to job queue (Redis, Beanstalkd, etc.)
    Queue::push('ReportJob', $payload);
});
```

### Dispatching Async Events

```php
// This will run in background
Events::dispatchAsync('email.send', [
    'to' => 'user@example.com',
    'subject' => 'Your Report is Ready'
]);

// Main script continues immediately
echo "Email queued for sending";
```

### Async Event Examples

```php
// Image processing
Events::dispatchAsync('image.process', [
    'path' => $uploadedFile,
    'sizes' => ['thumb', 'medium', 'large']
]);

// API webhook
Events::dispatchAsync('webhook.call', [
    'url' => 'https://api.example.com/webhook',
    'data' => $orderData
]);

// Analytics
Events::dispatchAsync('analytics.track', [
    'event' => 'purchase',
    'user_id' => $user->id,
    'amount' => $total
]);
```

---

## Event History

### Enable History

```php
// Enable event history tracking
Events::enableHistory(true);

// Dispatch some events
Events::dispatch('user.login', ['user_id' => 123]);
Events::dispatch('page.view', ['page' => '/home']);
Events::dispatch('user.logout', ['user_id' => 123]);
```

### View History

```php
// Get all history
$history = Events::getHistory();

/*
[
    [
        'event' => 'user.login',
        'timestamp' => 1704470400.123,
        'payload' => ['user_id' => 123]
    ],
    [
        'event' => 'page.view',
        'timestamp' => 1704470401.456,
        'payload' => ['page' => '/home']
    ],
    ...
]
*/

// Get history for specific event
$loginHistory = Events::getHistory('user.login');
```

### Replay Events

```php
// Replay all events
Events::replay();

// Replay specific event
Events::replay('user.login');
```

### Clear History

```php
Events::clearHistory();
```

### History Use Cases

```php
// Debugging
Events::enableHistory(true);
// ... run application ...
$history = Events::getHistory();
print_r($history); // See all events that fired

// Event sourcing
Events::enableHistory(true);
// ... application runs ...
// Save history to database
DB::insert('event_log', Events::getHistory());

// Testing
Events::enableHistory(true);
runTest();
$history = Events::getHistory('order.created');
assert(count($history) === 1);
```

---

## System Events

### Available System Events

```php
const PRE_SYSTEM                      = 'pre_system';
const BEFORE_CONTROLLER_CONSTRUCTOR   = 'before_controller_constructor';
const BEFORE_CONTROLLER_METHOD        = 'before_controller_method';
const AFTER_CONTROLLER_METHOD         = 'after_controller_method';
const POST_SYSTEM                     = 'post_system';
const EXCEPTION_THROWN                = 'exception_thrown';
const BEFORE_RENDER                   = 'before_render';
const AFTER_RENDER                    = 'after_render';
const CACHE_HIT                       = 'cache_hit';
const CACHE_MISS                      = 'cache_miss';
const DB_QUERY                        = 'db_query';
const USER_LOGIN                      = 'user_login';
const USER_LOGOUT                     = 'user_logout';
const MODEL_CREATED                   = 'model_created';
const MODEL_UPDATED                   = 'model_updated';
const MODEL_DELETED                   = 'model_deleted';
```

### System Event Helpers

```php
// Pre-system
Events::triggerPreSystem(['request' => $_REQUEST]);

// Exception handling
try {
    // code
} catch (Exception $e) {
    Events::triggerExceptionThrown($e);
}

// Cache events
$value = Cache::get('key');
if ($value) {
    Events::triggerCacheHit('key', $value);
} else {
    Events::triggerCacheMiss('key');
}

// Database queries
Events::triggerDbQuery($sql, $executionTime);

// User events
Events::triggerUserLogin($user);
Events::triggerUserLogout($user);

// Model events
Events::triggerModelCreated($model);
Events::triggerModelUpdated($model, $changes);
Events::triggerModelDeleted($model);
```

### Listening to System Events

```php
// Log all database queries
Events::listen(Events::DB_QUERY, function($event) {
    $query = $event->get('query');
    $time = $event->get('time');
    Logger::debug("Query: $query (${time}ms)");
});

// Track exceptions
Events::listen(Events::EXCEPTION_THROWN, function($event) {
    $exception = $event->get('exception');
    Sentry::captureException($exception);
});

// Cache monitoring
Events::listen('cache.*', function($event) {
    $key = $event->get('key');
    Metrics::increment('cache.' . $event->getName());
});

// User activity tracking
Events::listen('user.*', function($event) {
    $user = $event->get('user');
    ActivityLog::record($user->id, $event->getName());
});
```

---

## Advanced Features

### Check for Listeners

```php
if (Events::hasListeners('order.created')) {
    Events::dispatch('order.created', $order);
}
```

### Count Listeners

```php
$count = Events::countListeners('user.registered');
echo "Registered handlers: $count";
```

### Get All Listeners

```php
$listeners = Events::getListeners('user.login');
foreach ($listeners as $listener) {
    // Inspect listener
}
```

### Get All Events

```php
$events = Events::getEvents();
// ['user.registered', 'user.login', 'order.created', ...]
```

### Event Statistics

```php
$stats = Events::getStats();

/*
[
    'total_events' => 15,
    'total_listeners' => 42,
    'wildcard_patterns' => 3,
    'subscribers' => 2,
    'queued_events' => 5,
    'history_count' => 128,
    'history_enabled' => true,
    'middlewares' => 3
]
*/
```

### Clear All Events

```php
// Remove all listeners, history, queue
Events::clear();
```

---

## Best Practices

### 1. Use Event Objects for Complex Data

```php
// Bad: Using arrays
Events::dispatch('order.created', [
    'order_id' => 123,
    'user_id' => 456,
    'total' => 99.99,
    'items' => [...]
]);

// Good: Using event objects
class OrderCreatedEvent extends Event
{
    private $order;
    
    public function __construct(Order $order)
    {
        parent::__construct('order.created');
        $this->order = $order;
    }
    
    public function getOrder(): Order
    {
        return $this->order;
    }
}

Events::dispatch(new OrderCreatedEvent($order));
```

### 2. Use Subscribers for Related Events

```php
// Bad: Registering many listeners individually
Events::listen('user.registered', 'sendWelcomeEmail');
Events::listen('user.registered', 'createProfile');
Events::listen('user.registered', 'sendToAnalytics');
Events::listen('user.login', 'updateLastLogin');
Events::listen('user.logout', 'clearSession');

// Good: Using subscriber
class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'user.registered' => [
                ['sendWelcomeEmail', 10],
                ['createProfile', 20],
                ['sendToAnalytics', 30]
            ],
            'user.login' => 'updateLastLogin',
            'user.logout' => 'clearSession'
        ];
    }
    
    // ... methods
}
```

### 3. Use Wildcards for Cross-cutting Concerns

```php
// Logging all model events
Events::listen('model.*', function($event) {
    AuditLog::record($event);
});

// Analytics for all user actions
Events::listen('user.*', function($event) {
    Analytics::track($event->getName(), $event->getData());
});

// Cache invalidation
Events::listen('*.updated', function($event) {
    Cache::forget($event->get('model'));
});
```

### 4. Queue Heavy Operations

```php
// Bad: Blocking main request
Events::listen('order.created', function($event) {
    generatePdfInvoice($event->get('order')); // Slow!
});

// Good: Queue for background processing
Events::listen('order.created', function($event) {
    Events::queue('invoice.generate', ['order' => $event->get('order')]);
});
```

### 5. Use Middleware for Common Logic

```php
// Add timestamp to all events
Events::middleware(function($event) {
    $event->set('processed_at', microtime(true));
    return $event;
});

// Add user context to all events
Events::middleware(function($event) {
    if (Auth::check()) {
        $event->set('user_id', Auth::id());
    }
    return $event;
});
```

### 6. Name Events Consistently

```php
// Good naming convention:
// entity.action or entity.action.status

'user.registered'
'user.login'
'user.logout'
'user.deleted'

'order.created'
'order.updated'
'order.cancelled'
'order.payment.completed'
'order.payment.failed'

'email.sent'
'email.failed'
```

### 7. Use Priority Wisely

```php
// Critical first (low number)
Events::listen('user.login', 'validateIpAddress', 5);
Events::listen('user.login', 'checkBan', 10);

// Normal processing
Events::listen('user.login', 'updateLastLogin', 50);
Events::listen('user.login', 'logActivity', 50);

// Cleanup last (high number)
Events::listen('user.login', 'cleanupSessions', 100);
```

---

## Performance

### Optimization Tips

1. **Use Specific Events Instead of Wildcards**
```php
// Slower: Checks pattern for every event
Events::listen('*', $listener);

// Faster: Direct match
Events::listen('user.login', $listener);
```

2. **Limit Event History in Production**
```php
if (ENVIRONMENT === 'development') {
    Events::enableHistory(true);
}
```

3. **Queue Heavy Listeners**
```php
Events::listen('image.uploaded', function($event) {
    Events::queue('image.process', $event->getData());
});
```

4. **Use Subscribers for Organization**
```php
// Registers multiple listeners efficiently
Events::subscribe(new UserEventSubscriber());
```

### Performance Benchmarks

```
Test: 1000 events dispatched

No listeners:
- Time: ~2ms
- Memory: 100KB

10 listeners per event:
- Time: ~50ms
- Memory: 500KB

With middleware (3):
- Time: ~60ms
- Memory: 550KB

With history enabled:
- Time: ~70ms
- Memory: 1MB
```

---

## Complete Example

```php
<?php

// ======================================
// Event Classes
// ======================================

class UserRegisteredEvent extends Event
{
    private $user;
    
    public function __construct($user)
    {
        parent::__construct('user.registered');
        $this->user = $user;
    }
    
    public function getUser()
    {
        return $this->user;
    }
}

// ======================================
// Event Subscribers
// ======================================

class UserEventSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            'user.registered' => [
                ['sendWelcomeEmail', 10],
                ['createProfile', 20],
                ['notifyAdmin', 30]
            ],
            'user.login' => 'onLogin',
            'user.logout' => 'onLogout'
        ];
    }
    
    public function sendWelcomeEmail($event)
    {
        $user = $event->getUser();
        Mailer::send($user->email, 'Welcome!', [
            'name' => $user->name
        ]);
    }
    
    public function createProfile($event)
    {
        $user = $event->getUser();
        Profile::create([
            'user_id' => $user->id
        ]);
    }
    
    public function notifyAdmin($event)
    {
        $user = $event->getUser();
        Mailer::send('admin@example.com', 'New User', [
            'user' => $user
        ]);
    }
    
    public function onLogin($event)
    {
        $user = $event->get('user');
        $user->last_login = time();
        $user->save();
    }
    
    public function onLogout($event)
    {
        Session::destroy();
    }
}

// ======================================
// Bootstrap
// ======================================

// Enable history in development
if (ENVIRONMENT === 'development') {
    Events::enableHistory(true);
}

// Add middleware
Events::middleware(function($event) {
    $event->set('timestamp', microtime(true));
    $event->set('ip', $_SERVER['REMOTE_ADDR']);
    return $event;
}, 10);

// Subscribe to events
Events::subscribe(new UserEventSubscriber());

// Wildcard listeners
Events::listen('user.*', function($event) {
    Logger::info($event->getName(), $event->getData());
});

Events::listen('*.created', function($event) {
    Cache::flush();
});

// System event listeners
Events::listen(Events::EXCEPTION_THROWN, function($event) {
    $exception = $event->get('exception');
    Sentry::captureException($exception);
});

Events::listen(Events::DB_QUERY, function($event) {
    if ($event->get('time') > 100) {
        Logger::warning('Slow query', [
            'query' => $event->get('query'),
            'time' => $event->get('time')
        ]);
    }
});

// ======================================
// Usage in Application
// ======================================

// User registration
$user = User::create($_POST);
Events::dispatch(new UserRegisteredEvent($user));

// User login
Events::dispatch('user.login', ['user' => $user]);

// Order created (with queued processing)
Events::dispatch('order.created', ['order' => $order]);
Events::queue('invoice.generate', ['order' => $order]);
Events::queue('email.receipt', ['order' => $order]);

// Flush queued events at shutdown
register_shutdown_function(function() {
    Events::flush();
});

// View statistics
if (ENVIRONMENT === 'development') {
    print_r(Events::getStats());
    print_r(Events::getHistory());
}
```

---

## Migration from Old Events

### Old Code

```php
Events::on('user.registered', function($data) {
    $user = $data; // Direct data
    sendEmail($user);
});

Events::trigger('user.registered', $user);
```

### New Code

```php
Events::listen('user.registered', function($event) {
    $user = $event->get('user'); // Event object
    sendEmail($user);
});

Events::dispatch('user.registered', ['user' => $user]);

// Or with event object
Events::dispatch(new UserRegisteredEvent($user));
```

---

## Troubleshooting

### Events Not Firing

```php
// Check if listeners are registered
if (!Events::hasListeners('my.event')) {
    echo "No listeners registered!";
}

// Check listener count
echo Events::countListeners('my.event');

// View all events
print_r(Events::getEvents());
```

### Debugging Events

```php
// Enable history
Events::enableHistory(true);

// Run code
// ...

// Check what fired
print_r(Events::getHistory());
```

### Performance Issues

```php
// Check stats
$stats = Events::getStats();

if ($stats['total_listeners'] > 100) {
    echo "Too many listeners!";
}

// Disable history in production
Events::enableHistory(false);
```

---

## License

MIT License - See LICENSE file for details.

---

## Credits

Developed by **Tuncay TEKE** (https://www.tuncayteke.com.tr) 

For Sword Framework - Sharp. Fast. Immortal.