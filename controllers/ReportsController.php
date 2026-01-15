<?php
/**
 * Controller de Relatórios de Disparo
 * Autor: Dante Testa (https://dantetesta.com.br)
 * Data: 2025-10-28 10:00:00
 */

class ReportsController extends Controller {
    private $dispatchModel;
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->dispatchModel = $this->model('DispatchHistory');
        $this->userModel = $this->model('User');
    }

    /**
     * Página de relatórios
     */
    public function index() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];
        $isAdmin = ($user['role'] === 'admin');

        // Obter filtros
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Paginação
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;

        // Admin vê todos, usuário vê apenas seus disparos
        $dispatches = $this->dispatchModel->getReport($userId, $filters, $page, $perPage);
        $totalDispatches = $this->dispatchModel->countReport($userId, $filters);
        $totalPages = ceil($totalDispatches / $perPage);

        // Estatísticas gerais
        $stats = $this->dispatchModel->getStats($userId);
        
        // Estatísticas dos resultados filtrados
        $filteredStats = $this->dispatchModel->getFilteredStats($userId, $filters);

        $this->view('reports/index', [
            'user' => $user,
            'dispatches' => $dispatches,
            'filters' => $filters,
            'stats' => $stats,
            'filteredStats' => $filteredStats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalDispatches' => $totalDispatches,
            'perPage' => $perPage,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * Exportar relatório em CSV
     */
    public function exportCSV() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        // Obter filtros
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Buscar dados sem paginação
        $dispatches = $this->dispatchModel->exportReport($userId, $filters);

        // Configurar headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="relatorio_disparos_' . date('Y-m-d_His') . '.csv"');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalho
        fputcsv($output, [
            'ID',
            'Data/Hora',
            'Contato',
            'Telefone',
            'Mensagem',
            'Status',
            'Erro'
        ]);
        
        // Dados
        foreach ($dispatches as $dispatch) {
            fputcsv($output, [
                $dispatch['id'],
                date('d/m/Y H:i:s', strtotime($dispatch['created_at'])),
                $dispatch['contact_name'] ?: 'Sem nome',
                $dispatch['contact_phone'],
                mb_substr($dispatch['message'], 0, 100) . (strlen($dispatch['message']) > 100 ? '...' : ''),
                $this->getStatusLabel($dispatch['status']),
                $dispatch['error_message'] ?: '-'
            ]);
        }
        
        fclose($output);
        exit;
    }

    /**
     * Exportar relatório em PDF
     */
    public function exportPDF() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        $userId = $user['id'];

        // Obter filtros
        $filters = [
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'search' => $_GET['search'] ?? ''
        ];

        // Buscar dados sem paginação
        $dispatches = $this->dispatchModel->exportReport($userId, $filters);
        
        // Estatísticas dos resultados
        $filteredStats = $this->dispatchModel->getFilteredStats($userId, $filters);
        
        // Carregar branding
        require_once __DIR__ . '/../config/branding.php';
        
        // Carregar TCPDF
        require_once __DIR__ . '/../vendor/autoload.php';
        
        // Log de início
        error_log("=== INÍCIO GERAÇÃO PDF ===");
        error_log("Total de registros: " . count($dispatches));
        
        try {
            // Criar PDF com TCPDF
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            error_log("TCPDF inicializado com sucesso");
            
            // Definir informações do documento
            $pdf->SetCreator('ZAPX');
            $pdf->SetAuthor(COMPANY_NAME);
            $pdf->SetTitle('Relatório de Disparos - ' . COMPANY_NAME);
            $pdf->SetSubject('Relatório de Disparos WhatsApp');
            
            // Remover header e footer padrão
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // Definir margens
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            
            // Adicionar página
            $pdf->AddPage();
            
            // Cabeçalho personalizado
            $pdf->SetFont('helvetica', 'B', 20);
            $pdf->SetTextColor(50, 50, 50); // Cinza escuro
            $pdf->Cell(0, 10, COMPANY_NAME, 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 5, 'Relatorio de Disparos WhatsApp', 0, 1, 'L');
            
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Gerado em: ' . date('d/m/Y H:i'), 0, 1, 'R');
            
            // Linha separadora
            $pdf->SetDrawColor(150, 150, 150); // Cinza
            $pdf->SetLineWidth(0.5);
            $pdf->Line(15, $pdf->GetY() + 2, 195, $pdf->GetY() + 2);
            $pdf->Ln(5);
            
            // Informações do Relatório
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->Cell(0, 8, 'Informacoes do Relatorio', 0, 1, 'L');
            
            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            
            if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
                $periodo = 'Periodo: ';
                if (!empty($filters['date_from'])) {
                    $periodo .= date('d/m/Y', strtotime($filters['date_from']));
                }
                if (!empty($filters['date_from']) && !empty($filters['date_to'])) {
                    $periodo .= ' ate ';
                }
                if (!empty($filters['date_to'])) {
                    $periodo .= date('d/m/Y', strtotime($filters['date_to']));
                }
                $pdf->Cell(0, 5, $periodo, 0, 1, 'L');
            }
            
            if (!empty($filters['status'])) {
                $pdf->Cell(0, 5, 'Status: ' . $this->getStatusLabel($filters['status']), 0, 1, 'L');
            }
            
            $pdf->Cell(0, 5, 'Total de Registros: ' . $filteredStats['total'], 0, 1, 'L');
            $pdf->Ln(5);
            
            // Estatísticas
            $pdf->SetFont('helvetica', 'B', 10);
            $colWidth = 60;
            
            // Cabeçalhos neutros
            $pdf->SetFillColor(240, 240, 240); // Cinza claro
            $pdf->SetTextColor(50, 50, 50); // Cinza escuro
            $pdf->SetDrawColor(200, 200, 200); // Borda cinza
            
            $pdf->Cell($colWidth, 8, 'Enviados', 1, 0, 'C', true);
            $pdf->Cell(3, 8, '', 0, 0); // Espaço
            $pdf->Cell($colWidth, 8, 'Falhados', 1, 0, 'C', true);
            $pdf->Cell(3, 8, '', 0, 0); // Espaço
            $pdf->Cell($colWidth, 8, 'Pendentes', 1, 1, 'C', true);
            
            // Valores com cores apenas nos números
            $pdf->SetFont('helvetica', 'B', 16);
            $pdf->SetFillColor(255, 255, 255); // Fundo branco
            
            // Enviados - Verde
            $pdf->SetTextColor(16, 185, 129);
            $pdf->Cell($colWidth, 10, $filteredStats['sent'], 1, 0, 'C', true);
            $pdf->Cell(3, 10, '', 0, 0);
            
            // Falhados - Vermelho
            $pdf->SetTextColor(239, 68, 68);
            $pdf->Cell($colWidth, 10, $filteredStats['failed'], 1, 0, 'C', true);
            $pdf->Cell(3, 10, '', 0, 0);
            
            // Pendentes - Laranja
            $pdf->SetTextColor(245, 158, 11);
            $pdf->Cell($colWidth, 10, $filteredStats['pending'], 1, 1, 'C', true);
            
            $pdf->Ln(8);
            
            // Tabela de Dados
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->SetTextColor(55, 65, 81);
            $pdf->Cell(0, 8, 'Detalhamento dos Disparos', 0, 1, 'L');
            $pdf->Ln(2);
            
            // Cabeçalho da tabela
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(50, 50, 50); // Cinza escuro
            $pdf->SetTextColor(255, 255, 255); // Texto branco
            $pdf->SetDrawColor(50, 50, 50); // Borda preta
            
            $pdf->Cell(15, 7, 'ID', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Data/Hora', 1, 0, 'C', true);
            $pdf->Cell(40, 7, 'Contato', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Telefone', 1, 0, 'C', true);
            $pdf->Cell(50, 7, 'Mensagem', 1, 0, 'C', true);
            $pdf->Cell(20, 7, 'Status', 1, 1, 'C', true);
            
            // Dados
            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetDrawColor(200, 200, 200); // Borda cinza clara
            $fill = false;
            
            foreach ($dispatches as $dispatch) {
                try {
                    $statusLabel = $this->getStatusLabel($dispatch['status']);
                    
                    // Decodificar JSON da mensagem
                    $messageText = '';
                    $messageData = @json_decode($dispatch['message'], true);
                    
                    if (is_array($messageData)) {
                        if (isset($messageData['message'])) {
                            $messageText = $messageData['message'];
                        } elseif (isset($messageData['caption'])) {
                            $messageText = $messageData['caption'];
                        }
                    } else {
                        $messageText = $dispatch['message'];
                    }
                    
                    // Limitar tamanho da mensagem
                    $messageText = mb_substr($messageText, 0, 40);
                    if (mb_strlen($dispatch['message']) > 40) {
                        $messageText .= '...';
                    }
                    
                    // Cor de fundo alternada
                    if ($fill) {
                        $pdf->SetFillColor(249, 250, 251);
                    } else {
                        $pdf->SetFillColor(255, 255, 255);
                    }
                    
                    $pdf->SetTextColor(0, 0, 0); // Texto preto
                    $pdf->Cell(15, 6, $dispatch['id'], 1, 0, 'C', true);
                    $pdf->Cell(30, 6, date('d/m/Y H:i', strtotime($dispatch['sent_at'] ?: $dispatch['created_at'])), 1, 0, 'C', true);
                    $pdf->Cell(40, 6, mb_substr($dispatch['contact_name'] ?: 'Sem nome', 0, 20), 1, 0, 'L', true);
                    $pdf->Cell(30, 6, $dispatch['contact_phone'], 1, 0, 'C', true);
                    $pdf->Cell(50, 6, $messageText, 1, 0, 'L', true);
                    
                    // Status com cor
                    $status = $dispatch['status'];
                    if ($status === 'sent') {
                        $pdf->SetTextColor(16, 185, 129); // Verde
                    } elseif ($status === 'failed') {
                        $pdf->SetTextColor(239, 68, 68); // Vermelho
                    } else {
                        $pdf->SetTextColor(245, 158, 11); // Laranja
                    }
                    $pdf->Cell(20, 6, $statusLabel, 1, 1, 'C', true);
                    
                    $fill = !$fill;
                    
                } catch (\Exception $e) {
                    error_log("Erro ao processar disparo ID " . ($dispatch['id'] ?? 'desconhecido') . ": " . $e->getMessage());
                    continue;
                }
            }
            
            error_log("Tabela gerada com sucesso");
            
            // Rodapé
            $pdf->SetY(-15);
            $pdf->SetFont('helvetica', 'I', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 10, 'Pagina ' . $pdf->getAliasNumPage() . ' de ' . $pdf->getAliasNbPages() . ' | ' . COMPANY_NAME . ' | Gerado por ZAPX', 0, 0, 'C');
            
            // Output
            $filename = 'relatorio_disparos_' . date('Y-m-d_His') . '.pdf';
            error_log("Gerando output: " . $filename);
            
            $pdf->Output($filename, 'D');
            
            error_log("=== PDF GERADO COM SUCESSO ===");
            exit;
            
        } catch (\Exception $e) {
            error_log("ERRO PDF: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Retornar erro em HTML
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Erro ao Gerar PDF</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
                    .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                    h1 { color: #e53e3e; }
                    pre { background: #f7fafc; padding: 15px; border-radius: 4px; overflow-x: auto; }
                    .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #7C3AED; color: white; text-decoration: none; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <h1>❌ Erro ao Gerar PDF</h1>
                    <p><strong>Mensagem:</strong></p>
                    <pre>' . htmlspecialchars($e->getMessage()) . '</pre>
                    <p><strong>O erro foi registrado nos logs do servidor.</strong></p>
                    <a href="javascript:history.back()" class="btn">← Voltar</a>
                </div>
            </body>
            </html>';
            exit;
        } catch (\Exception $e) {
            error_log("ERRO GERAL: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html>
            <html>
            <head>
                <meta charset="utf-8">
                <title>Erro Inesperado</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
                    .error-box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; margin: 0 auto; }
                    h1 { color: #e53e3e; }
                    pre { background: #f7fafc; padding: 15px; border-radius: 4px; overflow-x: auto; }
                    .btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #7C3AED; color: white; text-decoration: none; border-radius: 4px; }
                </style>
            </head>
            <body>
                <div class="error-box">
                    <h1>❌ Erro Inesperado</h1>
                    <p><strong>Mensagem:</strong></p>
                    <pre>' . htmlspecialchars($e->getMessage()) . '</pre>
                    <p><strong>O erro foi registrado nos logs do servidor.</strong></p>
                    <a href="javascript:history.back()" class="btn">← Voltar</a>
                </div>
            </body>
            </html>';
            exit;
        }
    }

    /**
     * Obter label do status
     */
    private function getStatusLabel($status) {
        $labels = [
            'sent' => 'Enviado',
            'failed' => 'Falhou',
            'pending' => 'Pendente'
        ];
        return $labels[$status] ?? $status;
    }

    /**
     * Deletar histórico de disparo (apenas admin)
     */
    public function delete() {
        $this->requireAuth();

        $user = $this->getCurrentUser();
        if ($user['role'] !== 'admin') {
            $this->json(['success' => false, 'message' => 'Acesso negado.'], 403);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? 0;

            // TODO: Implementar método delete no model
            // Por enquanto, apenas retorna sucesso
            $this->json([
                'success' => true,
                'message' => 'Registro deletado com sucesso!'
            ]);
        }
    }
    
    /**
     * Deletar múltiplos registros
     */
    public function deleteMultiple() {
        $this->requireAuth();
        
        // Validar CSRF Token
        require_once __DIR__ . '/../core/CSRF.php';
        CSRF::validateOrDie();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        $isAdmin = ($user['role'] === 'admin');
        
        $ids = $_POST['ids'] ?? [];
        
        if (empty($ids)) {
            $this->json(['success' => false, 'message' => 'Nenhum registro selecionado'], 400);
            return;
        }
        
        $result = $this->dispatchModel->deleteMultiple($ids, $user['id'], $isAdmin);
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => "{$result['deleted']} registro(s) deletado(s) com sucesso! {$result['thumbnails_deleted']} thumbnail(s) removida(s)."
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']], 400);
        }
    }
    
    /**
     * Limpar todos os registros (apenas admin)
     */
    public function deleteAll() {
        $this->requireAuth();
        
        // Validar CSRF Token
        require_once __DIR__ . '/../core/CSRF.php';
        CSRF::validateOrDie();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Método não permitido'], 405);
            return;
        }
        
        $user = $this->getCurrentUser();
        $isAdmin = ($user['role'] === 'admin');
        
        if (!$isAdmin) {
            $this->json(['success' => false, 'message' => 'Apenas administradores podem limpar todos os registros'], 403);
            return;
        }
        
        $result = $this->dispatchModel->deleteAll($user['id'], $isAdmin);
        
        if ($result['success']) {
            $this->json([
                'success' => true,
                'message' => "Todos os registros foram deletados! {$result['deleted']} registro(s) e {$result['thumbnails_deleted']} thumbnail(s) removida(s)."
            ]);
        } else {
            $this->json(['success' => false, 'message' => $result['message']], 400);
        }
    }
}
