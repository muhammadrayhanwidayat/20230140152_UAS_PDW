<?php
$pageTitle = 'Manajemen Course - Assistant Panel';
$activePage = 'manage_courses';
require_once '../config.php';
require_once 'templates/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../index.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['tambah_praktikum'])) {
        $nama_praktikum = trim($_POST['nama_praktikum']);
        $deskripsi = trim($_POST['deskripsi']);

        if (!empty($nama_praktikum)) {
            $stmt = $conn->prepare("INSERT INTO mata_praktikum (nama_praktikum, deskripsi) VALUES (?, ?)");
            $stmt->bind_param("ss", $nama_praktikum, $deskripsi);
            if ($stmt->execute()) {
                $message = "Course successfully added.";
                $message_type = 'success';
            } else {
                $message = "Failed to add course: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Course name cannot be empty.";
            $message_type = 'error';
        }
    }
    elseif (isset($_POST['edit_praktikum'])) {
        $id_praktikum = (int)$_POST['id_praktikum'];
        $nama_praktikum = trim($_POST['nama_praktikum_edit']);
        $deskripsi = trim($_POST['deskripsi_edit']);

        if (!empty($nama_praktikum) && $id_praktikum > 0) {
            $stmt = $conn->prepare("UPDATE mata_praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ?");
            $stmt->bind_param("ssi", $nama_praktikum, $deskripsi, $id_praktikum);
            if ($stmt->execute()) {
                $message = "Course successfully updated.";
                $message_type = 'success';
            } else {
                $message = "Failed to update course: " . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
        } else {
            $message = "Course name cannot be empty and valid ID is required.";
            $message_type = 'error';
        }
    }
    elseif (isset($_POST['hapus_praktikum'])) {
        $id_praktikum = (int)$_POST['id_praktikum_hapus'];
        if ($id_praktikum > 0) {
            $stmt = $conn->prepare("DELETE FROM mata_praktikum WHERE id = ?");
            $stmt->bind_param("i", $id_praktikum);
            if ($stmt->execute()) {
                $message = "Course successfully deleted.";
                $message_type = 'success';
            } else {
                $message = "Failed to delete course: " . $stmt->error . ". Make sure there are no modules or students enrolled in this course if there is no ON DELETE CASCADE on the registration table.";
                $message_type = 'error';
            }
            $stmt->close();
        }
    }
}

$mata_praktikum_list = [];
$result = $conn->query("SELECT id, nama_praktikum, deskripsi FROM mata_praktikum ORDER BY nama_praktikum ASC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $mata_praktikum_list[] = $row;
    }
}
?>

<div class="container mx-auto px-4 py-8">


    <?php if (!empty($message)): ?>
        <div class="mb-4 p-4 rounded-md <?php echo $message_type === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

   
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Course</h2>
        <form action="manajemen_course.php" method="post">
            <div class="mb-4">
                <label for="nama_praktikum" class="block text-sm font-medium text-gray-700 mb-1">Course Name <span class="text-red-500">*</span></label>
                <input type="text" name="nama_praktikum" id="nama_praktikum" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
            </div>
            <div class="mb-4">
                <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
            </div>
            <div>
                <button type="submit" name="tambah_praktikum" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Add Course
                </button>
            </div>
        </form>
    </div>

 
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Course List</h2>
        <?php if (!empty($mata_praktikum_list)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($mata_praktikum_list as $praktikum): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?></td>
                                <td class="px-6 py-4 whitespace-normal text-sm text-gray-500"><?php echo nl2br(htmlspecialchars($praktikum['deskripsi'] ?? 'N/A')); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button onclick="openEditModal(<?php echo $praktikum['id']; ?>, '<?php echo htmlspecialchars(addslashes($praktikum['nama_praktikum'])); ?>', '<?php echo htmlspecialchars(addslashes($praktikum['deskripsi'] ?? '')); ?>')" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</button>
                                    <form action="manajemen_course.php" method="post" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this course? All related modules will also be deleted.');">
                                        <input type="hidden" name="id_praktikum_hapus" value="<?php echo $praktikum['id']; ?>">
                                        <button type="submit" name="hapus_praktikum" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-gray-500">No courses have been added yet.</p>
        <?php endif; ?>
    </div>
</div>


<div id="editModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
  <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <form action="manajemen_course.php" method="post">
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Edit Course</h3>
              <div class="mt-2">
                <input type="hidden" name="id_praktikum" id="edit_id_praktikum">
                <div class="mb-4">
                    <label for="edit_nama_praktikum" class="block text-sm font-medium text-gray-700 mb-1">Course Name <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_praktikum_edit" id="edit_nama_praktikum" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" required>
                </div>
                <div>
                    <label for="edit_deskripsi" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="deskripsi_edit" id="edit_deskripsi" rows="3" class="mt-1 block w-full px-3 py-2 bg-white border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"></textarea>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button type="submit" name="edit_praktikum" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
            Save Changes
          </button>
          <button type="button" onclick="closeEditModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function openEditModal(id, nama, deskripsi) {
    document.getElementById('edit_id_praktikum').value = id;
    document.getElementById('edit_nama_praktikum').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php
$conn->close();
require_once 'templates/footer.php';
?>