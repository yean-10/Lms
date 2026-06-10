<?php
require_once '../includes/db.php'; // ថយចេញពី member/ រួចចូលទៅ includes/

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ចាប់យកទិន្នន័យពី Form
    $role        = $_POST['role'];
    $member_code = $_POST['member_code']; // នេះជា ID ដែលអ្នកប្រើប្រាស់វាយបញ្ចូល
    $name        = $_POST['name'];
    $username    = $_POST['username'];
    $password    = password_hash($_POST['password'], PASSWORD_DEFAULT); // ការពារលេខសម្ងាត់
    $email       = $_POST['email'];
    $gender      = $_POST['gender'];
    $phone       = $_POST['phone'];
    $address     = $_POST['address'];
    
    // ចាប់យកតម្លៃ major រួចយកទៅដាក់ក្នុងប្រឡោះ class នៃតារាង members (ផ្អែកលើ Database ក្នុងរូបភាព)
    $class       = isset($_POST['major']) ? $_POST['major'] : ''; 

    // ១. ពិនិត្យលក្ខខណ្ឌ៖ បើជា "និស្សិត" ត្រូវទៅស្វែងរកមើលក្នុង Table `students` ថាមាន ID ហ្នឹងអត់
    if ($role === 'student') {
        $check_id = $conn->prepare("SELECT id_student FROM students WHERE id_student = ?");
        $check_id->bind_param("s", $member_code);
        $check_id->execute();
        $result = $check_id->get_result();

        if ($result->num_rows == 0) {
            // បើរកមិនឃើញ ID នេះក្នុងបញ្ជីនិស្សិតទេ មិនឱ្យចុះឈ្មោះឡើយ
            echo "<script>
                    alert('លេខសម្គាល់ ID និស្សិតនេះមិនមានក្នុងប្រព័ន្ធវិទ្យាស្ថានឡើយ! មិនអាចចុះឈ្មោះបានទេ។');
                    window.history.back();
                  </script>";
            $check_id->close();
            $conn->close();
            exit();
        }
        $check_id->close();
    }

    // ២. បើរកឃើញ (ឬបើគាត់ជាគ្រូ) ទើបអនុញ្ញាតឱ្យចុះឈ្មោះចូល Table `members`
    // យើងថែមជួរ `class` ទៅក្នុង SQL (សរុបមាន ១០ ជួរ ដូច្នេះត្រូវមានសញ្ញាសួរ ១០ ដែរ)
    $sql = "INSERT INTO members (member_code, name, username, password, role, email, gender, class, phone, address) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    
    // "ssssssssss" មានន័យថាជាប្រភេទ String ទាំង ១០ តម្លៃ
    $stmt->bind_param("ssssssssss", $member_code, $name, $username, $password, $role, $email, $gender, $class, $phone, $address);

    if ($stmt->execute()) {
        echo "<script>
                alert('ចុះឈ្មោះបានជោគជ័យ!'); 
                window.location='login.php';
              </script>";
    } else {
        echo "កំហុសក្នុងការចុះឈ្មោះ: " . $conn->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>