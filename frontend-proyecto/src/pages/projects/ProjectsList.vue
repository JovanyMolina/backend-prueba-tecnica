<script setup>
import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "../../stores/auth";
import api from "../../plugins/api";
import Swal from "sweetalert2";
import { useRouter } from "vue-router";

const auth = useAuthStore();
const router = useRouter();

const loading = ref(true);
const projects = ref([]);
const search = ref("");

const filtered = computed(() => {
  const s = search.value.trim().toLowerCase();
  if (!s) return projects.value;
  return projects.value.filter((p) =>
    [p.name, p.description, p.status].some((v) =>
      String(v || "")
        .toLowerCase()
        .includes(s)
    )
  );
});

async function load() {
  loading.value = true;
  try {
    const { data } = await api.get("/api/projects");

    if (data.data && Array.isArray(data.data)) {
      projects.value = data.data;
    } else if (Array.isArray(data)) {
      projects.value = data;
    } else {
      projects.value = [];
    }

    /*   console.log("Proyectos finales:", projects.value); */
  } finally {
    loading.value = false;
  }
}

function goNew() {
  router.push("/projects/new");
}

function goEdit(id) {
  router.push(`/projects/${id}/edit`);
}

function goTasks(id) {
  router.push(`/projects/${id}/tasks`);
}

async function remove(id) {
  const ok = await Swal.fire({
    icon: "warning",
    title: "Eliminar proyecto",
    text: "Esta acción no se puede deshacer",
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  }).then((r) => r.isConfirmed);

  if (!ok) return;

  await api.delete(`/api/projects/${id}`);
  await load();
  Swal.fire({
    icon: "success",
    title: "Proyecto eliminado",
    timer: 1000,
    showConfirmButton: false,
  });
}

onMounted(async () => {
  if (!auth.user) await auth.profile();
  await load();
});
</script>

<template>
  <div class="p-6">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold text-slate-800">Proyectos</h1>

      <div class="flex items-center gap-3">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar..."
          class="border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-300"
        />

        <button
          v-if="auth.user?.role === 'admin'"
          @click="goNew"
          class="px-3 py-2 rounded-lg bg-indigo-600 text-white text-sm hover:bg-indigo-700"
        >
          Nuevo proyecto
        </button>
      </div>
    </div>

    <div class="bg-white border rounded-xl overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-600">
          <tr>
            <th class="text-left p-3">Nombre</th>
            <th class="text-left p-3">Estado</th>
            <th class="text-left p-3">Inicio</th>
            <th class="text-left p-3">Fin</th>
            <th class="text-left p-3">Colaboradores</th>
            <th class="text-right p-3">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" class="p-6 text-center text-slate-500">
              Cargando...
            </td>
          </tr>

          <tr v-for="p in filtered" :key="p.id" class="border-t">
            <td class="p-3 font-medium text-slate-800">{{ p.name }}</td>
            <td class="p-3">
              <span
                class="px-2 py-1 rounded text-xs"
                :class="{
                  'bg-green-100 text-green-700': p.status === 'Activo',
                  'bg-yellow-100 text-yellow-700': p.status === 'Pausado',
                  'bg-slate-200 text-slate-700': p.status === 'Terminado',
                }"
              >
                {{ p.status }}
              </span>
            </td>
            <td class="p-3">{{ p.start_date }}</td>
            <td class="p-3">{{ p.end_date || "—" }}</td>
            <td class="p-3">
              <span class="text-slate-700">
                {{
                  Array.isArray(p.collaborators)
                    ? p.collaborators.length
                    : p.collaborators_count ?? 0
                }}
              </span>
            </td>
            <td class="p-3 text-right">
              <div class="inline-flex gap-2">
                <button
                  class="px-2 py-1 rounded bg-blue-100 text-blue-700 hover:bg-blue-200 text-xs"
                  @click="goTasks(p.id)"
                >
                  Ver Tareas
                </button>

                <button
                  class="px-2 py-1 rounded bg-slate-100 hover:bg-slate-200 text-xs"
                  @click="goEdit(p.id)"
                  v-if="auth.user?.role === 'admin'"
                >
                  Editar
                </button>
                <button
                  class="px-2 py-1 rounded bg-rose-100 text-rose-700 hover:bg-rose-200 text-xs"
                  @click="remove(p.id)"
                  v-if="auth.user?.role === 'admin'"
                >
                  Eliminar
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!loading && filtered.length === 0">
            <td colspan="6" class="p-6 text-center text-slate-500">
              Sin resultados.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
