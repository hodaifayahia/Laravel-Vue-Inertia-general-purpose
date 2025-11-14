<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Calendar, Clock, User, MapPin, FileText, CheckCircle2, XCircle, Clock3, Filter, X, AlertCircle, Eye, Trash2, Search, BarChart3, CalendarDays, Users, Activity, TrendingUp } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import * as appointmentsRoutes from '@/routes/appointments'
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { wTrans } from 'laravel-vue-i18n'

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
}

interface Props {
  appointments: {
    data: Appointment[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  isProvider: boolean
  isAdmin: boolean
  isPatient: boolean
  specializations: Record<string, string>
  cities: Record<string, string>
  statistics: {
    total: number
    pending: number
    confirmed: number
    completed: number
    cancelled: number
    no_show: number
    today: number
    this_week: number
    this_month: number
  }
  filters: {
    status: string
    date_from: string
    date_to: string
    specialization: string
    city: string
    search: string
  }
}

const props = defineProps<Props>()

const showFilters = ref(false)
const showConfirmModal = ref(false)
const confirmAction = ref<{ type: string; appointmentId: number; newStatus?: string } | null>(null)
const localFilters = ref({
  status: props.filters.status,
  date_from: props.filters.date_from,
  date_to: props.filters.date_to,
  specialization: props.filters.specialization,
  city: props.filters.city,
  search: '',
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
    if (value && value !== 'all' && value !== '') {
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
    search: '',
  }
  router.get(appointmentsRoutes.index.url())
  showFilters.value = false
}

const hasActiveFilters = computed(() => {
  return Object.values(props.filters).some(f => f && f !== 'all' && f !== '') ||
         localFilters.value.search !== ''
})

const applyQuickFilter = (period: string) => {
  const today = new Date()
  const startOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate())
  const endOfToday = new Date(today.getFullYear(), today.getMonth(), today.getDate(), 23, 59, 59)

  let dateFrom = ''
  let dateTo = ''

  switch (period) {
    case 'today':
      dateFrom = startOfToday.toISOString().split('T')[0]
      dateTo = endOfToday.toISOString().split('T')[0]
      break
    case 'week':
      const startOfWeek = new Date(today)
      startOfWeek.setDate(today.getDate() - today.getDay())
      const endOfWeek = new Date(startOfWeek)
      endOfWeek.setDate(startOfWeek.getDate() + 6)
      dateFrom = startOfWeek.toISOString().split('T')[0]
      dateTo = endOfWeek.toISOString().split('T')[0]
      break
    case 'month':
      const startOfMonth = new Date(today.getFullYear(), today.getMonth(), 1)
      const endOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0)
      dateFrom = startOfMonth.toISOString().split('T')[0]
      dateTo = endOfMonth.toISOString().split('T')[0]
      break
  }

  localFilters.value.date_from = dateFrom
  localFilters.value.date_to = dateTo
  localFilters.value.status = 'all'
  localFilters.value.specialization = 'all'
  localFilters.value.city = 'all'
  localFilters.value.search = ''

  applyFilters()
}

const cancelAppointment = (appointmentId: number) => {
  confirmAction.value = { type: 'cancel', appointmentId }
  showConfirmModal.value = true
}

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
    case 'cancel':
      router.post(appointmentsRoutes.cancel.url(appointmentId))
      break
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
    case 'cancel':
      return wTrans('appointments.confirm_cancel')
    case 'updateStatus':
      return wTrans('appointments.confirm_status_change', { status: newStatus ? newStatus.charAt(0).toUpperCase() + newStatus.slice(1).replace('_', ' ') : '' })
    case 'delete':
      return wTrans('appointments.confirm_delete')
    default:
      return wTrans('appointments.confirm')
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}

const getPageTitle = () => {
  if (props.isAdmin) return wTrans('appointments.all_appointments')
  if (props.isProvider) return wTrans('appointments.my_schedule')
  return wTrans('appointments.my_appointments')
}

const getPageDescription = () => {
  if (props.isAdmin) return wTrans('appointments.manage_all_description')
  if (props.isProvider) return wTrans('appointments.manage_provider_description')
  return wTrans('appointments.manage_patient_description')
}

const getAvailableStatusTransitions = (currentStatus: string, isProvider: boolean, isAdmin: boolean) => {
  if (isProvider) {
    const transitions: Record<string, string[]> = {
      pending: ['confirmed', 'cancelled'],
      confirmed: ['completed', 'no_show', 'cancelled'],
      completed: [],
      cancelled: [],
      no_show: [],
    }
    return transitions[currentStatus] || []
  }
  
  if (isAdmin) {
    const transitions: Record<string, string[]> = {
      pending: ['confirmed', 'cancelled'],
      confirmed: ['completed', 'no_show', 'cancelled'],
      completed: [],
      cancelled: [],
      no_show: [],
    }
    return transitions[currentStatus] || []
  }

  return []
}
</script>

<template>
  <AppLayout>
    <Head :title="getPageTitle()" />

    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
            {{ getPageTitle() }}
          </h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">
            {{ getPageDescription() }}
          </p>
        </div>
        <div class="flex items-center gap-2">
          <Button
            v-if="isAdmin || hasActiveFilters"
            @click="showFilters = !showFilters"
            variant="outline"
            class="gap-2"
          >
            <Filter class="w-4 h-4" />
            {{ wTrans('appointments.filters') }}
            <span v-if="hasActiveFilters" class="bg-indigo-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
              {{ Object.values(filters).filter(f => f && f !== 'all' && f !== '').length }}
            </span>
          </Button>
          <Link
            v-if="!isProvider && !isAdmin"
            :href="appointmentsRoutes.create.url()"
            class="px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all font-semibold"
          >
            {{ wTrans('appointments.book_new') }}
          </Link>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
        <!-- Total Appointments -->
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm font-medium">Total</p>
              <p class="text-2xl font-bold">{{ statistics.total }}</p>
            </div>
            <BarChart3 class="w-8 h-8 text-blue-200" />
          </div>
        </div>

        <!-- Today's Appointments -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-green-100 text-sm font-medium">Today</p>
              <p class="text-2xl font-bold">{{ statistics.today }}</p>
            </div>
            <CalendarDays class="w-8 h-8 text-green-200" />
          </div>
        </div>

        <!-- This Week -->
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 text-sm font-medium">This Week</p>
              <p class="text-2xl font-bold">{{ statistics.this_week }}</p>
            </div>
            <TrendingUp class="w-8 h-8 text-purple-200" />
          </div>
        </div>

        <!-- Pending -->
        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-yellow-100 text-sm font-medium">Pending</p>
              <p class="text-2xl font-bold">{{ statistics.pending }}</p>
            </div>
            <Clock3 class="w-8 h-8 text-yellow-200" />
          </div>
        </div>

        <!-- Confirmed -->
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-indigo-100 text-sm font-medium">Confirmed</p>
              <p class="text-2xl font-bold">{{ statistics.confirmed }}</p>
            </div>
            <CheckCircle2 class="w-8 h-8 text-indigo-200" />
          </div>
        </div>

        <!-- Completed -->
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-4 text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-emerald-100 text-sm font-medium">Completed</p>
              <p class="text-2xl font-bold">{{ statistics.completed }}</p>
            </div>
            <Activity class="w-8 h-8 text-emerald-200" />
          </div>
        </div>
      </div>

      <!-- Search and Filters Bar -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
          <!-- Search Bar -->
          <div class="flex-1 max-w-md">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" />
              <input
                v-model="localFilters.search"
                type="text"
                :placeholder="wTrans('bookings.search_appointments')"
                class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all"
                @keyup.enter="applyFilters"
              />
            </div>
          </div>

          <!-- Quick Filters -->
          <div class="flex flex-wrap gap-2">
            <button
              @click="applyQuickFilter('today')"
              class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-600 dark:border-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
            >
              {{ wTrans('appointments.today') }}
            </button>
            <button
              @click="applyQuickFilter('week')"
              class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-600 dark:border-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
            >
              {{ wTrans('appointments.this_week') }}
            </button>
            <button
              @click="applyQuickFilter('month')"
              class="px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-600 dark:border-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
            >
              {{ wTrans('appointments.this_month') }}
            </button>
            <button
              v-if="isAdmin || hasActiveFilters"
              @click="showFilters = !showFilters"
              class="px-4 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all gap-2 flex items-center"
            >
              <Filter class="w-4 h-4" />
              {{ wTrans('appointments.advanced_filters') }}
              <span v-if="hasActiveFilters" class="bg-indigo-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                {{ Object.values(filters).filter(f => f && f !== 'all' && f !== '').length + (localFilters.search ? 1 : 0) }}
              </span>
            </button>
          </div>
        </div>
      </div>
      <div
        v-if="showFilters && isAdmin"
        class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ wTrans('appointments.status') }}
            </label>
            <select
              v-model="localFilters.status"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">{{ wTrans('appointments.all_statuses') }}</option>
              <option value="pending">{{ wTrans('appointments.pending') }}</option>
              <option value="confirmed">{{ wTrans('appointments.confirmed') }}</option>
              <option value="completed">{{ wTrans('appointments.completed') }}</option>
              <option value="cancelled">{{ wTrans('appointments.cancelled') }}</option>
              <option value="no_show">{{ wTrans('appointments.no_show') }}</option>
            </select>
          </div>

          <!-- Specialization Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ wTrans('bookings.specialization') }}
            </label>
            <select
              v-model="localFilters.specialization"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">{{ wTrans('appointments.all_specializations') }}</option>
              <option v-for="(name, slug) in specializations" :key="slug" :value="slug">
                {{ name }}
              </option>
            </select>
          </div>

          <!-- City Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ wTrans('bookings.city') }}
            </label>
            <select
              v-model="localFilters.city"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
            >
              <option value="all">{{ wTrans('appointments.all_cities') }}</option>
              <option v-for="(name, id) in cities" :key="id" :value="id">
                {{ name }}
              </option>
            </select>
          </div>

          <!-- Date From -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              {{ wTrans('appointments.date_from') }}
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
              {{ wTrans('appointments.date_to') }}
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
            {{ wTrans('appointments.apply_filters') }}
          </Button>
          <Button
            @click="clearFilters"
            variant="outline"
          >
            {{ wTrans('appointments.clear_all') }}
          </Button>
        </div>
      </div>

      <!-- Appointments Display Container -->
      <div v-if="appointments.data.length > 0">
        <!-- Appointments Table (Desktop) -->
        <div class="hidden lg:block bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden mb-8">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">
                    Patient & Doctor
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">
                    {{ isProvider ? 'Appointment' : 'Specialization' }}
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Date & Time</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Status</th>
                  <th v-if="isAdmin" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Location</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr
                  v-for="appointment in appointments.data"
                  :key="appointment.id"
                  class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-200"
                >
                  <!-- Patient & Doctor Column -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm">
                          {{ appointment.user.name.charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ appointment.user.name }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          with Dr. {{ appointment.provider_profile.user.name }}
                        </div>
                        <div class="text-sm text-gray-400 dark:text-gray-500">
                          {{ appointment.provider_profile.specialization.name }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Appointment/Specialization Column -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="isProvider" class="text-sm text-gray-900 dark:text-white">
                      <div class="font-medium">{{ formatDate(appointment.appointment_date) }}</div>
                      <div class="text-gray-500 dark:text-gray-400">{{ appointment.start_time }} - {{ appointment.end_time }}</div>
                    </div>
                    <div v-else class="text-sm text-gray-900 dark:text-white">
                      <div class="font-medium">{{ appointment.provider_profile.specialization.name }}</div>
                      <div v-if="appointment.notes" class="text-gray-500 dark:text-gray-400 truncate max-w-xs" :title="appointment.notes">
                        {{ appointment.notes.length > 30 ? appointment.notes.substring(0, 30) + '...' : appointment.notes }}
                      </div>
                    </div>
                  </td>

                  <!-- Date & Time Column -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="!isProvider" class="text-sm text-gray-900 dark:text-white">
                      <div class="font-medium">{{ formatDate(appointment.appointment_date) }}</div>
                      <div class="text-gray-500 dark:text-gray-400">{{ appointment.start_time }} - {{ appointment.end_time }}</div>
                    </div>
                    <div v-else class="text-sm text-gray-900 dark:text-white">
                      <div class="font-medium">{{ appointment.provider_profile.specialization.name }}</div>
                      <div v-if="appointment.notes" class="text-gray-500 dark:text-gray-400 truncate max-w-xs" :title="appointment.notes">
                        {{ appointment.notes.length > 30 ? appointment.notes.substring(0, 30) + '...' : appointment.notes }}
                      </div>
                    </div>
                  </td>

                  <!-- Status Column -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold"
                      :class="getStatusColor(appointment.status)"
                    >
                      <component :is="getStatusIcon(appointment.status)" class="w-4 h-4 mr-2" />
                      {{ appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1).replace('_', ' ') }}
                    </span>
                  </td>

                  <!-- Location Column (Admin only) -->
                  <td v-if="isAdmin" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ appointment.provider_profile.city?.name_ar || 'N/A' }}
                  </td>

                  <!-- Actions Column -->
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center space-x-2">
                    <Link
                      :href="appointmentsRoutes.show.url(appointment.id)"
                      class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors duration-200"
                      title="View Details"
                    >
                        <Eye class="w-5 h-5" />
                      </Link>

                      <!-- Patient Actions -->
                      <button
                        v-if="!isProvider && ['pending', 'confirmed'].includes(appointment.status)"
                        @click="cancelAppointment(appointment.id)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                        title="Cancel Appointment"
                      >
                        <X class="w-5 h-5" />
                      </button>

                      <!-- Provider/Admin Status Dropdown -->
                      <div
                        v-if="(isProvider || isAdmin) && getAvailableStatusTransitions(appointment.status, isProvider, isAdmin).length > 0"
                        class="relative"
                      >
                        <select
                          @change="(e: Event) => {
                            const newStatus = (e.target as HTMLSelectElement).value
                            if (newStatus) updateStatus(appointment.id, newStatus)
                            ;(e.target as HTMLSelectElement).value = ''
                          }"
                          class="text-xs px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-600 transition-all duration-200 appearance-none cursor-pointer"
                        >
                          <option value="" disabled selected>Status</option>
                          <option
                            v-for="status in getAvailableStatusTransitions(appointment.status, isProvider, isAdmin)"
                            :key="status"
                            :value="status"
                          >
                            {{ status.charAt(0).toUpperCase() }}{{ status.slice(1).replace('_', ' ') }}
                          </option>
                        </select>
                      </div>

                      <!-- Admin Delete Action -->
                      <button
                        v-if="isAdmin"
                        @click="deleteAppointment(appointment.id)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                        title="Delete Appointment"
                      >
                        <Trash2 class="w-5 h-5" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Appointments Cards (Mobile) -->
        <div class="lg:hidden space-y-4 mb-8">
          <div
            v-for="appointment in appointments.data"
            :key="appointment.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 p-6"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <!-- Header -->
                <div class="flex items-start space-x-4">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-xl font-bold">
                      {{ appointment.user.name.charAt(0).toUpperCase() }}
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ appointment.user.name }}
                      </h3>
                      <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                        :class="getStatusColor(appointment.status)"
                      >
                        <component :is="getStatusIcon(appointment.status)" class="w-3 h-3 mr-1" />
                        {{ appointment.status }}
                      </span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      with Dr. {{ appointment.provider_profile.user.name }} • {{ appointment.provider_profile.specialization.name }}
                    </p>
                    <p v-if="isAdmin" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      {{ appointment.provider_profile.city?.name_ar }}
                    </p>
                  </div>
                </div>

                <!-- Details -->
                <div class="mt-4 grid grid-cols-2 gap-4">
                  <div class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
                    <Calendar class="w-5 h-5 text-indigo-600" />
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Date</p>
                      <p class="text-sm font-medium">{{ formatDate(appointment.appointment_date) }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
                    <Clock class="w-5 h-5 text-indigo-600" />
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Time</p>
                      <p class="text-sm font-medium">{{ appointment.start_time }} - {{ appointment.end_time }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
                    <User class="w-5 h-5 text-indigo-600" />
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Patient</p>
                      <p class="text-sm font-medium">{{ appointment.user.name }}</p>
                    </div>
                  </div>
                  <div class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
                    <User class="w-5 h-5 text-green-600" />
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Doctor</p>
                      <p class="text-sm font-medium">Dr. {{ appointment.provider_profile.user.name }}</p>
                    </div>
                  </div>
                </div>

                <!-- Notes -->
                <div v-if="appointment.notes" class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                  <div class="flex items-start space-x-2">
                    <FileText class="w-4 h-4 text-gray-500 mt-0.5" />
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Notes</p>
                      <p class="text-sm text-gray-700 dark:text-gray-300">{{ appointment.notes }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Actions -->
              <div class="ml-4 flex flex-col space-y-2">
                <Link
                  :href="appointmentsRoutes.show.url(appointment.id)"
                  class="px-4 py-2 text-sm font-medium text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 border border-indigo-600 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all"
                >
                  View Details
                </Link>

                <!-- Patient Actions -->
                <button
                  v-if="!isProvider && ['pending', 'confirmed'].includes(appointment.status)"
                  @click="cancelAppointment(appointment.id)"
                  class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 border border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                >
                  Cancel
                </button>

                <!-- Provider/Admin Status Dropdown -->
                <div
                  v-if="(isProvider || isAdmin) && getAvailableStatusTransitions(appointment.status, isProvider, isAdmin).length > 0"
                  class="relative"
                >
                  <select
                    @change="(e: Event) => {
                      const newStatus = (e.target as HTMLSelectElement).value
                      if (newStatus) updateStatus(appointment.id, newStatus)
                      ;(e.target as HTMLSelectElement).value = ''
                    }"
                    class="w-full px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-all appearance-none cursor-pointer"
                    :class="getStatusColor(appointment.status)"
                  >
                    <option value="" disabled selected>Change Status...</option>
                    <option
                      v-for="status in getAvailableStatusTransitions(appointment.status, isProvider, isAdmin)"
                      :key="status"
                      :value="status"
                    >
                      {{ status.charAt(0).toUpperCase() }}{{ status.slice(1).replace('_', ' ') }}
                    </option>
                  </select>
                </div>

                <!-- Admin Actions -->
                <button
                  v-if="isAdmin"
                  @click="deleteAppointment(appointment.id)"
                  class="px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 border border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="appointments.last_page > 1" class="flex flex-col items-center gap-4 mt-8">
          <!-- Pagination Info -->
          <p class="text-sm text-gray-600 dark:text-gray-400">
            Showing
            <span class="font-semibold text-gray-900 dark:text-white">
              {{ (appointments.current_page - 1) * appointments.per_page + 1 }}
            </span>
            to
            <span class="font-semibold text-gray-900 dark:text-white">
              {{ Math.min(appointments.current_page * appointments.per_page, appointments.total) }}
            </span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">
              {{ appointments.total }}
            </span>
            appointments
          </p>

          <!-- Pagination Controls -->
          <div class="flex justify-center items-center space-x-2">
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
              <!-- Show all pages if 7 or fewer -->
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
              <!-- Show first page -->
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

              <!-- Ellipsis -->
              <span v-if="appointments.current_page > 3" class="px-2 py-2 text-gray-500">...</span>

              <!-- Middle pages -->
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

              <!-- Ellipsis -->
              <span v-if="appointments.current_page < appointments.last_page - 2" class="px-2 py-2 text-gray-500">...</span>

              <!-- Last page -->
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
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full mb-4">
          <Calendar class="w-8 h-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No appointments found</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          {{ hasActiveFilters ? 'Try adjusting your filters.' : getPageDescription() }}
        </p>
        <Link
          v-if="!isProvider && !isAdmin"
          :href="appointmentsRoutes.create.url()"
          class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all font-semibold"
        >
          Book Appointment
        </Link>
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
          <!-- Modal Header -->
          <div class="flex items-center gap-3 p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-yellow-100 dark:bg-yellow-900">
              <AlertCircle class="h-6 w-6 text-yellow-600 dark:text-yellow-400" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
              {{ confirmAction?.type === 'delete' ? 'Delete Appointment' : confirmAction?.type === 'cancel' ? 'Cancel Appointment' : 'Change Status' }}
            </h3>
          </div>

          <!-- Modal Body -->
          <div class="p-6">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ getConfirmMessage() }}
            </p>
          </div>

          <!-- Modal Footer -->
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
                  : confirmAction?.type === 'cancel'
                  ? 'bg-red-600 hover:bg-red-700'
                  : 'bg-indigo-600 hover:bg-indigo-700'
              ]"
            >
              {{ confirmAction?.type === 'delete' ? 'Delete' : confirmAction?.type === 'cancel' ? 'Cancel Appointment' : 'Confirm' }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>


