<script setup>
import { ref, onMounted, computed } from "vue";
import { useAuthStore } from "../stores/auth";
import api from "../plugins/api";
import Swal from "sweetalert2";
import Navbar from "../components/Navbar.vue";
import Sidebar from "../components/Sidebar.vue";

const auth = useAuthStore();
const open = ref(false);

const users = ref([]);
const projects = ref([]);
const stats = ref({
  total_users: 0,
  active_users: 0,
  admins: 0,
  collaborators: 0,
});
const loading = ref(false);
const search = ref("");
const filterRole = ref("");
const filterActive = ref("");

const showUserModal = ref(false);
const showProjectModal = ref(false);
const selectedUser = ref(null);
const userProjects = ref([]);
const selectedProjects = ref([]);

const userForm = ref({
  name: "",
  email: "",
  password: "",
  role: "colaborador",
  active: true,
});

const filtered = computed(() => {
  let result = users.value;

  if (search.value.trim()) {
    const s = search.value.trim().toLowerCase();
    result = result.filter(
      (u) =>
        u.name.toLowerCase().includes(s) || u.email.toLowerCase().includes(s)
    );
  }

  if (filterRole.value) {
    result = result.filter((u) => u.role === filterRole.value);
  }

  if (filterActive.value !== "") {
    result = result.filter((u) => u.active === (filterActive.value === "1"));
  }

  return result;
});

async function loadUsers() {
  if (auth.user?.role !== "admin") return;

  loading.value = true;
  try {
    const { data } = await api.get("/api/users");
    users.value = Array.isArray(data?.data)
      ? data.data
      : Array.isArray(data)
      ? data
      : [];

    calculateStatsFromUsers();
  } catch (error) {
    console.error("Error cargando usuarios:", error);
    users.value = [];
  } finally {
    loading.value = false;
  }
}

async function loadProjects() {
  if (auth.user?.role !== "admin") return;

  try {
    const { data } = await api.get("/api/projects");
    projects.value = Array.isArray(data?.data)
      ? data.data
      : Array.isArray(data)
      ? data
      : [];
  } catch (error) {
    console.error("Error cargando proyectos:", error);
    projects.value = [];
  }
}

async function loadStats() {
  if (auth.user?.role !== "admin") return;

  try {
    const { data } = await api.get("/api/users/stats");
    stats.value = {
      total_users: data.total_users || 0,
      active_users: data.active_users || 0,
      admins: data.admins || 0,
      collaborators: data.collaborators || 0,
    };
  } catch (error) {
    console.error("Error cargando estadísticas:", error);
    calculateStatsFromUsers();
  }
}

function calculateStatsFromUsers() {
  if (!users.value || users.value.length === 0) return;

  stats.value = {
    total_users: users.value.length,
    active_users: users.value.filter((u) => u.active).length,
    admins: users.value.filter((u) => u.role === "admin").length,
    collaborators: users.value.filter((u) => u.role === "colaborador").length,
  };
}

async function toggleUserStatus(user) {
  try {
    await api.put(`/api/users/${user.id}/toggle-status`);
    user.active = !user.active;
    Swal.fire({
      icon: "success",
      title: user.active ? "Usuario activado" : "Usuario desactivado",
      timer: 1000,
      showConfirmButton: false,
    });
    calculateStatsFromUsers();
  } catch (error) {
    console.error("Error cambiando estado:", error);
    const message = error.response?.data?.message || "Error al cambiar estado";
    Swal.fire("Error", message, "error");
  }
}

async function updateUserRole(user, newRole) {
  try {
    await api.put(`/api/users/${user.id}/role`, { role: newRole });
    user.role = newRole;
    Swal.fire({
      icon: "success",
      title: "Rol actualizado",
      timer: 1000,
      showConfirmButton: false,
    });
    calculateStatsFromUsers();
  } catch (error) {
    console.error("Error actualizando rol:", error);
    const message = error.response?.data?.message || "Error al actualizar rol";
    Swal.fire("Error", message, "error");
  }
}

async function saveProjectAssignments() {
  try {
    await api.put(`/api/users/${selectedUser.value.id}/projects`, {
      project_ids: selectedProjects.value,
    });

    Swal.fire({
      icon: "success",
      title: "Proyectos asignados correctamente",
      timer: 1500,
      showConfirmButton: false,
    });

    showProjectModal.value = false;
    await loadUsers();
  } catch (error) {
    console.error("Error asignando proyectos:", error);
    Swal.fire("Error", "No se pudieron asignar los proyectos", "error");
  }
}

async function createUser() {
  try {
    await api.post("/api/users", userForm.value);

    Swal.fire({
      icon: "success",
      title: "Usuario creado correctamente",
      timer: 1500,
      showConfirmButton: false,
    });

    showUserModal.value = false;
    resetUserForm();
    await loadUsers();
  } catch (error) {
    console.error("Error creando usuario:", error);
    const message = error.response?.data?.message || "Error al crear usuario";
    Swal.fire("Error", message, "error");
  }
}

async function deleteUser(user) {
  const result = await Swal.fire({
    icon: "warning",
    title: "Eliminar usuario",
    text: `¿Eliminar a ${user.name}? Esta acción no se puede deshacer.`,
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
  });

  if (!result.isConfirmed) return;

  try {
    await api.delete(`/api/users/${user.id}`);
    Swal.fire({
      icon: "success",
      title: "Usuario eliminado",
      timer: 1000,
      showConfirmButton: false,
    });
    await loadUsers();
  } catch (error) {
    console.error("Error eliminando usuario:", error);
    const message =
      error.response?.data?.message || "Error al eliminar usuario";
    Swal.fire("Error", message, "error");
  }
}

function openUserModal() {
  resetUserForm();
  showUserModal.value = true;
}

function resetUserForm() {
  userForm.value = {
    name: "",
    email: "",
    password: "",
    role: "colaborador",
    active: true,
  };
}

function getRoleClass(role) {
  return role === "admin"
    ? "bg-purple-100 text-purple-700"
    : "bg-blue-100 text-blue-700";
}

function getStatusClass(active) {
  return active ? "bg-green-600 text-white" : "bg-red-600 text-white";
}

function closeSidebar() {
  open.value = false;
}

onMounted(async () => {
  if (!auth.user) await auth.profile();

  if (auth.user?.role === "admin") {
    await loadUsers();
    await loadProjects();
    await loadStats();
  }
});
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <div
      class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 grid lg:grid-cols-[16rem_1fr] gap-4"
    >
      <button
        class="lg:hidden inline-flex items-center gap-2 px-3 py-2 mb-2 rounded-lg bg-slate-900 text-white"
        @click="open = true"
      >
        <svg viewBox="0 0 24 24" class="h-5 w-5">
          <path
            fill="currentColor"
            d="M3 6h18v2H3V6m0 5h18v2H3v-2m0 5h18v2H3v-2"
          />
        </svg>
        Menú
      </button>

      <Sidebar
        :modelValue="open"
        @update:modelValue="open = $event"
        class="lg:sticky lg:top-4 lg:h-[calc(100vh-6rem)]"
      />

      <main class="bg-white border border-slate-200 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-semibold text-slate-800">Dashboard</h1>
        </div>

        <div
          v-if="auth.user?.role === 'admin'"
          class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8"
        >
          <div
            class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-xl border border-blue-200"
          >
            <div class="text-blue-600 text-sm font-medium">Total Usuarios</div>
            <div class="text-2xl font-bold text-blue-900">
              {{ stats.total_users }}
            </div>
          </div>

          <div
            class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-xl border border-green-200"
          >
            <div class="text-green-600 text-sm font-medium">
              Usuarios Activos
            </div>
            <div class="text-2xl font-bold text-green-900">
              {{ stats.active_users }}
            </div>
          </div>

          <div
            class="bg-gradient-to-r from-purple-50 to-purple-100 p-4 rounded-xl border border-purple-200"
          >
            <div class="text-purple-600 text-sm font-medium">
              Administradores
            </div>
            <div class="text-2xl font-bold text-purple-900">
              {{ stats.admins }}
            </div>
          </div>

          <div
            class="bg-gradient-to-r from-orange-50 to-orange-100 p-4 rounded-xl border border-orange-200"
          >
            <div class="text-orange-600 text-sm font-medium">Colaboradores</div>
            <div class="text-2xl font-bold text-orange-900">
              {{ stats.collaborators }}
            </div>
          </div>
        </div>

        <div v-if="auth.user?.role === 'admin'">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-slate-800">
              Gestión de Usuarios
            </h2>
          </div>

          <div class="bg-slate-50 p-4 rounded-xl mb-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <input
                v-model="search"
                type="text"
                placeholder="Buscar por nombre o email..."
                class="border rounded-lg px-3 py-2 text-sm"
              />

              <select
                v-model="filterRole"
                class="border rounded-lg px-3 py-2 text-sm"
              >
                <option value="">Todos los roles</option>
                <option value="admin">Administradores</option>
                <option value="colaborador">Colaboradores</option>
              </select>

              <select
                v-model="filterActive"
                class="border rounded-lg px-3 py-2 text-sm"
              >
                <option value="">Todos los estados</option>
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
              </select>

              <button
                @click="
                  search = '';
                  filterRole = '';
                  filterActive = '';
                "
                class="px-3 py-2 bg-slate-200 text-slate-600 rounded-lg hover:bg-slate-300 text-sm"
              >
                Limpiar
              </button>
            </div>
          </div>

          <div class="bg-white border rounded-xl overflow-hidden">
            <table class="w-full text-sm">
              <thead class="bg-slate-50 text-slate-600">
                <tr>
                  <th class="text-left p-4">Usuario</th>
                  <th class="text-left p-4">Rol</th>
                  <th class="text-left p-4">Estado</th>
                  <th class="text-left p-4">Proyectos</th>
                  <th class="text-right p-4">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="loading">
                  <td colspan="5" class="p-6 text-center text-slate-500">
                    Cargando usuarios...
                  </td>
                </tr>

                <tr
                  v-for="user in filtered"
                  :key="user.id"
                  class="border-t hover:bg-slate-50"
                >
                  <td class="p-4">
                    <div>
                      <div class="font-medium text-slate-800">
                        {{ user.name }}
                      </div>
                      <div class="text-slate-500 text-sm">{{ user.email }}</div>
                    </div>
                  </td>

                  <td class="p-4">
                    <select
                      :value="user.role"
                      @change="updateUserRole(user, $event.target.value)"
                      class="px-2 py-1 rounded text-xs font-medium border"
                      :class="getRoleClass(user.role)"
                    >
                      <option value="colaborador">Colaborador</option>
                      <option value="admin">Administrador</option>
                    </select>
                  </td>

                  <td class="p-4">
                    <button
                      @click="toggleUserStatus(user)"
                      class="px-2 py-1 rounded text-xs font-medium"
                      :class="getStatusClass(user.active)"
                    >
                      {{ user.active ? "Activo" : "Inactivo" }}
                    </button>
                  </td>

                  <td class="p-4">
                    <button
                      class="text-indigo-600 hover:text-indigo-800 text-sm"
                    >
                      {{ user.projects_count || 0 }} proyectos
                    </button>
                  </td>

                  <td class="p-4 text-right">
                    <button
                      @click="deleteUser(user)"
                      :disabled="user.id === auth.user?.id"
                      class="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700 text-xs disabled:opacity-50"
                    >
                      Eliminar
                    </button>
                  </td>
                </tr>

                <tr v-if="!loading && filtered.length === 0">
                  <td colspan="5" class="p-6 text-center text-slate-500">
                    No se encontraron usuarios
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div v-else>
          <p class="text-slate-600">
            Bienvenido al sistema de gestión de proyectos.
          </p>
          <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-6 bg-blue-50 rounded-xl border border-blue-200">
              <h3 class="font-semibold text-blue-900 mb-2">Mis Proyectos</h3>
              <p class="text-blue-700 text-sm">
                Ve tus proyectos asignados desde el menú lateral
              </p>
            </div>
            <div class="p-6 bg-green-50 rounded-xl border border-green-200">
              <h3 class="font-semibold text-green-900 mb-2">Mis Tareas</h3>
              <p class="text-green-700 text-sm">
                Gestiona tus tareas pendientes
              </p>
            </div>
          </div>
        </div>
      </main>
    </div>

    <div
      v-if="showProjectModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div
        class="bg-white p-6 rounded-xl max-w-md w-full mx-4 max-h-96 overflow-y-auto"
      >
        <h3 class="text-lg font-semibold mb-4">
          Asignar Proyectos a {{ selectedUser?.name }}
        </h3>

        <div class="space-y-2 mb-6" v-if="projects.length > 0">
          <label
            v-for="project in projects"
            :key="project.id"
            class="flex items-center gap-2"
          >
            <input
              v-model="selectedProjects"
              :value="project.id"
              type="checkbox"
            />
            <span class="text-sm">{{ project.name }}</span>
            <span class="text-xs text-slate-500">({{ project.status }})</span>
          </label>
        </div>

        <div v-else class="text-center text-slate-500 mb-6">
          No hay proyectos disponibles
        </div>

        <div class="flex gap-3">
          <button
            @click="saveProjectAssignments"
            class="flex-1 bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700"
            :disabled="projects.length === 0"
          >
            Guardar
          </button>
          <button
            @click="showProjectModal = false"
            class="flex-1 bg-slate-200 text-slate-600 py-2 rounded-lg hover:bg-slate-300"
          >
            Cancelar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
