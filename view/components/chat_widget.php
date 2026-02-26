<!-- Chat Widget Component -->

<!-- Botón flotante separado del widget (evita el problema de "clic al lado") -->
<button id="chat-fab" title="Abrir Asistente IA" aria-label="Asistente IA">
    <i class="fas fa-robot"></i>
    <span class="chat-fab-badge" id="chat-badge" style="display:none;">1</span>
</button>

<!-- Panel del chat -->
<div id="chat-widget" class="minimized">
    <div class="chat-header" id="chat-header">
        <div class="chat-header-info">
            <div class="chat-avatar-dot">
                <i class="fas fa-robot"></i>
            </div>
            <div>
                <h3>Asistente IA</h3>
                <span class="chat-status">● En línea</span>
            </div>
        </div>
        <div class="chat-header-buttons">
            <button id="chat-clear" title="Limpiar conversación">
                <i class="fas fa-eraser"></i>
            </button>
            <button id="chat-toggle" title="Minimizar">
                <i class="fas fa-chevron-down"></i>
            </button>
        </div>
    </div>
    
    <div class="chat-body" id="chat-body">
        <div class="welcome-message">
            <div class="welcome-icon">🤖</div>
            <h4>¡Hola! Soy tu asistente</h4>
            <p>Consulta expedientes, pendientes y estadísticas de tu área al instante.</p>
            
            <div class="suggestion-buttons">
                <button class="suggestion-btn" data-suggestion="¿Cuántos documentos tengo pendientes?">
                    📋 Ver pendientes
                </button>
                <button class="suggestion-btn" data-suggestion="Muéstrame las estadísticas de mi área">
                    📊 Estadísticas
                </button>
                <button class="suggestion-btn" data-suggestion="Busca el expediente ">
                    🔍 Buscar expediente
                </button>
            </div>
        </div>
    </div>
    
    <div class="chat-footer">
        <div class="chat-input-container">
            <input 
                type="text" 
                id="chat-input" 
                placeholder="Escribe tu pregunta..."
                autocomplete="off"
            >
            <button id="send-btn" title="Enviar mensaje">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('chat-widget')) {
        window.chatAssistant = new ChatAssistant();
    }
});
</script>
