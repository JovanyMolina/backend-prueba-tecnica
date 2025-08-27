<script setup>
import { computed } from "vue";
import { useRoute, useRouter, RouterLink } from "vue-router";
import { useAuthStore } from "../stores/auth";
import Swal from "sweetalert2";



const props = defineProps({
  modelValue: { type: Boolean, default: false },
});
const emit = defineEmits(["update:modelValue"]);

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const user = computed(() => auth.user);
const role = computed(() => auth.user?.role ?? "colaborador");

async function doLogout() {
  await auth.logout();
  Swal.fire({
    icon: "success",
    title: "Sesión cerrada",
    timer: 2000,
    showConfirmButton: false,
  });
  router.push("/");
}

// Links del menú organizados por rol
const links = computed(() => {
  const items = [
    {
      label: "Dashboard",
      to: "/dashboard",
      icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor" d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/>
      </svg>`,
    },
    {
      label: "Proyectos",
      to: "/projects",
      icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor" d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/>
      </svg>`,
    },
    {
      label: "Mis Tareas",
      to: "/mis-tareas",
      icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor" d="M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm-2 14l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/>
      </svg>`,
    },
  ];

  // Solo admin ve registro de usuarios
  if (role.value === "admin") {
    items.splice(1, 0, {
      label: "Registrar Usuarios",
      to: "/register",
      icon: `<svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor" d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
      </svg>`,
    });
  }

  return items;
});

function isActive(to) {
  return route.path === to || route.path.startsWith(to + "/");
}

function closeOnMobile() {
  emit("update:modelValue", false);
}
</script>

<template>
  <div
    class="fixed inset-0 z-40 bg-slate-900/30 lg:hidden"
    v-show="modelValue"
    @click="closeOnMobile"
  ></div>

  <aside
    class="fixed z-50 lg:z-0 lg:static top-0 left-0 h-full lg:h-auto w-72 lg:w-64 bg-white/80 backdrop-blur border border-slate-200 shadow-xl lg:shadow-none rounded-none lg:rounded-2xl transition-transform lg:translate-x-0"
    :class="[
      modelValue ? 'translate-x-0' : '-translate-x-full lg:translate-x-0',
    ]"
    aria-label="Menú lateral"
  >
    <div
      class="flex items-center gap-3 px-5 pt-5 pb-4 border-b border-slate-200"
    >
    <h2 class="text-lg font-semibold">Hola, {{ user?.name ?? "Cargando ..." }}</h2>
     <!--  <div
        class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center"
      >
        <svg viewBox="0 0 24 24" class="h-5 w-5 text-indigo-600">
          <path
            fill="currentColor"
            d="M21,20a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2,6,6,0,0,1,6-6h6A6,6,0,0,1,21,20Zm-9-8A5,5,0,1,0,7,7,5,5,0,0,0,12,12Z"
          />
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-sm font-medium text-slate-800 truncate">
          {{ user?.name ?? "—" }}
        </div>
        <div class="text-xs text-slate-500 capitalize">
          {{ role }}
        </div>
      </div> -->
    </div>

    <nav class="p-3">
      <ul class="space-y-1">
        <li v-for="item in links" :key="item.to">
          <RouterLink
            :to="item.to"
            class="group flex items-center gap-3 px-3 py-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-indigo-50/70 transition-colors"
            :class="isActive(item.to) ? 'bg-indigo-100/70 text-slate-900' : ''"
            @click="closeOnMobile"
          >
            <span
              class="text-slate-500 group-hover:text-indigo-600 transition-colors"
              :class="isActive(item.to) ? 'text-indigo-600' : ''"
              v-html="item.icon"
            ></span>
            <span class="text-sm font-medium">{{ item.label }}</span>
          </RouterLink>
        </li>
      </ul>
    </nav>

    <!-- Logout button -->
    <div class="mt-auto p-3 border-t border-slate-200">
      <button
        @click="doLogout"
        class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-slate-600 hover:text-red-700 hover:bg-red-50/70 transition-colors"
      >
        <svg viewBox="0 0 24 24" class="h-5 w-5">
          <path
            fill="currentColor"
            d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.59L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"
          />
        </svg>
        <span class="text-sm font-medium">Cerrar Sesión</span>
      </button>

     
    </div>

    <div class="p-4 text-[11px] text-slate-400 hidden lg:block">
      © {{ new Date().getFullYear() }} Sistema de Proyectos
    </div>
  </aside>
</template>
