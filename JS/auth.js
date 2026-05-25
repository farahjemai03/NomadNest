/* auth.js
Page: auth.html / auth.php
Role: login + register avec PHP/MySQL.
*/

function toggleAuthForm(formName) {
var loginForm = document.getElementById('loginForm');
var registerForm = document.getElementById('registerForm');
var message = document.getElementById('authMessage');

message.textContent = '';

if (formName === 'login') {
loginForm.classList.remove('hidden');
registerForm.classList.add('hidden');
} else {
registerForm.classList.remove('hidden');
loginForm.classList.add('hidden');
}
}

function validateLoginForm() {
var email = document.getElementById('loginEmail').value;
var password = document.getElementById('loginPassword').value;

if (email === '' || password === '') {
showAuthMessage('Remplir email et password', false);
return false;
}

return true;
}

function validateRegisterForm() {
var name = document.getElementById('registerName').value;
var email = document.getElementById('registerEmail').value;
var password = document.getElementById('registerPassword').value;

if (name === '' || email === '' || password === '') {
showAuthMessage('Remplir tous les champs', false);
return false;
}

if (password.length < 8) {
showAuthMessage('Password minimum 8 caracteres', false);
return false;
}

return true;
}

function loginUser(event) {
event.preventDefault();

if (!validateLoginForm()) return;

var formData = new FormData();
formData.append('email', document.getElementById('loginEmail').value);
formData.append('password', document.getElementById('loginPassword').value);

fetch('../api/login.php', {
method: 'POST',
body: formData
})
.then(function(response) {
return response.json();
})
.then(function(data) {
showAuthMessage(data.message, data.success);

if (data.success) {
if (data.user.role === 'host') {
    window.location.href = '/nomadnest/pages/manager.php';
} else {
    window.location.href = '/nomadnest/pages/dashboard.php';
}
}
})
.catch(function(error) {
showAuthMessage('Erreur serveur', false);
console.log(error);
});
}

function registerUser(event) {
event.preventDefault();

if (!validateRegisterForm()) return;

var formData = new FormData();
formData.append('name', document.getElementById('registerName').value);
formData.append('email', document.getElementById('registerEmail').value);
formData.append('password', document.getElementById('registerPassword').value);
formData.append('role', document.getElementById('registerRole').value);

fetch('../api/register.php', {
method: 'POST',
body: formData
})
.then(function(response) {
return response.json();
})
.then(function(data) {
showAuthMessage(data.message, data.success);

if (data.success) {
var role = document.getElementById('registerRole').value;
if (role === 'host') {
    window.location.href = '/nomadnest/pages/manager.php';
} else {
    window.location.href = '/nomadnest/pages/dashboard.php';
}
}
})
.catch(function(error) {
showAuthMessage('Erreur serveur', false);
console.log(error);
});
}

function showAuthMessage(message, success) {
var authMessage = document.getElementById('authMessage');
authMessage.textContent = message;

if (success) {
authMessage.style.color = 'green';
} else {
authMessage.style.color = 'red';
}
}

function clearAuthFields() {
document.getElementById('loginEmail').value = '';
document.getElementById('loginPassword').value = '';
document.getElementById('registerName').value = '';
document.getElementById('registerEmail').value = '';
document.getElementById('registerPassword').value = '';
}

function logoutUser() {
window.location.href = '../api/logout.php';
}

// Lancement de la page
var loginTab = document.getElementById('loginTab');
var registerTab = document.getElementById('registerTab');
var loginForm = document.getElementById('loginForm');
var registerForm = document.getElementById('registerForm');

if (loginTab) loginTab.addEventListener('click', function() { toggleAuthForm('login'); });
if (registerTab) registerTab.addEventListener('click', function() { toggleAuthForm('register'); });
if (loginForm) loginForm.addEventListener('submit', loginUser);
if (registerForm) registerForm.addEventListener('submit', registerUser);