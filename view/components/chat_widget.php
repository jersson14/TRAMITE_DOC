<!-- Chat Widget Component -->
<div id="chat-widget" class="minimized">
    <div class="chat-header" id="chat-header">
        <h3>
            <i class="fas fa-robot"></i>
            Asistente IA
        </h3>
        <div class="chat-header-buttons">
            <button id="chat-clear" title="Limpiar conversación">
                <i class="fas fa-eraser"></i>
            </button>
            <button id="chat-toggle" title="Minimizar/Expandir">
                <i class="fas fa-minus"></i>
            </button>
        </div>
    </div>
    
    <div class="chat-body" id="chat-body">
        <div class="welcome-message">
            <h4>¡Hola! Soy tu asistente virtual</h4>
            <p>Puedo ayudarte a consultar información sobre expedientes y documentos.</p>
            
            <div class="suggestion-buttons">
                <button class="suggestion-btn" data-suggestion="¿Cuántos documentos tengo pendientes?">
                    📋 ¿Cuántos documentos tengo pendientes?
                </button>
                <button class="suggestion-btn" data-suggestion="Busca el expediente ">
                    🔍 Buscar expediente
                </button>
                <button class="suggestion-btn" data-suggestion="Muéstrame las estadísticas de mi área">
                    📊 Ver estadísticas
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
// Inicializar el chat cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing chat...');
    
    // Verificar que el widget existe
    const widget = document.getElementById('chat-widget');
    console.log('Widget found:', widget);
    
    if (widget) {
        // Inicializar ChatAssistant
        window.chatAssistant = new ChatAssistant();
    } else {
        console.error('Chat widget not found in DOM!');
    }
});
</script>
