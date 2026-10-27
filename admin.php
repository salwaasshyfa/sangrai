<?php
session_start();
require __DIR__ . '/config.php';
require __DIR__ . '/db.php';

$adminPass = getenv('ADMIN_PASSWORD') ?: 'admin123'; // ganti lewat App Settings di Azure
$loginErr = '';

if (isset($_GET['logout'])) { session_destroy(); header('Location: admin.php'); exit; }

if (isset($_POST['login'])) {
    if (hash_equals($adminPass, (string)($_POST['password'] ?? ''))) {
        session_regenerate_id(true);
        $_SESSION['ok'] = true;
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        header('Location: admin.php'); exit;
    }
    $loginErr = 'Password salah.';
}

if (empty($_SESSION['ok'])) {
    include __DIR__ . '/includes/header.php'; ?>
    <main class="section">
      <h2>Masuk admin</h2>
      <?php if ($loginErr): ?><p class="alert err" role="alert"><?= e($loginErr) ?></p><?php endif; ?>
      <form method="post" class="form">
        <label>Password <input type="password" name="password" required autofocus></label>
        <button class="btn" name="login" value="1">Masuk</button>
      </form>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; exit;
}

$errors = [];
$flash = $_SESSION['flash'] ?? ''; unset($_SESSION['flash']);
$edit = ['id' => 0, 'name' => '', 'origin' => '', 'roast' => 3, 'notes' => '', 'price' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!hash_equals($_SESSION['csrf'], (string)($_POST['csrf'] ?? ''))) { http_response_code(400); exit('Token tidak valid.'); }

    if ($_POST['action'] === 'delete') {                                   // DELETE
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([(int)$_POST['id']]);
        $_SESSION['flash'] = 'Produk dihapus.';
        header('Location: admin.php'); exit;
    }

    if ($_POST['action'] === 'save') {
        $edit = [
            'id'     => (int)($_POST['id'] ?? 0),
            'name'   => trim((string)($_POST['name'] ?? '')),
            'origin' => trim((string)($_POST['origin'] ?? '')),
            'roast'  => (int)($_POST['roast'] ?? 0),
            'notes'  => trim((string)($_POST['notes'] ?? '')),
            'price'  => trim((string)($_POST['price'] ?? '')),
        ];
        if ($edit['name'] === '')   $errors['name'] = 'Nama wajib diisi.';
        if ($edit['origin'] === '') $errors['origin'] = 'Asal wajib diisi.';
        if ($edit['notes'] === '')  $errors['notes'] = 'Catatan rasa wajib diisi.';
        if (!isset($roastLabels[$edit['roast']])) $errors['roast'] = 'Pilih tingkat sangrai.';
        if (!ctype_digit($edit['price'])) $errors['price'] = 'Harga harus berupa angka bulat.';

        if (!$errors) {
            $data = [$edit['name'], $edit['origin'], $edit['roast'], $edit['notes'], (int)$edit['price']];
            if ($edit['id']) {                                             // UPDATE
                db()->prepare('UPDATE products SET name=?, origin=?, roast=?, notes=?, price=? WHERE id=?')
                    ->execute([...$data, $edit['id']]);
                $_SESSION['flash'] = 'Produk diperbarui.';
            } else {                                                       // CREATE
                db()->prepare('INSERT INTO products (name, origin, roast, notes, price) VALUES (?,?,?,?,?)')
                    ->execute($data);
                $_SESSION['flash'] = 'Produk ditambahkan.';
            }
            header('Location: admin.php'); exit;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit'])) {
    $st = db()->prepare('SELECT * FROM products WHERE id = ?');
    $st->execute([(int)$_GET['edit']]);
    if ($row = $st->fetch()) $edit = $row;
}

$rows = db()->query('SELECT * FROM products ORDER BY id')->fetchAll();   // READ
$msgCount = (int)db()->query('SELECT COUNT(*) FROM messages')->fetchColumn();
include __DIR__ . '/includes/header.php';
?>
<main class="section">
  <h2>Kelola produk</h2>
  <p class="meta"><?= count($rows) ?> produk, <?= $msgCount ?> pesan masuk. <a href="admin.php?logout=1">Keluar</a></p>
  <?php if ($flash): ?><p class="alert ok" role="status"><?= e($flash) ?></p><?php endif; ?>

  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>Nama</th><th>Asal</th><th>Sangrai</th><th>Harga</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= e($r['name']) ?></td>
          <td><?= e($r['origin']) ?></td>
          <td><?= e($roastLabels[(int)$r['roast']] ?? '-') ?></td>
          <td><?= rupiah((int)$r['price']) ?></td>
          <td class="actions">
            <a href="admin.php?edit=<?= (int)$r['id'] ?>#form">Ubah</a>
            <form method="post" onsubmit="return confirm('Hapus produk ini?')">
              <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
              <button class="link-btn">Hapus</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5">Belum ada produk. Tambahkan lewat form di bawah.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>

  <h3 id="form" style="margin:2.5rem 0 1rem"><?= $edit['id'] ? 'Ubah produk' : 'Tambah produk' ?></h3>
  <form method="post" class="form" novalidate>
    <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= (int)$edit['id'] ?>">
    <label>Nama <input name="name" value="<?= e((string)$edit['name']) ?>">
      <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?></label>
    <label>Asal <input name="origin" value="<?= e((string)$edit['origin']) ?>">
      <?php if (isset($errors['origin'])): ?><span class="err"><?= e($errors['origin']) ?></span><?php endif; ?></label>
    <label>Tingkat sangrai
      <select name="roast">
        <?php foreach ($roastLabels as $k => $label): ?>
          <option value="<?= $k ?>" <?= (int)$edit['roast'] === $k ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select></label>
    <label>Catatan rasa <input name="notes" value="<?= e((string)$edit['notes']) ?>">
      <?php if (isset($errors['notes'])): ?><span class="err"><?= e($errors['notes']) ?></span><?php endif; ?></label>
    <label>Harga (Rp) <input name="price" inputmode="numeric" value="<?= e((string)$edit['price']) ?>">
      <?php if (isset($errors['price'])): ?><span class="err"><?= e($errors['price']) ?></span><?php endif; ?></label>
    <div>
      <button class="btn" type="submit"><?= $edit['id'] ? 'Simpan perubahan' : 'Tambah produk' ?></button>
      <?php if ($edit['id']): ?> <a href="admin.php">Batal</a><?php endif; ?>
    </div>
  </form>
</main>
<?php include __DIR__ . '/includes/footer.php'; ?>
