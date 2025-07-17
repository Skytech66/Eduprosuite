<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Skill Evaluation</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #eef2ff;
            --secondary: #3f37c9;
            --text: #2b2d42;
            --text-light: #8d99ae;
            --bg: #f8f9fa;
            --card-bg: #ffffff;
            --border: #e9ecef;
            --success: #4cc9f0;
            --warning: #f8961e;
            --danger: #f94144;
            --star-active: #FFD700;
            --star-hover: #FFC107;
            --star-inactive: #e0e0e0;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.6;
            padding: 20px;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, var(--primary), var(--success));
        }
        
        .page-header {
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
            position: relative;
        }
        
        .page-header h1 {
            font-weight: 600;
            color: var(--primary);
            margin-bottom: 8px;
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .page-header h1 i {
            color: var(--secondary);
        }
        
        .page-header p {
            color: var(--text-light);
            font-size: 15px;
            max-width: 700px;
        }
        
        .controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
            background-color: var(--primary-light);
            padding: 15px;
            border-radius: 10px;
        }
        
        .week-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .week-selector label {
            font-weight: 500;
            color: var(--text);
            white-space: nowrap;
        }
        
        select {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background-color: var(--card-bg);
            font-family: inherit;
            color: var(--text);
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            min-width: 120px;
        }
        
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(67, 97, 238, 0.2);
        }
        
        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-1px);
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid var(--border);
            color: var(--text);
        }
        
        .btn-outline:hover {
            background-color: var(--bg);
            border-color: var(--primary-light);
        }
        
        .table-container {
            overflow-x: auto;
            margin-bottom: 25px;
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }
        
        th {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
            text-align: left;
            padding: 15px;
            position: sticky;
            top: 0;
            border-bottom: 2px solid var(--border);
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tr:hover td {
            background-color: rgba(67, 97, 238, 0.03);
        }
        
        .student-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--primary-light);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }
        
        .student-name {
            font-weight: 500;
            color: var(--text);
        }
        
        .skill-cell {
            min-width: 140px;
        }
        
        .rating-stars {
            display: flex;
            gap: 3px;
        }
        
        .rating-star {
            color: var(--star-inactive);
            cursor: pointer;
            transition: all 0.2s;
            font-size: 18px;
            position: relative;
        }
        
        .rating-star:hover {
            color: var(--star-hover);
            transform: scale(1.2);
        }
        
        .rating-star.active {
            color: var(--star-active);
            text-shadow: 0 0 4px rgba(255, 215, 0, 0.4);
        }
        
        .rating-star.active ~ .rating-star {
            color: var(--star-inactive) !important;
        }
        
        .notes-cell {
            min-width: 200px;
        }
        
        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border);
            font-family: inherit;
            resize: vertical;
            min-height: 60px;
            transition: all 0.2s;
            font-size: 14px;
        }
        
        textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        .status-message {
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 20px;
            display: none;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .status-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            display: flex;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            th, td {
                padding: 10px 12px;
            }
            
            .controls {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions {
                width: 100%;
                display: flex;
                gap: 10px;
            }
            
            .btn {
                flex: 1;
                justify-content: center;
            }
        }
        
        /* Tooltip styles */
        .skill-header {
            position: relative;
            cursor: help;
        }
        
        .skill-header:hover::after {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background-color: var(--text);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            white-space: nowrap;
            z-index: 100;
            margin-bottom: 8px;
        }
        
        .skill-header::after {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: var(--text) transparent transparent transparent;
            display: none;
        }
        
        .skill-header:hover::after {
            display: block;
        }
        
        /* Add star animation */
        @keyframes starPop {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        
        .rating-star.active {
            animation: starPop 0.3s ease-out;
        }
        
        /* Floating action button */
        .floating-action {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: var(--primary);
            color: white;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            cursor: pointer;
            transition: all 0.3s;
            z-index: 10;
        }
        
        .floating-action:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>
                <i class="fas fa-chart-line"></i>
                Weekly Skill Evaluation
            </h1>
            <p>Evaluate your students on key soft skills. Please update this every week to track their progress and provide valuable feedback.</p>
        </div>
        
        <div class="controls">
            <div class="week-selector">
                <label for="week-select">Evaluation Week:</label>
                <select id="week-select">
                    <option value="Week 1">Week 1</option>
                    <option value="Week 2">Week 2</option>
                    <option value="Week 3">Week 3</option>
                    <option value="Week 4">Week 4</option>
                    <option value="Week 5">Week 5</option>
                </select>
            </div>
            
            <div class="actions">
                <button class="btn btn-outline">
                    <i class="fas fa-download"></i> Export Data
                </button>
                <button class="btn btn-primary" id="save-btn">
                    <i class="fas fa-save"></i> Save Evaluations
                </button>
            </div>
        </div>
        
        <div class="table-container">
            <table id="evaluation-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th class="skill-header" data-tooltip="Ability to analyze facts and form judgments">Critical Thinking</th>
                        <th class="skill-header" data-tooltip="Ability to think through problems logically">Logical Reasoning</th>
                        <th class="skill-header" data-tooltip="Works well with others in team settings">Collaboration</th>
                        <th class="skill-header" data-tooltip="Demonstrates original thinking and innovation">Creativity</th>
                        <th class="skill-header" data-tooltip="Expresses ideas clearly and effectively">Communication</th>
                        <th class="skill-header" data-tooltip="Demonstrates positive classroom behavior">Behavior</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody id="student-rows">
                    <!-- Student rows will be dynamically added here -->
                </tbody>
            </table>
        </div>
        
        <div class="status-message status-success" id="status-message">
            <i class="fas fa-check-circle"></i>
            <span>Your evaluations have been saved successfully!</span>
        </div>
    </div>

    <div class="floating-action" id="add-student-btn" title="Add New Student">
        <i class="fas fa-user-plus"></i>
    </div>

    <script>
        const students = [
            { name: 'Emma Johnson', ratings: { 'Critical Thinking': 4, 'Logical Reasoning': 3, 'Collaboration': 5, 'Creativity': 3, 'Communication': 4, 'Behavior': 5 }, notes: 'Excellent participation this week' },
            { name: 'Michael Chen', ratings: { 'Critical Thinking': 3, 'Logical Reasoning': 4, 'Collaboration': 2, 'Creativity': 5, 'Communication': 2, 'Behavior': 3 }, notes: 'Needs to participate more in group work' },
            { name: 'Sophia Rodriguez', ratings: { 'Critical Thinking': 5, 'Logical Reasoning': 4, 'Collaboration': 4, 'Creativity': 5, 'Communication': 3, 'Behavior': 4 }, notes: 'Very creative in problem-solving' }
        ];

        function loadStudents() {
            const tbody = document.getElementById('student-rows');
            tbody.innerHTML = ''; // Clear existing rows

            students.forEach(student => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="student-info">
                            <div class="student-avatar">${student.name.charAt(0)}</div>
                            <span class="student-name">${student.name}</span>
                        </div>
                    </td>
                    ${Object.keys(student.ratings).map(skill => `
                        <td class="skill-cell">
                            <div class="rating-stars" data-rating="${student.ratings[skill]}">
                                ${[1, 2, 3, 4, 5].map(i => `
                                    <i class="fas fa-star rating-star ${i <= student.ratings[skill] ? 'active' : ''}" data-rating="${i}"></i>
                                `).join('')}
                            </div>
                        </td>
                    `).join('')}
                    <td class="notes-cell">
                        <textarea placeholder="Add notes...">${student.notes}</textarea>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            loadStudents();

            // Enhanced star rating functionality
            document.querySelectorAll('.rating-star').forEach(star => {
                star.addEventListener('click', function() {
                    const starsContainer = this.closest('.rating-stars');
                    const stars = starsContainer.querySelectorAll('.rating-star');
                    const clickedRating = parseInt(this.getAttribute('data-rating'));
                    
                    // Toggle rating - if clicking the same star that's already active, set to 0
                    const currentRating = parseInt(starsContainer.getAttribute('data-rating'));
                    const newRating = currentRating === clickedRating ? 0 : clickedRating;
                    
                    starsContainer.setAttribute('data-rating', newRating.toString());
                    
                    // Update visual state
                    stars.forEach((s, index) => {
                        const starRating = parseInt(s.getAttribute('data-rating'));
                        if (starRating <= newRating) {
                            s.classList.add('active');
                        } else {
                            s.classList.remove('active');
                        }
                    });
                });
                
                // Hover effects
                star.addEventListener('mouseover', function() {
                    const starsContainer = this.closest('.rating-stars');
                    const stars = starsContainer.querySelectorAll('.rating-star');
                    const hoverRating = parseInt(this.getAttribute('data-rating'));
                    const currentRating = parseInt(starsContainer.getAttribute('data-rating'));
                    
                    // Only show hover effect if no rating is selected yet
                    if (currentRating === 0) {
                        stars.forEach(s => {
                            const starRating = parseInt(s.getAttribute('data-rating'));
                            if (starRating <= hoverRating) {
                                s.style.color = 'var(--star-hover)';
                            }
                        });
                    }
                });
                
                star.addEventListener('mouseout', function() {
                    const starsContainer = this.closest('.rating-stars');
                    const stars = starsContainer.querySelectorAll('.rating-star');
                    const currentRating = parseInt(starsContainer.getAttribute('data-rating'));
                    
                    stars.forEach(s => {
                        const starRating = parseInt(s.getAttribute('data-rating'));
                        if (!s.classList.contains('active')) {
                            s.style.color = 'var(--star-inactive)';
                        }
                    });
                                    });
            });

            // Save button functionality
            document.getElementById('save-btn').addEventListener('click', function() {
                const statusMessage = document.getElementById('status-message');
                statusMessage.style.display = 'flex';
                
                // Add slight animation to button
                this.innerHTML = '<i class="fas fa-check"></i> Saved!';
                this.style.backgroundColor = 'var(--success)';
                
                setTimeout(() => {
                    statusMessage.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-save"></i> Save Evaluations';
                    this.style.backgroundColor = 'var(--primary)';
                }, 3000);
                
                // Collect all the data and send it to your backend
                const evaluations = collectEvaluationData();
                console.log('Saving evaluations:', evaluations);
            });
            
            function collectEvaluationData() {
                const evaluations = [];
                const week = document.getElementById('week-select').value;
                
                document.querySelectorAll('#evaluation-table tbody tr').forEach(row => {
                    const studentName = row.querySelector('.student-name').textContent;
                    const evaluation = {
                        student: studentName,
                        week: week,
                        skills: {},
                        notes: row.querySelector('textarea').value
                    };
                    
                    // Get all skill cells (all td except first and last)
                    const skillCells = Array.from(row.querySelectorAll('td')).slice(1, -1);
                    
                    skillCells.forEach((cell, index) => {
                        const skillName = document.querySelectorAll('th')[index + 1].textContent;
                        const rating = parseInt(cell.querySelector('.rating-stars').getAttribute('data-rating'));
                        evaluation.skills[skillName] = rating;
                    });
                    
                    evaluations.push(evaluation);
                });
                
                return evaluations;
            }
            
            // Week selector functionality
            document.getElementById('week-select').addEventListener('change', function() {
                console.log('Loading data for:', this.value);
                
                // Show loading state
                const saveBtn = document.getElementById('save-btn');
                saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
                saveBtn.disabled = true;
                
                // Simulate loading data
                setTimeout(() => {
                    saveBtn.innerHTML = '<i class="fas fa-save"></i> Save Evaluations';
                    saveBtn.disabled = false;
                }, 800);
            });
            
            // Add student button (floating action)
            document.getElementById('add-student-btn').addEventListener('click', function() {
                const studentName = prompt("Enter the student's name:");
                if (studentName) {
                    const newStudent = {
                        name: studentName,
                        ratings: { 'Critical Thinking': 0, 'Logical Reasoning': 0, 'Collaboration': 0, 'Creativity': 0, 'Communication': 0, 'Behavior': 0 },
                        notes: ''
                    };
                    students.push(newStudent);
                    loadStudents(); // Reload students to display the new one
                }
            });
        });
    </script>
</body>
</html>

               
