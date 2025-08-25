<script setup>
import { ref } from "vue";
import { useAuthStore } from "../stores/auth";
import Swal from "sweetalert2";
import { Form, Field, ErrorMessage } from "vee-validate";
import * as yup from "yup";

const auth = useAuthStore();
const showPass = ref(false);

const schema = yup.object({
  email: yup.string().email("Email inválido").required("Requerido"),
  password: yup.string().min(8, "Mínimo 8 caracteres").required("Requerido"),
});

async function onSubmit(values) {
  try {
    await auth.login(values.email, values.password);
    /* Swal.fire({
      icon: "success",
      title: "Bienvenido",
      timer: 1200,
      showConfirmButton: false,
    }); */
    window.location.href = "/dashboard";
  } catch (e) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: e?.response?.data?.message || "Credenciales inválidas",
    });
  }
}
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center p-4"
  >
    <Form
      :validation-schema="schema"
      @submit="onSubmit"
      v-slot="{ isSubmitting }"
      class="w-full max-w-md"
    >
      <div
        class="bg-white/80 backdrop-blur border border-slate-200 shadow-xl rounded-2xl p-8"
      >
       
        <div class="flex flex-col items-center text-center mb-6">
          <div
            class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center mb-3"
          >
            
            <svg viewBox="0 0 24 24" class="h-6 w-6 text-indigo-600">
              <path
                fill="currentColor"
                d="M21,20a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2,6,6,0,0,1,6-6h6A6,6,0,0,1,21,20Zm-9-8A5,5,0,1,0,7,7,5,5,0,0,0,12,12Z"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-semibold text-slate-800">Iniciar sesión</h1>
          <p class="text-sm text-slate-500">
            Ingresa tus credenciales para continuar
          </p>
        </div>

        
        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700"
            >Correo electrónico</label
          >
          <Field
            name="email"
            as="input"
            type="email"
            autocomplete="email"
            class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 border focus:ring-indigo-200 focus:ring-2 px-3 py-2 outline-none"
          />
          <ErrorMessage name="email" class="mt-1 text-xs text-red-600" />
        </div>

       
        <div class="mb-2">
          <label class="block text-sm font-medium text-slate-700"
            >Contraseña</label
          >
          <div class="relative mt-1">
            <Field
              :type="showPass ? 'text' : 'password'"
              name="password"
              autocomplete="current-password"
              as="input"
            class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 border focus:ring-indigo-200 focus:ring-2 px-3 py-2 outline-none"
            />
            <button
              type="button"
              @click="showPass = !showPass"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-500 hover:text-slate-700"
              aria-label="Mostrar/Ocultar contraseña"
            >
             
              <svg
                v-if="!showPass"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="h-5 w-5"
              >
                <path
                  fill="currentColor"
                  d="M12 5c-5 0-9 4.5-9 7s4 7 9 7s9-4.5 9-7s-4-7-9-7m0 12a5 5 0 1 1 0-10a5 5 0 0 1 0 10m0-2a3 3 0 1 0 0-6a3 3 0 0 0 0 6"
                />
              </svg>
              <svg
                v-else
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                class="h-5 w-5"
              >
                <path
                  fill="currentColor"
                  d="M2.4 1.7L1 3.1l4.1 4.1C3.5 8.5 2 10.3 2 12c0 2.5 5 7 10 7c2 0 3.9-.7 5.5-1.8l3.5 3.5l1.4-1.4zM12 17c-3.3 0-8-3.2-8-5c0-.9.9-2.1 2.3-3.2l2.3 2.3A3.99 3.99 0 0 0 12 16c.8 0 1.6-.2 2.2-.6l1.5 1.5c-.9.6-2.1 1.1-3.7 1.1M12 7c3.3 0 8 3.2 8 5c0 .6-.5 1.5-1.3 2.3l-2.2-2.2c.1-.3.2-.7.2-1.1a3 3 0 0 0-3-3c-.4 0-.8.1-1.1.2L10 6.5c.6-.3 1.3-.5 2-.5"
                />
              </svg>
            </button>
          </div>
          <ErrorMessage name="password" class="mt-1 text-xs text-red-600" />
        </div>

        <div class="flex items-center justify-between mb-6">
          <label class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input
              type="checkbox"
              class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500"
            />
            Recuérdame
          </label>
          
        </div>

        <button
          :disabled="isSubmitting"
          class="w-full inline-flex items-center justify-center gap-2 py-2.5 rounded-lg bg-indigo-600 text-white font-medium hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed"
        >
          <svg
            v-if="isSubmitting"
            class="h-5 w-5 animate-spin"
            viewBox="0 0 24 24"
          >
            <circle
              class="opacity-25"
              cx="12"
              cy="12"
              r="10"
              stroke="white"
              stroke-width="4"
              fill="none"
            />
            <path
              class="opacity-75"
              fill="white"
              d="M4 12a8 8 0 0 1 8-8v4A4 4 0 0 0 8 12H4z"
            />
          </svg>
          <span>{{ isSubmitting ? "Entrando..." : "Entrar" }}</span>
        </button>
        <div class="flex flex-col items-center text-center">
          <a class="text-sm text-indigo-600 hover:text-indigo-700" href="/register"
              >Regístrate aquí</a
            >
        </div>
      </div>
    </Form>
  </div>
</template>
