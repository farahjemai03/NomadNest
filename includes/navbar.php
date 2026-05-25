<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<nav class="navbar">
    <a class="navbar-brand" href="/nomadnest/index.php">
        <span>N</span>NomadNest
    </a>

    <div class="navbar-links">
        <a href="/nomadnest/pages/spaces.php"   class="<?= $current === 'spaces.php'    ? 'active' : '' ?>">Spaces</a>
        <a href="/nomadnest/pages/members.php"  class="<?= $current === 'members.php'   ? 'active' : '' ?>">Members</a>
        <a href="/nomadnest/pages/dashboard.php"class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
        <a href="/nomadnest/pages/manager.php"  class="<?= $current === 'manager.php'   ? 'active' : '' ?>">For Hosts</a>
        <a href="/nomadnest/pages/messages.php" class="<?= $current === 'messages.php'  ? 'active' : '' ?>">Messages</a>
    </div>

    <div class="navbar-actions">
        <?php if (isset($_SESSION['user_id'])): ?>
            <span style="color:#94a3b8;font-size:.85rem;">Hi, <?= htmlspecialchars($_SESSION['user_name']) ?></span>
            <a href="/nomadnest/api/logout.php" class="btn btn-secondary btn-sm">Sign out</a>
        <?php else: ?>
            <a href="/nomadnest/pages/auth.php" class="btn btn-secondary btn-sm">Sign in</a>
            <a href="/nomadnest/pages/auth.php" class="btn btn-primary btn-sm">Get started</a>
        <?php endif; ?>
    </div>
</nav>