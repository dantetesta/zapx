<!-- Toast Global (canto inferior direito) -->
<div
    id="globalToast"
    class="hidden fixed bottom-5 right-5 max-w-md w-full sm:w-auto p-4 rounded-lg shadow-2xl pointer-events-auto"
    style="z-index: 2147483647; min-width: 300px;"
>
    <div class="flex items-center">
        <i id="globalToastIcon" class="mr-3 text-xl"></i>
        <p id="globalToastMessage" class="font-medium flex-1"></p>
        <button onclick="closeGlobalToast()" class="ml-3 text-gray-400 hover:text-gray-600">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
// Função global para mostrar toast
window.showGlobalToast = function(message, type = 'info') {
    const toast = document.getElementById('globalToast');
    const icon = document.getElementById('globalToastIcon');
    const messageEl = document.getElementById('globalToastMessage');
    const baseClasses = 'fixed bottom-5 right-5 max-w-md w-full sm:w-auto mx-4 sm:mx-0 p-4 rounded-lg shadow-2xl pointer-events-auto flex items-start gap-3';
    
    // Limpar classes anteriores
    toast.className = baseClasses;
    
    // Garantir posicionamento acima de qualquer header/menu
    toast.style.position = 'fixed';
    toast.style.top = 'auto';
    toast.style.left = 'auto';
    toast.style.transform = 'none';
    toast.style.right = '20px';
    toast.style.bottom = '20px';
    toast.style.zIndex = '2147483647';
    
    // Adicionar classes baseadas no tipo
    switch(type) {
        case 'success':
            toast.classList.add('bg-green-50', 'border-l-4', 'border-green-500', 'text-green-700');
            icon.className = 'fas fa-check-circle mr-3 text-xl text-green-500';
            break;
        case 'error':
            toast.classList.add('bg-red-50', 'border-l-4', 'border-red-500', 'text-red-700');
            icon.className = 'fas fa-exclamation-circle mr-3 text-xl text-red-500';
            break;
        case 'warning':
            toast.classList.add('bg-yellow-50', 'border-l-4', 'border-yellow-500', 'text-yellow-700');
            icon.className = 'fas fa-exclamation-triangle mr-3 text-xl text-yellow-500';
            break;
        default:
            toast.classList.add('bg-blue-50', 'border-l-4', 'border-blue-500', 'text-blue-700');
            icon.className = 'fas fa-info-circle mr-3 text-xl text-blue-500';
    }
    
    messageEl.textContent = message;
    toast.classList.remove('hidden');
    
    // Auto-hide após 5 segundos
    setTimeout(() => {
        closeGlobalToast();
    }, 5000);
};

window.closeGlobalToast = function() {
    const toast = document.getElementById('globalToast');
    toast.classList.add('hidden');
};
</script>
