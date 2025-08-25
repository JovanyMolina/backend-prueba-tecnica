import { defineStore } from "pinia";
import api from "../plugins/api";

export const useAuthStore = defineStore("auth", {
  state: () => ({ user: null, loading: true }),
  actions: {
    async bootstrap() {
      const token = localStorage.getItem("token");
      if (!token) { this.loading = false; return; }
      try {
        const { data } = await api.get("/api/me");
        this.user = data;
      } catch {
        localStorage.removeItem("token");
      }
      this.loading = false;
    },
    async login(email, password) {
      const { data } = await api.post("/api/login", { email, password });
      localStorage.setItem("token", data.token);
      console.log("data token: ", data.token);
      const me = await api.get("/api/me");
      this.user = me.data;
    },
    async logout() {
      try { await api.post("/api/logout"); } catch {}
      localStorage.removeItem("token");
      this.user = null;
    }
  },
});
