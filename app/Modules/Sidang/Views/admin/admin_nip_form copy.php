<!DOCTYPE html>
<html lang="id">
<head>
    <title><?= $title ?></title>
    <!-- Tambahkan CSS untuk tampilan admin panel Tron Anda di sini -->
    <style>
        .container { max-width: 900px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; font-size: 14px; }
        .active { color: green; font-weight: bold; }
        .inactive { color: orange; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= $title ?></h1>
        
        <?php if (session()->getFlashdata('error')): ?>
            <div style="color: red; border: 1px solid red; padding: 10px;"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('success')): ?>
            <div style="color: green; border: 1px solid green; padding: 10px;"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>

        <!-- Form Tambah/Edit NIP -->
        <h2>Tambah/Setup NIP Baru</h2>
        <form action="<?= site_url('admin/sidang/save-nip') ?>" method="post">
                    <!-- ⬇️ FIX CSRF TOKEN WAJIB ⬇️ -->
            <?= csrf_field() ?>
            <label for="nip">NIP Pegawai:</label>
            <input type="text" id="nip" name="nip" required><br><br>
            
            <label for="nama">Nama Pegawai:</label>
            <input type="text" id="nama" name="nama" required><br><br>
            
            <button type="submit" style="background-color: blue; color: white; padding: 10px;">Simpan/Update NIP</button>
        </form>

        <h2 style="margin-top: 30px;">Daftar NIP Akses Sidang</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>NIP</th>
                    <th>Nama Pegawai</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list_admins as $admin): ?>
                <tr>
                    <td><?= $admin['id'] ?></td>
                    <td><?= $admin['nip'] ?></td>
                    <td><?= $admin['nama_pegawai'] ?></td>
                    <td>
                        <?php if ($admin['sidang_2fa_secret'] && $admin['is_active'] == 1): ?>
                            <span class="active">Aktif</span>
                        <?php else: ?>
                            <span class="inactive">Belum Konfigurasi</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <!-- Tombol untuk generate QR Code -->
                        <a href="<?= site_url('admin/sidang/generate/' . $admin['id']) ?>" 
                           style="background-color: orange; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none;">
                           Generate QR Code
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>