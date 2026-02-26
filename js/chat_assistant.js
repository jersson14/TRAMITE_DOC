/**
 * Chat Assistant con IA
 * Maneja la interacción del usuario con el asistente virtual
 */

class ChatAssistant {
  constructor() {
    this.widget = document.getElementById("chat-widget");
    this.chatBody = document.getElementById("chat-body");
    this.chatInput = document.getElementById("chat-input");
    this.chatSend = document.getElementById("send-btn");
    this.chatToggle = document.getElementById("chat-toggle");
    this.chatClear = document.getElementById("chat-clear");
    this.chatHeader = document.getElementById("chat-header");

    this.isMinimized = true;
    this.isTyping = false;
    this.messageHistory = [];

    this.init();
  }

  init() {
    console.log("ChatAssistant initialized");

    // Verificar que los elementos existen
    if (!this.widget || !this.chatToggle) {
      console.error("Chat elements not found!");
      return;
    }

    // Event listeners
    if (this.chatToggle) {
      this.chatToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        this.toggleChat();
      });
    }

    if (this.chatSend) {
      this.chatSend.addEventListener("click", () => this.sendMessage());
    }

    if (this.chatClear) {
      this.chatClear.addEventListener("click", (e) => {
        e.stopPropagation();
        this.clearChat();
      });
    }

    if (this.chatInput) {
      this.chatInput.addEventListener("keypress", (e) => {
        if (e.key === "Enter") this.sendMessage();
      });
    }

    // Click en todo el widget cuando está minimizado
    this.widget.addEventListener("click", (e) => {
      if (this.isMinimized && !e.target.closest("button")) {
        this.toggleChat();
      }
    });

    // Suggestion buttons iniciales
    this.attachSuggestionListeners();

    // Make header draggable
    this.makeDraggable();
  }

  attachSuggestionListeners() {
    document.querySelectorAll(".suggestion-btn").forEach((btn) => {
      btn.addEventListener("click", (e) => {
        const suggestion = e.currentTarget.dataset.suggestion;
        if (this.chatInput) {
          this.chatInput.value = suggestion;
          this.chatInput.focus();
        }
      });
    });
  }

  toggleChat() {
    this.isMinimized = !this.isMinimized;

    if (this.isMinimized) {
      this.widget.classList.add("minimized");
      this.chatToggle.innerHTML = '<i class="fas fa-comment-dots"></i>';
    } else {
      this.widget.classList.remove("minimized");
      this.chatToggle.innerHTML = '<i class="fas fa-minus"></i>';
      this.chatInput.focus();
      this.scrollToBottom();
    }
  }

  async sendMessage() {
    const message = this.chatInput.value.trim();

    if (!message || this.isTyping) return;

    // Limpiar input
    this.chatInput.value = "";

    // Remover mensaje de bienvenida si existe
    const welcomeMsg = this.chatBody.querySelector(".welcome-message");
    if (welcomeMsg) {
      welcomeMsg.remove();
    }

    // Agregar mensaje del usuario
    this.addMessage(message, "user");

    // Mostrar indicador de escritura
    this.showTypingIndicator();

    try {
      // Enviar a backend
      const response = await this.callAPI(message);

      // Remover indicador de escritura
      this.hideTypingIndicator();

      if (response.success) {
        this.addMessage(response.message, "bot");
      } else {
        this.addErrorMessage(
          response.error || "Error al procesar la solicitud",
        );
      }
    } catch (error) {
      this.hideTypingIndicator();
      this.addErrorMessage("Error de conexión. Por favor intenta de nuevo.");
      console.error("Chat error:", error);
    }

    this.scrollToBottom();
  }

  async callAPI(message) {
    const formData = new FormData();
    formData.append("message", message);

    const response = await fetch("controller/chat/chat_controller.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) {
      throw new Error("Network response was not ok");
    }

    return await response.json();
  }

  addMessage(text, type) {
    const messageDiv = document.createElement("div");
    messageDiv.className = `chat-message ${type}`;

    const avatar = document.createElement("div");
    avatar.className = `message-avatar ${type}`;
    avatar.innerHTML =
      type === "bot"
        ? '<i class="fas fa-robot"></i>'
        : '<i class="fas fa-user"></i>';

    const content = document.createElement("div");
    content.className = "message-content";

    // Convertir markdown básico a HTML
    const formattedText = this.formatMessage(text);
    content.innerHTML = formattedText;

    const time = document.createElement("div");
    time.className = "message-time";
    time.textContent = this.getCurrentTime();

    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    content.appendChild(time);

    this.chatBody.appendChild(messageDiv);

    // Guardar en historial
    this.messageHistory.push({ text, type, time: new Date() });
  }

  formatMessage(text) {
    // Convertir saltos de línea
    text = text.replace(/\n/g, "<br>");

    // Convertir listas
    text = text.replace(/^- (.+)$/gm, "<li>$1</li>");
    text = text.replace(/(<li>.*<\/li>)/s, "<ul>$1</ul>");

    // Convertir negritas
    text = text.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");

    // Convertir cursivas
    text = text.replace(/\*(.+?)\*/g, "<em>$1</em>");

    return text;
  }

  addErrorMessage(errorText) {
    const errorDiv = document.createElement("div");
    errorDiv.className = "error-message";
    errorDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${errorText}`;
    this.chatBody.appendChild(errorDiv);
    this.scrollToBottom();
  }

  showTypingIndicator() {
    this.isTyping = true;
    this.chatSend.disabled = true;

    const typingDiv = document.createElement("div");
    typingDiv.className = "chat-message bot";
    typingDiv.id = "typingIndicator";

    const avatar = document.createElement("div");
    avatar.className = "message-avatar bot";
    avatar.innerHTML = '<i class="fas fa-robot"></i>';

    const indicator = document.createElement("div");
    indicator.className = "typing-indicator";
    indicator.innerHTML =
      '<div class="typing-dot"></div><div class="typing-dot"></div><div class="typing-dot"></div>';

    typingDiv.appendChild(avatar);
    typingDiv.appendChild(indicator);

    this.chatBody.appendChild(typingDiv);
    this.scrollToBottom();
  }

  hideTypingIndicator() {
    this.isTyping = false;
    this.chatSend.disabled = false;

    const indicator = document.getElementById("typingIndicator");
    if (indicator) {
      indicator.remove();
    }
  }

  clearChat() {
    if (confirm("¿Estás seguro de que quieres limpiar la conversación?")) {
      this.chatBody.innerHTML = `
                <div class="welcome-message">
                    <i class="fas fa-robot"></i>
                    <h4>¡Hola! Soy tu asistente virtual</h4>
                    <p>Puedo ayudarte a consultar información sobre expedientes y documentos.</p>
                    
                    <div class="welcome-suggestions">
                        <button class="suggestion-btn" data-suggestion="¿Cuántos documentos tengo pendientes?">
                            <i class="fas fa-file-alt"></i> ¿Cuántos documentos tengo pendientes?
                        </button>
                        <button class="suggestion-btn" data-suggestion="Busca el expediente ">
                            <i class="fas fa-search"></i> Buscar un expediente por número
                        </button>
                        <button class="suggestion-btn" data-suggestion="Muéstrame las estadísticas de mi área">
                            <i class="fas fa-chart-bar"></i> Ver estadísticas de mi área
                        </button>
                    </div>
                </div>
            `;

      this.attachSuggestionListeners();
      this.messageHistory = [];
    }
  }

  scrollToBottom() {
    setTimeout(() => {
      this.chatBody.scrollTop = this.chatBody.scrollHeight;
    }, 100);
  }

  getCurrentTime() {
    const now = new Date();
    return now.toLocaleTimeString("es-PE", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  makeDraggable() {
    let isDragging = false;
    let currentX;
    let currentY;
    let initialX;
    let initialY;

    this.chatHeader.addEventListener("mousedown", (e) => {
      if (e.target.closest("button")) return;

      isDragging = true;
      initialX = e.clientX - this.widget.offsetLeft;
      initialY = e.clientY - this.widget.offsetTop;
    });

    document.addEventListener("mousemove", (e) => {
      if (!isDragging) return;

      e.preventDefault();
      currentX = e.clientX - initialX;
      currentY = e.clientY - initialY;

      this.widget.style.left = currentX + "px";
      this.widget.style.top = currentY + "px";
      this.widget.style.right = "auto";
      this.widget.style.bottom = "auto";
    });

    document.addEventListener("mouseup", () => {
      isDragging = false;
    });
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  window.chatAssistant = new ChatAssistant();
});
