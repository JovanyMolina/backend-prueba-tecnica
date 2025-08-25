import { defineStore } from "pinia";
import api from "../plugins/api";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    user: null,
    loading: true,
  }),
  actions: {
    async profile() {
      const token = localStorage.getItem("token");
      if (!token) { this.user = null; this.loading = false; return; }

      this.loading = true;
      try {
        const { data } = await api.get("/api/me");
        this.user = data;
      } catch {
        localStorage.removeItem("token");
        this.user = null;
      } finally {
        this.loading = false;
      }
    },

    async login(email, password) {
      const { data } = await api.post("/api/login", { email, password });
      localStorage.setItem("token", data.token);
      await this.profile(); 
    },

    async logout() {
      try { await api.post("/api/logout"); } catch {}
      localStorage.removeItem("token");
      this.user = null;
      this.loading = false;
    },
  },
});
