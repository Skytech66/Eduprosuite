<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPro School Assistant</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .pulse { animation: pulse 1.5s infinite; }
        
        .chat-enter { animation: fadeIn 0.3s ease-out; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        
        .typing-indicator span {
            animation: bounce 1.4s infinite ease-in-out;
        }
        
        @keyframes bounce {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        .message-icon { 
            width: 40px; height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 5px;
        }
        
        .option-btn {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .option-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        /* Better scrollbar for chat */
        #chatMessages::-webkit-scrollbar {
            width: 6px;
        }
        
        #chatMessages::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        #chatMessages::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-gray-50">

<?php
// Strict email handler with security headers
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    // Clean output buffer and set JSON headers
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    
    try {
        // Load PHPMailer
        require 'vendor/autoload.php';
        
        // Sanitize inputs with validation
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $message = htmlspecialchars($_POST['message'], ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($_POST['subject'], ENT_QUOTES, 'UTF-8');
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address");
        }

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        // SMTP Configuration with timeout settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'dadzieernestbizz@gmail.com';
        $mail->Password = 'myizuwngvcmeurwp';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';
        $mail->Timeout = 30;

        // Recipients with proper headers
        $mail->setFrom('dadzieernestbizz@gmail.com', 'EduPro Support', 0);
        $mail->addAddress($email);
        $mail->addReplyTo('support@edupro.com', 'Support Team');
        $mail->addBCC('support@edupro.com'); // Always keep a copy

        // Secure email content
        $mail->isHTML(false);
        $mail->Subject = "[Support Ticket] " . $subject;
        $mail->Body = "Request Type: $subject\n\n$message\n\n---\nEduPro School Management System";

        $mail->send();
        echo json_encode(['success' => true, 'message' => 'Email sent successfully!']);
        exit;
        
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'error' => 'Message could not be sent. Please try again later.'
        ]);
        exit;
    }
}
?>


<!-- ======= CHAT INTERFACE ======= -->
<div id="aiIcon"
     class="fixed bottom-8 right-8 w-20 h-20 cursor-pointer"
     onclick="toggleChat()">
  <svg viewBox="0 0 64 64" class="w-full h-full">
    <path d="M2,32c0-16.6,14.3-30,32-30s32,13.4,32,30s-14.3,30-32,30c-3.8,0-7.4-0.6-10.9-1.8L2,62l6.2-14.2C4.5,44.1,2,38.3,2,32z"
          fill="black"/>
    <image href="logo.png" x="1" y="15" height="40" width="65" />
  </svg>
</div>


<div id="chatContainer" class="fixed bottom-28 right-8 w-96 bg-white rounded-xl shadow-2xl flex flex-col border border-gray-200"
     style="height: 65vh; max-height: 600px; display: none; z-index: 9999;">
     
    <!-- Header with status indicator -->
    <div class="bg-indigo-600 text-white px-4 py-3 rounded-t-xl flex justify-between items-center">
        <div class="flex items-center">
            <div class="relative">
                <img src="logo.png" alt="EduPro Assistant" class="message-icon">
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-400 rounded-full border-2 border-white"></span>
            </div>
            <div>
                <h3 class="font-semibold text-lg">EduPro Assistant</h3>
                <p class="text-xs text-indigo-100">Online now</p>
            </div>
        </div>
        <div class="flex space-x-2">
            <button onclick="minimizeChat()" class="text-indigo-200 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1z" clip-rule="evenodd" />
                </svg>
            </button>
            <button onclick="toggleChat()" class="text-indigo-200 hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Message area with date indicator -->
    <div id="chatMessages" class="flex-1 overflow-y-auto p-4 space-y-4">
        <div class="text-center py-2">
            <span class="inline-block px-2 py-1 text-xs text-gray-500 bg-gray-100 rounded-full">Today</span>
        </div>
        <div class="chat-enter">
            <div class="flex items-start">
                <img src="logo.png" alt="Assistant" class="message-icon">
                <div class="bg-indigo-50 text-gray-800 rounded-2xl py-2 px-4 max-w-xs">
                    <p class="font-medium">Welcome to EduPro Support!</p>
                    <p class="mt-1 text-sm">I'm here to help with school management system questions. How can I assist you today?</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Input area with features -->
    <div class="border-t border-gray-200 p-3 bg-gray-50 rounded-b-xl">
        <div id="quickOptions" class="mb-2 flex flex-wrap gap-2">
            <button onclick="quickSelect('Password reset')" class="option-btn text-xs bg-white border border-indigo-200 rounded-full px-3 py-1 hover:bg-indigo-50">
                Password help
            </button>
            <button onclick="quickSelect('Student reports')" class="option-btn text-xs bg-white border border-indigo-200 rounded-full px-3 py-1 hover:bg-indigo-50">
                Generate reports
            </button>
            <button onclick="quickSelect('Billing question')" class="option-btn text-xs bg-white border border-indigo-200 rounded-full px-3 py-1 hover:bg-indigo-50">
                Billing help
            </button>
        </div>
        <div class="flex items-center space-x-2">
            <input id="userInput" type="text" placeholder="Type your question..." 
                   class="flex-1 border border-gray-300 rounded-full py-3 px-4 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
            <button onclick="sendMessage()" class="bg-indigo-600 text-white rounded-full p-3 hover:bg-indigo-700 focus:outline-none shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    // ========== STATE MANAGEMENT ==========
    const chatState = {
        isOpen: false,
        awaitingResponse: false,
        currentFlow: null,
        ticketInfo: {
            category: null,
            description: null,
            email: null
        },
        lastActive: new Date()
    };

    // ========== CORE FUNCTIONS ==========
    function toggleChat() {
        const chat = document.getElementById('chatContainer');
        chatState.isOpen = !chatState.isOpen;
        chat.style.display = chatState.isOpen ? 'flex' : 'none';
        
        if (chatState.isOpen) {
            document.getElementById('userInput').focus();
            chatState.lastActive = new Date();
        }
    }

    function minimizeChat() {
        // Implement minimize functionality
        console.log("Minimize chat");
    }

    function sendMessage() {
        const input = document.getElementById('userInput');
        const message = input.value.trim();
        
        if (message && !chatState.awaitingResponse) {
            addUserMessage(message);
            input.value = '';
            processUserMessage(message);
        }
    }

    // ========== MESSAGE HANDLING ==========
    function addMessage(content, sender, isHTML = false) {
        const messagesContainer = document.getElementById('chatMessages');
        
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-enter flex ${sender === 'user' ? 'justify-end' : 'items-start'}`;
        
        if (sender === 'user') {
            messageDiv.innerHTML = `
                <div class="bg-indigo-600 text-white rounded-2xl py-2 px-4 max-w-xs">
                    ${content}
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <img src="logo.png" alt="Assistant" class="message-icon">
                <div class="bg-indigo-50 text-gray-800 rounded-2xl py-2 px-4 max-w-xs">
                    ${isHTML ? content : content}
                </div>
            `;
        }
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addUserMessage(message) {
        addMessage(message, 'user');
        chatState.lastActive = new Date();
    }

    function showTyping() {
        chatState.awaitingResponse = true;
        const messagesContainer = document.getElementById('chatMessages');
        
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-enter flex items-start';
        typingDiv.id = 'typingIndicator';
        
        typingDiv.innerHTML = `
            <img src="logo.png" alt="Assistant" class="message-icon">
            <div class="bg-indigo-50 text-gray-800 rounded-2xl py-2 px-4 max-w-xs">
                <div class="flex space-x-1 items-center">
                    <span class="typing-indicator">
                        <span class="inline-block w-2 h-2 bg-indigo-400 rounded-full"></span>
                        <span class="inline-block w-2 h-2 bg-indigo-400 rounded-full ml-1"></span>
                        <span class="inline-block w-2 h-2 bg-indigo-400 rounded-full ml-1"></span>
                    </span>
                    <span class="text-xs ml-2 text-gray-500">Assistant is typing</span>
                </div>
            </div>
        `;
        
        messagesContainer.appendChild(typingDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function hideTyping() {
        chatState.awaitingResponse = false;
        const typingIndicator = document.getElementById('typingIndicator');
        if (typingIndicator) typingIndicator.remove();
    }

    // ========== CONVERSATION FLOWS ==========
    function processUserMessage(message) {
        showTyping();
        
        setTimeout(() => {
            hideTyping();
            
            const lowerMessage = message.toLowerCase();
            
            if (chatState.currentFlow === 'ticket') {
                handleTicketFlow(message);
                return;
            }
            
            // Check for greetings
            if (/hi|hello|hey/i.test(message)) {
                showMainMenu("Hello! How can I help you with EduPro today?");
                return;
            }
            
            // Check for help request
            if (/help|support|issue|problem/i.test(message)) {
                startTicketFlow();
                return;
            }
            
            // Check for common questions
            const response = getPredefinedResponse(lowerMessage);
            if (response) {
                showPredefinedResponse(response);
                return;
            }
            
            // Default fallback
            showMainMenu("I'm not sure I understand. Please choose an option below:");
        }, 1000 + Math.random() * 1000); // Random delay for natural feel
    }

    // ========== PREDEFINED RESPONSES ==========
    const predefinedResponses = {
        'password': {
            question: "Password reset",
            answer: "You can reset your password by:\n1. Going to the login page\n2. Clicking 'Forgot Password'\n3. Entering your email\n4. Following instructions in the email",
            followUp: "Would you like me to email you password reset instructions?"
        },
        'report': {
            question: "Student reports",
            answer: "To generate reports:\n1. Navigate to Reports section\n2. Select class/student\n3. Choose date range\n4. Click 'Generate'\n5. Export as PDF/Excel",
            followUp: "Need help with specific report settings?"
        },
        'billing': {
            question: "Payment questions",
            answer: "For billing help:\n• View invoices under Billing > History\n• Make payments via Payment portal\n• Contact accounts@edupro.com for disputes",
            followUp: "Is this related to a specific invoice?"
        }
    };

    function getPredefinedResponse(message) {
        if (/password|login/i.test(message)) return predefinedResponses['password'];
        if (/report|grade|performance/i.test(message)) return predefinedResponses['report'];
        if (/payment|bill|invoice|fee/i.test(message)) return predefinedResponses['billing'];
        return null;
    }

    function showPredefinedResponse(response) {
        addMessage(response.answer, 'bot');
        
        setTimeout(() => {
            addMessage(response.followUp, 'bot');
            
            const options = `
                <div class="mt-2 flex space-x-2">
                    <button onclick="addUserMessage('Yes'); startTicketFlow('${response.question}')" 
                            class="option-btn bg-green-500 text-white px-3 py-1 rounded-full text-sm">
                        Yes
                    </button>
                    <button onclick="showMainMenu('What else can I help with?')" 
                            class="option-btn bg-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm">
                        No
                    </button>
                </div>
            `;
            
            addMessage(options, 'bot', true);
        }, 800);
    }

    // ========== TICKET FLOW ==========
  function startTicketFlow(presetCategory = null) {
    if (presetCategory) {
        chatState.ticketInfo.category = presetCategory;
        
        showTyping();
        setTimeout(() => {
            hideTyping();
            addMessage(`You're asking about: ${presetCategory}. Please describe your issue in detail:`, 'bot');
            chatState.currentFlow = 'ticket';
        }, 1000);
    } else {
            showTyping();
            setTimeout(() => {
                hideTyping();
                addMessage("Let me connect you with our support team. What is this regarding?", 'bot');
                
                const options = `
                    <div class="mt-3 space-y-2">
                        <button onclick="selectTicketCategory('Technical Issue')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                            🖥️ Technical Issue
                        </button>
                        <button onclick="selectTicketCategory('Billing Question')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                            💰 Billing Question
                        </button>
                        <button onclick="selectTicketCategory('Account Help')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                            👤 Account Help
                        </button>
                        <button onclick="selectTicketCategory('Other')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                            ❓ Other Inquiry
                        </button>
                    </div>
                `;
                
                addMessage(options, 'bot', true);
                chatState.currentFlow = 'ticket';
            }, 1000);
        }
    }

    function selectTicketCategory(category) {
        chatState.ticketInfo.category = category;
        addUserMessage(category);
        
        showTyping();
        setTimeout(() => {
            hideTyping();
            addMessage(`Thank you. Please describe your ${category.toLowerCase()} in detail:`, 'bot');
        }, 1000);
    }

    function handleTicketFlow(message) {
        if (!chatState.ticketInfo.description) {
            chatState.ticketInfo.description = message;
            showEmailForm();
        }
    }

    // ========== EMAIL FORM ==========
    function showEmailForm() {
        showTyping();
        setTimeout(() => {
            hideTyping();
            
            const formHTML = `
                <div class="space-y-3">
                    <p>To help you best, we'll email this request to our support team.</p>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Your Email:</label>
                        <input type="email" id="supportEmail" placeholder="your@email.com" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Request Summary:</label>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-200 text-sm">
                            <p class="font-medium">${chatState.ticketInfo.category}</p>
                            <p class="mt-1 text-gray-600">${chatState.ticketInfo.description}</p>
                        </div>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button onclick="submitSupportTicket()" class="flex-1 bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none">
                            Send Request
                        </button>
                        <button onclick="cancelTicket()" class="bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 focus:outline-none">
                            Cancel
                        </button>
                    </div>
                </div>
            `;
            
            addMessage(formHTML, 'bot', true);
        }, 800);
    }

    async function submitSupportTicket() {
        const email = document.getElementById('supportEmail').value.trim();
        
        if (!email || !validateEmail(email)) {
            addMessage("Please enter a valid email address", 'bot');
            return;
        }
        
        chatState.ticketInfo.email = email;
        showTyping();
        
        try {
            const response = await fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'email': email,
                    'message': `${chatState.ticketInfo.category}\n\n${chatState.ticketInfo.description}`,
                    'subject': `Support: ${chatState.ticketInfo.category}`
                })
            });
            
            const data = await response.json();
            
            hideTyping();
            if (data.success) {
                addMessage("✅ Your request has been sent! Our team will respond within 24 hours.", 'bot');
                addMessage("Your ticket reference: #" + Math.floor(100000 + Math.random() * 900000), 'bot');
            } else {
                addMessage(" ✅ email sent we will reply shortly", 'bot');
            }
            
            resetConversation();
            showMainMenu("Is there anything else I can help with?");
            
        } catch (error) {
            hideTyping();
            addMessage("❌ Network error. Please check your connection and try again.", 'bot');
        }
    }

    function cancelTicket() {
        resetConversation();
        showMainMenu("Request cancelled. How else can I help?");
    }

    // ========== UTILITIES ==========
    function validateEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function resetConversation() {
        chatState.currentFlow = null;
        chatState.ticketInfo = {
            category: null,
            description: null,
            email: null
        };
    }

    function showMainMenu(message) {
        resetConversation();
        addMessage(message, 'bot');
        
        const options = `
            <div class="mt-3 space-y-2">
                <button onclick="quickSelect('Password help')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                    🔑 Password Help
                </button>
                <button onclick="quickSelect('Generate reports')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                    📊 Report Assistance
                </button>
                <button onclick="quickSelect('Billing question')" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                    💳 Billing Support
                </button>
                <button onclick="startTicketFlow()" class="option-btn w-full text-left bg-white border border-indigo-200 rounded-lg py-2 px-4 hover:bg-indigo-50">
                    ✉️ Contact Support
                </button>
            </div>
        `;
        
        addMessage(options, 'bot', true);
    }

    function quickSelect(option) {
        addUserMessage(option);
        processUserMessage(option);
    }

    // ========== INITIALIZATION ==========
    document.addEventListener('DOMContentLoaded', () => {
        // Auto-open chat after 5 seconds if not interacted with
        setTimeout(() => {
            if (!chatState.isOpen && new Date() - chatState.lastActive > 5000) {
                toggleChat();
            }
        }, 5000);
        
        // Handle Enter key in input
        document.getElementById('userInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    });
</script>
</body>
</html>
