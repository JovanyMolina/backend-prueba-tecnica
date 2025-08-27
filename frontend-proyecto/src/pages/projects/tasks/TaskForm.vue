<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../../stores/auth";
import api from "../../../plugins/api";
import Swal from "sweetalert2";

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const isEdit = computed(() => Boolean(route.params.taskId));
const projectId = computed(() => route.params.projectId);

const form = ref({
  title: "",
  description: "",
  due_date: "",
  state: "Pendiente",
  priority: "Media",
  assigned_users: [],
});

const loading = ref(false);
const project = ref(null);

async function loadProject() {
  try {
    const response = await api.get(`/api/projects/${projectId.value}`);
    project.value = response.data.data || response.data;
    /*   console.log("Proyecto cargado:", project.value); */
  } catch (error) {
    console.error("Error cargando proyecto:", error);
    Swal.fire("Error", "No se pudo cargar el proyecto", "error");
    router.push("/projects");
  }
}

async function loadTask() {
  if (!isEdit.value) return;

  try {
    const response = await api.get(`/api/tasks?project_id=${projectId.value}`);
    const tasks = Array.isArray(response.data?.data)
      ? response.data.data
      : response.data;
    const task = tasks.find((t) => t.id == route.params.taskId);

    if (!task) {
      throw new Error("Tarea no encontrada");
    }

    form.value = {
      title: task.title || "",
      description: task.description || "",
      due_date: task.due_date || "",
      state: task.state || "Pendiente",
      priority: task.priority || "Media",
      assigned_users: task.assignees
        ? task.assignees.map((u) => Number(u.id))
        : [],
    };

    /*  console.log("Tarea cargada:", task);
    console.log("Usuarios asignados:", form.value.assigned_users); */
  } catch (error) {
    console.error("Error cargando tarea:", error);
    Swal.fire("Error", "No se pudo cargar la tarea", "error");
    router.push(`/projects/${projectId.value}/tasks`);
  }
}

async function submit() {
  if (!form.value.title.trim()) {
    Swal.fire("Error", "El título es obligatorio", "error");
    return;
  }

  if (!form.value.assigned_users.length) {
    Swal.fire(
      "Error",
      "Debe asignar la tarea a al menos un colaborador",
      "error"
    );
    return;
  }

  loading.value = true;

  const payload = {
    title: form.value.title,
    description: form.value.description,
    due_date: form.value.due_date || null,
    state: form.value.state,
    priority: form.value.priority,
    assigned_users: form.value.assigned_users.map(Number),
  };

  console.log("=== ENVIANDO TAREA ===");
  console.log("Payload:", payload);

  try {
    if (isEdit.value) {
      await api.put(`/api/tasks/${route.params.taskId}`, payload);
      Swal.fire("Éxito", "Tarea actualizada correctamente", "success");
    } else {
      await api.post(`/api/projects/${projectId.value}/tasks`, payload);
      Swal.fire("Éxito", "Tarea creada correctamente", "success");
    }

    router.push(`/projects/${projectId.value}/tasks`);
  } catch (error) {
    console.error("Error guardando tarea:", error);
    const message =
      error.response?.data?.message || "Error al guardar la tarea";
    Swal.fire("Error", message, "error");
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  if (!auth.user) await auth.profile();

  if (auth.user?.role !== "admin") {
    Swal.fire("Error", "Solo administradores pueden gestionar tareas", "error");
    router.push(`/projects/${projectId.value}/tasks`);
    return;
  }

  await loadProject();

  if (isEdit.value) {
    await loadTask();
  }
});
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center text-sm text-slate-600 mb-4">
      <router-link to="/projects" class="hover:text-indigo-600"
        >Proyectos</router-link
      >
      <span class="mx-2">›</span>
      <router-link
        :to="`/projects/${projectId}/tasks`"
        class="hover:text-indigo-600"
      >
        {{ project?.name || "Proyecto" }}
      </router-link>
      <span class="mx-2">›</span>
      <span class="text-slate-800">{{
        isEdit ? "Editar tarea" : "Nueva tarea"
      }}</span>
    </div>

    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-slate-800">
        {{ isEdit ? "Editar tarea" : "Nueva tarea" }}
      </h1>
      <button
        class="text-indigo-600 hover:underline"
        @click="$router.push(`/projects/${projectId}/tasks`)"
      >
        Volver a tareas
      </button>
    </div>

    <div
      v-if="project"
      class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6"
    >
      <div class="flex items-center justify-between">
        <div>
          <div class="text-indigo-800 font-medium">{{ project.name }}</div>
          <div class="text-indigo-600 text-sm mt-1">
            {{ project.collaborators_count }} colaborador(es) disponible(s)
          </div>
        </div>
        <span class="px-2 py-1 bg-indigo-200 text-indigo-800 rounded text-xs">
          {{ project.status }}
        </span>
      </div>
    </div>

    <div class="bg-white border rounded-xl p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
          Título *
        </label>
        <input
          v-model="form.title"
          type="text"
          placeholder="¿Qué hay que hacer?"
          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
          Descripción
        </label>
        <textarea
          v-model="form.description"
          placeholder="Detalles adicionales sobre la tarea..."
          rows="3"
          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
        ></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Fecha límite
          </label>
          <input
            v-model="form.due_date"
            type="date"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Prioridad *
          </label>
          <select
            v-model="form.priority"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
          >
            <option value="Baja">Baja</option>
            <option value="Media">Media</option>
            <option value="Alta">Alta</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-slate-700 mb-1">
            Estado *
          </label>
          <select
            v-model="form.state"
            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500"
          >
            <option value="Pendiente">Pendiente</option>
            <option value="En progreso">En progreso</option>
            <option value="Hecha">Hecha</option>
          </select>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700 mb-1">
          Asignar a *
        </label>
        <select
          v-model="form.assigned_users"
          multiple
          class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-200 focus:border-indigo-500 min-h-[120px]"
        >
          <option
            v-for="collaborator in project?.collaborators || []"
            :key="collaborator.id"
            :value="collaborator.id"
          >
            {{ collaborator.name }} — {{ collaborator.email }}
          </option>
        </select>

        <p class="text-xs text-slate-500 mt-1">Mantén CTRL</p>

        <div v-if="form.assigned_users.length" class="mt-3">
          <div class="text-sm text-slate-600 mb-2">
            Colaboradores seleccionados:
          </div>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="userId in form.assigned_users"
              :key="userId"
              class="px-2 py-1 bg-indigo-100 text-indigo-700 rounded text-xs"
            >
              {{
                project?.collaborators?.find((c) => c.id === userId)?.name ||
                `Usuario ${userId}`
              }}
            </span>
          </div>
        </div>

        <p
          v-if="!project?.collaborators?.length"
          class="mt-2 text-sm text-red-600 bg-red-50 p-3 rounded"
        >
          Este proyecto no tiene colaboradores asignados.
          <br />
          <router-link
            :to="`/projects/${projectId}/edit`"
            class="text-indigo-600 hover:underline font-medium"
          >
            Ir a editar proyecto para agregar colaboradores
          </router-link>
        </p>
      </div>

      <div class="pt-4 border-t">
        <div class="flex gap-3">
          <button
            :disabled="loading"
            @click="submit"
            class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60 font-medium"
          >
            {{
              loading
                ? "Guardando..."
                : isEdit
                ? "Guardar cambios"
                : "Crear tarea"
            }}
          </button>

          <button
            @click="$router.push(`/projects/${projectId}/tasks`)"
            class="px-4 py-2 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
