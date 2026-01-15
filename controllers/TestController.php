<?php
/**
 * Controller de Teste para Debug
 */

class TestController extends Controller {
    
    /**
     * Teste do QR Code AJAX
     */
    public function qrcode() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        echo "<!DOCTYPE html>";
        echo "<html><head><meta charset='utf-8'><title>Teste QR Code</title></head><body>";
        echo "<div style='max-width: 800px; margin: 0 auto; font-family: Arial; padding: 20px;'>";
        
        echo "<h1>🧪 Teste QR Code (Dentro do Sistema)</h1>";
        echo "<p><strong>👤 Usuário:</strong> " . $user['name'] . " (ID: " . $user['id'] . ")</p>";
        
        echo "<h2>1️⃣ Teste do Endpoint getQRCode</h2>";
        echo "<button onclick='testarQR()' style='background: #10b981; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>";
        echo "🔄 Testar QR Code";
        echo "</button>";
        
        echo "<div id='resultado' style='margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; display: none;'>";
        echo "<h3>📊 Resultado:</h3>";
        echo "<div id='status'></div>";
        echo "<div id='response'></div>";
        echo "<div id='qrcode'></div>";
        echo "</div>";
        
        echo "<h2>2️⃣ Console do Navegador</h2>";
        echo "<p>Abra o <strong>Console</strong> (F12) para ver logs detalhados!</p>";
        
        echo "<script>";
        echo "console.log('🚀 Teste QR carregado dentro do sistema');";
        
        echo "async function testarQR() {";
        echo "  console.log('🔄 Testando QR Code...');";
        echo "  ";
        echo "  const resultado = document.getElementById('resultado');";
        echo "  const status = document.getElementById('status');";
        echo "  const response = document.getElementById('response');";
        echo "  const qrcode = document.getElementById('qrcode');";
        echo "  ";
        echo "  resultado.style.display = 'block';";
        echo "  status.innerHTML = '⏳ Carregando...';";
        echo "  response.innerHTML = '';";
        echo "  qrcode.innerHTML = '';";
        echo "  ";
        echo "  try {";
        echo "    const res = await fetch('" . APP_URL . "/whatsapp/getQRCode?_=' + Date.now());";
        echo "    console.log('📊 Response:', res);";
        echo "    console.log('📊 Status:', res.status);";
        echo "    ";
        echo "    status.innerHTML = `📊 Status: \${res.status} \${res.statusText}`;";
        echo "    ";
        echo "    const text = await res.text();";
        echo "    console.log('📝 Response Text:', text);";
        echo "    ";
        echo "    if (text.trim() === '') {";
        echo "      throw new Error('Resposta vazia do servidor');";
        echo "    }";
        echo "    ";
        echo "    const data = JSON.parse(text);";
        echo "    console.log('✅ JSON Data:', data);";
        echo "    ";
        echo "    response.innerHTML = `";
        echo "      <strong>Response JSON:</strong><br>";
        echo "      <pre style='background: #f5f5f5; padding: 10px; border-radius: 5px; overflow: auto; max-height: 200px;'>\${JSON.stringify(data, null, 2)}</pre>";
        echo "    `;";
        echo "    ";
        echo "    if (data.success && data.qrcode) {";
        echo "      console.log('🖼️ QR Code encontrado, tamanho:', data.qrcode.length);";
        echo "      qrcode.innerHTML = `";
        echo "        <strong>QR Code:</strong><br>";
        echo "        <img src='\${data.qrcode}' alt='QR Code' style='max-width: 300px; border: 2px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
        echo "        <br><small>Tamanho: \${data.qrcode.length} caracteres</small>";
        echo "      `;";
        echo "    } else {";
        echo "      console.log('❌ QR Code não encontrado:', data.message);";
        echo "      qrcode.innerHTML = `<strong style='color: red;'>❌ \${data.message || 'QR Code não disponível'}</strong>`;";
        echo "    }";
        echo "    ";
        echo "  } catch (error) {";
        echo "    console.error('❌ Erro:', error);";
        echo "    status.innerHTML = '❌ Erro';";
        echo "    response.innerHTML = `<strong style='color: red;'>❌ Erro:</strong> \${error.message}`;";
        echo "  }";
        echo "}";
        
        echo "</script>";
        
        echo "<hr>";
        echo "<p><a href='" . APP_URL . "/whatsapp/conectar'>← Voltar para WhatsApp</a></p>";
        
        echo "</div>";
        echo "</body></html>";
    }
}
?>
