<?php

use Rammewerk\Router\Router;

class PageRouter extends Router
{
    private static ?PageRouter $instance = null;

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new PageRouter(fn(string $class) => new $class());
        }
        return self::$instance;
    }

    public function __construct(?Closure $container = null)
    {
        parent::__construct($container);
    }

    private string $path = '';
    private string $controller = '';
    private string $method = '';
    private array $params = [];

    public array $route_paths = [];

    public function initRoutes(): void
    {
        $this->add('', fn() => $this->setRoute('index', 'index'));
        $this->add('callback', fn() => $this->setRoute('index', 'callback'));
        $this->add('vote/([0-9]+)', fn(...$args) => $this->setRoute('index', 'vote', ['id' => $args[0]]));
        $this->add('buttons', fn() => $this->setRoute('index', 'buttons'));

        $this->add('admin', fn() => $this->setRoute('admin', 'index'));
        $this->add('admin/api', fn() => $this->setRoute('api', 'index'));
        $this->add('api/users', fn() => $this->setRoute('api', 'users'));
        $this->add('api/users/([A-Za-z0-9 ]+)', fn(...$args) => $this->setRoute('api', 'users', ['username' => $args[0]]));
        $this->add('api/users/([A-Za-z0-9 ]+)/votes', fn(...$args) => $this->setRoute('api', 'votes', ['username' => $args[0]]));

        $this->add('admin/login', fn() => $this->setRoute('login', 'index'));
        $this->add('admin/login/authenticate', fn() => $this->setRoute('login', 'authenticate'));
        $this->add('admin/mfa', fn() => $this->setRoute('admin', 'mfa'));

        $this->add('admin/links', fn() => $this->setRoute('links', 'index'));
        $this->add('admin/links/add', fn() => $this->setRoute('links', 'add'));
        $this->add('admin/links/edit/([0-9]+)', fn(...$args) => $this->setRoute('links', 'edit', ['id' => $args[0]]));
        $this->add('admin/links/delete/([0-9]+)', fn(...$args) => $this->setRoute('links', 'delete', ['id' => $args[0]]));
        $this->add('admin/links/toggle/([0-9]+)', fn(...$args) => $this->setRoute('links', 'toggle', ['id' => $args[0]]));

        $this->add('admin/voters', fn() => $this->setRoute('admin', 'voters'));
        $this->add('admin/votes', fn() => $this->setRoute('admin', 'votes'));

        $this->add('admin/users', fn() => $this->setRoute('users', 'index'));
        $this->add('admin/users/add', fn() => $this->setRoute('users', 'add'));
        $this->add('admin/users/edit/([0-9]+)', fn(...$args) => $this->setRoute('users', 'edit', ['id' => $args[0]]));
        $this->add('admin/users/delete/([0-9]+)', fn(...$args) => $this->setRoute('users', 'delete', ['id' => $args[0]]));

        foreach ($this->routes as $pattern => $route) {
            //error_log("📌 Registered route: $pattern");
        }
    }

    public function dispatch(?string $path = null, ?object $serverRequest = null): mixed
    {
        $this->path = trim($path ?? $this->getUriPath(), '/ ');
        //error_log("🔍 Dispatching path: '{$this->path}'");

        $reflection = new \ReflectionClass(Router::class);
        $property = $reflection->getProperty('node');
        $property->setAccessible(true);
        $node = $property->getValue($this);
        //error_log("🧠 Node is " . ($node ? 'set' : 'null'));

        //error_log("📜 Available routes: " . implode(', ', array_keys($this->routes)));

        $route = $this->routes[$this->path] ?? null;
        $args = [];

        if (!$route && $node) {
            $matched = $node->match($this->path);
            //error_log("🧪 Node match result: " . ($matched ? 'matched' : 'null'));
            if ($matched) {
                //error_log("🧪 Matched route pattern: " . ($matched->pattern ?? '[unknown]'));
                $route = $matched;
                $args = $route->getArguments();
            }
        }

        // 🔧 Manual regex fallback
        if (!$route) {
            foreach ($this->routes as $pattern => $candidate) {
                $regex = '#^' . $pattern . '$#';
                if (preg_match($regex, $this->path, $matches)) {
                    error_log("🔧 Regex fallback matched: $pattern");
                    array_shift($matches);
                    $args = $matches;
                    $route = $candidate;
                    break;
                }
            }
        }

        if (!$route) {
            //error_log("❌ 404: No route matched for '{$this->path}'");
            throw new \Rammewerk\Router\Error\InvalidRoute("No route found for path: {$this->path}");
        }

        $route->context = $route->nodeContext;
        $route->nodeContext = '';

        if (isset($args[0]) && is_string($args[0]) && str_starts_with($args[0], $this->path)) {
            array_shift($args);
        }

        if ($route->skipReflection && $route instanceof \Rammewerk\Router\Definition\ClosureRoute) {
            return ($route->getHandler())(...$args);
        }

        if (!$route->factory) {
            $method = $reflection->getMethod('requestHandlerFactory');
            $method->setAccessible(true);
            $route = $method->invoke($this, $route);
        }

        $handlerFactory = $route->factory
            ?? throw new \Rammewerk\Router\Error\InvalidRoute("Unable to handle route for: '{$this->path}'");

        return $route->middleware
            ? $this->runPipeline(
                $this->createMiddlewareFactories($route->middleware),
                static fn(?object $serverRequest) => $handlerFactory($args, $serverRequest),
                $serverRequest
            )
            : $handlerFactory($args, $serverRequest);
    }

    public function setRoute(string $controller, string $method, array $params = []): array
    {
        $this->controller = $controller;
        $this->method = $method;
        $this->params = $params;
        return [$controller, $method, $params];
    }

    public function getController(bool $formatted = false): string
    {
        return $formatted ? ucfirst($this->controller) . 'Controller' : $this->controller;
    }

    public function getViewPath(): string
    {
        return $this->getController() . '/' . $this->getMethod();
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function isSecure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
    }

    public function getUrl(): string
    {
        $baseUrl = 'http' . ($this->isSecure() ? 's' : '') . '://' . $_SERVER['HTTP_HOST'];
        return $baseUrl . web_root;
    }
}