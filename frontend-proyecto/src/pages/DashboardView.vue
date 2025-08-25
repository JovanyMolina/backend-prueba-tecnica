<script setup>
import { useAuthStore } from "../stores/auth";
import Navbar from "../components/Navbar.vue";
import Swal from "sweetalert2";

const auth = useAuthStore();


</script>

<template>
  <Navbar />
  <div class="p-6 space-y-4">
    <h1 class="text-2xl font-semibold">Dashboard</h1>
    <pre class="bg-slate-100 p-4 rounded">{{ auth.user }}</pre>
    <button @click="salir" class="px-4 py-2 rounded bg-red-600 text-white">Cerrar sesión</button>
  </div>
</template>
