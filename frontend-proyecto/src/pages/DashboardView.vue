<!-- src/pages/DashboardView.vue -->
<script setup>
import { ref } from "vue";
import Navbar from "../components/Navbar.vue";
import Sidebar from "../components/Sidebar.vue";

const open = ref(false); // para móvil
</script>

<template>
  <div class="min-h-screen bg-slate-50">
    <Navbar />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 grid lg:grid-cols-[16rem_1fr] gap-4">
      <button
        class="lg:hidden inline-flex items-center gap-2 px-3 py-2 mb-2 rounded-lg
               bg-slate-900 text-white"
        @click="open = true"
      >
        <svg viewBox="0 0 24 24" class="h-5 w-5"><path fill="currentColor" d="M3 6h18v2H3V6m0 5h18v2H3v-2m0 5h18v2H3v-2"/></svg>
        Menú
      </button>

      <Sidebar v-model="open" class="lg:sticky lg:top-4 lg:h-[calc(100vh-6rem)]" />

      <main class="bg-white border border-slate-200 rounded-2xl p-6">
        <h1 class="text-xl font-semibold text-slate-800 mb-4">Dashboard</h1>
        <p class="text-slate-600">Contenido…</p>
      </main>
    </div>
  </div>
</template>
