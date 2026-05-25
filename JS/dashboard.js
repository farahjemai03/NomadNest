/* dashboard.js
Page: dashboard.html / dashboard.php
Role: dashboard utilisateur avec PHP/MySQL.
*/

function loadDashboardData() {
fetchDashboardStats();
}

function fetchDashboardStats() {
fetch('../api/dashboard.php')
.then(function(response) {
return response.json();
})
.then(function(data) {
if (data.success) {
renderStats(data.stats, data.subscription);
renderBookingsTable(data.bookings);
renderSubscriptions(data.subscription);
} else {
alert('Erreur dashboard');
}
})
.catch(function(error) {
console.log(error);
alert('Connecte toi avant');
});
}

function renderStats(stats, subscription) {
document.getElementById('totalBookings').textContent = stats.active;

if (subscription && subscription.active == 1) {
document.getElementById('activeSubscriptions').textContent = 1;
} else {
document.getElementById('activeSubscriptions').textContent = 0;
}
}

function renderBookingsTable(bookings) {
var table = document.getElementById('bookingsTable');
table.innerHTML = '';

if (!bookings || bookings.length === 0) {
table.innerHTML = '<tr><td colspan="3">Aucune reservation</td></tr>';
return;
}

for (var i = 0; i < bookings.length; i++) {
table.innerHTML += `
<tr>
<td>${bookings[i].space_name}</td>
<td>${bookings[i].date_start} -> ${bookings[i].date_end}</td>
<td>${formatBookingStatus(bookings[i].status)}</td>
</tr>
`;
}
}

function renderSubscriptions(subscription) {
console.log('Subscription:', subscription);
}

function formatBookingStatus(status) {
if (status === 'confirmed') return 'Confirmee';
if (status === 'pending') return 'En attente';
if (status === 'cancelled') return 'Annulee';
return status;
}

function cancelBooking(id) {
alert('Annulation cote utilisateur non disponible dans api/dashboard.php');
}

function refreshDashboard() {
loadDashboardData();
}

loadDashboardData();