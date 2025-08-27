<script setup>
import { onMounted, ref } from "vue";
import { useAuthStore } from "../../stores/auth";
import api from "../../plugins/api";
import Swal from "sweetalert2";

const auth = useAuthStore();

const loading = ref(false);
const tasks = ref([]);

async function load() {
  loading.value = true;
  try {
    const { data } = await api.get("/api/tasks");
    tasks.value = Array.isArray(data?.data) ? data.data : data;
  } finally {
    loading.value = false;
  }
}

async function updateStatus(t, newState) {
  const oldState = t.state;
  t.state = newState;
  try {
    await api.put(`/api/tasks/${t.id}`, { state: newState });
    Swal.fire({ 
      icon: "success", 
      title: "Tarea actualizada", 
      timer: 800, 
      showConfirmButton: false 
    });
  } catch (error) {
    t.state = oldState;
    console.error("Error actualizando tarea:", error);
    Swal.fire({ 
      icon: "error", 
      title: "Error al actualizar la tarea", 
      timer: 1500, 
      showConfirmButton: false 
    });
  }
}

function getPriorityClass(priority) {
  switch (priority) {
    case 'Alta': return 'bg-red-100 text-red-700';
    case 'Media': return 'bg-yellow-100 text-yellow-700';
    case 'Baja': return 'bg-green-100 text-green-700';
    default: return 'bg-gray-100 text-gray-700';
  }
}

function getStateClass(state) {
  switch (state) {
    case 'Hecha': return 'bg-green-100 text-green-700';
    case 'En progreso': return 'bg-blue-100 text-blue-700';
    case 'Pendiente': return 'bg-gray-100 text-gray-700';
    default: return 'bg-gray-100 text-gray-700';
  }
}

onMounted(async () => {
  if (!auth.user) await auth.profile();
  await load();
});
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold text-slate-800">
        {{ auth.user?.role === 'admin' ? 'Todas las tareas' : 'Mis tareas' }}
      </h1>
      <button 
        @click="load()" 
        class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
      >
        Actualizar
      </button>
    </div>

    <div class="bg-white border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left p-3">Título</th>
            <th class="text-left p-3">Proyecto</th>
            <th class="text-left p-3">Asignado a</th>
            <th class="text-left p-3">Prioridad</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-left p-3">Vence</th>
            <th class="text-left p-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="7" class="p-6 text-center text-slate-500">Cargando...</td>
          </tr>
          
          <tr v-for="t in tasks" :key="t.id" class="border-t hover:bg-slate-50">
            <td class="p-3">
              <div>
                <div class="font-medium text-slate-800">{{ t.title }}</div>
                <div v-if="t.description" class="text-xs text-slate-500 mt-1 truncate max-w-xs">
                  {{ t.description }}
                </div>
              </div>
            </td>
            
            <td class="p-3">
              <span class="text-slate-700">{{ t.project?.name || '—' }}</span>
            </td>
            
            <td class="p-3">
              <span class="text-slate-700">{{ t.assignee?.name || '—' }}</span>
            </td>
            
            <td class="p-3">
              <span 
                class="px-2 py-1 rounded text-xs font-medium"
                :class="getPriorityClass(t.priority)"
              >
                {{ t.priority }}
              </span>
            </td>
            
            <td class="p-3">
              <span 
                class="px-2 py-1 rounded text-xs font-medium"
                :class="getStateClass(t.state)"
              >
                {{ t.state }}
              </span>
            </td>
            
            <td class="p-3">
              <span class="text-slate-700">{{ t.due_date || '—' }}</span>
            </td>
            
            <td class="p-3">
              <div v-if="auth.user?.role !== 'admin' && t.assigned_to === auth.user?.id">
                <select
                  class="border rounded px-2 py-1 text-xs"
                  :value="t.state"
                  @change="updateStatus(t, $event.target.value)"
                >
                  <option value="Pendiente">Pendiente</option>
                  <option value="En progreso">En progreso</option>
                  <option value="Hecha">Hecha</option>
                </select>
              </div>
              
              <div v-else-if="auth.user?.role === 'admin'" class="flex gap-1">
                <select
                  class="border rounded px-2 py-1 text-xs"
                  :value="t.state"
                  @change="updateStatus(t, $event.target.value)"
                >
                  <option value="Pendiente">Pendiente</option>
                  <option value="En progreso">En progreso</option>
                  <option value="Hecha">Hecha</option>
                </select>
              </div>
              
              <span v-else class="text-slate-400 text-xs">—</span>
            </td>
          </tr>

          <tr v-if="!loading && tasks.length === 0">
            <td colspan="7" class="p-6 text-center text-slate-500">
              {{ auth.user?.role === 'admin' ? 'No hay tareas creadas.' : 'No hay tareas asignadas.' }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>