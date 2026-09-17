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
            <p>Pregúntame en tus palabras: consulto los trámites, plazos, movimientos y acuses del sistema.</p>

            <div class="suggestion-buttons">
                <button class="suggestion-btn" data-suggestion="¿Cuántos trámites tengo pendientes y de qué áreas vinieron?">
                    📋 Mis pendientes
                </button>
                <button class="suggestion-btn" data-suggestion="¿Qué trámites están vencidos y con cuántos días de atraso?">
                    ⏰ Vencidos
                </button>
                <button class="suggestion-btn" data-suggestion="Lista los trámites enviados que todavía no tienen acuse de recepción">
                    ✅ Sin acuse
                </button>
                <button class="suggestion-btn" data-suggestion="¿Cuántos trámites se registraron cada mes de este año?">
                    📊 Por mes
                </button>
                <button class="suggestion-btn" data-suggestion="Dame el detalle y el recorrido del expediente ">
                    🔍 Un expediente
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
