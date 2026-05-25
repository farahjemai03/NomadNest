/* helpers.js
Fonctions tres simples utilisees par toutes les pages.
*/

function $(id) {
return document.getElementById(id);
}

function formatPrice(price) {
return Number(price || 0).toFixed(2) + ' DT';
}

function formatDate(date) {
if (!date) return '';
return date;
}

function showToast(message) {
alert(message);
}

function showLoader() {
console.log('Loading...');
}

function hideLoader() {
console.log('Loading finished');
}

function handleApiError(error) {
console.log(error);
alert('Erreur de connexion avec le serveur PHP');
}

function apiPath(fileName) {
// Le projet est souvent dans: http://localhost/nomadnest/pages/...
// Donc on utilise ../api/... depuis les pages.
return '../api/' + fileName;
}

function postData(url, data) {
return fetch(url, {
method: 'POST',
body: data
}).then(function(response) {
return response.json();
});
}