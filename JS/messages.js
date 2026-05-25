/* messages.js
Page: messages.html / messages.php
Role: conversations + messages avec PHP/MySQL.
*/

var currentConversationUserId = null;

function loadConversations() {
fetchConversations();

var sendBtn = document.getElementById('sendMessageBtn');
if (sendBtn) {
sendBtn.addEventListener('click', sendMessage);
}
}

function fetchConversations() {
fetch('../api/messages.php')
.then(function(response) {
return response.json();
})
.then(function(data) {
if (data.success) {
renderConversationList(data.data);
}
})
.catch(function(error) {
console.log(error);
document.getElementById('conversationsList').innerHTML = '<p>Connecte toi avant</p>';
});
}

function renderConversationList(conversations) {
var list = document.getElementById('conversationsList');
list.innerHTML = '';

if (!conversations || conversations.length === 0) {
list.innerHTML = '<p style="padding:15px;">Aucune conversation</p>';
return;
}

for (var i = 0; i < conversations.length; i++) {
list.innerHTML += `
<div class="member-card" onclick="openConversation(${conversations[i].partner_id})" style="cursor:pointer;margin:10px;">
<h3>${conversations[i].partner_name}</h3>
<p>${conversations[i].last_message}</p>
<small>Non lus: ${conversations[i].unread_count}</small>
</div>
`;
}
}

function openConversation(userId) {
currentConversationUserId = userId;
loadMessagesThread(userId);
}

function loadMessagesThread(userId) {
fetch('../api/messages.php?with=' + userId)
.then(function(response) {
return response.json();
})
.then(function(data) {
if (data.success) {
renderMessages(data.data);
markMessagesAsRead();
}
})
.catch(function(error) {
console.log(error);
alert('Erreur messages');
});
}

function renderMessages(messages) {
var thread = document.getElementById('messagesThread');
thread.innerHTML = '';

if (!messages || messages.length === 0) {
thread.innerHTML = '<p>Aucun message</p>';
return;
}

for (var i = 0; i < messages.length; i++) {
thread.innerHTML += createMessageBubble(messages[i]);
}

autoScrollChat();
}

function sendMessage() {
var input = document.getElementById('messageText');
var text = input.value;

if (currentConversationUserId === null) {
alert('Choisir une conversation');
return;
}

if (text.trim() === '') {
alert('Ecrire un message');
return;
}

var formData = new FormData();
formData.append('to', currentConversationUserId);
formData.append('body', text);

fetch('../api/messages.php', {
method: 'POST',
body: formData
})
.then(function(response) {
return response.json();
})
.then(function(data) {
if (data.success) {
input.value = '';
loadMessagesThread(currentConversationUserId);
fetchConversations();
}
})
.catch(function(error) {
console.log(error);
alert('Erreur envoi message');
});
}

function createMessageBubble(message) {
return `
<div class="message-bubble">
<b>${message.sender_name}</b><br>
${message.body}
</div>
`;
}

function autoScrollChat() {
var thread = document.getElementById('messagesThread');
thread.scrollTop = thread.scrollHeight;
}

function deleteMessage(id) {
alert('Suppression message non disponible dans API PHP actuelle');
}

function markMessagesAsRead() {
console.log('Messages marques comme lus par api/messages.php');
}

loadConversations();