import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/store/modules/authStore'

// Lazy-load ang views para dili bug-at ang initial load
const LoginView    = () => import('@/views/auth/LoginView.vue')
const DashboardView = () => import('@/views/dashboard/DashboardView.vue')
const OrderView    = () => import('@/views/order/OrderView.vue')
const ProductView  = () => import('@/views/product/ProductView.vue')
const TableView    = () => import('@/views/table/TableView.vue')
const ReportView   = () => import('@/views/report/ReportView.vue')
const UsersView    = () => import('@/views/users/UsersView.vue')
const CategoryView = () => import('@/views/category/CategoryView.vue')
const ArchiveView  = () => import('@/views/settings/ArchiveView.vue')
const MainLayout   = () => import('@/layouts/MainLayout.vue')

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { requiresAuth: false, title: 'Login' },
  },
  {
    path: '/',
    component: MainLayout,
    meta: { requiresAuth: true },
    children: [
      { path: '',         name: 'dashboard',  component: DashboardView, meta: { title: 'Dashboard'        } },
      { path: 'orders',   name: 'orders',     component: OrderView,     meta: { title: 'Orders'           } },
      { path: 'products', name: 'products',   component: ProductView,   meta: { title: 'Menu Management'  } },
      { path: 'tables',   name: 'tables',     component: TableView,     meta: { title: 'Tables'           } },
      { path: 'reports',  name: 'reports',    component: ReportView,    meta: { title: 'Reports',    roles: ['manager', 'admin'] } },
      { path: 'categories', name: 'categories', component: CategoryView, meta: { title: 'Categories', roles: ['manager', 'admin'] } },
      { path: 'users',    name: 'users',      component: UsersView,     meta: { title: 'User Management', roles: ['admin'] } },
      { path: 'settings/archive', name: 'archive', component: ArchiveView, meta: { title: 'Product Archive', roles: ['manager', 'admin'] } },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// Route guard — i-check kung naka-login ba
router.beforeEach(async (to) => {
  const authStore = useAuthStore()

  // I-update ang browser tab title
  document.title = to.meta.title ? `${to.meta.title} — RestoPos` : 'RestoPos'

  // Kung wala pa ang user data, fetch it
  if (authStore.token && !authStore.user) {
    await authStore.fetchUser()
  }

  // Kung ang page need og login pero wala pa
  if (to.meta.requiresAuth !== false && !authStore.isLoggedIn) {
    return { name: 'login' }
  }

  // Kung naka-login na pero nag-try moadto sa login page
  if (to.name === 'login' && authStore.isLoggedIn) {
    return { name: 'dashboard' }
  }

  // I-check ang role kung required
  if (to.meta.roles && !to.meta.roles.includes(authStore.user?.role)) {
    return { name: 'dashboard' }
  }
})

export default router
