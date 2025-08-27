# Frontend — Gestión de Proyectos

Aplicación frontend para administrar proyectos, tareas y usuarios, con autenticación, registro y control de roles (admin y colaborador). Pensada para consumir un backend REST (Laravel) vía Axios, usando Vite + Vue 3 + Tailwind CSS.

## Características
- **Auth y registro** con token (Bearer) y **rutas protegidas**.
- **Roles**: admin (CRUD completo) y colaborador (con acceso limitado).
- **CRUDs**: Proyectos, Tareas, Usuarios.

## Stack
- **Vue 3**, **Vite**, **Tailwind CSS**, **Vue Router**
- **Axios** (interceptor para token)
- **Prettier**
- **BD Mysql workbench**

## Variables de entorno
Crea `.env` en la raíz del frontend:
```bash
VITE_API_URL=http://localhost:8000/api
```

## Estructura sugerida
```
src/
├─ assets/            # imágenes, estilos globales
├─ components/        # componentes UI
├─ layouts/           # PublicLayout, PrivateLayout
├─ pages/             # Login, Register, Dashboard, task and users
├─ router/            # rutas + guards (auth/role)
├─ services/          # api.ts (Axios + token)
├─ modules/           # auth, projects

```

## Flujo
1) Registro/Login
2) Dashboard privado
3) Admin gestiona usuarios/roles, proyectos y tareas
4) Colaborador trabaja en tareas asignadas.

## Instalación
**Frontend**
```json
{
  Version de Node: V22.13.0
}
```
```bash
npm install
npm run dev
```
**Backend**

```bash
Debes de tener la version de PHP 8.4.11
Instalar dependencias de : composer install (Si lo tienes lo lo instales)
Si es necesario se debera de generar la App_key con este comando: php artisan key:generate
Para prender el servidor: php artisan serve

```
**Base de datos**

En tu MySQL Workbench debes de 
1) Crear un schema con nombre **laravel**
2) Debes de ir a las opciones de arriba y selecionas **Server**
3) Selecciona **Data import** 
4) Escoge la opción de **Import from Self-ContainedFile** y selecciona la bd **laravel**
5) En **Default Target Schema** selecionas la **laravel**
6) y colocas en **Dump Structure and Dat** y dale click en **Start Import**
7) Listo ya tienes la bd



