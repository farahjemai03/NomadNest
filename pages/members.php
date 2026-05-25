<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$currentUserId = $_SESSION['user_id'] ?? null;

// Fetch members directly here — no JS API needed
$members = $pdo->query("
    SELECT id, name, city, bio, tags, status, avatar
    FROM users
    WHERE role = 'member'
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

$cities = array_unique(array_filter(array_column($members, 'city')));
sort($cities);

function initials(string $name): string {
    $parts = explode(' ', trim($name));
    return strtoupper(substr($parts[0],0,1) . (isset($parts[1]) ? substr($parts[1],0,1) : ''));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Members — NomadNest</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
</head>
<body>
<?php require_once __DIR__ . '/../includes/navbar.php'; ?>

<div class="members-page">

    <div class="members-header">
        <h1>Members</h1>
        <span style="color:var(--text-muted);font-size:.9rem;"><?= count($members) ?> nomads</span>
    </div>

    <!-- City filter tabs -->
    <div class="city-tabs">
        <button class="city-tab active" onclick="filterCity('', this)">All cities</button>
        <?php foreach ($cities as $city): ?>
            <button class="city-tab" onclick="filterCity('<?= htmlspecialchars($city) ?>', this)">
                <?= htmlspecialchars($city) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <!-- Members grid — all cards rendered by PHP, JS just shows/hides -->
    <div id="membersGrid">
        <?php foreach ($members as $m):
            $tags = $m['tags'] ? array_map('trim', explode(',', $m['tags'])) : [];
        ?>
        <div class="member-card" data-city="<?= htmlspecialchars($m['city'] ?? '') ?>">

            <div class="member-card-top">
                <?php if (!empty($m['avatar'])): ?>
                    <img src="<?= htmlspecialchars($m['avatar']) ?>"
                         alt="<?= htmlspecialchars($m['name']) ?>"
                         style="width:52px;height:52px;border-radius:50%;object-fit:cover;flex-shrink:0;">
                <?php else: ?>
                    <div class="member-avatar"><?= initials($m['name']) ?></div>
                <?php endif; ?>

                <div style="flex:1;min-width:0;">
                    <div class="member-name"><?= htmlspecialchars($m['name']) ?></div>
                    <div class="member-city"><?= htmlspecialchars($m['city'] ?? '—') ?></div>
                    <div style="margin-top:4px;font-size:.78rem;color:var(--text-muted);">
                        <span class="status-dot <?= htmlspecialchars($m['status'] ?? 'offline') ?>"></span>
                        <?= ucfirst($m['status'] ?? 'offline') ?>
                    </div>
                </div>
            </div>

            <?php if ($tags): ?>
                <div class="member-tags">
                    <?php foreach ($tags as $tag): ?>
                        <span class="member-tag"><?= htmlspecialchars($tag) ?></span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($m['bio'])): ?>
                <p class="member-bio"><?= htmlspecialchars($m['bio']) ?></p>
            <?php endif; ?>

            <?php if ($currentUserId && $currentUserId != $m['id']): ?>
                <div class="member-actions">
                    <a href="/nomadnest/pages/messages.php?to=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Message</a>
                    <a href="/nomadnest/pages/profile.php?id=<?= $m['id'] ?>" class="btn btn-ghost btn-sm">View profile</a>
                </div>
            <?php endif; ?>

        </div>
        <?php endforeach; ?>

        <?php if (empty($members)): ?>
            <div class="empty-state">
                <div class="empty-icon">👤</div>
                <p>No members yet. Run the seed SQL first.</p>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<script>
function filterCity(city, btn) {
    // Update active tab
    document.querySelectorAll('.city-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');

    // Show/hide cards
    document.querySelectorAll('.member-card').forEach(function(card) {
        card.style.display = (!city || card.dataset.city === city) ? '' : 'none';
    });
}
</script>
</body>
</html>