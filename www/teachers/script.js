const API_BASE_URL = 'api.php'; // PHP backend file

// Open the modal
document.getElementById('addStudentBtn').onclick = function () {
    document.getElementById('addStudentModal').style.display = 'block';
};

// Close the modal
document.querySelector('.close').onclick = function () {
    document.getElementById('addStudentModal').style.display = 'none';
};

// Handle form submission
document.getElementById('addStudentForm').onsubmit = async function (event) {
    event.preventDefault();

    const name = document.getElementById('name').value;
    const rollNumber = document.getElementById('rollNumber').value;

    try {
        const formData = new FormData();
        formData.append('action', 'add_student');
        formData.append('name', name);
        formData.append('rollNumber', rollNumber);

        const response = await fetch(API_BASE_URL, {
            method: 'POST',
            body: formData,
        });
        const data = await response.json();
        console.log('Student added:', data);

        // Refresh the table
        fetchAttendance();
    } catch (error) {
        console.error('Error adding student:', error);
    }

    // Close the modal
    document.getElementById('addStudentModal').style.display = 'none';

    // Clear the form
    document.getElementById('addStudentForm').reset();
};

// Fetch attendance records and populate the table
async function fetchAttendance() {
    try {
        const response = await fetch(`${API_BASE_URL}?action=fetch_attendance`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const data = await response.json();
        console.log('Fetched attendance data:', data); // Debugging: Log the data
        populateTable(data);
    } catch (error) {
        console.error('Error fetching attendance:', error);
    }
}

// Populate the table with data
function populateTable(data) {
    const tableBody = document.querySelector('#attendanceTable tbody');
    if (!tableBody) {
        console.error('Table body not found!'); // Debugging: Check if table body exists
        return;
    }

    tableBody.innerHTML = ''; // Clear existing rows

    if (!Array.isArray(data)) {
        console.error('Data is not an array:', data); // Debugging: Check if data is an array
        return;
    }

    data.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${row.id}</td>
            <td>${row.name}</td>
            <td>${row.roll_number}</td>
            <td>${row.date}</td>
            <td>${row.time}</td>
        `;
        tableBody.appendChild(tr);
    });
}

// Fetch attendance records on page load
fetchAttendance();