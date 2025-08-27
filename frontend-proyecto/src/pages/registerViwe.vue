<script setup>
import { ref, computed, onMounted } from "vue";
import Swal from "sweetalert2";
import api from "../plugins/api";
import { useAuthStore } from "../stores/auth";
import { Form, Field, ErrorMessage } from "vee-validate";
import * as yup from "yup";

const auth = useAuthStore();
const showPass = ref(false);
const showPass2 = ref(false);

const schema = yup.object({
  name: yup.string().required("Requerido").min(3, "Mín. 3 caracteres"),
  email: yup.string().email("Email inválido").required("Requerido"),
  password: yup.string().min(8, "Mín. 8 caracteres").required("Requerido"),
  password_confirmation: yup
    .string()
    .oneOf([yup.ref("password")], "Las contraseñas no coinciden")
    .required("Requerido"),
  role: yup.string().oneOf(["colaborador", "admin"], "Rol inválido"),
});

onMounted(async () => {
  if (localStorage.getItem("token") && !auth.user) {
    await auth.profile();
  }
});

async function onSubmit(values) {
  try {
    const payload = {
      name: values.name,
      email: values.email,
      password: values.password,
      password_confirmation: values.password_confirmation,
    };
    if (localStorage.getItem("token") && auth.user?.role === "admin") {
      payload.role = values.role;
    }

    const { data } = await api.post("/api/register", payload);

    /*     if (data?.token) {
      localStorage.setItem("token", data.token);
      const me = await api.get("/api/me");
      auth.user = me.data;
    } else {
      await auth.login(values.email, values.password);
    }
 */
    Swal.fire({
      icon: "success",
      title: "Cuenta creada",
      timer: 1300,
      showConfirmButton: false,
    });
    window.location.href = "/dashboard";
  } catch (e) {
    const msg = "No se pudo registrar el usuario por que este correo fue registrado";
    Swal.fire({ icon: "error", title: "Error", text: msg });
  }
}

const isAdminWithToken = computed(
  () => Boolean(localStorage.getItem("token")) && auth.user?.role === "admin"
);
</script>

<template>
  <div
    class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-cyan-50 flex items-center justify-center p-4"
  >
    <Form
      :validation-schema="schema"
      @submit="onSubmit"
      :initial-values="{ role: 'colaborador' }"
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
                d="M20 3V5M20 5V7M20 5H22M20 5H18M16 8C16 10.2091 14.2091 12 12 12C9.79086 12 8 10.2091 8 8C8 5.79086 9.79086 4 12 4C14.2091 4 16 5.79086 16 8ZM9.31765 14H14.6824C15.1649 14 15.4061 14 15.6219 14.0461C16.3688 14.2056 17.0147 14.7661 17.3765 15.569C17.4811 15.8009 17.5574 16.0765 17.71 16.6278C17.8933 17.2901 17.985 17.6213 17.9974 17.8884C18.0411 18.8308 17.5318 19.6817 16.7756 19.9297C16.5613 20 16.2714 20 15.6916 20H8.30844C7.72864 20 7.43875 20 7.22441 19.9297C6.46818 19.6817 5.95888 18.8308 6.00261 17.8884C6.01501 17.6213 6.10668 17.2901 6.29003 16.6278C6.44262 16.0765 6.51891 15.8009 6.62346 15.569C6.9853 14.7661 7.63116 14.2056 8.37806 14.0461C8.59387 14 8.83513 14 9.31765 14Z"
                stroke="#464455"
                stroke-width="0.9"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
          </div>
          <h1 class="text-2xl font-semibold text-slate-800">Crear cuenta</h1>
          <p class="text-sm text-slate-500">Regístrate para comenzar</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700">Nombre</label>
          <Field
            name="name"
            as="input"
            type="text"
            class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 border focus:ring-indigo-200 focus:ring-2 px-3 py-2 outline-none"
          />
          <ErrorMessage name="name" class="mt-1 text-xs text-red-600" />
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

        <div v-if="isAdminWithToken" class="mb-4">
          <label class="block text-sm font-medium text-slate-700">Rol</label>
          <Field
            name="role"
            as="select"
            class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 border focus:ring-indigo-200 focus:ring-2 px-3 py-2 outline-none"
          >
            <option value="colaborador">Colaborador</option>
            <option value="admin">Administrador</option>
          </Field>
          <ErrorMessage name="role" class="mt-1 text-xs text-red-600" />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-slate-700"
            >Contraseña</label
          >
          <div class="relative mt-1">
            <Field
              :type="showPass ? 'text' : 'password'"
              name="password"
              autocomplete="new-password"
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

        <div class="mb-6">
          <label class="block text-sm font-medium text-slate-700"
            >Confirmar contraseña</label
          >
          <div class="relative mt-1">
            <Field
              :type="showPass2 ? 'text' : 'password'"
              name="password_confirmation"
              autocomplete="new-password"
              as="input"
              class="mt-1 w-full rounded-lg border-slate-300 focus:border-indigo-500 border focus:ring-indigo-200 focus:ring-2 px-3 py-2 outline-none"
            />
            <button
              type="button"
              @click="showPass2 = !showPass2"
              class="absolute inset-y-0 right-0 px-3 flex items-center text-slate-500 hover:text-slate-700"
              aria-label="Mostrar/Ocultar confirmación"
            >
              <svg
                v-if="!showPass2"
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
          <ErrorMessage
            name="password_confirmation"
            class="mt-1 text-xs text-red-600"
          />
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
          <span>{{ isSubmitting ? "Creando cuenta..." : "Crear cuenta" }}</span>
        </button>

        <p
          v-if="!auth.user || auth.user.role !== 'admin'"
          class="text-center text-sm text-slate-600 mt-4"
        >
          ¿Ya tienes cuenta?
          <a href="/" class="text-indigo-600 hover:text-indigo-700"
            >Inicia sesión</a
          >
        </p>
      </div>
    </Form>
  </div>
</template>
