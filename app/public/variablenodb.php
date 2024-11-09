<?php
// Inisialisasi data (sebagai pengganti database)
$students = [
    ["id" => 1, "name" => "Alice", "email" => "alice@example.com"],
    ["id" => 2, "name" => "Bob", "email" => "bob@example.com"]
];

// Fungsi untuk mendapatkan data student berdasarkan ID
function getStudentById($id) {
    global $students;
    foreach ($students as $student) {
        if ($student['id'] == $id) {
            return $student;
        }
    }
    return null;
}

// Handler untuk Create, Update, dan Delete
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action == 'create') {
            // Menambah data baru
            $id = count($students) + 1;
            $name = $_POST['name'];
            $email = $_POST['email'];
            $students[] = ["id" => $id, "name" => $name, "email" => $email];
        } elseif ($action == 'update') {
            // Mengupdate data
            $id = $_POST['id'];
            foreach ($students as &$student) {
                if ($student['id'] == $id) {
                    $student['name'] = $_POST['name'];
                    $student['email'] = $_POST['email'];
                    break;
                }
            }
        } elseif ($action == 'delete') {
            // Menghapus data
            $id = $_POST['id'];
            foreach ($students as $key => $student) {
                if ($student['id'] == $id) {
                    unset($students[$key]);
                    break;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CRUD Sederhana PHP Satu File</title>
</head>
<body>

<h1>CRUD Sederhana PHP Satu File</h1>

<!-- Form Tambah Data -->
<h2>Tambah Data</h2>
<form method="post">
    <input type="hidden" name="action" value="create">
    <label>Nama: <input type="text" name="name" required></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <button type="submit">Tambah</button>
</form>

<!-- Tabel Data Mahasiswa -->
<h2>Data Mahasiswa</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Nama</th>
        <th>Email</th>
        <th>Aksi</th>
    </tr>
    <?php foreach ($students as $student): ?>
    <tr>
        <td><?php echo $student['id']; ?></td>
        <td><?php echo $student['name']; ?></td>
        <td><?php echo $student['email']; ?></td>
        <td>
            <!-- Form Update Data -->
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                <input type="text" name="name" value="<?php echo $student['name']; ?>" required>
                <input type="email" name="email" value="<?php echo $student['email']; ?>" required>
                <button type="submit">Update</button>
            </form>
            
            <!-- Form Hapus Data -->
            <form method="post" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                <button type="submit">Hapus</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
