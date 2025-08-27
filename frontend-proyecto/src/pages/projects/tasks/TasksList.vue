<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../../stores/auth";
import api from "../../../plugins/api";
import Swal from "sweetalert2";

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const projectId = computed(() => route.params.projectId);

const loading = ref(true);
const tasks = ref([]);
const project = ref(null);
const search = ref("");
const filterState = ref("");
const filterPriority = ref("");
const filterAssigned = ref("");

const filtered = computed(() => {
  let result = tasks.value;

  if (search.value.trim()) {
    const s = search.value.trim().toLowerCase();
    result = result.filter((t) =>
      [t.title, t.description].some((v) =>
        String(v || "")
          .toLowerCase()
          .includes(s)
      )
    );
  }

  if (filterState.value) {
    result = result.filter((t) => t.state === filterState.value);
  }

  if (filterPriority.value) {
    result = result.filter((t) => t.priority === filterPriority.value);
  }

  if (filterAssigned.value) {
    result = result.filter(
      (t) =>
        t.assignees &&
        t.assignees.some((assignee) => assignee.id == filterAssigned.value)
    );
  }

  return result;
});

const assignedUsers = computed(() => {
  const uniqueUsers = [];
  const seenIds = new Set();

  tasks.value.forEach((task) => {
    if (task.assignees && Array.isArray(task.assignees)) {
      task.assignees.forEach((assignee) => {
        if (!seenIds.has(assignee.id)) {
          uniqueUsers.push(assignee);
          seenIds.add(assignee.id);
        }
      });
    }
  });

  return uniqueUsers;
});

async function loadProject() {
  try {
    const response = await api.get(`/api/projects/${projectId.value}`);
    project.value = response.data.data || response.data;
  } catch (error) {
    console.error("Error cargando proyecto:", error);
    Swal.fire("Error", "No se pudo cargar el proyecto", "error");
    router.push("/projects");
  }
}

async function loadTasks() {
  loading.value = true;
  try {
    const { data } = await api.get(`/api/tasks?project_id=${projectId.value}`);
    tasks.value = Array.isArray(data?.data) ? data.data : data;
    /* console.log("Tareas cargadas:", tasks.value); */
  } catch (error) {
    console.error("Error cargando tareas:", error);
    Swal.fire("Error", "No se pudieron cargar las tareas", "error");
  } finally {
    loading.value = false;
  }
}

function goNewTask() {
  router.push(`/projects/${projectId.value}/tasks/new`);
}

function goEditTask(taskId) {
  router.push(`/projects/${projectId.value}/tasks/${taskId}/edit`);
}

function canUpdateTask(task) {
  /*  console.log('Verificando permisos:', {
    userRole: auth.user?.role,
    userId: auth.user?.id,
    taskAssignees: task.assignees,
    canUpdate: auth.user?.role === 'admin' || (task.assignees && task.assignees.some(a => a.id === auth.user?.id))
  }); */

  if (auth.user?.role === "admin") return true;

  return (
    task.assignees &&
    task.assignees.some((assignee) => assignee.id === auth.user?.id)
  );
}

async function updateTaskState(task, newState) {
  const oldState = task.state;
  task.state = newState;

  try {
    await api.put(`/api/tasks/${task.id}`, { state: newState });
    Swal.fire({
      icon: "success",
      title: "Estado actualizado",
      timer: 800,
      showConfirmButton: false,
    });
  } catch (error) {
    task.state = oldState;
    console.error("Error actualizando tarea:", error);

    if (error.response?.status === 403) {
      Swal.fire(
        "Error",
        "No tienes permisos para actualizar esta tarea",
        "error"
      );
    } else {
      Swal.fire("Error", "No se pudo actualizar el estado", "error");
    }
  }
}

async function deleteTask(taskId) {
  const result = await Swal.fire({
    icon: "warning",
    title: "Eliminar tarea",
    text: "Esta acción no se puede deshacer",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });

  if (!result.isConfirmed) return;

  try {
    await api.delete(`/api/tasks/${taskId}`);
    await loadTasks();
    Swal.fire({
      icon: "success",
      title: "Tarea eliminada",
      timer: 1000,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error("Error eliminando tarea:", error);
    Swal.fire("Error", "No se pudo eliminar la tarea", "error");
  }
}

function getPriorityClass(priority) {
  switch (priority) {
    case "Alta":
      return "bg-red-100 text-red-700 border-red-200";
    case "Media":
      return "bg-yellow-100 text-yellow-700 border-yellow-200";
    case "Baja":
      return "bg-green-100 text-green-700 border-green-200";
    default:
      return "bg-gray-100 text-gray-700 border-gray-200";
  }
}

function getStateClass(state) {
  switch (state) {
    case "Hecha":
      return "bg-green-100 text-green-700";
    case "En progreso":
      return "bg-blue-100 text-blue-700";
    case "Pendiente":
      return "bg-gray-100 text-gray-700";
    default:
      return "bg-gray-100 text-gray-700";
  }
}

function clearFilters() {
  search.value = "";
  filterState.value = "";
  filterPriority.value = "";
  filterAssigned.value = "";
}

function getAssigneesNames(task) {
  if (!task.assignees || !Array.isArray(task.assignees)) return "—";
  return task.assignees.map((a) => a.name).join(", ");
}

onMounted(async () => {
  if (!auth.user) await auth.profile();

  await Promise.all([loadProject(), loadTasks()]);
});
</script>

<template>
  <div class="p-6">
    <div class="flex items-center text-sm text-slate-600 mb-4">
      <router-link to="/projects" class="hover:text-indigo-600"
        >Proyectos</router-link
      >
      <span class="mx-2">›</span>
      <span class="text-slate-800">{{ project?.name || "Cargando..." }}</span>
      <span class="mx-2">›</span>
      <span class="text-slate-800">Tareas</span>
    </div>

    <div
      v-if="project"
      class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6"
    >
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-semibold text-indigo-900">
            {{ project.name }}
          </h1>
          <p v-if="project.description" class="text-indigo-700 text-sm mt-1">
            {{ project.description }}
          </p>
          <div class="flex items-center gap-4 text-indigo-600 text-xs mt-2">
            <span>{{ project.collaborators_count }} colaboradores</span>
            <span>{{ tasks.length }} tareas</span>
            <span class="px-2 py-1 bg-indigo-200 text-indigo-800 rounded">{{
              project.status
            }}</span>
          </div>
        </div>

        <div class="flex items-center gap-2">
          <button
            @click="loadTasks"
            class="px-3 py-2 rounded-lg bg-indigo-100 text-indigo-700 hover:bg-indigo-200 text-sm"
          >
            Actualizar
          </button>

          <button
            v-if="auth.user?.role === 'admin'"
            @click="goNewTask"
            class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
          >
            Nueva Tarea
          </button>
        </div>
      </div>
    </div>

    <div class="bg-white border rounded-xl p-4 mb-6">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
          <input
            v-model="search"
            type="text"
            placeholder="Buscar tareas..."
            class="w-full border rounded-lg px-3 py-2 text-sm"
          />
        </div>

        <div>
          <select
            v-model="filterState"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
            <option value="">Todos los estados</option>
            <option value="Pendiente">Pendiente</option>
            <option value="En progreso">En progreso</option>
            <option value="Hecha">Hecha</option>
          </select>
        </div>

        <div>
          <select
            v-model="filterPriority"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
            <option value="">Todas las prioridades</option>
            <option value="Alta">Alta</option>
            <option value="Media">Media</option>
            <option value="Baja">Baja</option>
          </select>
        </div>

        <div>
          <select
            v-model="filterAssigned"
            class="w-full border rounded-lg px-3 py-2 text-sm"
          >
            <option value="">Todos los asignados</option>
            <option v-for="u in assignedUsers" :key="u.id" :value="u.id">
              {{ u.name }}
            </option>
          </select>
        </div>
      </div>

      <div class="flex justify-end mt-3">
        <button
          @click="clearFilters"
          class="px-3 py-1 text-sm text-slate-600 hover:text-slate-800"
        >
          Limpiar filtros
        </button>
      </div>
    </div>

    <div class="bg-white border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left p-3">Tarea</th>
            <th class="text-left p-3">Asignados</th>
            <th class="text-left p-3">Prioridad</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-left p-3">Vence</th>
            <th class="text-right p-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="p-6 text-center text-slate-500">
              Cargando tareas...
            </td>
          </tr>

          <tr
            v-for="task in filtered"
            :key="task.id"
            class="border-t hover:bg-slate-50"
          >
            <td class="p-3">
              <div class="max-w-xs">
                <div class="font-medium text-slate-800">{{ task.title }}</div>
                <div
                  v-if="task.description"
                  class="text-xs text-slate-500 mt-1 line-clamp-2"
                  :title="task.description"
                >
                  {{ task.description }}
                </div>
              </div>
            </td>

            <td class="p-3">
              <div class="max-w-xs">
                <span class="text-slate-700 text-sm">{{
                  getAssigneesNames(task)
                }}</span>
                <div
                  v-if="task.assignees && task.assignees.length > 1"
                  class="flex flex-wrap gap-1 mt-1"
                >
                  <!--  <span
                    v-for="assignee in task.assignees"
                    :key="assignee.id"
                    class="px-1 py-0.5 bg-slate-100 text-slate-600 rounded text-xs"
                  >
                    {{ assignee.name }}
                  </span> -->
                </div>
              </div>
            </td>

            <td class="p-3">
              <span
                class="px-2 py-1 rounded text-xs font-medium border"
                :class="getPriorityClass(task.priority)"
              >
                {{ task.priority }}
              </span>
            </td>

            <td class="p-3">
              <select
                v-if="canUpdateTask(task)"
                :value="task.state"
                @change="updateTaskState(task, $event.target.value)"
                class="px-2 py-1 rounded text-xs font-medium border"
                :class="getStateClass(task.state)"
              >
                <option value="Pendiente">Pendiente</option>
                <option value="En progreso">En progreso</option>
                <option value="Hecha">Hecha</option>
              </select>

              <span
                v-else
                class="px-2 py-1 rounded text-xs font-medium"
                :class="getStateClass(task.state)"
              >
                {{ task.state }}
              </span>
            </td>

            <td class="p-3">
              <span
                class="text-slate-700"
                :class="{
                  'text-red-600 font-medium':
                    task.due_date &&
                    new Date(task.due_date) < new Date() &&
                    task.state !== 'Hecha',
                }"
              >
                {{ task.due_date || "—" }}
              </span>
            </td>

            <td class="p-3 text-right">
              <div v-if="auth.user?.role === 'admin'" class="inline-flex gap-2">
                <button
                  @click="goEditTask(task.id)"
                  class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs"
                >
                  Editar
                </button>
                <button
                  @click="deleteTask(task.id)"
                  class="px-2 py-1 rounded bg-rose-100 text-rose-700 hover:bg-rose-200 text-xs"
                >
                  Eliminar
                </button>
              </div>
              <span v-else class="text-slate-400 text-xs">—</span>
            </td>
          </tr>

          <tr v-if="!loading && filtered.length === 0">
            <td colspan="6" class="p-6 text-center text-slate-500">
              {{
                tasks.length === 0
                  ? "No hay tareas en este proyecto."
                  : "No se encontraron tareas con los filtros aplicados."
              }}
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
