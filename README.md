<?php
require_once __DIR__ . "/includes/koneksi.php";
require_once __DIR__ . "/includes/functions.php";

$title = "Foodie - Kumpulan Resep";

$recommended = $conn->query("
  SELECT r.*,
    COALESCE(AVG(ra.rating),0) avg_rating,
    COUNT(ra.id) rating_count,
    u.username
  FROM recipes r
  JOIN users u ON u.id=r.user_id
  LEFT JOIN ratings ra ON ra.recipe_id=r.id
  WHERE r.status='approved'
  GROUP BY r.id
  ORDER BY avg_rating DESC, rating_count DESC, r.created_at DESC
  LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$daily = $conn->query("
  SELECT r.*,
    COALESCE(AVG(ra.rating),0) avg_rating,
    COUNT(ra.id) rating_count,
    u.username
  FROM recipes r
  JOIN users u ON u.id=r.user_id
  LEFT JOIN ratings ra ON ra.recipe_id=r.id
  WHERE r.status='approved'
  GROUP BY r.id
  ORDER BY CRC32(CONCAT(r.id, CURDATE()))
  LIMIT 6
")->fetch_all(MYSQLI_ASSOC);

$latest = $conn->query("
  SELECT r.*,
    COALESCE(AVG(ra.rating),0) avg_rating,
    COUNT(ra.id) rating_count,
    u.username
  FROM recipes r
  JOIN users u ON u.id=r.user_id
  LEFT JOIN ratings ra ON ra.recipe_id=r.id
  WHERE r.status='approved'
  GROUP BY r.id
  ORDER BY r.created_at DESC
  LIMIT 9
")->fetch_all(MYSQLI_ASSOC);

include __DIR__ . "/includes/header.php";
?>


<div class="section-title">
  <h2>⭐ Recommended</h2>
</div>
<div class="carousel" id="recommendedCarousel">
  <div class="carousel-track" id="recommendedTrack">
    <?php foreach ($recommended as $r): ?>
      <a class="card carousel-card" href="/ProyekWeb/recipe.php?id=<?= (int)$r['id'] ?>">
        <img class="thumb" src="<?= h($r['photo_path'] ?: '/ProyekWeb/assets/placeholder.png') ?>" alt="Foto resep">
        <div class="card-body">
          <h3><?= h($r['title']) ?></h3>
          <div class="muted small"><?= h($r['category']) ?> • <?= (int)$r['cook_time'] ?> menit • oleh <?= h($r['username']) ?></div>
          <div class="badges">
            <span class="badge blue">★ <?= number_format((float)$r['avg_rating'], 1) ?> (<?= (int)$r['rating_count'] ?>)</span>
            <span class="badge"><?= h($r['difficulty']) ?></span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
</div>


<div class="section-title">
  <h2>🍱 Menu Harian</h2>
</div>
<div class="grid">
  <?php foreach ($daily as $r): ?>
    <a class="card" href="/ProyekWeb/recipe.php?id=<?= (int)$r['id'] ?>">
      <img class="thumb" src="<?= h($r['photo_path'] ?: '/ProyekWeb/assets/placeholder.png') ?>" alt="Foto resep">
      <div class="card-body">
        <h3><?= h($r['title']) ?></h3>
        <div class="muted small"><?= h($r['category']) ?> • <?= (int)$r['cook_time'] ?> menit</div>
        <div class="badges">
          <span class="badge green">Random harian</span>
          <span class="badge blue">★ <?= number_format((float)$r['avg_rating'], 1) ?></span>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<div class="section-title">
  <h2>🆕 Resep Terbaru</h2>
</div>
<div class="grid">
  <?php foreach ($latest as $r): ?>
    <a class="card" href="/ProyekWeb/recipe.php?id=<?= (int)$r['id'] ?>">
      <img class="thumb" src="<?= h($r['photo_path'] ?: '/ProyekWeb/assets/placeholder.png') ?>" alt="Foto resep">
      <div class="card-body">
        <h3><?= h($r['title']) ?></h3>
        <div class="muted small"><?= h($r['category']) ?> • <?= (int)$r['cook_time'] ?> menit</div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const track = document.getElementById("recommendedTrack");
  const wrap  = document.getElementById("recommendedCarousel");
  if (!track || !wrap) return;

  const cards = () => Array.from(track.querySelectorAll(".carousel-card"));
  let i = 0;
  let timer = null;
  const intervalMs = 3000; // 3 detik

  function scrollToIndex(idx){
    const list = cards();
    if (!list.length) return;
    i = (idx + list.length) % list.length;
    list[i].scrollIntoView({ behavior: "smooth", inline: "start", block: "nearest" });
  }

  function next(){
    scrollToIndex(i + 1);
  }

  function start(){
    stop();
    timer = setInterval(next, intervalMs);
  }

  function stop(){
    if (timer) clearInterval(timer);
    timer = null;
  }

  // mulai
  start();

  // pause kalau user interaksi
  wrap.addEventListener("mouseenter", stop);
  wrap.addEventListener("mouseleave", start);
  wrap.addEventListener("touchstart", stop, {passive:true});
  wrap.addEventListener("touchend", start, {passive:true});

  // kalau user scroll manual, update index aktif
  let raf = null;
  track.addEventListener("scroll", () => {
    if (raf) cancelAnimationFrame(raf);
    raf = requestAnimationFrame(() => {
      const list = cards();
      if (!list.length) return;
      const left = track.scrollLeft;
      // cari card yang paling dekat dengan posisi scrollLeft
      let best = 0, bestDist = Infinity;
      list.forEach((c, idx) => {
        const dist = Math.abs(c.offsetLeft - left);
        if (dist < bestDist) { bestDist = dist; best = idx; }
      });
      i = best;
    });
  });
});
</script>


<?php include __DIR__ . "/includes/footer.php"; ?>
