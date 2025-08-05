import {defineStore} from 'pinia';

export const useEmployeeStore = defineStore('employee', {
    state: () => ({
        employees: [],
        loading: false,
        error: null,
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
        }
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
        }
    }
});
