const API_EMPLEADOS_URL = 'api_empleados.php';

let employees = [];
let employeeModalInstance = null;


document.addEventListener('DOMContentLoaded', () => {
   
    employeeModalInstance = new bootstrap.Modal(document.getElementById('employeeModal'));

    
    document.getElementById('loginForm').addEventListener('submit', handleLogin);
    document.getElementById('logoutButton').addEventListener('click', handleLogout);
    document.getElementById('employeeModalForm').addEventListener('submit', handleEmployeeSubmit);

    
    checkSession();
});



function handleLogin(e) {
    e.preventDefault();
    const user = document.getElementById('username').value;
    const pass = document.getElementById('password').value;

    
    if (user === 'admin' && pass === '1234') {
        localStorage.setItem('cinemas_user', user);
        checkSession();
    } else {
        document.getElementById('loginError').style.display = 'block';
    }
}

function handleLogout() {
    localStorage.removeItem('cinemas_user');
    location.reload();
}

function checkSession() {
    const user = localStorage.getItem('cinemas_user');
    if (user) {
        document.getElementById('login-container').style.display = 'none';
        document.getElementById('app-container').style.display = 'block';
        document.getElementById('nav-username').textContent = user;
        
       
        loadEmployees();
    } else {
        document.getElementById('login-container').style.display = 'flex';
        document.getElementById('app-container').style.display = 'none';
    }
}


async function apiFetch(url, formData = null) {
    try {
        const options = {
            method: formData ? 'POST' : 'GET'
        };
        if (formData) {
            options.body = formData;
        }

        const response = await fetch(url, options);
        
        
        const text = await response.text();
        
        try {
            const json = JSON.parse(text);
            if (json.status === 'error') {
                throw new Error(json.mensaje);
            }
            return json;
        } catch (jsonError) {
            console.error("Respuesta del servidor (No JSON):", text);
            throw new Error("Error en el servidor: Recibimos datos no válidos.");
        }

    } catch (error) {
        showAlert(error.message, 'danger');
        throw error;
    }
}



async function loadEmployees() {
    try {
        
        const formData = new FormData();
        formData.append('accion', 'obtener');
        
        const result = await apiFetch(API_EMPLEADOS_URL, formData);
        employees = result.data;
        renderEmployeeTable();
    } catch (error) {
        console.error(error);
    }
}

function renderEmployeeTable() {
    const tbody = document.getElementById('employeesTableBody');
    tbody.innerHTML = '';

    if (employees.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">No hay empleados registrados.</td></tr>';
        return;
    }

    employees.forEach(emp => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${emp.nombre}</td>
            <td>${emp.puesto}</td>
            <td>${emp.horas} hrs</td>
            <td>
                <button class="btn btn-sm btn-primary me-1" onclick="editEmployee(${emp.id})">
                    <i class="fas fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${emp.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}


window.openEmployeeModal = function() {
    document.getElementById('employeeModalForm').reset();
    document.getElementById('modalEmployeeId').value = '';
    document.getElementById('employeeModalTitle').textContent = 'Nuevo Empleado';
    employeeModalInstance.show();
}


window.editEmployee = function(id) {
    const emp = employees.find(e => e.id == id); // Nota: id puede ser string o numero
    if (emp) {
        document.getElementById('modalEmployeeId').value = emp.id;
        document.getElementById('modalEmployeeName').value = emp.nombre;
        document.getElementById('modalEmployeePosition').value = emp.puesto;
        document.getElementById('modalEmployeeHours').value = emp.horas;
        document.getElementById('employeeModalTitle').textContent = 'Editar Empleado';
        employeeModalInstance.show();
    }
}


async function handleEmployeeSubmit(e) {
    e.preventDefault();

    const id = document.getElementById('modalEmployeeId').value;
    const nombre = document.getElementById('modalEmployeeName').value;
    const puesto = document.getElementById('modalEmployeePosition').value;
    const horas = document.getElementById('modalEmployeeHours').value;

    const formData = new FormData();
    formData.append('accion', id ? 'actualizar' : 'guardar');
    if (id) formData.append('id', id);
    formData.append('nombre', nombre);
    formData.append('puesto', puesto);
    formData.append('horas', horas);

    try {
        const result = await apiFetch(API_EMPLEADOS_URL, formData);
        showAlert(result.mensaje, 'success');
        employeeModalInstance.hide();
        loadEmployees(); // Recargar tabla
    } catch (error) {
       
    }
}


window.deleteEmployee = async function(id) {
    if (!confirm('¿Estás seguro de eliminar este empleado?')) return;

    const formData = new FormData();
    formData.append('accion', 'eliminar');
    formData.append('id', id);

    try {
        const result = await apiFetch(API_EMPLEADOS_URL, formData);
        showAlert(result.mensaje, 'warning');
        loadEmployees();
    } catch (error) {
        console.error(error);
    }
}



function showAlert(message, type) {
    const placeholder = document.getElementById('alertPlaceholder');
    const wrapper = document.createElement('div');
    wrapper.innerHTML = `
        <div class="alert alert-${type} alert-dismissible" role="alert">
           ${message}
           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    placeholder.append(wrapper);

    
    setTimeout(() => {
        wrapper.remove();
    }, 3000);
}