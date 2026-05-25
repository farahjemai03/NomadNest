/* space.js
   Page: space.html / space.php
   Role: afficher details d'un espace + reserver.
*/

var currentSpace = null;
var bookedDates = [];

function loadSpaceDetails() {
    var id = getSpaceIdFromUrl();

    if (!id) {
        alert('ID espace manquant dans URL');
        return;
    }

    fetchSpaceById(id);
    loadBookedDates(id);

    var bookBtn = document.getElementById('bookBtn');
    if (bookBtn) {
        bookBtn.addEventListener('click', createBooking);
    }
}

function getSpaceIdFromUrl() {
    var params = new URLSearchParams(window.location.search);
    return params.get('id');
}

function fetchSpaceById(id) {
    fetch('../api/space.php?id=' + id)
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            currentSpace = data.data;
            renderSpaceData(currentSpace);
            loadGallery(currentSpace);
            renderReviews(currentSpace.reviews);
        } else {
            alert(data.message);
        }
    })
    .catch(function(error) {
        console.log(error);
        alert('Erreur serveur');
    });
}

function renderSpaceData(space) {
    document.getElementById('spaceName').textContent = space.name;
    document.getElementById('spaceDescription').textContent = space.description;
    document.getElementById('spacePrice').textContent = space.price_per_day + ' DT / jour';
}

function loadGallery(space) {
    var gallery = document.getElementById('spaceGallery');

    if (space.image) {
        gallery.innerHTML = '<img src="' + space.image + '" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">';
    } else {
        gallery.innerHTML = '<p style="padding:20px;">Pas image</p>';
    }
}

function loadReviews(reviews) {
    renderReviews(reviews);
}

function renderReviews(reviews) {
    var container = document.getElementById('reviewsContainer');
    container.innerHTML = '<h3>Reviews</h3>';

    if (!reviews || reviews.length === 0) {
        container.innerHTML += '<p>Aucun review.</p>';
        return;
    }

    for (var i = 0; i < reviews.length; i++) {
        container.innerHTML += `
            <div class="member-card">
                <p><b>${reviews[i].reviewer_name}</b></p>
                <p>Rating: ${reviews[i].rating}/5</p>
                <p>${reviews[i].comment}</p>
            </div>
        `;
    }
}

function loadBookedDates(id) {
    fetch('../api/space.php?id=' + id + '&booked=1')
    .then(function(response) {
        return response.json();
    })
    .then(function(data) {
        if (data.success) {
            bookedDates = data.booked_dates;
        }
    });
}

function calculateTotalPrice() {
    var checkIn = document.getElementById('checkIn').value;
    var checkOut = document.getElementById('checkOut').value;

    if (!checkIn || !checkOut || !currentSpace) {
        return 0;
    }

    var start = new Date(checkIn);
    var end = new Date(checkOut);
    var difference = end - start;
    var days = difference / (1000 * 60 * 60 * 24) + 1;

    if (days <= 0) return 0;

    return days * currentSpace.price_per_day;
}

function validateBookingDates() {
    var checkIn = document.getElementById('checkIn').value;
    var checkOut = document.getElementById('checkOut').value;

    if (checkIn === '' || checkOut === '') {
        alert('Choisir check in et check out');
        return false;
    }

    if (checkOut < checkIn) {
        alert('Date fin doit etre apres date debut');
        return false;
    }

    if (bookedDates.indexOf(checkIn) !== -1 || bookedDates.indexOf(checkOut) !== -1) {
        alert('Cette date est deja reservee');
        return false;
    }

    return true;
}

function createBooking() {
    if (!validateBookingDates()) return;

    var spaceId = getSpaceIdFromUrl();
    var checkIn = document.getElementById('checkIn').value;
    var checkOut = document.getElementById('checkOut').value;

    var formData = new FormData();
    formData.append('space_id', spaceId);
    formData.append('date_start', checkIn);
    formData.append('date_end', checkOut);
    formData.append('seats', 1);

    fetch('../api/book.php', {
        method: 'POST',
        body: formData
    })
    .then(function(data) {
showBookingMessage(data.message);

if (data.success) {
alert('Reservation envoyee. Total: ' + data.booking.total_price + ' DT');
}
})
.catch(function(error) {
console.log(error);
alert('Erreur serveur');
});
}

function showBookingMessage(message) {
alert(message);
}

loadSpaceDetails();