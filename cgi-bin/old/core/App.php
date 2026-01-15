<?php
/**
 * Classe Principal da Aplicação
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class App {
    protected $controller = 'HomeController';
    protected $method = 'index';
    protected $params = [];

    public function __construct() {
        $url = $this->parseUrl();

        // Verificar se o controller existe
        if (isset($url[0]) && file_exists('controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]) . 'Controller';
            unset($url[0]);
        }

        // Instanciar o controller
        require_once 'controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Verificar se o método existe
        if (isset($url[1])) {
            if (method_exists($this->controller, $url[1])) {
                $this->method = $url[1];
                unset($url[1]);
            }
        }

        // Obter parâmetros
        $this->params = $url ? array_values($url) : [];

        // Chamar o método do controller com os parâmetros
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parse da URL
     */
    private function parseUrl() {
        if (isset($_GET['url'])) {
            return explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        }
        return [];
    }
}
