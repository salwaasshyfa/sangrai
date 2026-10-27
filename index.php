<?php
require __DIR__ . '/config.php';

// --- Proses form kontak ---
$errors = [];
$sent = false;
$old = ['name' => '', 'email' => '', 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($old as $k => $_) {
        $old[$k] = trim((string)($_POST[$k] ?? ''));
    }
    if (!empty($_POST['website'])) { exit; } // honeypot anti-bot

    if ($old['name'] === '')                              $errors['name'] = 'Nama wajib diisi.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Format email belum benar.';
    if (mb_strlen($old['message']) < 10)                  $errors['message'] = 'Pesan minimal 10 karakter.';

    if (!$errors) {
        $fh = fopen(__DIR__ . '/storage/messages.csv', 'a');
        if ($fh) {
            fputcsv($fh, [date('c'), $old['name'], $old['email'], $old['message']]);
            fclose($fh);
            $sent = true;
            $old = ['name' => '', 'email' => '', 'message' => ''];
        } else {
            $errors['form'] = 'Pesan gagal disimpan. Coba lagi sebentar.';
        }
    }
}

include __DIR__ . '/includes/header.php';
?>
<main id="top">
  <section class="hero">
    <h1><?= e(SITE_TAGLINE) ?></h1>
    <p>Single origin dari petani Indonesia. Pilih tingkat sangrai, kami kirim kopinya.</p>
    <a href="#produk" class="btn">Lihat kopi</a>
    <div class="roast-bar" role="img" aria-label="Skala tingkat sangrai dari light sampai dark">
      <?php foreach ($roastLabels as $i => $label): ?>
        <span class="r<?= $i ?>"><?= e($label) ?></span>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="produk" class="section">
    <h2>Kopi minggu ini</h2>
    <div class="grid">
      <?php foreach ($products as $p): ?>
        <article class="card">
          <div class="swatch r<?= $p['roast'] ?>"></div>
          <h3><?= e($p['name']) ?></h3>
          <p class="meta"><?= e($p['origin']) ?>, <?= e($roastLabels[$p['roast']]) ?></p>
          <p><?= e($p['notes']) ?></p>
          <p class="price"><?= rupiah($p['price']) ?> <small>/ 200 g</small></p>
        </article>
      <?php endforeach; ?>
    </div>
  </section>

  <section id="proses" class="section alt">
    <h2>Dari kebun ke cangkir</h2>
    <ol class="steps">
      <?php foreach ($steps as [$title, $text]): ?>
        <li><h3><?= e($title) ?></h3><p><?= e($text) ?></p></li>
      <?php endforeach; ?>
    </ol>
  </section>

  <section id="kontak" class="section">
    <h2>Pesan atau tanya dulu</h2>
    <?php if ($sent): ?>
      <p class="alert ok" role="status">Pesan terkirim. Kami balas lewat email dalam 1 hari kerja.</p>
    <?php endif; ?>
    <?php if (isset($errors['form'])): ?>
      <p class="alert err" role="alert"><?= e($errors['form']) ?></p>
    <?php endif; ?>

    <form method="post" action="#kontak" class="form" novalidate>
      <label>Nama
        <input name="name" value="<?= e($old['name']) ?>" required>
        <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?>
      </label>
      <label>Email
        <input type="email" name="email" value="<?= e($old['email']) ?>" required>
        <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>
      </label>
      <label>Pesan
        <textarea name="message" rows="4" required><?= e($old['message']) ?></textarea>
        <?php if (isset($errors['message'])): ?><span class="err"><?= e($errors['message']) ?></span><?php endif; ?>
      </label>
      <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">
      <button class="btn" type="submit">Kirim pesan</button>
    </form>
  </section>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
