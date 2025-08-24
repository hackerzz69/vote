<?php

use Rammewerk\Router\Router;
use Rammewerk\Router\Error\InvalidRoute;

class App
{
    /** @var Controller controller */
    private $controller;
    private $router;

    public function __construct()
    {
        $this->router = PageRouter::getInstance();
        $this->router->initRoutes();

        try {
            $this->router->dispatch();
            error_log("[Router] Dispatched route: " . json_encode([
                'controller' => $this->router->getController(true),
                'method'     => $this->router->getMethod(),
                'params'     => $this->router->getParams()
            ]));
        } catch (InvalidRoute $e) {
            error_log("[Router] Invalid route: " . $e->getMessage());
            $this->router->setRoute("errors", "show404");
        }

        $controller = $this->router->getController(true);
        error_log("[App] Instantiating controller: $controller");

        /** @var Controller controller */
        $this->controller = new $controller();

        if (!method_exists($this->controller, $this->router->getMethod())) {
            error_log("[App] Method not found: " . $this->router->getMethod());
            $this->router->setRoute("errors", "show404");

            $controller = $this->router->getController(true);
            error_log("[App] Fallback controller: $controller");
            $this->controller = new $controller();
        }

        $this->controller->setView($this->router->getViewPath());
        $this->controller->setActionName($this->router->getMethod());
        $this->controller->setRouter($this->router);

        if ($this->initRoute()) {
            error_log("[App] Showing view: " . $this->controller->getView());
            $this->controller->show();
        } else {
            error_log("[App] View rendering skipped.");
        }
    }

    public function initRoute()
    {
        if (method_exists($this->controller, "beforeExecute")) {
            error_log("[App] Calling beforeExecute()");
            call_user_func_array([$this->controller, "beforeExecute"], []);
        }

        $bearer_token = $this->controller->getRequest()->getBearerToken();
        error_log("[App] Bearer token: $bearer_token");

        if ($this->controller->isJson()) {
            error_log("[App] JSON mode enabled");

            if ($bearer_token != api_key) {
                error_log("[App] Invalid API key");
                $output = ["error" => "Invalid API Key: " . $bearer_token];
            } else {
                error_log("[App] Executing JSON method: " . $this->router->getMethod());
                $output = call_user_func_array(
                    [$this->controller, $this->router->getMethod()],
                    $this->router->getParams()
                );
            }

            if (is_subclass_of($this->controller, "Controller")) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($output, JSON_PRETTY_PRINT);
            }
            return false;
        } else {
            error_log("[App] Executing HTML method: " . $this->router->getMethod());
            $output = call_user_func_array(
                [$this->controller, $this->router->getMethod()],
                $this->router->getParams()
            );

            if ($this->controller->getActionName() == "callback") {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($output, JSON_PRETTY_PRINT);
                return false;
            }

            return true;
        }
    }
}