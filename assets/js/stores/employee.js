import {defineStore} from 'pinia';

export const useEmployeeStore = defineStore('employee', {
    state: () => ({
        employees: [],
        loading: false,
        error: null,
        // Estados para filtros y búsqueda
        searchTerm: '',
        selectedDepartment: '',
        selectedStatus: '',
        selectedRole: '',
        // Nuevos estados para paginación
        currentPage: 1,
        itemsPerPage: 10,
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
            this.currentPage = 1; // Reset pagination when clearing filters
        },

        // Nuevas acciones para paginación
        setCurrentPage(page) {
            this.currentPage = page;
        },

        setItemsPerPage(items) {
            this.itemsPerPage = items;
            this.currentPage = 1; // Reset to first page when changing items per page
        },

        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },

        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },

        goToFirstPage() {
            this.currentPage = 1;
        },

        goToLastPage() {
            this.currentPage = this.totalPages;
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
        filteredActiveEmployees: (state, getters) => {
            // Extra safety checks to prevent initialization errors
            if (!getters || !getters.filteredEmployees || !Array.isArray(getters.filteredEmployees)) {
                return [];
            }
            return getters.filteredEmployees.filter(emp => emp && emp.status === 'active');
        },
        filteredInactiveEmployees: (state, getters) => {
            // Extra safety checks to prevent initialization errors
            if (!getters || !getters.filteredEmployees || !Array.isArray(getters.filteredEmployees)) {
                return [];
            }
            return getters.filteredEmployees.filter(emp => emp && emp.status === 'inactive');
        },

        // Nuevos getters para paginación
        totalPages: (state, getters) => {
            if (!getters || !getters.filteredEmployees || !Array.isArray(getters.filteredEmployees)) {
                return 0;
            }
            return Math.ceil(getters.filteredEmployees.length / (state.itemsPerPage || 10));
        },

        paginatedEmployees: (state, getters) => {
            if (!getters || !getters.filteredEmployees || !Array.isArray(getters.filteredEmployees)) {
                return [];
            }
            const itemsPerPage = state.itemsPerPage || 10;
            const currentPage = state.currentPage || 1;
            const start = (currentPage - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            return getters.filteredEmployees.slice(start, end);
        },

        totalFilteredEmployees: (state, getters) => {
            if (!getters || !getters.filteredEmployees || !Array.isArray(getters.filteredEmployees)) {
                return 0;
            }
            return getters.filteredEmployees.length;
        },

        paginationInfo: (state, getters) => {
            if (!getters.filteredEmployees || !getters.totalFilteredEmployees) {
                return {
                    start: 0,
                    end: 0,
                    total: 0,
                    currentPage: 1,
                    totalPages: 0,
                    hasNextPage: false,
                    hasPreviousPage: false,
                };
            }
            const start = (state.currentPage - 1) * state.itemsPerPage + 1;
            const end = Math.min(state.currentPage * state.itemsPerPage, getters.totalFilteredEmployees);
            return {
                start,
                end,
                total: getters.totalFilteredEmployees,
                currentPage: state.currentPage,
                totalPages: getters.totalPages,
                hasNextPage: state.currentPage < getters.totalPages,
                hasPreviousPage: state.currentPage > 1,
            };
        },

        // Páginas visibles para el paginador
        visiblePages: (state, getters) => {
            const total = getters.totalPages;
            const current = state.currentPage;
            const delta = 2; // Número de páginas a mostrar a cada lado de la actual

            let start = Math.max(1, current - delta);
            let end = Math.min(total, current + delta);

            // Ajustar si estamos cerca del inicio o final
            if (current <= delta + 1) {
                end = Math.min(total, delta * 2 + 1);
            }
            if (current >= total - delta) {
                start = Math.max(1, total - delta * 2);
            }

            const pages = [];
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        },
    }
});
