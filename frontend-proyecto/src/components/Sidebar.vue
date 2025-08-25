<script setup>
import { computed } from "vue";
import { useRoute, useRouter, RouterLink } from "vue-router";
import { useAuthStore } from "../stores/auth";

const props = defineProps({
  modelValue: { type: Boolean, default: false }, // abierto en móvil
});
const emit = defineEmits(["update:modelValue"]);

const route = useRoute();
const router = useRouter();
const auth = useAuthStore();

const user = computed(() => auth.user);
const role = computed(() => auth.user?.role ?? "colaborador");

// Links del menú (agrega/quita según tus rutas)
const links = computed(() => {
  const items = [
    {
      label: "Dashboard",
      to: "/dashboard",
      icon: `
      <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor"
          d="M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z"/>
      </svg>`,
    },
    {
      label: "CRUD de Proyectos ",
      to: "/proyectos",
      icon: `
      <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor"
          d="M12 12a5 5 0 1 0-5-5a5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
      </svg>`,
    },
     {
      label: "CRUD de Tareas",
      to: "/tareas",
      icon: `
      <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor"
          d="M12 12a5 5 0 1 0-5-5a5 5 0 0 0 5 5Zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5Z"/>
      </svg>`,
    },
  ];

  if (role.value === "admin") {
    items.splice(1, 0, {
      label: "CRUD de Usuarios",
      to: "/usuarios",
      icon: `
      <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
        <path fill="currentColor"
          d="M16 11a4 4 0 1 0-4-4a4 4 0 0 0 4 4Zm-8 0a3 3 0 1 0-3-3a3 3 0 0 0 3 3Zm0 2c-3.31 0-6 1.79-6 4v1h6v-1c0-1.39.56-2.64 1.47-3.58A8.1 8.1 0 0 0 8 13Zm8 0c-3.87 0-7 2.13-7 4.75V20h14v-2.25C23 15.13 19.87 13 16 13Z"/>
      </svg>`,
    });
  }

  return items;
});

function isActive(to) {
  return route.path === to;
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
    class="fixed z-50 lg:z-0 lg:static top-0 left-0 h-full lg:h-auto w-72 lg:w-64
           bg-white/80 backdrop-blur border border-slate-200 shadow-xl lg:shadow-none
           rounded-none lg:rounded-2xl
           transition-transform lg:translate-x-0"
    :class="[
      modelValue ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
    aria-label="Menú lateral"
  >
    <div class="flex items-center gap-3 px-5 pt-5 pb-4 border-b border-slate-200">
      <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
        <svg viewBox="0 0 24 24" class="h-5 w-5 text-indigo-600">
          <path
            fill="currentColor"
            d="M21,20a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2,6,6,0,0,1,6-6h6A6,6,0,0,1,21,20Zm-9-8A5,5,0,1,0,7,7,5,5,0,0,0,12,12Z"
          />
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-sm font-medium text-slate-800 truncate">
          {{ user?.name ?? '—' }}
        </div>
        <div class="text-xs text-slate-500 capitalize">
          {{ role }}
        </div>
      </div>
    </div>

    <nav class="p-3">
      <ul class="space-y-1">
        <li v-for="item in links" :key="item.to">
          <RouterLink
            :to="item.to"
            class="group flex items-center gap-3 px-3 py-2 rounded-xl
                   text-slate-600 hover:text-slate-900
                   hover:bg-indigo-50/70"
            :class="isActive(item.to)
              ? 'bg-indigo-100/70 text-slate-900'
              : ''"
            @click="closeOnMobile"
          >
            <span class="text-slate-500 group-hover:text-indigo-600"
                  v-html="item.icon"></span>
            <span class="text-sm font-medium">{{ item.label }}</span>
          </RouterLink>
        </li>
      </ul>
    </nav>

    <div class="mt-auto p-4 text-[11px] text-slate-400 hidden lg:block">
      © {{ new Date().getFullYear() }} Prueba Técnica
    </div>
  </aside>
</template>
