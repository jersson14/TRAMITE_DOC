/**
 * Chat Assistant - UX Fixed Version
 */

class ChatAssistant {
  constructor() {
    this.fab = document.getElementById("chat-fab");
    this.widget = document.getElementById("chat-widget");
    this.chatBody = document.getElementById("chat-body");
    this.chatInput = document.getElementById("chat-input");
    this.chatSend = document.getElementById("send-btn");
    this.chatToggle = document.getElementById("chat-toggle");
    this.chatClear = document.getElementById("chat-clear");
    this.chatHeader = document.getElementById("chat-header");

    this.isOpen = false;
    this.isTyping = false;
    this.messageHistory = [];

    this.init();
  }

  init() {
    if (!this.widget || !this.fab) {
      console.error("Chat elements not found!");
      return;
    }

    // El botón FAB es el único que abre el chat
    this.fab.addEventListener("click", (e) => {
      e.stopPropagation();
      this.openChat();
    });

    // El botón de la cabecera cierra el chat
    if (this.chatToggle) {
      this.chatToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        this.closeChat();
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

    // Cerrar el chat si se hace clic fuera
    document.addEventListener("click", (e) => {
      if (
        this.isOpen &&
        !this.widget.contains(e.target) &&
        e.target !== this.fab
      ) {
        this.closeChat();
      }
    });

    // Suggestion buttons
    this.attachSuggestionListeners();

    // Arrastrable solo por la cabecera
    this.makeDraggable();
  }

  openChat() {
    this.isOpen = true;
    this.widget.classList.remove("minimized");
    this.fab.classList.add("hidden");
    setTimeout(() => this.chatInput && this.chatInput.focus(), 300);
    this.scrollToBottom();
  }

  closeChat() {
    this.isOpen = false;
    this.widget.classList.add("minimized");
    this.fab.classList.remove("hidden");
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

  async sendMessage() {
    const message = this.chatInput.value.trim();
    if (!message || this.isTyping) return;

    this.chatInput.value = "";

    const welcomeMsg = this.chatBody.querySelector(".welcome-message");
    if (welcomeMsg) welcomeMsg.remove();

    this.addMessage(message, "user");
    this.showTypingIndicator();

    try {
      const response = await this.callAPI(message);
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

    const response = await fetch("../controller/chat/chat_controller.php", {
      method: "POST",
      body: formData,
    });

    if (!response.ok) throw new Error("Network response was not ok");
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
    content.innerHTML = this.formatMessage(text);

    const time = document.createElement("div");
    time.className = "message-time";
    time.textContent = this.getCurrentTime();

    messageDiv.appendChild(avatar);
    messageDiv.appendChild(content);
    content.appendChild(time);
    this.chatBody.appendChild(messageDiv);

    this.messageHistory.push({ text, type, time: new Date() });
  }

  formatMessage(text) {
    text = text.replace(/\n/g, "<br>");
    text = text.replace(/^- (.+)$/gm, "<li>$1</li>");
    text = text.replace(/(<li>.*<\/li>)/s, "<ul>$1</ul>");
    text = text.replace(/\*\*(.+?)\*\*/g, "<strong>$1</strong>");
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
    if (this.chatSend) this.chatSend.disabled = true;

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
    if (this.chatSend) this.chatSend.disabled = false;
    const indicator = document.getElementById("typingIndicator");
    if (indicator) indicator.remove();
  }

  clearChat() {
    if (confirm("¿Limpiar la conversación?")) {
      this.chatBody.innerHTML = `
        <div class="welcome-message">
          <div class="welcome-icon">🤖</div>
          <h4>¡Hola! Soy tu asistente</h4>
          <p>Consulta expedientes, pendientes y estadísticas.</p>
          <div class="suggestion-buttons">
            <button class="suggestion-btn" data-suggestion="¿Cuántos documentos tengo pendientes?">📋 Ver pendientes</button>
            <button class="suggestion-btn" data-suggestion="Muéstrame las estadísticas de mi área">📊 Estadísticas</button>
            <button class="suggestion-btn" data-suggestion="Busca el expediente ">🔍 Buscar expediente</button>
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
    return new Date().toLocaleTimeString("es-PE", {
      hour: "2-digit",
      minute: "2-digit",
    });
  }

  makeDraggable() {
    if (!this.chatHeader) return;
    let isDragging = false,
      startX,
      startY,
      startLeft,
      startTop;

    this.chatHeader.addEventListener("mousedown", (e) => {
      if (e.target.closest("button")) return;
      isDragging = true;
      const rect = this.widget.getBoundingClientRect();
      startX = e.clientX;
      startY = e.clientY;
      startLeft = rect.left;
      startTop = rect.top;
    });

    document.addEventListener("mousemove", (e) => {
      if (!isDragging) return;
      e.preventDefault();
      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
      this.widget.style.left = startLeft + dx + "px";
      this.widget.style.top = startTop + dy + "px";
      this.widget.style.right = "auto";
      this.widget.style.bottom = "auto";
    });

    document.addEventListener("mouseup", () => {
      isDragging = false;
    });
  }
}
