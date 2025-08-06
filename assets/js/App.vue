<template>
  <div class="page-header">
    <h1>Employee Management</h1>
    <EmployeeManager/>
  </div>
</template>

<script setup>
import EmployeeManager from './components/EmployeeManager.vue';
</script>

<style scoped>
.page-header {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
}

h1 {
  margin-top: 48px;
  margin-bottom: 32px;
  font-size: 2.5rem;
  font-weight: 700;
  color: #334155;
  letter-spacing: 0.03em;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}
</style>
