<script setup>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import Swal from "sweetalert2";
import { useAuthStore } from "../stores/auth";

const auth = useAuthStore();
const router = useRouter();

onMounted(() => {
  if (localStorage.getItem("token") && !auth.user) {
    auth.profile();
  }
});

const loading = computed(() => auth.loading);
const name = computed(() => auth.user?.name ?? "No encontrado");
const role = computed(() => auth.user?.role ?? "Sin rol");

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
</script>

<template>
  <header
    class="sticky top-0 z-50 bg-white/80 backdrop-blur border-b border-slate-200"
  >
    <div
      class="max-w-7xl mx-auto h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between"
    >
      <div class="text-slate-800 font-semibold">
        <a href="/dashboard">Prueba Técnica</a>
      </div>
      <!--  <a
         href="/register"
          class="ml-3 inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-800 text-white text-sm font-medium hover:opacity-90"
        >
          Registrar usuarios
        </a> -->

      <div class="flex items-center gap-3">
        <div
          class="h-9 w-9 rounded-full bg-indigo-100 flex items-center justify-center"
        >
          <svg viewBox="0 0 24 24" class="h-5 w-5 text-indigo-600">
            <path
              fill="currentColor"
              d="M21,20a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2,6,6,0,0,1,6-6h6A6,6,0,0,1,21,20Zm-9-8A5,5,0,1,0,7,7,5,5,0,0,0,12,12Z"
            />
          </svg>
        </div>

        <div v-if="loading" class="leading-tight">
          <div class="h-3 w-28 bg-slate-200 rounded animate-pulse mb-1"></div>
          <div class="h-2.5 w-16 bg-slate-200 rounded animate-pulse"></div>
        </div>

        <div v-else class="leading-tight">
          <div class="text-sm font-medium text-slate-800 truncate">
            {{ name }}
          </div>
          <div class="text-xs text-slate-500 capitalize">{{ role }}</div>
        </div>

        <!--  <button
          @click="doLogout"
          class="ml-3 inline-flex items-center px-3 py-1.5 rounded-lg bg-slate-800 text-white text-sm font-medium hover:opacity-90"
        >
          Cerrar sesión
        </button> -->
      </div>
    </div>
  </header>
</template>
