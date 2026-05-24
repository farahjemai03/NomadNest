<?php
session_start();
require_once __DIR__ . '/includes/db.php';

// Fetch featured spaces
$spaces = $pdo->query("
    SELECT s.id, s.name, s.type, s.city, s.address, s.price_per_day,
           s.availability_status, s.image, s.rating,
           GROUP_CONCAT(a.name ORDER BY a.name SEPARATOR ',') AS amenities
    FROM spaces s
    LEFT JOIN amenities a ON a.space_id = s.id
    GROUP BY s.id
    ORDER BY s.rating DESC
    LIMIT 6
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NomadNest — Your space. Your tribe. Your city.</title>
    <link rel="stylesheet" href="/nomadnest/css/style.css">
    <style>
        /* ── Hero ── */
        .hero {
            background: var(--navy);
            padding: 5rem 2rem 4rem;
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 70% 50%, rgba(201,169,110,.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .hero-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            align-items: center;
        }
        .hero-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(201,169,110,.15);
            color: var(--accent);
            font-size: .78rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 999px;
            border: 1px solid rgba(201,169,110,.25);
            margin-bottom: 1.5rem;
        }
        .hero h1 {
            color: var(--white);
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            line-height: 1.15;
            margin-bottom: 1.25rem;
        }
        .hero h1 em {
            font-style: normal;
            color: var(--accent);
        }
        .hero-sub {
            color: #94a3b8;
            font-size: 1.05rem;
            line-height: 1.7;
            margin-bottom: 2rem;
            max-width: 480px;
        }
        .hero-actions { display: flex; gap: 1rem; flex-wrap: wrap; }
        .hero-img {
            border-radius: var(--radius-lg);
            overflow: hidden;
            height: 420px;
            position: relative;
        }
        .hero-img img {
            width: 100%; height: 100%;
            object-fit: cover;
            filter: brightness(.85);
        }
        .hero-img-badge {
            position: absolute;
            bottom: 1.25rem; left: 1.25rem;
            background: rgba(15,23,42,.85);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: var(--radius-md);
            padding: .75rem 1rem;
            color: var(--white);
        }
        .hero-img-badge .badge-title { font-weight: 600; font-size: .9rem; }
        .hero-img-badge .badge-sub   { font-size: .78rem; color: #94a3b8; }

        /* ── Stats bar ── */
        .stats-bar {
            background: var(--navy-mid);
            border-top: 1px solid #1e293b;
            border-bottom: 1px solid #1e293b;
            padding: 1.5rem 2rem;
        }
        .stats-bar-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .stat-item { text-align: center; }
        .stat-item .num {
            font-family: var(--font-display);
            font-size: 1.8rem;
            color: var(--white);
            line-height: 1;
        }
        .stat-item .lbl {
            font-size: .78rem;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .06em;
            margin-top: 3px;
        }

        /* ── Section ── */
        .section { padding: 5rem 2rem; }
        .section-inner { max-width: 1280px; margin: 0 auto; }
        .section-head { margin-bottom: 2.5rem; }
        .section-head h2 { color: var(--navy); }
        .section-head .section-top {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 1rem;
        }

        /* ── How it works ── */
        .how-section { background: var(--navy); padding: 5rem 2rem; }
        .steps {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin-top: 2.5rem;
        }
        .step {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: var(--radius-md);
            padding: 2rem;
        }
        .step-num {
            font-family: var(--font-display);
            font-size: 2.5rem;
            color: rgba(201,169,110,.3);
            line-height: 1;
            margin-bottom: .75rem;
        }
        .step h3 { color: var(--white); margin-bottom: .5rem; }
        .step p  { color: #64748b; font-size: .9rem; }

        /* ── Testimonials ── */
        .testimonials {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.5rem;
        }
        .testimonial {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.75rem;
            box-shadow: var(--shadow-sm);
        }
        .testimonial-text {
            font-size: .9rem;
            color: var(--text-muted);
            line-height: 1.7;
            margin-bottom: 1.25rem;
            font-style: italic;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .testimonial-avatar {
            width: 44px; height: 44px;
            border-radius: 50%;
            object-fit: cover;
            background: var(--navy);
        }
        .testimonial-name { font-weight: 600; font-size: .875rem; color: var(--navy); }
        .testimonial-role { font-size: .78rem; color: var(--text-muted); }

        /* ── Cities ── */
        .cities-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }
        .city-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all .2s;
            box-shadow: var(--shadow-sm);
        }
        .city-card:hover {
            border-color: var(--accent);
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }
        .city-flag { font-size: 2rem; margin-bottom: .5rem; }
        .city-name { font-weight: 600; color: var(--navy); font-size: .95rem; }
        .city-count { font-size: .78rem; color: var(--text-muted); margin-top: .2rem; }

        /* ── CTA banner ── */
        .cta-section {
            background: var(--navy);
            padding: 5rem 2rem;
            text-align: center;
        }
        .cta-section h2 { color: var(--white); margin-bottom: .75rem; }
        .cta-section p  { color: #64748b; margin-bottom: 2rem; font-size: 1rem; }

        /* ── Responsive ── */
        @media (max-width: 900px) {
            .hero-inner { grid-template-columns: 1fr; }
            .hero-img   { height: 280px; }
            .steps, .testimonials { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .cities-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>

<?php require_once __DIR__ . '/includes/navbar.php'; ?>

<!-- ── HERO ── -->
<section class="hero">
    <div class="hero-inner">
        <div>
            <div class="hero-label">✦ 500+ spaces · 48 cities</div>
            <h1>Work from anywhere.<br><em>Belong</em> everywhere.</h1>
            <p class="hero-sub">NomadNest connects freelancers, founders and teams with hand-picked co-working spaces — and the people who make them tick.</p>
            <div class="hero-actions">
                <a href="/nomadnest/pages/spaces.php" class="btn btn-primary btn-lg">Find a space</a>
                <a href="/nomadnest/pages/auth.php"   class="btn btn-secondary btn-lg">List your space</a>
            </div>
        </div>
        <div class="hero-img">
            <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80" alt="Co-working space">
            <div class="hero-img-badge">
                <div class="badge-title">📍 Casa Mareta · Lisbon</div>
                <div class="badge-sub">⭐ 4.9 · Booked just now</div>
            </div>
        </div>
    </div>
</section>

<!-- ── STATS BAR ── -->
<div class="stats-bar">
    <div class="stats-bar-inner">
        <div class="stat-item"><div class="num">500+</div><div class="lbl">Spaces</div></div>
        <div class="stat-item"><div class="num">12,000+</div><div class="lbl">Members</div></div>
        <div class="stat-item"><div class="num">48</div><div class="lbl">Cities</div></div>
        <div class="stat-item"><div class="num">98%</div><div class="lbl">Happy nomads</div></div>
    </div>
</div>

<!-- ── FEATURED SPACES ── -->
<section class="section" style="background: var(--cream);">
    <div class="section-inner">
        <div class="section-head">
            <div class="section-top">
                <div>
                    <div class="section-label">// Featured</div>
                    <h2>Spaces we love this week</h2>
                </div>
                <a href="/nomadnest/pages/spaces.php" class="btn btn-ghost">View all →</a>
            </div>
        </div>

        <div class="spaces-grid">
            <?php foreach ($spaces as $space):
                $amenities = $space['amenities'] ? explode(',', $space['amenities']) : [];
                $statusClass = ['available'=>'badge-green','limited'=>'badge-amber','full'=>'badge-red'][$space['availability_status']] ?? 'badge-green';
                $amenityHtml = implode('', array_map(fn($a) => "<span class='amenity-pill'>$a</span>", array_slice($amenities, 0, 3)));
            ?>
            <div class="space-card" onclick="window.location='/nomadnest/pages/space.php?id=<?= $space['id'] ?>'" style="cursor:pointer;">
                <div class="space-card-img">
                    <?php if ($space['image']): ?>
                        <img src="<?= htmlspecialchars($space['image']) ?>" alt="<?= htmlspecialchars($space['name']) ?>">
                    <?php endif; ?>
                </div>
                <div class="space-card-body">
                    <div class="space-card-top">
                        <span class="space-card-name"><?= htmlspecialchars($space['name']) ?></span>
                        <span class="space-card-rating"><?= $space['rating'] ?></span>
                    </div>
                    <div class="space-card-location">📍 <?= htmlspecialchars($space['city']) ?></div>
                    <div class="space-card-amenities"><?= $amenityHtml ?></div>
                    <div class="space-card-footer">
                        <span class="space-card-price"><?= $space['price_per_day'] ?> DT <span>/ day</span></span>
                        <span class="badge <?= $statusClass ?>"><?= $space['availability_status'] ?></span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section class="how-section">
    <div class="section-inner">
        <div class="section-label">// How it works</div>
        <h2 style="color:var(--white);">Three steps to your next great workday.</h2>
        <div class="steps">
            <div class="step">
                <div class="step-num">01</div>
                <h3>Search</h3>
                <p>Filter by city, vibe, amenities or whether they have oat milk.</p>
            </div>
            <div class="step">
                <div class="step-num">02</div>
                <h3>Book</h3>
                <p>Real-time availability. Pay by the hour, day or month.</p>
            </div>
            <div class="step">
                <div class="step-num">03</div>
                <h3>Work</h3>
                <p>Show up, plug in, meet people. Repeat in a new city tomorrow.</p>
            </div>
        </div>
    </div>
</section>

<!-- ── TESTIMONIALS ── -->
<section class="section" style="background:var(--white);">
    <div class="section-inner">
        <div class="section-label">// Members</div>
        <h2 style="margin-bottom:2rem;">Loved by people who refuse to commute.</h2>
        <div class="testimonials">
            <div class="testimonial">
                <p class="testimonial-text">"I tried six co-workings in Paris before NomadNest. Now I just open the app and pick my mood for the day."</p>
                <div class="testimonial-author">
                    <img class="testimonial-avatar" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop" alt="Léa">
                    <div>
                        <div class="testimonial-name">Léa Moreau</div>
                        <div class="testimonial-role">Product Designer</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <p class="testimonial-text">"Booked a meeting room in Berlin from a train. Thirty seconds. Coffee was hot when I arrived."</p>
                <div class="testimonial-author">
                    <img class="testimonial-avatar" src="https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=100&h=100&fit=crop" alt="Daniel">
                    <div>
                        <div class="testimonial-name">Daniel Okafor</div>
                        <div class="testimonial-role">Indie Founder</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                <p class="testimonial-text">"The community side is the killer feature. Met two of my collaborators through the member directory."</p>
                <div class="testimonial-author">
                    <img class="testimonial-avatar" src="https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=100&h=100&fit=crop" alt="Priya">
                    <div>
                        <div class="testimonial-name">Priya Shah</div>
                        <div class="testimonial-role">ML Engineer</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ── CITIES ── -->
<section class="section" style="background:var(--cream);">
    <div class="section-inner">
        <div class="section-label">// Cities</div>
        <h2 style="margin-bottom:2rem;">Where we're set up.</h2>
        <div class="cities-grid">
            <?php
            $cities = [
                ['flag'=>'🇫🇷','name'=>'Paris',       'count'=>42],
                ['flag'=>'🇩🇪','name'=>'Berlin',      'count'=>38],
                ['flag'=>'🇵🇹','name'=>'Lisbon',      'count'=>24],
                ['flag'=>'🇯🇵','name'=>'Tokyo',       'count'=>51],
                ['flag'=>'🇺🇸','name'=>'New York',    'count'=>67],
                ['flag'=>'🇲🇽','name'=>'Mexico City', 'count'=>19],
            ];
            foreach ($cities as $city): ?>
            <div class="city-card" onclick="window.location='/nomadnest/pages/spaces.php?city=<?= urlencode($city['name']) ?>'">
                <div class="city-flag"><?= $city['flag'] ?></div>
                <div class="city-name"><?= $city['name'] ?></div>
                <div class="city-count"><?= $city['count'] ?> spaces</div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
    <div class="section-label">// Join us</div>
    <h2>Join NomadNest today.</h2>
    <p>Your first day is on us. Pick any space, anywhere.</p>
    <a href="/nomadnest/pages/auth.php" class="btn btn-primary btn-lg">Create free account</a>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
