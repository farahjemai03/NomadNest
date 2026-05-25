/* manager.js
Page: manager.html / manager.php
Role: dashboard host avec PHP/MySQL.
*/

function loadManagerData() {
fetchManagerStats();
}

function fetchManagerStats() {
fetch('../api/manager.php')
.then(function(response) {
return response.json();
})
.then(function(data) {
if (data.success) {
renderKPIs(data.kpis);
renderListings(data.listings);
renderPendingBookings(data.booking_requests);
} else {
alert('Erreur manager');
}
})
.catch(function(error) {
console.log(error);
alert('Tu dois etre connecte comme host');
});
}

function renderKPIs(kpis) {
var revenue = kpis.revenue_month || 0;
var bookings = kpis.total_reservations || 0;

document.getElementById('totalRevenue').textContent = revenue + ' DT';
document.getElementById('managerBookings').textContent = bookings;
}

function renderListings(listings) {
var grid = document.getElementById('listingsGrid');
grid.innerHTML = '';

if (!listings || listings.length === 0) {
grid.innerHTML = '<p>Aucune annonce</p>';
return;
}

for (var i = 0; i < listings.length; i++) {
grid.innerHTML += createListingCard(listings[i]);
}
}

function createListingCard(listing) {
return `
<div class="member-card">
<h3>${listing.name}</h3>
<p>${listing.city}</p>
<p>${listing.price_per_day} DT / jour</p>
<p>Status: ${listing.availability_status}</p>
<button onclick="pauseListing(${listing.id})">Desactiver</button>
<button onclick="deleteListing(${listing.id})">Supprimer</button>
</div>
`;
}

function renderPendingBookings(bookings) {
var container = document.getElementById('pendingBookings');
container.innerHTML = '';

if (!bookings || bookings.length === 0) {
container.innerHTML = '<p>Aucune reservation</p>';
return;
}

for (var i = 0; i < bookings.length; i++) {
container.innerHTML += `
<div class="member-card">
<h3>${bookings[i].space_name}</h3>
<p>Client: ${bookings[i].member_name}</p>
<p>${bookings[i].date_start} -> ${bookings[i].date_end}</p>
<p>Status: ${bookings[i].status}</p>
<button onclick="approveBooking(${bookings[i].id})">Accepter</button>
<button onclick="rejectBooking(${bookings[i].id})">Refuser</button>
</div>
`;
}
}

function approveBooking(bookingId) {
sendManagerAction('approve', 'booking_id', bookingId);
}

function rejectBooking(bookingId) {
sendManagerAction('cancel', 'booking_id', bookingId);
}

function pauseListing(spaceId) {
sendManagerAction('pause', 'space_id', spaceId);
}

function deleteListing(spaceId) {
if (!confirm('Supprimer cette annonce ?')) return;
sendManagerAction('delete_listing', 'space_id', spaceId);
}

function editListing(spaceId) {
alert('Modification annonce non faite dans API PHP actuelle');
}

function sendManagerAction(action, fieldName, id) {
var formData = new FormData();
formData.append('action', action);
formData.append(fieldName, id);

fetch('../api/manager.php', {
method: 'POST',
body: formData
})
.then(function(response) {
return response.json();
})
.then(function(data) {
alert(data.message);
loadManagerData();
})
.catch(function(error) {
console.log(error);
alert('Erreur serveur');
});
}

loadManagerData();