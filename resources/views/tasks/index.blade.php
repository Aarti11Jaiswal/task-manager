<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Task Manager</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<style>
/* Body styling */
body {
    background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Card container for task manager */
.container {
    max-width: 900px;
    margin-top: 50px;
}

/* Header */
.header {
    background-color: #08090cff;
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}

.header h1 {
    margin: 0;
    font-size: 2rem;
}

.user-info {
    display: flex;
    align-items: center;
}

.user-btn {
    background-color: #f5f5f5ff;
    border: none;
    color: black;
    padding: 5px 15px;
    border-radius: 50px;
    margin-right: 10px;
}

/* Task Form */
#taskForm {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

#taskForm input, #taskForm textarea, #taskForm select {
    border-radius: 5px;
}

#taskForm button {
    border-radius: 5px;
    background-color: #333;
    border: 2px solid black;
}

/* Task list styling */
.list-group-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px;
    margin-bottom: 10px;
    padding: 15px 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    transition: transform 0.2s;
}

.list-group-item:hover {
    transform: scale(1.02);
}

.completed {
    text-decoration: line-through;
    color: gray;
    opacity: 0.8;
}

.due-soon {
    border-left: 5px solid #ff6b6b;
    background-color: #ffe6e6;
}

/* Task buttons */
.list-group-item button {
    margin-left: 5px;
}

/* Logout button */
#logoutBtn {
    border-radius: 50px;
}

/* Responsive adjustments */
@media(max-width: 576px) {
    .header {
        text-align: center;
    }
    .user-info {
        justify-content: center;
        margin-top: 10px;
    }
}
</style>
</head>
<body>
<div class="container">
    <!-- Header -->
    <div class="header d-flex justify-content-between align-items-center">
        <h1>Task Manager</h1>
        <div class="user-info">
            <button class="user-btn" id="userBtn" disabled></button>
            <button id="logoutBtn" class="btn btn-danger">Logout</button>
        </div>
    </div>

    <!-- Task Form -->
    <form id="taskForm">
        <input type="hidden" id="edit_task_id">
        <input type="text" id="task_name" placeholder="Task Name" required class="form-control mb-2">
        <textarea id="description" placeholder="Description" class="form-control mb-2"></textarea>
        <input type="datetime-local" id="due_date" class="form-control mb-2" required>
        <select id="priority" class="form-control mb-2">
            <option value="high">High</option>
            <option value="medium" selected>Medium</option>
            <option value="low">Low</option>
        </select>
        <button class="btn btn-primary w-100">Save Task</button>
    </form>

    <!-- Task List -->
    <ul class="list-group" id="taskList"></ul>
</div>

<script>
// Token & headers
const token = sessionStorage.getItem('token');
if(!token) window.location.href = '/login';
const headers = {
    'Accept': 'application/json',
    'Authorization': `Bearer ${token}`
};

const userBtn = document.getElementById('userBtn');
const userName = sessionStorage.getItem('userName');
if(userName) userBtn.textContent = userName;

const taskForm = document.getElementById('taskForm');
const taskList = document.getElementById('taskList');
const logoutBtn = document.getElementById('logoutBtn');

async function loadTasks() {
    try {
        const res = await axios.get('/api/tasks', { headers });
        taskList.innerHTML = '';
        res.data.forEach(task => {
            const dueSoon = !task.is_completed && new Date(task.due_date) - new Date() <= 24*60*60*1000;
            const li = document.createElement('li');
            li.className = `list-group-item ${task.is_completed ? 'completed' : ''} ${dueSoon ? 'due-soon' : ''}`;
            li.dataset.id = task.id;
            li.dataset.due_date = task.due_date;
            li.dataset.priority = task.priority;
            li.innerHTML = `
                <div>
                    <strong>${task.task_name}</strong> - <span class="description">${task.description || ''}</span><br>
                    Due: ${new Date(task.due_date).toLocaleString()} | Priority: ${task.priority}
                </div>
                <div>
                    <button class="btn btn-success btn-sm markComplete">Toggle Complete</button>
                    <button class="btn btn-warning btn-sm editTask">Edit</button>
                    <button class="btn btn-danger btn-sm deleteTask">Delete</button>
                </div>
            `;
            taskList.appendChild(li);
        });
    } catch(err) {
        console.error(err);
        alert('Failed to load tasks. Login again.');
        window.location.href = '/login';
    }
}

// Add/Edit tasks
taskForm.addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('edit_task_id').value;
    const data = {
        task_name: document.getElementById('task_name').value,
        description: document.getElementById('description').value,
        due_date: document.getElementById('due_date').value,
        priority: document.getElementById('priority').value
    };

    try {
        if(id){
            await axios.put(`/api/tasks/${id}`, data, { headers });
            document.getElementById('edit_task_id').value = '';
        } else {
            await axios.post('/api/tasks', data, { headers });
        }
        taskForm.reset();
        loadTasks();
    } catch(err) {
        console.error(err);
        alert('Error saving task!');
    }
});

taskList.addEventListener('click', async e => {
    const li = e.target.closest('li');
    if(!li) return;
    const id = li.dataset.id;

    try {
        if(e.target.classList.contains('deleteTask')){
            await axios.delete(`/api/tasks/${id}`, { headers });
            loadTasks();
        } else if(e.target.classList.contains('markComplete')){
            await axios.patch(`/api/tasks/${id}/toggle`, {}, { headers });
            loadTasks();
        } else if(e.target.classList.contains('editTask')){
            document.getElementById('edit_task_id').value = id;
            document.getElementById('task_name').value = li.querySelector('strong').innerText;
            document.getElementById('description').value = li.querySelector('.description').innerText;
            document.getElementById('due_date').value = li.dataset.due_date.slice(0,16);
            document.getElementById('priority').value = li.dataset.priority;
        }
    } catch(err){
        console.error(err);
        alert('Action failed!');
    }
});

// Logout
logoutBtn.addEventListener('click', async () => {
    try {
        await axios.post('/api/logout', {}, { headers });
        sessionStorage.removeItem('token');
        window.location.href = '/login';
    } catch(err) {
        console.error(err);
        alert('Logout failed!');
    }
});

loadTasks();
</script>
</body>
</html>
