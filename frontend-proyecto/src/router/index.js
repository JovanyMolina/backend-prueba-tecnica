import { createRouter, createWebHistory } from "vue-router";
import LoginView from "../pages/LoginView.vue";
import DashboardView from "../pages/DashboardView.vue";
import registerViwe from "../pages/registerViwe.vue";
import ProjectsList from "../pages/projects/ProjectsList.vue";
import ProjectForm from "../pages/projects/ProjectForm.vue";
import MyTasks from "../pages/tasks/MyTasks.vue";
import TaskForm from "../pages/projects/tasks/TaskForm.vue";
import TasksList from "../pages/projects/tasks/TasksList.vue";

const routes = [
  //login
  {
    path: "/",
    component: LoginView,
  },
  //register
  {
    path: "/register",
    component: registerViwe,
  },
  //dash
  {
    path: "/dashboard",
    component: DashboardView,
    meta: { requiresAuth: true },
  },
//project
  { path: "/projects", component: ProjectsList, meta: { requiresAuth: true } },
  {
    path: "/projects/new",
    component: ProjectForm,
    meta: { requiresAuth: true },
  },
  {
    path: "/projects/:id/edit",
    component: ProjectForm,
    meta: { requiresAuth: true },
  },
  //tasks
  {
    path: "/projects/:projectId/tasks",
    component: TasksList,
    meta: { requiresAuth: true }
  },
  {
    path: "/projects/:projectId/tasks/new",
    component: TaskForm,
    meta: { requiresAuth: true }
  },

  {
    path: "/projects/:projectId/tasks/:taskId/edit",
    component: TaskForm,
    meta: { requiresAuth: true }
  },

    { path: "/mis-tareas", component: MyTasks, meta: { requiresAuth: true } },
  
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
