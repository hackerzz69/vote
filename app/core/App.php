<?php

use Router\Router;

class App {

    /** @var Controller controller */
    private $controller;
    private $router;

    public function __construct() {
        $this->router = PageRouter::getInstance();
        $this->router->initRoutes();

        try {
            $this->router->route();
        } catch (\Router\RouteNotFoundException $e) {
            $this->router->setRoute("errors", "show404");
        }

        $controller  = $this->router->getController(true);

        /** @var Controller controller */
        $this->controller = new $controller;

        /** Redirects to 404 is method doesn't exist. */
        if (!method_exists($this->controller, $this->router->getMethod())) {
            $this->router->setRoute("errors", "show404");

            $controller  = $this->router->getController(true);
            $this->controller = new $controller;
        }

        $this->controller->setView($this->router->getViewPath());
        $this->controller->setActionName($this->router->getMethod());
        $this->controller->setRouter($this->router);

        if ($this->initRoute()) {
            $this->controller->show();
        }
    }

    /**
     * Calls the action within a controller
     * TODO: revisit later
     */
    public function initRoute() {
        if (method_exists($this->controller, "beforeExecute")) {
            call_user_func_array([$this->controller, "beforeExecute"], []);
        }

        $bearer_token = $this->controller->getRequest()->getBearerToken();

        if ($this->controller->isJson()) {
            if ($bearer_token != api_key) {
                $output = ['error' => 'Invalid API Key: '.$bearer_token];
            } else {
                $output =
                    call_user_func_array([$this->controller, $this->router->getMethod()], $this->router->getParams());
            }

            if (is_subclass_of($this->controller, "Controller")) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($output, JSON_PRETTY_PRINT);
            }
            return false;
        } else {
            $output = call_user_func_array([$this->controller, $this->router->getMethod()], $this->router->getParams());

            if ($this->controller->getActionName() == "callback") {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode($output, JSON_PRETTY_PRINT);
                return false;
            }
            return true;
        }



        /*if (!$this->controller->isJson()) {
            if (!file_exists('app/views/'.$this->controller->getView().'.twig')) {
                $this->controller->setView("errors/show404");
                return false;
            }
            return true;
        } else {
            $bearer_token = $this->controller->getRequest()->getBearerToken();
            return true;
        }*/
    }


}
?>
