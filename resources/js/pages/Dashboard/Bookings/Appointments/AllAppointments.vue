<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Calendar, Clock, User, MapPin, FileText, CheckCircle2, XCircle, Clock3, Filter, X, AlertCircle, Download, Eye, Trash2 } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import * as appointmentsRoutes from '@/routes/appointments'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
}

interface Specialization {
  id: number
  name: string
  slug: string
}

interface City {
  id: number
  name_ar: string
  name_en: string
}

interface ProviderProfile {
  id: number
  user: User
  specialization: Specialization
  city?: City
}

interface Appointment {
  id: number
  appointment_date: string
  start_time: string
  end_time: string
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show'
  notes: string | null
  user: User
  provider_profile: ProviderProfile
  created_at: string
  updated_at: string
}

interface Props {
  appointments: {
    data: Appointment[]
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
  }
  specializations: Record<string, string>
  cities: Record<string, string>
  filters: {
    status: string
    date_from: string
    date_to: string
    specialization: string
    city: string
  }
}

const props = defineProps<Props>()

const showFilters = ref(false)
const showConfirmModal = ref(false)
const confirmAction = ref<{ type: string; appointmentId: number; newStatus?: string } | null>(null)
const sortBy = ref('date')
const sortOrder = ref('desc')
const localFilters = ref({
  status: props.filters.status,
  date_from: props.filters.date_from,
  date_to: props.filters.date_to,
  specialization: props.filters.specialization,
  city: props.filters.city,
})

const getStatusColor = (status: string) => {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    confirmed: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    completed: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
    no_show: 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
  }
  return colors[status as keyof typeof colors] || colors.pending
}

const getStatusIcon = (status: string) => {
  if (status === 'confirmed') return CheckCircle2
  if (status === 'cancelled') return XCircle
  return Clock3
}

const applyFilters = () => {
  const queryParams = new URLSearchParams()
  
  Object.entries(localFilters.value).forEach(([key, value]) => {
    if (value && value !== 'all') {
      queryParams.append(key, value as string)
    }
  })
  
  router.get(appointmentsRoutes.index.url({ query: { page: 1, ...Object.fromEntries(queryParams) } }))
  showFilters.value = false
}

const clearFilters = () => {
  localFilters.value = {
    status: 'all',
    date_from: '',
    date_to: '',
    specialization: 'all',
    city: 'all',
  }
  router.get(appointmentsRoutes.index.url())
  showFilters.value = false
}

const hasActiveFilters = computed(() => {
  return Object.values(props.filters).some(f => f && f !== 'all' && f !== '')
})

const updateStatus = (appointmentId: number, status: string) => {
  confirmAction.value = { type: 'updateStatus', appointmentId, newStatus: status }
  showConfirmModal.value = true
}

const deleteAppointment = (appointmentId: number) => {
  confirmAction.value = { type: 'delete', appointmentId }
  showConfirmModal.value = true
}

const executeAction = () => {
  if (!confirmAction.value) return

  const { type, appointmentId, newStatus } = confirmAction.value

  switch (type) {
    case 'updateStatus':
      if (newStatus) {
        router.post(appointmentsRoutes.updateStatus.url(appointmentId), { status: newStatus })
      }
      break
    case 'delete':
      router.delete(appointmentsRoutes.destroy.url(appointmentId))
      break
  }

  showConfirmModal.value = false
  confirmAction.value = null
}

const getConfirmMessage = () => {
  if (!confirmAction.value) return ''

  const { type, newStatus } = confirmAction.value

  switch (type) {
    case 'updateStatus':
      return `Are you sure you want to change the status to ${newStatus?.charAt(0).toUpperCase()}${newStatus?.slice(1)}?`
    case 'delete':
      return 'Are you sure you want to permanently delete this appointment? This action cannot be undone.'
    default:
      return 'Are you sure?'
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

const getAvailableStatusTransitions = (currentStatus: string) => {
  const transitions: Record<string, string[]> = {
    pending: ['confirmed', 'cancelled'],
    confirmed: ['completed', 'no_show', 'cancelled'],
    completed: [],
    cancelled: [],
    no_show: [],
  }
  return transitions[currentStatus] || []
}

const statsData = computed(() => {
  const data = props.appointments.data
  return {
    total: props.appointments.total,
    pending: data.filter(a => a.status === 'pending').length,
    confirmed: data.filter(a => a.status === 'confirmed').length,
    completed: data.filter(a => a.status === 'completed').length,
    cancelled: data.filter(a => a.status === 'cancelled').length,
  }
})

const exportCSV = () => {
  let csv = 'Patient,Provider,Specialization,Date,Time,Status,Location\n'
  
  props.appointments.data.forEach(appointment => {
    csv += `"${appointment.user.name}","${appointment.provider_profile.user.name}","${appointment.provider_profile.specialization.name}","${appointment.appointment_date}","${appointment.start_time} - ${appointment.end_time}","${appointment.status}","${appointment.provider_profile.city?.name_ar || 'N/A'}"\n`
  })

  const element = document.createElement('a')
  element.setAttribute('href', 'data:text/csv;charset=utf-8,' + encodeURIComponent(csv))
  element.setAttribute('download', `appointments-${new Date().toISOString().split('T')[0]}.csv`)
  element.style.display = 'none'
  document.body.appendChild(element)
  element.click()
  document.body.removeChild(element)
}
</script>

<template>
  <AppLayout>
    <Head title="All Appointments" />

    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            All Appointments
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            View and manage all appointments in the system
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button
            @click="exportCSV"
            variant="outline"
            class="gap-2"
          >
            <Download class="w-4 h-4" />
            Export
          </Button>
          <Button
            @click="showFilters = !showFilters"
            variant="outline"
            class="gap-2"
          >
            <Filter class="w-4 h-4" />
            Filters
            <span v-if="hasActiveFilters" class="bg-indigo-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
              {{ Object.values(filters).filter(f => f && f !== 'all' && f !== '').length }}
            </span>
          </Button>
        </div>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Total</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ statsData.total }}</p>
            </div>
            <Calendar class="w-8 h-8 text-blue-600 opacity-20" />
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Pending</p>
              <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400 mt-1">{{ statsData.pending }}</p>
            </div>
            <Clock3 class="w-8 h-8 text-yellow-600 opacity-20" />
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Confirmed</p>
              <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ statsData.confirmed }}</p>
            </div>
            <CheckCircle2 class="w-8 h-8 text-green-600 opacity-20" />
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Completed</p>
              <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1">{{ statsData.completed }}</p>
            </div>
            <CheckCircle2 class="w-8 h-8 text-blue-600 opacity-20" />
          </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-gray-600 dark:text-gray-400 text-sm font-medium">Cancelled</p>
              <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ statsData.cancelled }}</p>
            </div>
            <XCircle class="w-8 h-8 text-red-600 opacity-20" />
          </div>
        </div>
      </div>

      <!-- Filters Panel -->
      <div
        v-if="showFilters"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Status
            </label>
            <select
              v-model="localFilters.status"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="confirmed">Confirmed</option>
              <option value="completed">Completed</option>
              <option value="cancelled">Cancelled</option>
              <option value="no_show">No Show</option>
            </select>
          </div>

          <!-- Specialization Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Specialization
            </label>
            <select
              v-model="localFilters.specialization"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">All Specializations</option>
              <option v-for="(name, slug) in specializations" :key="slug" :value="slug">
                {{ name }}
              </option>
            </select>
          </div>

          <!-- City Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              City
            </label>
            <select
              v-model="localFilters.city"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">All Cities</option>
              <option v-for="(name, id) in cities" :key="id" :value="id">
                {{ name }}
              </option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Date From
            </label>
            <input
              v-model="localFilters.date_from"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
          </div>

          <!-- Date To -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Date To
            </label>
            <input
              v-model="localFilters.date_to"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            />
          </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex gap-2 mt-4">
          <Button
            @click="applyFilters"
            class="bg-indigo-600 hover:bg-indigo-700 text-white"
          >
            Apply Filters
          </Button>
          <Button
            @click="clearFilters"
            variant="outline"
          >
            Clear All
          </Button>
        </div>
      </div>

      <!-- Appointments Table (For Desktop) -->
      <div v-if="appointments.data.length > 0" class="hidden lg:block bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Patient</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Provider</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Specialization</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Date & Time</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Location</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 dark:text-gray-300 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="appointment in appointments.data" :key="appointment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-indigo-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                      {{ appointment.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ appointment.user.name }}</p>
                      <p class="text-xs text-gray-600 dark:text-gray-400">{{ appointment.user.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-8 w-8 bg-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                      {{ appointment.provider_profile.user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ appointment.provider_profile.user.name }}</p>
                      <p class="text-xs text-gray-600 dark:text-gray-400">{{ appointment.provider_profile.user.email }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                    {{ appointment.provider_profile.specialization.name }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ formatDate(appointment.appointment_date) }}</p>
                    <p class="text-xs text-gray-600 dark:text-gray-400">{{ appointment.start_time }} - {{ appointment.end_time }}</p>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                  {{ appointment.provider_profile.city?.name_ar || 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                    :class="getStatusColor(appointment.status)"
                  >
                    <component :is="getStatusIcon(appointment.status)" class="w-3 h-3 mr-1" />
                    {{ appointment.status }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex items-center gap-2">
                    <Link
                      :href="appointmentsRoutes.show.url(appointment.id)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                      title="View Details"
                    >
                      <Eye class="w-4 h-4" />
                    </Link>
                    <select
                      v-if="getAvailableStatusTransitions(appointment.status).length > 0"
                      @change="(e) => {
                        const newStatus = (e.target as HTMLSelectElement).value
                        if (newStatus) updateStatus(appointment.id, newStatus)
                        ;(e.target as HTMLSelectElement).value = ''
                      }"
                      class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                    >
                      <option value="" disabled selected>Change...</option>
                      <option
                        v-for="status in getAvailableStatusTransitions(appointment.status)"
                        :key="status"
                        :value="status"
                      >
                        {{ status }}
                      </option>
                    </select>
                    <button
                      @click="deleteAppointment(appointment.id)"
                      class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                      title="Delete"
                    >
                      <Trash2 class="w-4 h-4" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Appointments Cards (For Mobile) -->
      <div v-if="appointments.data.length > 0" class="lg:hidden space-y-4 mb-8">
        <div
          v-for="appointment in appointments.data"
          :key="appointment.id"
          class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"
        >
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-semibold text-gray-900 dark:text-white">{{ appointment.user.name }}</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Patient</p>
            </div>
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="getStatusColor(appointment.status)"
            >
              <component :is="getStatusIcon(appointment.status)" class="w-3 h-3 mr-1" />
              {{ appointment.status }}
            </span>
          </div>

          <div class="space-y-2 mb-4 text-sm">
            <p class="text-gray-700 dark:text-gray-300">
              <span class="font-medium">Provider:</span> {{ appointment.provider_profile.user.name }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
              <span class="font-medium">Specialization:</span> {{ appointment.provider_profile.specialization.name }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
              <span class="font-medium">Date:</span> {{ formatDate(appointment.appointment_date) }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
              <span class="font-medium">Time:</span> {{ appointment.start_time }} - {{ appointment.end_time }}
            </p>
            <p class="text-gray-700 dark:text-gray-300">
              <span class="font-medium">Location:</span> {{ appointment.provider_profile.city?.name_ar || 'N/A' }}
            </p>
          </div>

          <div class="flex gap-2">
            <Link
              :href="appointmentsRoutes.show.url(appointment.id)"
              class="flex-1 px-3 py-2 text-sm text-center font-medium text-indigo-600 border border-indigo-600 rounded hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
            >
              View
            </Link>
            <select
              v-if="getAvailableStatusTransitions(appointment.status).length > 0"
              @change="(e) => {
                const newStatus = (e.target as HTMLSelectElement).value
                if (newStatus) updateStatus(appointment.id, newStatus)
                ;(e.target as HTMLSelectElement).value = ''
              }"
              class="flex-1 px-3 py-2 text-sm font-medium border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="" disabled selected>Status...</option>
              <option
                v-for="status in getAvailableStatusTransitions(appointment.status)"
                :key="status"
                :value="status"
              >
                {{ status }}
              </option>
            </select>
            <button
              @click="deleteAppointment(appointment.id)"
              class="px-3 py-2 text-sm font-medium text-red-600 border border-red-600 rounded hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
            >
              Delete
            </button>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="appointments.last_page > 1" class="flex flex-col items-center gap-4 mb-8">
        <!-- Pagination Info -->
        <p class="text-sm text-gray-600 dark:text-gray-400">
          Showing
          <span class="font-semibold text-gray-900 dark:text-white">
            {{ appointments.from }}
          </span>
          to
          <span class="font-semibold text-gray-900 dark:text-white">
            {{ appointments.to }}
          </span>
          of
          <span class="font-semibold text-gray-900 dark:text-white">
            {{ appointments.total }}
          </span>
          appointments
        </p>

        <!-- Pagination Controls -->
        <div class="flex justify-center items-center space-x-2 flex-wrap">
          <!-- Previous -->
          <Link
            v-if="appointments.current_page > 1"
            :href="appointmentsRoutes.index.url({ query: { page: appointments.current_page - 1, ...filters } })"
            class="px-3 py-2 rounded-lg text-sm font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
          >
            ← Previous
          </Link>

          <!-- Page Numbers -->
          <template v-if="appointments.last_page <= 7">
            <Link
              v-for="page in appointments.last_page"
              :key="page"
              :href="appointmentsRoutes.index.url({ query: { page, ...filters } })"
              class="px-4 py-2 rounded-lg transition-all text-sm font-medium"
              :class="
                page === appointments.current_page
                  ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white'
                  : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
              "
            >
              {{ page }}
            </Link>
          </template>
          <template v-else>
            <Link
              v-if="appointments.current_page !== 1"
              :href="appointmentsRoutes.index.url({ query: { page: 1, ...filters } })"
              class="px-4 py-2 rounded-lg transition-all text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              1
            </Link>
            <span v-else class="px-4 py-2 rounded-lg transition-all text-sm font-medium bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
              1
            </span>

            <span v-if="appointments.current_page > 3" class="px-2 py-2 text-gray-500">...</span>

            <Link
              v-for="page in (() => {
                const pages = []
                const start = Math.max(2, appointments.current_page - 1)
                const end = Math.min(appointments.last_page - 1, appointments.current_page + 1)
                for (let i = start; i <= end; i++) pages.push(i)
                return pages
              })()"
              :key="page"
              :href="appointmentsRoutes.index.url({ query: { page, ...filters } })"
              class="px-4 py-2 rounded-lg transition-all text-sm font-medium"
              :class="
                page === appointments.current_page
                  ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white'
                  : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700'
              "
            >
              {{ page }}
            </Link>

            <span v-if="appointments.current_page < appointments.last_page - 2" class="px-2 py-2 text-gray-500">...</span>

            <Link
              v-if="appointments.current_page !== appointments.last_page"
              :href="appointmentsRoutes.index.url({ query: { page: appointments.last_page, ...filters } })"
              class="px-4 py-2 rounded-lg transition-all text-sm font-medium bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              {{ appointments.last_page }}
            </Link>
            <span v-else class="px-4 py-2 rounded-lg transition-all text-sm font-medium bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
              {{ appointments.last_page }}
            </span>
          </template>

          <!-- Next -->
          <Link
            v-if="appointments.current_page < appointments.last_page"
            :href="appointmentsRoutes.index.url({ query: { page: appointments.current_page + 1, ...filters } })"
            class="px-3 py-2 rounded-lg text-sm font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
          >
            Next →
          </Link>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
          <Calendar class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No appointments found</h3>
        <p class="text-gray-600 dark:text-gray-400">
          {{ hasActiveFilters ? 'Try adjusting your filters.' : 'There are no appointments in the system.' }}
        </p>
      </div>
    </div>

    <!-- Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showConfirmModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4"
        @click.self="showConfirmModal = false"
      >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full animate-in">
          <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900">
              <AlertCircle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ confirmAction?.type === 'delete' ? 'Delete Appointment' : 'Change Status' }}
            </h3>
          </div>

          <div class="p-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ getConfirmMessage() }}
            </p>
          </div>

          <div class="flex gap-3 p-6 border-t border-gray-200 dark:border-gray-700 justify-end">
            <button
              @click="showConfirmModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-all"
            >
              Cancel
            </button>
            <button
              @click="executeAction"
              :class="[
                'px-4 py-2 text-sm font-medium text-white rounded-lg transition-all',
                confirmAction?.type === 'delete'
                  ? 'bg-red-600 hover:bg-red-700'
                  : 'bg-indigo-600 hover:bg-indigo-700'
              ]"
            >
              {{ confirmAction?.type === 'delete' ? 'Delete' : 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>
