<?php
session_start();
$pageTitle   = 'Manajemen Modul - Assistant Panel';
require_once '../config.php';
require_once 'templates/header.php';

// Pastikan user asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../index.php");
    exit();
}

// Ambil daftar praktikum untuk dropdown
$praktikum_list = [];
$res = $conn->query("SELECT id, nama_praktikum FROM mata_praktikum ORDER BY nama_praktikum ASC");
while ($row = $res->fetch_assoc()) {
    $praktikum_list[] = $row;
}


$filter_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;

// Buat direktori upload jika belum ada
$upload_dir = '../uploads/materi/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambah Modul
    if (isset($_POST['tambah_modul'])) {
        $id_praktikum = (int)$_POST['id_praktikum'];
        $nama_modul = trim($_POST['nama_modul']);
        $file_name = null;
        $file_path = null;

        if ($id_praktikum > 0 && $nama_modul !== '') {
            if (!empty($_FILES['file_materi']['name'])) {
                $ext = pathinfo($_FILES['file_materi']['name'], PATHINFO_EXTENSION);
                $allowed = ['pdf','doc','docx','ppt','pptx','zip','rar'];
                if (in_array(strtolower($ext), $allowed)) {
                    $new_name = 'materi_' . $id_praktikum . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['file_materi']['tmp_name'], $upload_dir . $new_name)) {
                        $file_name = $_FILES['file_materi']['name'];
                        $file_path = 'uploads/materi/' . $new_name;
                    }
                }
            }
            $stmt = $conn->prepare("INSERT INTO modul (id_praktikum,nama_modul,nama_file_materi,path_file_materi) VALUES (?,?,?,?)");
            $stmt->bind_param("isss", $id_praktikum, $nama_modul, $file_name, $file_path);
            if ($stmt->execute()) {
                $message = 'Module added successfully.';
                $message_type = 'success';
            } else {
                $message = 'Error adding module: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = 'Please select a course and enter a module name.';
            $message_type = 'error';
        }
    }

    // Edit Modul
    if (isset($_POST['edit_modul'])) {
        $mid = (int)$_POST['id_modul_edit'];
        $new_praktikum = (int)$_POST['id_praktikum_edit'];
        $nama_modul = trim($_POST['nama_modul_edit']);
        $old_path = $_POST['path_lama'];
        $file_name = null;
        $file_path = $old_path;

        if ($mid > 0 && $new_praktikum > 0 && $nama_modul !== '') {
            if (!empty($_FILES['file_materi_edit']['name'])) {
                $ext = pathinfo($_FILES['file_materi_edit']['name'], PATHINFO_EXTENSION);
                $allowed = ['pdf','doc','docx','ppt','pptx','zip','rar'];
                if (in_array(strtolower($ext), $allowed)) {
                    $new_file = 'materi_' . $new_praktikum . '_' . time() . '.' . $ext;
                    if (move_uploaded_file($_FILES['file_materi_edit']['tmp_name'], $upload_dir . $new_file)) {
                        if ($old_path && file_exists('../' . $old_path)) {
                            unlink('../' . $old_path);
                        }
                        $file_name = $_FILES['file_materi_edit']['name'];
                        $file_path = 'uploads/materi/' . $new_file;
                    }
                }
            }
            $stmt = $conn->prepare("UPDATE modul SET id_praktikum=?, nama_modul=?, nama_file_materi=?, path_file_materi=? WHERE id=?");
            $stmt->bind_param("isssi", $new_praktikum, $nama_modul, $file_name, $file_path, $mid);
            if ($stmt->execute()) {
                $message = 'Module updated successfully.';
                $message_type = 'success';
            } else {
                $message = 'Error updating module: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        }
    }

    // Delete Modul
    if (isset($_POST['hapus_modul'])) {
        $mid = (int)$_POST['id_modul_hapus'];
        $path_hapus = $_POST['path_hapus'];
        $stmt = $conn->prepare("DELETE FROM modul WHERE id=?");
        $stmt->bind_param("i", $mid);
        if ($stmt->execute()) {
            if ($path_hapus && file_exists('../' . $path_hapus)) {
                unlink('../' . $path_hapus);
            }
            $message = 'Module deleted successfully.';
            $message_type = 'success';
        } else {
            $message = 'Error deleting module: ' . $stmt->error;
            $message_type = 'error';
        }
        $stmt->close();
    }
}

// Ambil list modul berdasarkan filter atau semua
if ($filter_id > 0) {
    $stmt = $conn->prepare("SELECT m.*, mp.nama_praktikum FROM modul m JOIN mata_praktikum mp ON m.id_praktikum=mp.id WHERE m.id_praktikum=? ORDER BY m.id");
    $stmt->bind_param("i", $filter_id);
} else {
    $stmt = $conn->prepare("SELECT m.*, mp.nama_praktikum FROM modul m JOIN mata_praktikum mp ON m.id_praktikum=mp.id ORDER BY mp.nama_praktikum, m.id");
}
$stmt->execute();
$mods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<div class="container mx-auto px-4 py-8">

    <!-- Filter Course -->
    <form method="get" class="mb-6">
        <label class="mr-2">Filter by Course:</label>
        <select name="course_id" onchange="this.form.submit()" class="border p-2 rounded">
            <option value="">All Courses</option>
            <?php foreach ($praktikum_list as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $filter_id === $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['nama_praktikum']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <?php if ($message): ?>
        <div class="p-4 mb-4 rounded <?= $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- Add New Module -->
    <div class="bg-white p-6 rounded shadow mb-8">
        <h2 class="font-semibold mb-4">Add New Module</h2>
        <form action="manajemen_modul.php?course_id=<?= $filter_id ?>" method="post" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="id_praktikum" class="block text-sm font-medium text-gray-700">Course <span class="text-red-500">*</span></label>
                <select name="id_praktikum" id="id_praktikum" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">-- Pilih Course --</option>
                    <?php foreach ($praktikum_list as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_praktikum']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="nama_modul" class="block text-sm font-medium text-gray-700">Module Name <span class="text-red-500">*</span></label>
                <input type="text" name="nama_modul" id="nama_modul" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
            </div>
            <div>
                <label for="file_materi" class="block text-sm font-medium text-gray-700">Material File</label>
                <input type="file" name="file_materi" id="file_materi" class="mt-1 block w-full text-sm">
            </div>
            <div class="text-right">
                <button type="submit" name="tambah_modul" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Add Module</button>
            </div>
        </form>
    </div>

    <!-- List Modules -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="font-semibold mb-4">Existing Modules</h2>
        <?php if (empty($mods)): ?>
            <p class="text-gray-500">No modules found.</p>
        <?php else: ?>
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">#</th>
                        <th class="px-4 py-2 text-left">Course</th>
                        <th class="px-4 py-2 text-left">Module Name</th>
                        <th class="px-4 py-2 text-left">File</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($mods as $index => $m): ?>
                    <tr>
                        <td class="px-4 py-2"><?= $index+1 ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($m['nama_praktikum']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($m['nama_modul']) ?></td>
                        <td class="px-4 py-2">
                            <?php if ($m['path_file_materi']): ?>
                                <a href="../<?= $m['path_file_materi'] ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($m['nama_file_materi']) ?></a>
                            <?php else: ?>
                                <span class="text-gray-500">No file</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-2 space-x-2">
                      
                            <details class="inline-block">
                                <summary class="cursor-pointer text-green-600 hover:underline">Edit</summary>
                                <form action="manajemen_modul.php?course_id=<?= $filter_id ?>" method="post" enctype="multipart/form-data" class="mt-2 space-y-2">
                                    <input type="hidden" name="id_modul_edit" value="<?= $m['id'] ?>">
                                    <input type="hidden" name="path_lama" value="<?= $m['path_file_materi'] ?>">
                                    <div>
                                        <label class="block text-sm font-medium">Course <span class="text-red-500">*</span></label>
                                        <select name="id_praktikum_edit" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                            <?php foreach ($praktikum_list as $p): ?>
                                                <option value="<?= $p['id'] ?>" <?= $m['id_praktikum']==$p['id']?'selected':'' ?>>
                                                    <?= htmlspecialchars($p['nama_praktikum']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Module Name <span class="text-red-500">*</span></label>
                                        <input type="text" name="nama_modul_edit" value="<?= htmlspecialchars($m['nama_modul']) ?>" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium">Replace File</label>
                                        <input type="file" name="file_materi_edit" class="mt-1 block w-full text-sm">
                                    </div>
                                    <div class="text-right">
                                        <button type="submit" name="edit_modul" class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Save</button>
                                    </div>
                                </form>
                            </details>
                            <!-- Delete -->
                            <form action="manajemen_modul.php?course_id=<?= $filter_id ?>" method="post" class="inline-block" onsubmit="return confirm('Are you sure to delete this module?');">
                                <input type="hidden" name="id_modul_hapus" value="<?= $m['id'] ?>">
                                <input type="hidden" name="path_hapus" value="<?= $m['path_file_materi'] ?>">
                                <button type="submit" name="hapus_modul" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php
require_once 'templates/footer.php';
?>
