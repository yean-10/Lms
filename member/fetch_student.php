<?php
// ភ្ជាប់ទៅកាន់ Database (សូមប្តូរព័ត៌មានទៅតាម Database របស់អ្នក)
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_library";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Database connection failed']));
}

if (isset($_GET['id_student'])) {
    $id_student = $conn->real_escape_string($_GET['id_student']);
    
    // ចាប់យកទិន្នន័យ name, sex, major ពីតារាង students តាម id_student
    $query = "SELECT name, sex, major FROM students WHERE id_student = '$id_student' LIMIT 1";
    $result = $conn->query($query);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'name' => $row['name'],
            'sex' => $row['sex'],    // តម្លៃអាចជា Male ឬ Female តាមរូបភាព Database របស់អ្នក
            'major' => $row['major']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'រកមិនឃើញទិន្នន័យនិស្សិតឡើយ']);
    }
}
$conn->close();
?>