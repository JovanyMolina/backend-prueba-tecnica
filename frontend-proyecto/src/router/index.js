import { createRouter, createWebHistory } from "vue-router";
import LoginView from "../pages/LoginView.vue";
import DashboardView from "../pages/DashboardView.vue";
import registerViwe from "../pages/registerViwe.vue";

const routes = [
  {
    path: "/",
    component: LoginView,
  },
  {
    path: "/register",
    component: registerViwe,
  },
  {
    path: "/dashboard",
    component: DashboardView,
    meta: { requiresAuth: true },
  },
];

export default createRouter({
  history: createWebHistory(),
  routes,
});
