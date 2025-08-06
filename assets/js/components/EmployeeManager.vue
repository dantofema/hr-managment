<template>
  <div class="table-container">
    <table>
      <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <tr v-for="employee in employees"
          :key="employee.id">
        <td>{{ employee.id }}</td>
        <td>{{ employee.name }}</td>
        <td>{{ employee.email }}</td>
        <td>
          <button class="action-btn"
                  title="Edit"
                  @click="editEmployee(employee)">
            <!-- Pencil SVG icon -->
            <svg fill="none"
                 height="18"
                 viewBox="0 0 20 20"
                 width="18">
              <path d="M4 13.5V16h2.5l7.06-7.06-2.5-2.5L4 13.5z"
                    stroke="#2563eb"
                    stroke-width="1.5"/>
              <path d="M14.06 6.44a1.5 1.5 0 0 0 0-2.12l-1.38-1.38a1.5 1.5 0 0 0-2.12 0l-.88.88 3.5 3.5.88-.88z"
                    stroke="#2563eb"
                    stroke-width="1.5"/>
            </svg>
          </button>
          <button class="action-btn"
                  title="Delete"
                  @click="deleteEmployee(employee.id)">
            <!-- Trash SVG icon -->
            <svg fill="none"
                 height="18"
                 viewBox="0 0 20 20"
                 width="18">
              <path d="M6 7v7a2 2 0 0 0 2 2h4a2 2 0 0 0 2-2V7"
                    stroke="#dc2626"
                    stroke-width="1.5"/>
              <path d="M9 9v4M11 9v4"
                    stroke="#dc2626"
                    stroke-width="1.5"/>
              <rect height="2"
                    rx="1"
                    stroke="#dc2626"
                    stroke-width="1.5"
                    width="12"
                    x="4"
                    y="4"/>
            </svg>
          </button>
        </td>
      </tr>
      </tbody>
    </table>
    <div v-if="loading">Loading...</div>
    <div v-if="error">{{ error }}</div>

    <!-- Edit Form -->
    <div v-if="editingEmployee"
         class="edit-form">
      <h3>Edit Employee</h3>
      <form @submit.prevent="saveEmployee">
        <label>
          Name:
          <input v-model="form.name"
                 required
                 type="text"/>
        </label>
        <label>
          Email:
          <input v-model="form.email"
                 required
                 type="email"/>
        </label>
        <button type="submit">Save</button>
        <button type="button"
                @click="cancelEdit">Cancel
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import {onMounted, ref} from 'vue';

const employees = ref([]);
const loading = ref(false);
const error = ref('');
const editingEmployee = ref(null);
const form = ref({name: '', email: ''});

function editEmployee(employee) {
  editingEmployee.value = employee;
  form.value = {name: employee.name, email: employee.email};
}

function cancelEdit() {
  editingEmployee.value = null;
  form.value = {name: '', email: ''};
}

async function saveEmployee() {
  if (!editingEmployee.value) return;
  loading.value = true;
  try {
    const res = await fetch(`/api/v1/employees/${editingEmployee.value.id}`, {
      method: 'PUT',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify(form.value)
    });
    if (!res.ok) throw new Error('Failed to update employee');
    // Update local list
    editingEmployee.value.name = form.value.name;
    editingEmployee.value.email = form.value.email;
    cancelEdit();
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
}

async function deleteEmployee(id) {
  if (!confirm('Are you sure you want to delete this employee?')) return;
  loading.value = true;
  try {
    const res = await fetch(`/api/v1/employees/${id}`, {method: 'DELETE'});
    if (!res.ok) throw new Error('Failed to delete employee');
    employees.value = employees.value.filter(e => e.id !== id);
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  loading.value = true;
  try {
    const res = await fetch('/api/v1/employees');
    if (!res.ok) throw new Error('Failed to fetch employees');
    const json = await res.json();
    employees.value = json.data;
  } catch (e) {
    error.value = e.message;
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.table-container {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  min-height: 400px;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

table {
  border-collapse: collapse;
  width: 700px;
  background: #fff;
  box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08), 0 1.5px 4px rgba(0, 0, 0, 0.04);
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 24px;
}

th, td {
  border-bottom: 1px solid #e2e8f0;
  padding: 16px 12px;
  text-align: left;
}

th {
  background: #f1f5f9;
  font-size: 1.1rem;
  font-weight: 600;
  color: #334155;
  letter-spacing: 0.02em;
}

tbody tr:last-child td {
  border-bottom: none;
}

tbody tr {
  transition: background 0.2s;
}

tbody tr:hover {
  background: #f8fafc;
}

td {
  font-size: 1rem;
  color: #475569;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  margin-right: 8px;
  padding: 4px;
  vertical-align: middle;
}

.action-btn:last-child {
  margin-right: 0;
}

.edit-form {
  background: #fff;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
  border-radius: 10px;
  padding: 24px;
  width: 400px;
  margin-top: 12px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

.edit-form h3 {
  margin-bottom: 16px;
  color: #2563eb;
  font-size: 1.2rem;
}

.edit-form label {
  display: flex;
  flex-direction: column;
  margin-bottom: 12px;
  width: 100%;
  font-weight: 500;
  color: #334155;
}

.edit-form input {
  margin-top: 4px;
  padding: 8px;
  border: 1px solid #e2e8f0;
  border-radius: 6px;
  font-size: 1rem;
  width: 100%;
}

.edit-form button {
  margin-right: 8px;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  background: #2563eb;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.edit-form button[type="button"] {
  background: #64748b;
}

.edit-form button:hover {
  background: #1d4ed8;
}

.edit-form button[type="button"]:hover {
  background: #334155;
}

@media (max-width: 800px) {
  table {
    width: 100%;
  }

  .table-container {
    padding: 0 8px;
  }

  .edit-form {
    width: 100%;
  }
}
</style>
