<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Task Manager API</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<style>
.completed { text-decoration: line-through; color: gray; }
.due-soon { border-left: 5px solid red; }
</style>
</head>
<body class="p-5">
<div class="container">
    <h1 class="mb-4">Task Manager (API)</h1>

    <form id="taskForm" class="mb-4">
        <input type="hidden" id="edit_task_id" value="">
        <input type="text" id="task_name" placeholder="Task Name" required class="form-control mb-2">
        <textarea id="description" placeholder="Description" class="form-control mb-2"></textarea>
        <input type="datetime-local" id="due_date" class="form-control mb-2" required>
        <select id="priority" class="form-control mb-2">
            <option value="high">High</option>
            <option value="medium" selected>Medium</option>
            <option value="low">Low</option>
        </select>
        <button class="btn btn-primary">Save Task</button>
    </form>

    <ul class="list-group" id="taskList"></ul>
</div>

<script>
const taskForm = document.getElementById('taskForm');
const taskList = document.getElementById('taskList');

// Load tasks from API
async function loadTasks(){
    const res = await axios.get('/api/tasks');
    taskList.innerHTML = '';
    res.data.forEach(task => {
        const dueSoon = !task.is_completed && new Date(task.due_date) - new Date() <= 24*60*60*1000;
        const li = document.createElement('li');
        li.className = `list-group-item d-flex justify-content-between align-items-center ${task.is_completed ? 'completed' : ''} ${dueSoon ? 'due-soon' : ''}`;
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
}

// Initial load
loadTasks();

// Add/Edit Task Handler
taskForm.addEventListener('submit', async e => {
    e.preventDefault();
    const id = document.getElementById('edit_task_id').value; // check if editing
    const data = {
        task_name: document.getElementById('task_name').value,
        description: document.getElementById('description').value,
        due_date: document.getElementById('due_date').value,
        priority: document.getElementById('priority').value
    };

    try {
        if(id){ // EDIT mode
            await axios.put(`/api/tasks/${id}`, data, { headers: { 'Accept': 'application/json' } });
            document.getElementById('edit_task_id').value = ''; // reset edit mode
        } else { // ADD mode
            await axios.post('/api/tasks', data, { headers: { 'Accept': 'application/json' } });
        }
        taskForm.reset();
        loadTasks();
    } catch (error) {
        console.error(error);
        alert('Error saving task!');
    }
});

// Handle Delete, Toggle, and Edit
taskList.addEventListener('click', async e => {
    const li = e.target.closest('li');
    const id = li.dataset.id;

    if(e.target.classList.contains('deleteTask')){
        await axios.delete(`/api/tasks/${id}`);
        loadTasks();
    } else if(e.target.classList.contains('markComplete')){
        await axios.patch(`/api/tasks/${id}/toggle`);
        loadTasks();
    } else if(e.target.classList.contains('editTask')){
        // Fill form for editing
        document.getElementById('edit_task_id').value = id;
        document.getElementById('task_name').value = li.querySelector('strong').innerText;
        document.getElementById('description').value = li.querySelector('.description').innerText;
        document.getElementById('due_date').value = li.dataset.due_date.slice(0,16); // format for datetime-local
        document.getElementById('priority').value = li.dataset.priority;
    }
});
</script>
</body>
</html>
