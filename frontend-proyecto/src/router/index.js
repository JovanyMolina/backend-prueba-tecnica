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

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  if (to.meta.requiresAuth) {
    const token = localStorage.getItem("token");
    if (!token) {
      next("/"); 
      return;
    }
  }
  next();
});

export default router;
