<?php
/**
 * Controller de Tags/Categorias
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-25 07:13:00
 */

class TagsController extends Controller {
    private $tagModel;

    public function __construct() {
        $this->tagModel = $this->model('Tag');
    }

    /**
     * Listar tags
     */
    public function index() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        $tags = $this->tagModel->getByUser($userId);

        $this->view('tags/index', [
            'user' => $user,
            'tags' => $tags
        ]);
    }

    /**
     * Criar tag
     */
    public function create() {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            $name = $_POST['name'] ?? '';
            $color = $_POST['color'] ?? '#3B82F6';

            // Validações
            if (empty($name)) {
                $this->json(['success' => false, 'message' => 'Nome é obrigatório.'], 400);
                return;
            }

            $tagId = $this->tagModel->create($userId, $name, $color);

            if ($tagId) {
                $this->json(['success' => true, 'message' => 'Tag criada com sucesso!', 'id' => $tagId]);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao criar tag.'], 500);
            }
        }
    }

    /**
     * Atualizar tag
     */
    public function update($id) {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            $name = $_POST['name'] ?? '';
            $color = $_POST['color'] ?? '#3B82F6';

            // Validações
            if (empty($name)) {
                $this->json(['success' => false, 'message' => 'Nome é obrigatório.'], 400);
                return;
            }

            $success = $this->tagModel->update($id, $userId, $name, $color);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Tag atualizada com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao atualizar tag.'], 500);
            }
        }
    }

    /**
     * Deletar tag
     */
    public function delete($id) {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->getCurrentUser();
            $userId = $user['id'];

            $success = $this->tagModel->delete($id, $userId);

            if ($success) {
                $this->json(['success' => true, 'message' => 'Tag deletada com sucesso!']);
            } else {
                $this->json(['success' => false, 'message' => 'Erro ao deletar tag.'], 500);
            }
        }
    }

    /**
     * Obter todas as tags (API)
     */
    public function getAll() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        $tags = $this->tagModel->getByUser($userId);

        $this->json(['success' => true, 'tags' => $tags]);
    }
}
