<?php
session_start();
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}

$id_laporan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$message_type = '';

if ($id_laporan <= 0) {
    echo "<div class='container mx-auto p-6'>Invalid Report ID. <a href='submitted_reports.php'>Back</a></div>";
    require_once 'templates/footer.php';
    exit();
}


$sql_laporan = "SELECT lp.*, u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
                       m.nama_modul, mp.nama_praktikum
                FROM laporan_praktikum lp
                JOIN users u ON lp.id_mahasiswa = u.id
                JOIN modul m ON lp.id_modul = m.id
                JOIN mata_praktikum mp ON m.id_praktikum = mp.id
                WHERE lp.id = ?";
$stmt_laporan = $conn->prepare($sql_laporan);
$stmt_laporan->bind_param("i", $id_laporan);
$stmt_laporan->execute();
$laporan = $stmt_laporan->get_result()->fetch_assoc();
$stmt_laporan->close();

if (!$laporan) {
    echo "<div class='container mx-auto p-6'>Report not found. <a href='submitted_reports.php'>Back</a></div>";
    $conn->close();
    exit();
}

// Judul halaman
$pageTitle = 'Grade: ' . htmlspecialchars($laporan['nama_modul']) . ' - ' . htmlspecialchars($laporan['nama_mahasiswa']);
echo "<script>document.querySelector('header h1').textContent = '" . addslashes($pageTitle) . "'; document.title = 'Assistant Panel - " . addslashes($pageTitle) . "';</script>";

// Handle POST simpan nilai
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['simpan_nilai'])) {
    $nilai = trim($_POST['nilai']);
    $feedback = trim($_POST['feedback']);

    if ($nilai !== '' && (!is_numeric($nilai) || $nilai < 0 || $nilai > 100)) {
        $message = "Grade must be a number between 0 and 100.";
        $message_type = 'error';
    } else {
        $tanggal_dinilai = ($nilai !== '') ? date("Y-m-d H:i:s") : null;
        if ($nilai === '') $nilai = null;

        $stmt_update = $conn->prepare(
            "UPDATE laporan_praktikum SET nilai = ?, feedback = ?, tanggal_dinilai = ? WHERE id = ?"
        );
        $stmt_update->bind_param("issi", $nilai, $feedback, $tanggal_dinilai, $id_laporan);
        if ($stmt_update->execute()) {
            $message = "Grade and feedback saved successfully.";
            $message_type = 'success';
            // refresh data
            $stmt_refresh = $conn->prepare($sql_laporan);
            $stmt_refresh->bind_param("i", $id_laporan);
            $stmt_refresh->execute();
            $laporan = $stmt_refresh->get_result()->fetch_assoc();
            $stmt_refresh->close();
        } else {
            $message = "Failed to save grade: " . $stmt_update->error;
            $message_type = 'error';
        }
        $stmt_update->close();
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="mb-4">
        <a href="submitted_reports.php" class="text-blue-600 hover:text-blue-800 hover:underline">&larr; Back to Submitted Reports</a>
    </div>
    <?php if (!empty($message)): ?>
        <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-lg rounded-lg p-6 md:p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500">Lab Course</h3>
                <p class="mt-1 text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Module</h3>
                <p class="mt-1 text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($laporan['nama_modul']); ?></p>
            </div>
            <div>
                <h3 class="text-sm font-medium text-gray-500">Student</h3>
                <p class="mt-1 text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?></p>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($laporan['email_mahasiswa']); ?></p>
            </div>
        </div>

        <form action="report_penilaian.php?id=<?php echo $id_laporan; ?>" method="post">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="nilai" class="block text-sm font-medium text-gray-700">Grade (0-100)</label>
                    <input type="number" name="nilai" id="nilai" min="0" max="100" step="1" value="<?php echo htmlspecialchars($laporan['nilai'] ?? ''); ?>" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div class="md:col-span-2">
                    <label for="feedback" class="block text-sm font-medium text-gray-700">Feedback</label>
                    <textarea id="feedback" name="feedback" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"><?php echo htmlspecialchars($laporan['feedback'] ?? ''); ?></textarea>
                </div>
            </div>
            <div class="mt-6 text-right">
                <button type="submit" name="simpan_nilai" class="inline-flex justify-center py-2 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save Grade
                </button>
            </div>
        </form>
    </div>
</div>