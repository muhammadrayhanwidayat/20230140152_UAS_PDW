<?php
$pageTitle = 'Report Penilaian - Assistant Panel';
$activePage = 'laporan'; 
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../index.php");
    exit();
}

$filter_praktikum_id = isset($_GET['filter_praktikum']) ? (int)$_GET['filter_praktikum'] : null;
$filter_modul_id = isset($_GET['filter_modul']) ? (int)$_GET['filter_modul'] : null;
$filter_mahasiswa_id = isset($_GET['filter_mahasiswa']) ? (int)$_GET['filter_mahasiswa'] : null;
$filter_status = isset($_GET['filter_status']) ? $_GET['filter_status'] : null; 

$praktikum_list_filter = [];
$result_praktikum_filter = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC");
if ($result_praktikum_filter) {
    while ($row = $result_praktikum_filter->fetch_assoc()) {
        $praktikum_list_filter[] = $row;
    }
}

$modul_list_filter = [];
if ($filter_praktikum_id) {
    $stmt_modul_filter = $conn->prepare("SELECT id, nama_modul FROM modul WHERE id_praktikum = ? ORDER BY nama_modul ASC");
    $stmt_modul_filter->bind_param("i", $filter_praktikum_id);
    $stmt_modul_filter->execute();
    $result_modul_filter = $stmt_modul_filter->get_result();
    if ($result_modul_filter) {
        while ($row = $result_modul_filter->fetch_assoc()) {
            $modul_list_filter[] = $row;
        }
    }
    $stmt_modul_filter->close();
}


$mahasiswa_list_filter = [];
$result_mahasiswa_filter = $conn->query(
    "SELECT DISTINCT u.id, u.nama
     FROM users u
     JOIN laporan_praktikum lp ON u.id = lp.id_mahasiswa
     WHERE u.role = 'mahasiswa' ORDER BY u.nama ASC"
);
if ($result_mahasiswa_filter) {
    while ($row = $result_mahasiswa_filter->fetch_assoc()) {
        $mahasiswa_list_filter[] = $row;
    }
}


$sql = "SELECT lp.id, lp.tanggal_kumpul, lp.nilai, lp.tanggal_dinilai,
               u.nama AS nama_mahasiswa, u.email AS email_mahasiswa,
               m.nama_modul,
               mp.nama_praktikum
        FROM laporan_praktikum lp
        JOIN users u ON lp.id_mahasiswa = u.id
        JOIN modul m ON lp.id_modul = m.id
        JOIN mata_praktikum mp ON m.id_praktikum = mp.id
        WHERE 1=1";

$params = [];
$types = "";

if ($filter_praktikum_id) {
    $sql .= " AND mp.id = ?";
    $params[] = $filter_praktikum_id;
    $types .= "i";
}
if ($filter_modul_id) {
    $sql .= " AND m.id = ?";
    $params[] = $filter_modul_id;
    $types .= "i";
}
if ($filter_mahasiswa_id) {
    $sql .= " AND u.id = ?";
    $params[] = $filter_mahasiswa_id;
    $types .= "i";
}
if ($filter_status === 'dinilai') {
    $sql .= " AND lp.nilai IS NOT NULL";
} elseif ($filter_status === 'belum_dinilai') {
    $sql .= " AND lp.nilai IS NULL";
}

$sql .= " ORDER BY lp.tanggal_kumpul DESC";

$stmt_laporan = $conn->prepare($sql);
if (!empty($params)) {
    $stmt_laporan->bind_param($types, ...$params);
}
$stmt_laporan->execute();
$result_laporan = $stmt_laporan->get_result();
$laporan_list = [];
if ($result_laporan && $result_laporan->num_rows > 0) {
    while ($row = $result_laporan->fetch_assoc()) {
        $laporan_list[] = $row;
    }
}
$stmt_laporan->close();

?>

<div class="container mx-auto px-4 py-8">

    <!-- Filter Form -->
    <form action="submitted_reports.php" method="get" class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label for="filter_praktikum" class="block text-sm font-medium text-gray-700">Lab Course:</label>
                <select name="filter_praktikum" id="filter_praktikum" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="this.form.submit()">
                    <option value="">All Courses</option>
                    <?php foreach ($praktikum_list_filter as $p): ?>
                        <option value="<?php echo $p['id']; ?>" <?php echo ($filter_praktikum_id == $p['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($p['nama_praktikum']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="filter_modul" class="block text-sm font-medium text-gray-700">Module:</label>
                <select name="filter_modul" id="filter_modul" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" <?php if (!$filter_praktikum_id && empty($modul_list_filter)) echo 'disabled'; ?>>
                    <option value="">All Modules</option>
                    <?php foreach ($modul_list_filter as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo ($filter_modul_id == $m['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['nama_modul']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                 <?php if (!$filter_praktikum_id && empty($modul_list_filter)): ?>
                    <p class="text-xs text-gray-500 mt-1">Select a lab course first to filter modules.</p>
                <?php endif; ?>
            </div>
            <div>
                <label for="filter_mahasiswa" class="block text-sm font-medium text-gray-700">Student:</label>
                <select name="filter_mahasiswa" id="filter_mahasiswa" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Students</option>
                     <?php foreach ($mahasiswa_list_filter as $mahasiswa): ?>
                        <option value="<?php echo $mahasiswa['id']; ?>" <?php echo ($filter_mahasiswa_id == $mahasiswa['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($mahasiswa['nama']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="filter_status" class="block text-sm font-medium text-gray-700">Grading Status:</label>
                <select name="filter_status" id="filter_status" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                    <option value="">All Status</option>
                    <option value="belum_dinilai" <?php echo ($filter_status == 'belum_dinilai') ? 'selected' : ''; ?>>Not Graded</option>
                    <option value="dinilai" <?php echo ($filter_status == 'dinilai') ? 'selected' : ''; ?>>Graded</option>
                </select>
            </div>
        </div>
        <div class="mt-4 flex justify-end space-x-2">
            <a href="submitted_reports.php" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Reset Filter</a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Filter</button>
        </div>
    </form>

    <!-- Report List -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Submitted Reports</h2>
        <?php if (!empty($laporan_list)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lab Course</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Submission Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($laporan_list as $laporan): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($laporan['nama_praktikum']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?php echo htmlspecialchars($laporan['nama_modul']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo htmlspecialchars($laporan['nama_mahasiswa']); ?><br>
                                    <span class="text-xs text-gray-500"><?php echo htmlspecialchars($laporan['email_mahasiswa']); ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo date('d M Y, H:i', strtotime($laporan['tanggal_kumpul'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <?php if ($laporan['nilai'] !== null): ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Graded (<?php echo htmlspecialchars($laporan['nilai']); ?>)
                                        </span>
                                        <br><span class="text-xs text-gray-500">Date: <?php echo date('d M Y, H:i', strtotime($laporan['tanggal_dinilai'])); ?></span>
                                    <?php else: ?>
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                            Not Graded
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="report_penilaian.php?id=<?php echo $laporan['id']; ?>" class="text-indigo-600 hover:text-indigo-900">
                                        <?php echo ($laporan['nilai'] !== null) ? 'View/Edit Grade' : 'Grade Report'; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No reports match the current filter, or no reports have been submitted yet.</p>
        <?php endif; ?>
    </div>
</div>
<script>
    document.getElementById('filter_praktikum').addEventListener('change', function() {
        var praktikumId = this.value;
        var modulSelect = document.getElementById('filter_modul');

        var currentModulValue = modulSelect.value;

        modulSelect.innerHTML = '<option value="">Loading modules...</option>'; 
        modulSelect.disabled = true;

        if (praktikumId) {
            fetch('ajax_get_modules.php?praktikum_id=' + praktikumId) 
                .then(response => response.json())
                .then(data => {
                    modulSelect.innerHTML = '<option value="">All Modules</option>';  
                    if (data.length > 0) {
                        data.forEach(function(modul) {
                            var option = document.createElement('option');
                            option.value = modul.id;
                            option.textContent = modul.nama_modul;
                            if (modul.id == currentModulValue) {
                                option.selected = true;
                            }
                            modulSelect.appendChild(option);
                        });
                        modulSelect.disabled = false;
                    } else {
                        modulSelect.innerHTML = '<option value="">Select Course First</option>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching modules:', error);
                    modulSelect.innerHTML = '<option value="">Failed to load modules</option>';
                });
        } else {
            modulSelect.innerHTML = '<option value="">Select Course First</option>';
        }
    });
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>