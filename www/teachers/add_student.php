<?php
// add_student.php - Adds a new student to SQLite

$db = new SQLite3('students_records.db');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO students (name) VALUES (:name)");
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
        echo json_encode(["success" => true, "message" => "Student added successfully!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Student name cannot be empty."]);
    }
}

// get_students.php - Fetches all students from SQLite
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $db->query("SELECT * FROM students ORDER BY id DESC");
    $students = [];
    
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $students[] = $row;
    }
    
    echo json_encode($students);
}
?>

<script>
document.addEventListener("DOMContentLoaded", function () {
    fetchStudents();

    document.querySelector(".add-student").addEventListener("click", function () {
        const studentName = prompt("Enter Student Name:");
        if (studentName) {
            fetch("add_student.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `name=${encodeURIComponent(studentName)}`
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                fetchStudents(); // Refresh student list
            });
        }
    });
});

function fetchStudents() {
    fetch("get_students.php")
        .then(response => response.json())
        .then(students => {
            const studentList = document.querySelector(".student-list");
            studentList.innerHTML = "";
            students.forEach(student => {
                const studentItem = document.createElement("div");
                studentItem.className = "student-item";
                studentItem.innerHTML = `
                    <div>\${student.name}</div>
                    <div>#STD\${student.id}</div>
                    <select class="attendance-status">
                        <option value="present">Present</option>
                        <option value="absent">Absent</option>
                        <option value="late">Late</option>
                    </select>
                `;
                studentList.appendChild(studentItem);
            });
        });
}
</script>