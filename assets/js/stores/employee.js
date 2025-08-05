import {defineStore} from 'pinia';

export const useEmployeeStore = defineStore('employee', {
    state: () => ({
        employees: [],
        loading: false,
        error: null,
        // Nuevos estados para filtros y búsqueda
        searchTerm: '',
        selectedDepartment: '',
        selectedStatus: '',
        selectedRole: '',
    }),
    actions: {
        async fetchEmployees() {
            this.loading = true;
            this.error = null;
            try {
                const response = await fetch('/api/employees');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                // ApiPlatform devuelve los datos en data.hydra:member o directamente en data
                this.employees = data['hydra:member'] || data;
            } catch (e) {
                this.error = 'Failed to fetch employees: ' + e.message;
                console.error('Error fetching employees:', e);
            } finally {
                this.loading = false;
            }
        },

        async updateEmployeeStatus(employeeId, newStatus) {
            try {
                const response = await fetch(`/api/employees/${employeeId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/merge-patch+json',
                    },
                    body: JSON.stringify({status: newStatus}),
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                // Actualizar el empleado en el estado local
                const employeeIndex = this.employees.findIndex(emp => emp.id === employeeId);
                if (employeeIndex !== -1) {
                    this.employees[employeeIndex].status = newStatus;
                }

                return true;
            } catch (e) {
                this.error = 'Failed to update employee status: ' + e.message;
                console.error('Error updating employee status:', e);
                return false;
            }
        },

        clearError() {
            this.error = null;
        },

        // Nuevas acciones para filtros
        setSearchTerm(term) {
            this.searchTerm = term;
        },

        setDepartmentFilter(department) {
            this.selectedDepartment = department;
        },

        setStatusFilter(status) {
            this.selectedStatus = status;
        },

        setRoleFilter(role) {
            this.selectedRole = role;
        },

        clearFilters() {
            this.searchTerm = '';
            this.selectedDepartment = '';
            this.selectedStatus = '';
            this.selectedRole = '';
        },
    },

    getters: {
        activeEmployees: (state) => state.employees.filter(emp => emp.status === 'active'),
        inactiveEmployees: (state) => state.employees.filter(emp => emp.status === 'inactive'),
        employeesByDepartment: (state) => {
            return state.employees.reduce((acc, emp) => {
                if (!acc[emp.department]) {
                    acc[emp.department] = [];
                }
                acc[emp.department].push(emp);
                return acc;
            }, {});
        },

        // Nuevos getters para datos filtrados
        filteredEmployees: (state) => {
            let filtered = [...state.employees];

            // Filtro por término de búsqueda
            if (state.searchTerm) {
                const searchLower = state.searchTerm.toLowerCase();
                filtered = filtered.filter(emp =>
                    emp.name.toLowerCase().includes(searchLower) ||
                    emp.email.toLowerCase().includes(searchLower)
                );
            }

            // Filtro por departamento
            if (state.selectedDepartment) {
                filtered = filtered.filter(emp => emp.department === state.selectedDepartment);
            }

            // Filtro por estado
            if (state.selectedStatus) {
                filtered = filtered.filter(emp => emp.status === state.selectedStatus);
            }

            // Filtro por rol
            if (state.selectedRole) {
                filtered = filtered.filter(emp => emp.role === state.selectedRole);
            }

            return filtered;
        },

        uniqueDepartments: (state) => {
            const departments = state.employees.map(emp => emp.department);
            return [...new Set(departments)].sort();
        },

        uniqueRoles: (state) => {
            const roles = state.employees.map(emp => emp.role);
            return [...new Set(roles)].sort();
        },

        // Estadísticas actualizadas para datos filtrados
        filteredActiveEmployees: (state, getters) => getters.filteredEmployees.filter(emp => emp.status === 'active'),
        filteredInactiveEmployees: (state, getters) => getters.filteredEmployees.filter(emp => emp.status === 'inactive'),
    }
});
