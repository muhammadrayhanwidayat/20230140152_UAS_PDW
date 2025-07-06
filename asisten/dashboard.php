<?php

session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../index.php");
    exit();
}


require_once '../config.php';

$pageTitle = 'Dashboard - Assistant Panel';
$activePage = 'dashboard';
require_once 'templates/header.php';


try {
    // Total Modul
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_modul FROM modul");
    $stmt->execute();
    $total_modul = (int)$stmt->get_result()->fetch_assoc()['total_modul'];
    $stmt->close();

    // Total Laporan Masuk
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_laporan FROM laporan_praktikum");
    $stmt->execute();
    $total_laporan = (int)$stmt->get_result()->fetch_assoc()['total_laporan'];
    $stmt->close();

    // Total Belum Dinilai
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_belum FROM laporan_praktikum WHERE nilai IS NULL");
    $stmt->execute();
    $total_belum = (int)$stmt->get_result()->fetch_assoc()['total_belum'];
    $stmt->close();

    // Aktivitas Laporan Terbaru (5 terbaru)
    $stmt = $conn->prepare(
        "SELECT lp.tanggal_kumpul, u.nama AS nama_mahasiswa, m.nama_modul
         FROM laporan_praktikum lp
         JOIN users u ON lp.id_mahasiswa = u.id
         JOIN modul m ON lp.id_modul = m.id
         ORDER BY lp.tanggal_kumpul DESC
         LIMIT 5"
    );
    $stmt->execute();
    $recent = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (Exception $e) {
    // Tangani error
    $total_modul = $total_laporan = $total_belum = 0;
    $recent = [];
}
?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Total Modul -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-blue-100 p-3 rounded-full">
            <!-- icon modul -->
            <svg class="w-6 h-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Modul Diajarkan</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_modul; ?></p>
        </div>
    </div>

    <!-- Total Laporan -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-green-100 p-3 rounded-full">
            <!-- icon laporan -->
            <svg class="w-6 h-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Total Laporan Masuk</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_laporan; ?></p>
        </div>
    </div>

    <!-- Belum Dinilai -->
    <div class="bg-white p-6 rounded-lg shadow-md flex items-center space-x-4">
        <div class="bg-yellow-100 p-3 rounded-full">
            <!-- icon belum dinilai -->
            <svg class="w-6 h-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm text-gray-500">Laporan Belum Dinilai</p>
            <p class="text-2xl font-bold text-gray-800"><?php echo $total_belum; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mt-8">
    <h3 class="text-xl font-bold text-gray-800 mb-4">Aktivitas Laporan Terbaru</h3>
    <div class="space-y-4">
        <?php if (!empty($recent)): ?>
            <?php foreach ($recent as $item): ?>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center mr-4">
                        <?php echo strtoupper(substr(htmlspecialchars($item['nama_mahasiswa']), 0, 2)); ?>
                    </div>
                    <div>
                        <p class="text-gray-800"><strong><?php echo htmlspecialchars($item['nama_mahasiswa']); ?></strong> mengumpulkan laporan untuk <strong><?php echo htmlspecialchars($item['nama_modul']); ?></strong></p>
                        <p class="text-sm text-gray-500"><?php echo date('d M Y, H:i', strtotime($item['tanggal_kumpul'])); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-gray-500">Belum ada aktivitas laporan.</p>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
