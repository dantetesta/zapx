<?php
/**
 * Controller da Home/Landing Page
 * 
 * @author Dante Testa - https://dantetesta.com.br
 * @date 2025-10-25 14:10:00
 * @version 1.0.0
 */

class HomeController extends Controller {
    
    /**
     * Página inicial - Landing Page
     */
    public function index() {
        // Não requer autenticação - página pública
        $this->view('home/index');
    }
    
    /**
     * Redirecionar para landing page se acessar /home
     */
    public function home() {
        $this->redirect('');
    }
}
?>
