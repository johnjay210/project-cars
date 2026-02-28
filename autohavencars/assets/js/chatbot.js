// AutoHavenCars - Chatbot Feature

class Chatbot {
    constructor() {
        this.isOpen = false;
        this.messages = [];
        this.init();
    }

    init() {
        this.createChatbotHTML();
        this.attachEventListeners();
        this.addWelcomeMessage();
    }

    createChatbotHTML() {
        const chatbotHTML = `
            <div id="chatbot-container" class="chatbot-container">
                <div id="chatbot-window" class="chatbot-window">
                    <div class="chatbot-header">
                        <div class="chatbot-header-content">
                            <i class="fas fa-robot"></i>
                            <span>AutoHavenCars Assistant</span>
                        </div>
                        <button id="chatbot-close" class="chatbot-close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div id="chatbot-messages" class="chatbot-messages"></div>
                    <div class="chatbot-input-container">
                        <input 
                            type="text" 
                            id="chatbot-input" 
                            class="chatbot-input" 
                            placeholder="Type your message..."
                            autocomplete="off"
                        >
                        <button id="chatbot-send" class="chatbot-send">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
                <button id="chatbot-toggle" class="chatbot-toggle">
                    <i class="fas fa-comments"></i>
                </button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    attachEventListeners() {
        const toggle = document.getElementById('chatbot-toggle');
        const close = document.getElementById('chatbot-close');
        const send = document.getElementById('chatbot-send');
        const input = document.getElementById('chatbot-input');

        toggle.addEventListener('click', () => this.toggleChat());
        close.addEventListener('click', () => this.toggleChat());
        send.addEventListener('click', () => this.sendMessage());
        
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                this.sendMessage();
            }
        });

        // Close on outside click
        document.getElementById('chatbot-window').addEventListener('click', (e) => {
            e.stopPropagation();
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        const window = document.getElementById('chatbot-window');
        const toggle = document.getElementById('chatbot-toggle');
        
        if (this.isOpen) {
            window.classList.add('chatbot-open');
            toggle.style.display = 'none';
            document.getElementById('chatbot-input').focus();
        } else {
            window.classList.remove('chatbot-open');
            toggle.style.display = 'flex';
        }
    }

    addWelcomeMessage() {
        const welcomeMessage = "Hello! I'm your AutoHavenCars assistant. How can I help you today? You can ask me about:\n• How to browse cars\n• How to sell your car\n• Account registration\n• Contact information\n• General questions";
        this.addMessage(welcomeMessage, 'bot');
    }

    addMessage(text, sender = 'user') {
        const messagesContainer = document.getElementById('chatbot-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chatbot-message chatbot-message-${sender}`;
        
        const icon = sender === 'bot' ? '<i class="fas fa-robot"></i>' : '<i class="fas fa-user"></i>';
        messageDiv.innerHTML = `
            <div class="chatbot-message-content">
                <div class="chatbot-message-icon">${icon}</div>
                <div class="chatbot-message-text">${this.formatMessage(text)}</div>
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
        
        this.messages.push({ text, sender });
    }

    formatMessage(text) {
        // Convert line breaks to <br>
        return text.replace(/\n/g, '<br>');
    }

    sendMessage() {
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        
        if (!message) return;
        
        this.addMessage(message, 'user');
        input.value = '';
        
        // Simulate typing delay
        setTimeout(() => {
            const response = this.getResponse(message);
            this.addMessage(response, 'bot');
        }, 500);
    }

    getResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        // Greetings
        if (lowerMessage.match(/hi|hello|hey|greetings/)) {
            return "Hello! Welcome to AutoHavenCars. How can I assist you today?";
        }
        
        // Browse cars
        if (lowerMessage.match(/browse|search|find|look for|view cars|see cars|available cars/)) {
            return "You can browse cars by:\n1. Clicking 'Browse Cars' in the navigation menu\n2. Using the search filters on the homepage\n3. Filtering by make, price range, and year\n\nWould you like to know more about searching for specific cars?";
        }
        
        // Sell car
        if (lowerMessage.match(/sell|list|post|add car|sell my car|list my car/)) {
            return "To sell your car:\n1. Click 'Sell Your Car' in the navigation\n2. Fill out the car details form\n3. Upload a photo of your car\n4. Submit the listing\n\nYou need to be logged in to post a car. Would you like help with registration?";
        }
        
        // Registration
        if (lowerMessage.match(/register|sign up|create account|new account|signup/)) {
            return "To create an account:\n1. Click 'Sign Up' in the navigation\n2. Fill in your username, email, and password\n3. Optionally add your phone number\n4. Click 'Register'\n\nAfter registration, you can log in and start listing cars!";
        }
        
        // Login
        if (lowerMessage.match(/login|sign in|log in/)) {
            return "To log in:\n1. Click 'Login' in the navigation\n2. Enter your email and password\n3. Click 'Login'\n\nIf you don't have an account, you can register first.";
        }
        
        // Contact
        if (lowerMessage.match(/contact|email|phone|support|help|reach|get in touch/)) {
            return "You can contact us at:\n📧 Email: info@autohavencars.com\n📞 Phone: +1 (555) 123-4567\n\nYou can also find this information in the footer of any page.";
        }
        
        // Price
        if (lowerMessage.match(/price|cost|how much|pricing|expensive|cheap/)) {
            return "Car prices vary based on make, model, year, and condition. You can:\n1. Browse cars to see current listings\n2. Use price filters to find cars in your budget\n3. Contact sellers directly for more information\n\nIs there a specific price range you're looking for?";
        }
        
        // Account
        if (lowerMessage.match(/account|profile|my account|my profile/)) {
            return "Once logged in, you can:\n1. View 'My Cars' to see your listings\n2. Edit or manage your posted cars\n3. Update your account information\n\nNeed help logging in or registering?";
        }
        
        // Payment
        if (lowerMessage.match(/payment|pay|buy|purchase|payment method|how to pay/)) {
            return "AutoHavenCars is a marketplace platform. We connect buyers and sellers. Payment arrangements are made directly between you and the seller. We recommend:\n1. Meeting in person to inspect the car\n2. Using secure payment methods\n3. Getting all paperwork in order\n\nWould you like tips on safe car buying?";
        }
        
        // Safety
        if (lowerMessage.match(/safe|safety|secure|trust|reliable|scam/)) {
            return "Safety tips for buying/selling:\n1. Always meet in a public place\n2. Inspect the car thoroughly\n3. Verify all documents\n4. Use secure payment methods\n5. Trust your instincts\n\nWe're here to help make your transaction safe and smooth!";
        }
        
        // Features
        if (lowerMessage.match(/features|what can|what does|capabilities|options/)) {
            return "AutoHavenCars offers:\n✅ Browse and search cars\n✅ Advanced filtering (make, price, year)\n✅ Detailed car listings\n✅ User accounts and profiles\n✅ Easy car posting\n✅ Image uploads\n✅ Contact sellers\n\nWhat would you like to know more about?";
        }
        
        // Thank you
        if (lowerMessage.match(/thank|thanks|appreciate|grateful/)) {
            return "You're welcome! I'm here to help anytime. Feel free to ask if you have more questions!";
        }
        
        // Goodbye
        if (lowerMessage.match(/bye|goodbye|see you|farewell|exit/)) {
            return "Goodbye! Thanks for visiting AutoHavenCars. Have a great day and happy car hunting! 🚗";
        }
        
        // Default response
        return "I understand you're asking about: \"" + message + "\"\n\nI can help you with:\n• Browsing and searching for cars\n• How to sell your car\n• Account registration and login\n• Contact information\n• General questions about the platform\n\nCould you rephrase your question or ask about one of these topics?";
    }
}

// Initialize chatbot when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    new Chatbot();
});

