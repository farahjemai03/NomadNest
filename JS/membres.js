/* members.js */

var allMembers = [];

function loadMembers() {
    fetchMembers();

    var cityFilter = document.getElementById('memberCityFilter');
    if (cityFilter) {
        cityFilter.addEventListener('change', function () {
            fetchMembers(this.value);
        });
    }
}

function fetchMembers(city) {
    var url = '/nomadnest/api/members.php';
    if (city && city !== '') {
        url += '?city=' + encodeURIComponent(city);
    }

    var grid = document.getElementById('membersGrid');
    grid.innerHTML = '<div class="loading-state"><div class="loading-spinner"></div><p>Loading members…</p></div>';

    fetch(url)
        .then(function (response) { return response.json(); })
        .then(function (data) {
            if (data.success) {
                allMembers = data.data;
                renderMembers(allMembers);
                fillCityDropdown(allMembers);
            } else {
                grid.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Could not load members.</p></div>';
            }
        })
        .catch(function () {
            grid.innerHTML = '<div class="empty-state"><div class="empty-icon">⚠️</div><p>Server error — please try again.</p></div>';
        });
}

function renderMembers(members) {
    var grid = document.getElementById('membersGrid');
    grid.innerHTML = '';

    if (!members || members.length === 0) {
        grid.innerHTML = '<div class="empty-state"><div class="empty-icon">👤</div><p>No members in this city yet.</p></div>';
        return;
    }

    members.forEach(function (m) {
        var card = document.createElement('div');
        card.className = 'member-card';
        card.innerHTML = createMemberCard(m);
        grid.appendChild(card);
    });
}

function createMemberCard(m) {
    var avatarHtml = m.avatar
        ? '<img src="' + escHtml(m.avatar) + '" alt="' + escHtml(m.name) + '" style="width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;">'
        : '<div class="member-avatar">' + initials(m.name) + '</div>';

    var tagsHtml = '';
    if (m.tags && m.tags.length) {
        tagsHtml = '<div class="member-tags">'
            + m.tags.map(function (t) { return '<span class="member-tag">' + escHtml(t) + '</span>'; }).join('')
            + '</div>';
    }

    var bioHtml = m.bio ? '<p class="member-bio">' + escHtml(m.bio) + '</p>' : '';

    var statusLabel = m.status ? m.status.charAt(0).toUpperCase() + m.status.slice(1) : 'Offline';
    var statusClass = m.status || 'offline';

    var actionsHtml = CURRENT_USER_ID && CURRENT_USER_ID !== m.id
        ? '<div class="member-actions">'
          + '<button class="btn btn-primary btn-sm" onclick="sendConnectionRequest(' + m.id + ', this)">Connect</button>'
          + '<button class="btn btn-ghost btn-sm" onclick="openMemberProfile(' + m.id + ')">View profile</button>'
          + '</div>'
        : '';

    return ''
        + '<div class="member-card-top">'
        +   avatarHtml
        +   '<div style="flex:1;min-width:0;">'
        +     '<div class="member-name">' + escHtml(m.name) + '</div>'
        +     '<div class="member-city">' + escHtml(m.city || '—') + '</div>'
        +     '<div style="margin-top:4px;font-size:.78rem;color:var(--text-muted);">'
        +       '<span class="status-dot ' + statusClass + '"></span>' + statusLabel
        +     '</div>'
        +   '</div>'
        + '</div>'
        + tagsHtml
        + bioHtml
        + actionsHtml;
}

function fillCityDropdown(members) {
    var select = document.getElementById('memberCityFilter');
    if (!select || select.options.length > 1) return;

    var seen = {};
    members.forEach(function (m) {
        if (m.city && !seen[m.city]) {
            seen[m.city] = true;
            var opt = document.createElement('option');
            opt.value = m.city;
            opt.textContent = m.city;
            select.appendChild(opt);
        }
    });
}

function sendConnectionRequest(memberId, btn) {
    var formData = new FormData();
    formData.append('action', 'connect');
    formData.append('to_user', memberId);

    btn.disabled = true;
    btn.textContent = 'Sending…';

    fetch('/nomadnest/api/members.php', { method: 'POST', body: formData })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            btn.textContent = data.success ? 'Sent ✓' : 'Already sent';
            btn.classList.replace('btn-primary', 'btn-ghost');
        })
        .catch(function () {
            btn.textContent = 'Error';
            btn.disabled = false;
        });
}

function openMemberProfile(memberId) {
    window.location.href = '/nomadnest/pages/profile.php?id=' + memberId;
}

// ── Helpers ──────────────────────────────────────────────────
function initials(name) {
    var parts = (name || '').trim().split(' ');
    return ((parts[0] || '')[0] || '') + ((parts[1] || '')[0] || '').toUpperCase();
}

function escHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

loadMembers();