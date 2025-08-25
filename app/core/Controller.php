<?php
use Fox\Request;
use Fox\Cookies;
use Fox\Template;
use Phalcon\Db\Index;

class Controller {

    protected $view;
    protected $viewVars = array();
    protected $actionName;

    private $disableView;
    private $json_output;

    /** @var PageRouter */
    protected $router;

    /** @var Request $request */
    protected $request;

    /** @var Security $security */
    protected $security;

    /** @var Cookies $cookies */
    protected $cookies;

    /**
     * Gets the name of the action
     * @return mixed
     */
    public function getActionName() {
        return $this->actionName;
    }

    /**
     * Sets the action to be used.
     * @param $name
     */
    public function setActionName($name) {
        $this->actionName = $name;
    }

    /**
     * Sets a specific variable for the view with a value
     * @param $variableName
     * @param $value
     */
    public function set($variableName, $value) {
        $this->viewVars[$variableName] = $value;
    }

    /**
     * Sets variables to be used in the view
     * @param $params
     */
    public function setVars($params) {
        $this->viewVars = $params;
    }

    /**
     * Displays the necessary template using Twig
     */
    public function show() {
        if ($this->disableView) {
            return;
        }

        $loader = new Template('app/views');
        $loader->setCacheEnabled(false);

        try {
            $template = $loader->load($this->view);
            echo $template->render($this->viewVars);
        } catch (Exception $e) {
            // Optional: log or display error
        }
    }

    /**
     * Sets which view to use.
     * @param $view
     */
    public function setView($view) {
        $this->view = $view;
    }

    public function getView() {
        return $this->view;
    }

    /**
     * @param $router PageRouter
     */
    public function setRouter(PageRouter $router) {
        $this->router = $router;
    }

    /**
     * Filters a string (PHP 8.1+ safe)
     * Removes HTML tags and strips low/high ASCII
     * @param $str
     * @return string
     */
    public function filter($str) {
        $str = strip_tags($str);
        $str = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $str);
        return trim($str);
    }

    /**
     * Filters an integer.
     * @param $int
     * @return mixed
     */
    public function filterInt($int) {
        return filter_var($int, FILTER_SANITIZE_NUMBER_INT,
            FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
    }

    public function beforeExecute() {
        $this->request  = Request::getInstance();
        $this->cookies  = Cookies::getInstance();
        $this->security = Security::getInstance($this);

        $hasAccess = $this->security->checkAccess();

        if (!$hasAccess) {
            $this->setView("errors/show401");
            return;
        }
    }

    public static function debug($array) {
        echo "<pre>" . json_encode($array, JSON_PRETTY_PRINT) . "</pre>";
    }

    public static function printStr($str) {
        echo "<pre>" . $str . "</pre>";
    }

    public function disableView($is_json = false) {
        $this->disableView = true;
        $this->json_output = $is_json;
    }

    public function isJson() {
        return $this->json_output;
    }

    public function getCookies() {
        return $this->cookies;
    }

    public function getRequest() {
        return $this->request;
    }

    public function getRouter() {
        return $this->router;
    }

    public function redirect($location, $internal = true) {
        $this->request->redirect($location, $internal);
    }

    public function delayedRedirect($url, $time, $internal = false) {
        $this->request->delayedRedirect($url, $time, $internal);
    }
}