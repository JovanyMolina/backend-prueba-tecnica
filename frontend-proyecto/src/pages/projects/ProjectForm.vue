<script setup>
import { ref, onMounted, computed } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useAuthStore } from "../../stores/auth";
import api from "../../plugins/api";
import Swal from "sweetalert2";

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();

const isEdit = computed(() => Boolean(route.params.id));

const form = ref({
  name: "",
  description: "",
  start_date: "",
  end_date: "",
  status: "Activo",
  user_ids: [],
});
const collaborators = ref([]);
const users = ref([]);
const loading = ref(false);


async function loadUsers() {
  try {
    console.log("Cargando usuarios...");
    const { data } = await api.get("/api/users");
    collaborators.value = Array.isArray(data) ? data : data?.data ?? [];
    collaborators.value = collaborators.value.map((u) => ({
      id: Number(u.id),
      name: u.name,
      email: u.email,
      role: u.role,
    }));
    console.log("Colaboradores cargados:", collaborators.value);
  } catch (err) {
    console.error("Error cargando usuarios:", err);
    collaborators.value = [];
  }
}

async function loadProject() {
  try {
    console.log("Cargando proyecto ID:", route.params.id);
    const response = await api.get(`/api/projects/${route.params.id}`);
    console.log("Respuesta completa:", response);
    
    const projectData = response.data.data || response.data;
    console.log("Datos del proyecto:", projectData);
    
    form.value = {
      name: projectData.name || "",
      description: projectData.description || "",
      start_date: projectData.start_date || "",
      end_date: projectData.end_date || "",
      status: projectData.status || "Activo",
      user_ids: Array.isArray(projectData.collaborators) 
        ? projectData.collaborators.map((u) => Number(u.id))
        : [],
    };
    
    console.log("Formulario después de cargar:", form.value);
  } catch (error) {
    console.error("Error cargando proyecto:", error);
  }
}

onMounted(async () => {
  if (!auth.user) await auth.profile();
  if (auth.user?.role !== "admin") {
    router.replace("/projects");
    return;
  }
  await loadUsers();
  if (isEdit.value) await loadProject();
});

async function submit() {
  if (auth.user?.role !== "admin") {
    Swal.fire({ icon: "error", title: "Solo admin puede guardar" });
    router.replace("/projects");
    return;
  }

  loading.value = true;
  
  const payload = {
    name: form.value.name,
    description: form.value.description,
    start_date: form.value.start_date,
    end_date: form.value.end_date,
    status: form.value.status,
    collaborators: form.value.user_ids.map(Number),
  };

  try {
    if (isEdit.value) {
      await api.put(`/api/projects/${route.params.id}`, payload);
      Swal.fire("Éxito", "Proyecto actualizado correctamente", "success");
    } else {
      await api.post("/api/projects", payload);
      Swal.fire("Éxito", "Proyecto creado correctamente", "success");
    }
    router.push("/projects");
  } catch (error) {
    console.error("Error guardando proyecto:", error);
    Swal.fire("Error", "No se pudo guardar el proyecto", "error");
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <div class="p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-semibold text-slate-800">
        {{ isEdit ? "Editar proyecto" : "Nuevo proyecto" }}
      </h1>
      <button class="text-indigo-600 hover:underline" @click="$router.back()">
        Volver
      </button>
    </div>

    <div class="bg-white border rounded-xl p-6 space-y-4">
      <div>
        <label class="block text-sm font-medium text-slate-700">Nombre</label>
        <input
          v-model="form.name"
          type="text"
          class="w-full border rounded-lg px-3 py-2"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700"
          >Descripción</label
        >
        <textarea
          v-model="form.description"
          class="w-full border rounded-lg px-3 py-2"
        ></textarea>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-slate-700">Inicio</label>
          <input
            v-model="form.start_date"
            type="date"
            class="w-full border rounded-lg px-3 py-2"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-700">Fin</label>
          <input
            v-model="form.end_date"
            type="date"
            class="w-full border rounded-lg px-3 py-2"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-slate-700">Estado</label>
        <select
          v-model="form.status"
          class="w-full border rounded-lg px-3 py-2"
        >
          <option value="Activo">Activo</option>
          <option value="Pausado">Pausado</option>
          <option value="Terminado">Terminado</option>
        </select>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-slate-700">
          Colaboradores
        </label>

        <select
          v-model="form.user_ids"
          multiple
          class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 border focus:ring-indigo-200 focus:ring-2 px-3 py-2 outline-none min-h-[140px]"
        >
          <option v-for="u in collaborators" :key="u.id" :value="u.id">
            {{ u.name }} — {{ u.email }} ({{ u.role }})
          </option>
        </select>

        <p v-if="!collaborators.length" class="mt-2 text-sm text-slate-500">
          No hay colaboradores disponibles o no tienes permisos.
        </p>
        <p class="text-xs text-slate-500 mt-1">
          Mantén CTRL para seleccionar varios.
        </p>
      </div>

      <div class="pt-2">
        <button
          :disabled="loading"
          @click="submit"
          class="px-4 py-2 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 disabled:opacity-60"
        >
          {{
            loading
              ? "Guardando..."
              : isEdit
              ? "Guardar cambios"
              : "Crear proyecto"
          }}
        </button>
      </div>
    </div>
  </div>
</template>
