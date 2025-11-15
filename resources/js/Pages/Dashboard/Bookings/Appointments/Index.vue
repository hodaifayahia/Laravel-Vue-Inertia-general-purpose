<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Calendar, Clock, User, MapPin, FileText, CheckCircle2, XCircle, Clock3, Filter, X, AlertCircle, Eye, Trash2, Search, BarChart3, CalendarDays, Users, Activity, TrendingUp, Plus, Building2, Stethoscope, ChevronDown, ArrowLeft, ArrowRight, Loader2, MessageSquare } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import * as appointmentsRoutes from '@/routes/appointments'
import { ref, computed, watch, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { wTrans } from 'laravel-vue-i18n'
import { Textarea } from '@/components/ui/textarea'

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
}

interface Child {
  id: number
  name: string
  date_of_birth: string
  gender: string
  medical_notes: string | null
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
  child?: Child | null
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
  provinces?: Array<{ id: number; name_en: string; name_ar: string; code: string }>
  allCities?: Array<{ id: number; name_en: string; name_ar: string; province_id: number }>
}

interface TimeSlot {
  start_time: string
  end_time: string
  is_available: boolean
}

interface Provider {
  id: number
  user_id: number
  bio: string | null
  years_experience: number
  slot_duration: number
  is_available: boolean
  title: string
  qualifications: string | null
  consultation_fee?: number
  user: User
}

interface AvailableDate {
  date: string
  has_slots: boolean
}

const props = defineProps<Props>()

// Existing state
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

// Booking Modal State
const showBookingModal = ref(false)
const bookingStep = ref(1)
const selectedChild = ref<Child | null>(null)
const selectedProvince = ref<any>(null)
const selectedCity = ref<any>(null)
const selectedProvider = ref<Provider | null>(null)
const selectedDate = ref('')
const selectedSlot = ref<TimeSlot | null>(null)
const bookingNotes = ref('')
const bookingLoading = ref(false)
const showSuccessModal = ref(false)
const bookedAppointment = ref<any>(null)

const children = ref<Child[]>([])
const providers = ref<Provider[]>([])
const availableDates = ref<AvailableDate[]>([])
const availableSlots = ref<TimeSlot[]>([])

// Searchable dropdown state
const provinceSearch = ref('')
const citySearch = ref('')
const providerSearch = ref('')
const showProvinceDropdown = ref(false)
const showCityDropdown = ref(false)
const showProviderDropdown = ref(false)
const showChildDropdown = ref(false)

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

const getChildAge = (dateOfBirth: string): number => {
  const today = new Date()
  const birth = new Date(dateOfBirth)
  let age = today.getFullYear() - birth.getFullYear()
  const monthDiff = today.getMonth() - birth.getMonth()
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
    age--
  }
  return age
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
      return ''
  }
}

// Booking Modal Functions
const openBookingModal = () => {
  showBookingModal.value = true
  bookingStep.value = 1
  loadChildren()
  if (props.provinces && props.provinces.length > 0 && props.allCities && props.allCities.length > 0) {
    // Provinces already loaded
  }
}

const closeBookingModal = () => {
  showBookingModal.value = false
  resetBookingForm()
}

const resetBookingForm = () => {
  bookingStep.value = 1
  selectedChild.value = null
  selectedProvince.value = null
  selectedCity.value = null
  selectedProvider.value = null
  selectedDate.value = ''
  selectedSlot.value = null
  bookingNotes.value = ''
  providers.value = []
  availableDates.value = []
  availableSlots.value = []
  // Clear search terms
  provinceSearch.value = ''
  citySearch.value = ''
  providerSearch.value = ''
  showProvinceDropdown.value = false
  showCityDropdown.value = false
  showProviderDropdown.value = false
  showChildDropdown.value = false
}

const loadChildren = async () => {
  try {
    const response = await fetch('/api/children', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })
    
    if (!response.ok) throw new Error('Failed to load children')
    
    const data = await response.json()
    children.value = data
  } catch (error) {
    console.error('Failed to load children:', error)
    children.value = []
  }
}

const loadProviders = async () => {
  if (!selectedCity.value) return

  bookingLoading.value = true
  try {
    const response = await fetch(`/api/providers?city_id=${selectedCity.value.id}&specialization=dysgraphia`)
    const data = await response.json()
    providers.value = data
  } catch (error) {
    console.error('Failed to load providers:', error)
  } finally {
    bookingLoading.value = false
  }
}

const selectProviderAndLoadDates = async (provider: Provider) => {
  selectedProvider.value = provider
  selectedDate.value = ''
  selectedSlot.value = null

  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  selectedDate.value = tomorrow.toISOString().split('T')[0]

  await loadAvailableDates()
  bookingStep.value = 2
}

const loadAvailableDates = async () => {
  if (!selectedProvider.value || !selectedDate.value) return
  
  bookingLoading.value = true
  try {
    const date = new Date(selectedDate.value)
    const year = date.getFullYear()
    const month = date.getMonth()
    
    const response = await fetch(`/api/providers/${selectedProvider.value.id}/available-dates?year=${year}&month=${month + 1}`)
    const data = await response.json()
    availableDates.value = data.dates
  } catch (error) {
    console.error('Failed to load available dates:', error)
  } finally {
    bookingLoading.value = false
  }
}

const loadAvailableSlots = async (dateStr: string) => {
  if (!selectedProvider.value) return
  
  selectedDate.value = dateStr
  selectedSlot.value = null
  bookingLoading.value = true
  
  try {
    const response = await fetch(`/api/providers/${selectedProvider.value.id}/slots?date=${dateStr}`)
    const data = await response.json()
    availableSlots.value = data.slots
  } catch (error) {
    console.error('Failed to load slots:', error)
  } finally {
    bookingLoading.value = false
  }
}

const previousMonth = () => {
  const current = new Date(selectedDate.value)
  current.setMonth(current.getMonth() - 1)
  selectedDate.value = current.toISOString().split('T')[0]
  loadAvailableDates()
}

const nextMonth = () => {
  const current = new Date(selectedDate.value)
  current.setMonth(current.getMonth() + 1)
  selectedDate.value = current.toISOString().split('T')[0]
  loadAvailableDates()
}

const submitBooking = async () => {
  if (!selectedProvider.value || !selectedDate.value || !selectedSlot.value) return

  bookingLoading.value = true
  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
    
    const response = await fetch('/appointments', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(csrfToken && { 'X-CSRF-Token': csrfToken }),
      },
      body: JSON.stringify({
        child_id: selectedChild.value?.id || null,
        provider_profile_id: selectedProvider.value.id,
        appointment_date: selectedDate.value,
        start_time: selectedSlot.value.start_time,
        end_time: selectedSlot.value.end_time,
        notes: bookingNotes.value,
      })
    })

    const data = await response.json()

    if (!response.ok) {
      alert(data.message || 'Failed to book appointment')
      return
    }

    // Store appointment details for confirmation
    bookedAppointment.value = {
      child: selectedChild.value,
      provider: selectedProvider.value,
      city: selectedCity.value,
      province: selectedProvince.value,
      date: selectedDate.value,
      slot: selectedSlot.value,
      notes: bookingNotes.value,
      status: data.appointment?.status || 'pending'
    }

    // Close booking modal and show success
    closeBookingModal()
    showSuccessModal.value = true
  } catch (error) {
    console.error('Error booking appointment:', error)
    alert('An error occurred. Please try again.')
  } finally {
    bookingLoading.value = false
  }
}

const calendarDates = computed(() => {
  if (!selectedDate.value) return []
  
  const selected = new Date(selectedDate.value)
  const year = selected.getFullYear()
  const month = selected.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  
  const dates = []
  const startPadding = firstDay.getDay()
  
  for (let i = 0; i < startPadding; i++) {
    dates.push(null)
  }
  
  for (let day = 1; day <= lastDay.getDate(); day++) {
    const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`
    const availableDate = availableDates.value.find(d => d.date === dateStr)
    dates.push({
      date: dateStr,
      day: day,
      isAvailable: availableDate?.has_slots || false,
      isToday: dateStr === new Date().toISOString().split('T')[0],
      isSelected: dateStr === selectedDate.value
    })
  }
  
  return dates
})

const currentMonthName = computed(() => {
  if (!selectedDate.value) return ''
  const date = new Date(selectedDate.value)
  return date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })
})

const filteredCities = computed(() => {
  if (!selectedProvince.value || !props.allCities) return []
  let cities = props.allCities.filter(city => city.province_id === selectedProvince.value.id)
  if (citySearch.value) {
    cities = cities.filter(city =>
      city.name_ar.toLowerCase().includes(citySearch.value.toLowerCase()) ||
      city.name_en.toLowerCase().includes(citySearch.value.toLowerCase())
    )
  }
  return cities
})

const filteredProvinces = computed(() => {
  if (!provinceSearch.value) return props.provinces || []
  return (props.provinces || []).filter(province =>
    province.name_ar.toLowerCase().includes(provinceSearch.value.toLowerCase()) ||
    province.name_en.toLowerCase().includes(provinceSearch.value.toLowerCase())
  )
})

const filteredProviders = computed(() => {
  if (!providerSearch.value) return providers.value
  return providers.value.filter(provider =>
    provider.title.toLowerCase().includes(providerSearch.value.toLowerCase()) ||
    provider.user.name.toLowerCase().includes(providerSearch.value.toLowerCase())
  )
})

const selectProvince = (province: any) => {
  selectedProvince.value = province
  selectedCity.value = null
  providers.value = []
  provinceSearch.value = ''
}

watch(selectedCity, () => {
  if (selectedCity.value) {
    loadProviders()
  }
})

onMounted(() => {
  // Close dropdowns when clicking outside
  const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement
    if (!target.closest('.dropdown-container')) {
      showProvinceDropdown.value = false
      showCityDropdown.value = false
      showProviderDropdown.value = false
      showChildDropdown.value = false
    }
  }
  document.addEventListener('click', handleClickOutside)
})

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
          <Button
            @click="openBookingModal"
            class="gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 hover:shadow-lg"
          >
            <Plus class="w-4 h-4" />
            {{ wTrans('appointments.book_new') }}
          </Button>
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
                    {{ wTrans('appointments.patient_and_doctor') }}
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">
                    {{ isProvider ? wTrans('appointments.appointment') : wTrans('bookings.specialization') }}
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">{{ wTrans('appointments.date_and_time') }}</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">{{ wTrans('bookings.child_name') }}</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">{{ wTrans('bookings.status') }}</th>
                  <th v-if="isAdmin" class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">{{ wTrans('appointments.location') }}</th>
                  <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider">{{ wTrans('appointments.actions') }}</th>
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
                          {{ appointment.child ? appointment.child.name.charAt(0).toUpperCase() : appointment.user.name.charAt(0).toUpperCase() }}
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ appointment.child ? appointment.child.name : appointment.user.name }}
                        </div>
                        <div v-if="appointment.child" class="text-xs text-gray-500 dark:text-gray-400">
                          {{ wTrans('appointments.age') }}: {{ getChildAge(appointment.child.date_of_birth) }} {{ wTrans('bookings.years_old') }}
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                          {{ wTrans('appointments.with_doctor') }} {{ appointment.provider_profile.user.name }}
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

                  <!-- Child Name Column -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-white">
                      <div v-if="appointment.child" class="font-medium">
                        {{ appointment.child.name }}
                      </div>
                      <div v-else class="text-gray-500 dark:text-gray-400">
                        {{ wTrans('appointments.not_available') }}
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

                      <!-- Confirm Button (for both patients and providers) -->
                      <button
                        v-if="appointment.status === 'pending'"
                        @click="updateStatus(appointment.id, 'confirmed')"
                        class="px-3 py-1 text-xs font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 rounded-md transition-colors duration-200"
                        title="Confirm Appointment"
                      >
                        Confirm
                      </button>

                      <!-- Patient Cancel Button -->
                      <button
                        v-if="!isProvider && ['pending', 'confirmed'].includes(appointment.status)"
                        @click="cancelAppointment(appointment.id)"
                        class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 transition-colors duration-200"
                        title="Cancel Appointment"
                      >
                        <X class="w-5 h-5" />
                      </button>

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
                      {{ appointment.child ? appointment.child.name.charAt(0).toUpperCase() : appointment.user.name.charAt(0).toUpperCase() }}
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center space-x-3">
                      <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ appointment.child ? appointment.child.name : appointment.user.name }}
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
                  <div v-if="appointment.child" class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
                    <Users class="w-5 h-5 text-indigo-600" />
                    <div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Child's Age</p>
                      <p class="text-sm font-medium">{{ getChildAge(appointment.child.date_of_birth) }} years</p>
                    </div>
                  </div>
                  <div v-else class="flex items-center space-x-3 text-gray-700 dark:text-gray-300">
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

                <!-- Confirm Button (for both patients and providers) -->
                <button
                  v-if="appointment.status === 'pending'"
                  @click="updateStatus(appointment.id, 'confirmed')"
                  class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-600 rounded-lg transition-all"
                >
                  ✓ Confirm Appointment
                </button>

                <!-- Patient Cancel Button -->
                <button
                  v-if="!isProvider && ['pending', 'confirmed'].includes(appointment.status)"
                  @click="cancelAppointment(appointment.id)"
                  class="w-full px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 border border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all"
                >
                  Cancel
                </button>

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
        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">{{ wTrans('bookings.no_appointments') }}</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
          {{ hasActiveFilters ? wTrans('appointments.adjust_filters') : getPageDescription() }}
        </p>
        <Button
          @click="openBookingModal"
          class="gap-2 bg-gradient-to-r from-indigo-500 to-purple-600"
        >
          <Plus class="w-4 h-4" />
          {{ wTrans('bookings.book_appointment') }}
        </Button>
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
              {{ confirmAction?.type === 'delete' ? wTrans('appointments.delete_appointment') : confirmAction?.type === 'cancel' ? wTrans('appointments.cancel_appointment') : wTrans('appointments.change_status') }}
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
              {{ wTrans('bookings.cancel') }}
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
              {{ confirmAction?.type === 'delete' ? wTrans('appointments.delete') : confirmAction?.type === 'cancel' ? wTrans('appointments.cancel_appointment') : wTrans('bookings.confirm') }}
            </button>
          </div>
        </div>
      </div>

      <!-- Booking Modal -->
      <div
        v-if="showBookingModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="closeBookingModal"
      >
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-6xl w-full h-[75vh] overflow-hidden flex flex-col my-2 mx-2 sm:my-4 sm:mx-auto">
          <!-- Modal Header with Tabs -->
          <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex items-center justify-between z-10">
            <div class="flex items-center gap-4">
              <h2 class="text-2xl font-bold text-white flex items-center gap-2">
                <Calendar class="w-6 h-6" />
                {{ wTrans('bookings.book_appointment') }}
              </h2>
              <!-- Tab Indicators -->
              <div class="hidden md:flex items-center gap-2 ml-8">
                <div class="flex items-center gap-2">
                  <div :class="['w-8 h-8 rounded-full font-bold flex items-center justify-center', bookingStep >= 1 ? 'bg-white text-indigo-600' : 'bg-white/30 text-white']">1</div>
                  <span class="text-white/90 text-sm">Select</span>
                </div>
                <div class="h-1 w-8 bg-white/30"></div>
                <div class="flex items-center gap-2">
                  <div :class="['w-8 h-8 rounded-full font-bold flex items-center justify-center', bookingStep >= 2 ? 'bg-white text-indigo-600' : 'bg-white/30 text-white']">2</div>
                  <span class="text-white/90 text-sm">Date</span>
                </div>
                <div class="h-1 w-8 bg-white/30"></div>
                <div class="flex items-center gap-2">
                  <div :class="['w-8 h-8 rounded-full font-bold flex items-center justify-center', bookingStep >= 3 ? 'bg-white text-indigo-600' : 'bg-white/30 text-white']">3</div>
                  <span class="text-white/90 text-sm">Confirm</span>
                </div>
              </div>
            </div>
            <button
              @click="closeBookingModal"
              class="text-white/80 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10"
            >
              <X class="w-6 h-6" />
            </button>
          </div>

          <!-- Modal Body -->
          <div class="flex-1 overflow-y-auto p-6 sm:p-8 lg:p-10">
            <!-- Step 1: Select Location & Provider -->
            <div v-if="bookingStep === 1" class="space-y-8">
              <!-- Selection Grid - Horizontal Layout -->
              <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                <!-- Child Selection -->
                <div class="relative dropdown-container">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <Users class="w-4 h-4 inline mr-1" />
                    {{ wTrans('bookings.child_name') }} ({{ wTrans('bookings.optional') }})
                  </label>
                  <div class="relative">
                    <button
                      @click="showChildDropdown = !showChildDropdown"
                      class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-800 transition-all flex items-center justify-between hover:border-purple-400"
                    >
                      <span class="text-left truncate" :class="selectedChild ? 'text-gray-900 dark:text-white' : 'text-gray-500'">
                        {{ selectedChild ? selectedChild.name : wTrans('bookings.select_child') }}
                      </span>
                      <ChevronDown class="w-4 h-4 text-gray-400 flex-shrink-0" :class="showChildDropdown ? 'rotate-180' : ''" />
                    </button>

                    <!-- Child Dropdown -->
                    <div v-if="showChildDropdown" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-purple-300 dark:border-purple-600 rounded-xl shadow-2xl overflow-hidden">
                      <div class="max-h-80 overflow-y-auto">
                        <button
                          v-for="child in children"
                          :key="child.id"
                          @click="selectedChild = child; showChildDropdown = false"
                          class="w-full px-5 py-4 text-left hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-all border-b border-gray-200 dark:border-gray-700 last:border-b-0 cursor-pointer group"
                        >
                          <div class="font-semibold text-gray-900 dark:text-white text-base group-hover:text-purple-600 dark:group-hover:text-purple-400">{{ child.name }}</div>
                          <div class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-purple-600">{{ getChildAge(child.date_of_birth) }} years old</div>
                        </button>
                        <div v-if="children.length === 0" class="px-4 py-3 text-center text-gray-500">
                          No children available
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Province Selection -->
                <div class="relative dropdown-container">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <MapPin class="w-4 h-4 inline mr-1" />
                    {{ wTrans('bookings.province') }}
                  </label>
                  <div class="relative z-40">
                    <button
                      @click="showProvinceDropdown = !showProvinceDropdown"
                      class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-800 transition-all flex items-center justify-between hover:border-purple-400"
                    >
                      <span class="text-left truncate" :class="selectedProvince ? 'text-gray-900 dark:text-white' : 'text-gray-500'">
                        {{ selectedProvince ? selectedProvince.name_en : wTrans('bookings.select_province') }}
                      </span>
                      <ChevronDown class="w-4 h-4 text-gray-400 flex-shrink-0" :class="showProvinceDropdown ? 'rotate-180' : ''" />
                    </button>

                    <!-- Province Dropdown -->
                    <div v-if="showProvinceDropdown" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-purple-300 dark:border-purple-600 rounded-xl shadow-2xl overflow-hidden top-full">
                      <div class="p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                        <input
                          v-model="provinceSearch"
                          type="text"
                          placeholder="Search provinces..."
                          class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-300 transition-all"
                          @click.stop
                        />
                      </div>
                      <div class="max-h-80 overflow-y-auto">
                        <button
                          v-for="province in filteredProvinces"
                          :key="province.id"
                          @click="selectProvince(province); showProvinceDropdown = false"
                          class="w-full px-5 py-4 text-left hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-all border-b border-gray-200 dark:border-gray-700 last:border-b-0 cursor-pointer group"
                        >
                          <div class="font-semibold text-gray-900 dark:text-white text-base group-hover:text-purple-600 dark:group-hover:text-purple-400">{{ province.name }}</div>
                          <div class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-purple-600">{{ province.name_ar }}</div>
                        </button>
                        <div v-if="filteredProvinces.length === 0" class="px-4 py-3 text-center text-gray-500">
                          No provinces found
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- City Selection -->
                <div class="relative dropdown-container">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <Building2 class="w-4 h-4 inline mr-1" />
                    {{ wTrans('bookings.city') }}
                  </label>
                  <div class="relative">
                    <button
                      @click="showCityDropdown = !showCityDropdown"
                      class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:border-purple-500 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-800 transition-all flex items-center justify-between hover:border-purple-400"
                      :disabled="!selectedProvince"
                    >
                      <span class="text-left truncate" :class="selectedCity ? 'text-gray-900 dark:text-white' : 'text-gray-500'">
                        {{ selectedCity ? selectedCity.name_en : wTrans('bookings.select_city') }}
                      </span>
                      <ChevronDown class="w-4 h-4 text-gray-400 flex-shrink-0" :class="showCityDropdown ? 'rotate-180' : ''" />
                    </button>

                    <!-- City Dropdown -->
                    <div v-if="showCityDropdown" class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-purple-300 dark:border-purple-600 rounded-xl shadow-2xl overflow-hidden">
                      <div class="p-3 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
                        <input
                          v-model="citySearch"
                          type="text"
                          placeholder="Search cities..."
                          class="w-full px-4 py-3 border-2 border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-purple-500 focus:ring-2 focus:ring-purple-300 transition-all"
                        />
                      </div>
                      <div class="max-h-80 overflow-y-auto">
                        <button
                          v-for="city in filteredCities"
                          :key="city.id"
                          @click="selectedCity = city; showCityDropdown = false; citySearch = ''"
                          class="w-full px-5 py-4 text-left hover:bg-purple-100 dark:hover:bg-purple-900/40 transition-all border-b border-gray-200 dark:border-gray-700 last:border-b-0 cursor-pointer group"
                        >
                          <div class="font-semibold text-gray-900 dark:text-white text-base group-hover:text-purple-600 dark:group-hover:text-purple-400">{{ city.name }}</div>
                          <div class="text-sm text-gray-600 dark:text-gray-400 group-hover:text-purple-600">{{ city.name_ar }}</div>
                        </button>
                        <div v-if="filteredCities.length === 0" class="px-4 py-3 text-center text-gray-500">
                          No cities found
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Provider Selection -->
                <div class="relative dropdown-container">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <Stethoscope class="w-4 h-4 inline mr-1" />
                    {{ wTrans('bookings.select_specialist') }}
                  </label>

                  <div v-if="bookingLoading" class="text-center py-8">
                    <Loader2 class="w-8 h-8 animate-spin mx-auto text-purple-600" />
                    <p class="text-sm text-gray-500 mt-2">Loading specialists...</p>
                  </div>

                  <div v-else-if="providers.length === 0" class="text-center py-8 text-gray-500">
                    <Users class="w-12 h-12 mx-auto mb-2 opacity-50" />
                    <p>No specialists available in this city</p>
                  </div>

                  <div v-else>
                    <!-- Search Bar for Providers -->
                    <div class="relative mb-3">
                      <Search class="w-4 h-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="providerSearch"
                        type="text"
                        placeholder="Search specialists..."
                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md bg-gray-50 dark:bg-gray-700 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                      />
                    </div>

                    <!-- Provider Grid -->
                    <div class="grid gap-3 max-h-64 overflow-y-auto md:overflow-visible">
                      <button
                        v-for="provider in filteredProviders"
                        :key="provider.id"
                        @click="selectProviderAndLoadDates(provider)"
                        class="p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all text-left group hover:shadow-md"
                      >
                        <div class="flex items-start justify-between">
                          <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 dark:text-white group-hover:text-purple-600 dark:group-hover:text-purple-400 truncate">
                              {{ provider.title }} {{ provider.user.name }}
                            </div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                              {{ provider.years_experience }} years experience
                              <span v-if="provider.consultation_fee" class="text-purple-600 dark:text-purple-400 font-medium"> • {{ provider.consultation_fee }} DZD</span>
                            </div>
                          </div>
                          <ArrowRight class="w-5 h-5 text-gray-400 group-hover:text-purple-600 transition-colors flex-shrink-0 ml-2" />
                        </div>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 2: Select Date & Time -->
            <div v-if="bookingStep === 2" class="space-y-6">
              <!-- Back Button -->
              <button
                @click="bookingStep = 1; selectedProvider = null"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1"
              >
                <ArrowLeft class="w-4 h-4" />
                Change Specialist
              </button>

              <!-- Selected Provider Info -->
              <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-lg p-4 border border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                    {{ selectedProvider?.user.name.charAt(0) }}
                  </div>
                  <div>
                    <div class="font-semibold text-gray-900 dark:text-white">
                      {{ selectedProvider?.title }} {{ selectedProvider?.user.name }}
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      {{ selectedCity?.name_ar }}, {{ selectedProvince?.name_ar }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Calendar & Slots Grid -->
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Calendar -->
                <div class="lg:col-span-1">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <Calendar class="w-4 h-4 inline mr-1" />
                    Select Date / اختر التاريخ
                  </label>

                  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <!-- Month Navigation -->
                    <div class="flex items-center justify-between mb-4">
                      <Button @click="previousMonth" size="sm" variant="outline">
                        <ArrowLeft class="w-4 h-4" />
                      </Button>
                      <h3 class="text-sm font-semibold text-gray-900 dark:text-white text-center">{{ currentMonthName }}</h3>
                      <Button @click="nextMonth" size="sm" variant="outline">
                        <ArrowRight class="w-4 h-4" />
                      </Button>
                    </div>

                    <!-- Day Headers -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                      <div v-for="day in ['S', 'M', 'T', 'W', 'T', 'F', 'S']" :key="day" 
                        class="text-center text-xs font-bold text-gray-600 dark:text-gray-400 py-1">
                        {{ day }}
                      </div>
                    </div>

                    <!-- Calendar Days -->
                    <div class="grid grid-cols-7 gap-1">
                      <button
                        v-for="(date, index) in calendarDates"
                        :key="index"
                        @click="date && date.isAvailable ? loadAvailableSlots(date.date) : null"
                        :disabled="!date || !date.isAvailable"
                        class="aspect-square flex items-center justify-center rounded text-xs font-medium transition-all"
                        :class="{
                          'bg-indigo-600 text-white font-bold': date && date.isSelected,
                          'bg-green-200 dark:bg-green-900 text-green-900 dark:text-green-100 hover:bg-green-300 dark:hover:bg-green-800 cursor-pointer': date && date.isAvailable && !date.isSelected,
                          'text-gray-300 dark:text-gray-600 cursor-not-allowed': date && !date.isAvailable,
                          'border-2 border-indigo-500': date && date.isToday && !date.isSelected,
                        }"
                      >
                        {{ date?.day }}
                      </button>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-2 text-xs mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                      <div class="w-3 h-3 bg-green-200 dark:bg-green-900 rounded"></div>
                      <span class="text-gray-600 dark:text-gray-400">Available</span>
                    </div>
                  </div>
                </div>

                <!-- Right: Time Slots -->
                <div class="lg:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                    <Clock class="w-4 h-4 inline mr-1" />
                    Select Time / اختر الوقت
                  </label>

                  <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 min-h-96">
                    <div v-if="!selectedDate" class="text-center py-12 text-gray-500">
                      <Clock class="w-12 h-12 mx-auto mb-3 opacity-50" />
                      <p class="font-medium">Select a date to see available times</p>
                      <p class="text-sm">اختر تاريخاً لرؤية الأوقات المتاحة</p>
                    </div>

                    <div v-else-if="bookingLoading" class="text-center py-12">
                      <Loader2 class="w-8 h-8 animate-spin mx-auto text-indigo-600" />
                      <p class="text-sm text-gray-500 mt-2">Loading time slots...</p>
                    </div>

                    <div v-else-if="availableSlots.length === 0" class="text-center py-12 text-gray-500">
                      <Clock class="w-12 h-12 mx-auto mb-3 opacity-50" />
                      <p class="font-medium">No time slots available</p>
                      <p class="text-sm">Please select another date</p>
                    </div>

                    <div v-else class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                      <button
                        v-for="slot in availableSlots"
                        :key="`${slot.start_time}-${slot.end_time}`"
                        @click="selectedSlot = slot"
                        :disabled="!slot.is_available"
                        class="px-3 py-3 rounded-lg border-2 transition-all text-sm font-medium"
                        :class="selectedSlot === slot
                          ? 'border-indigo-600 bg-indigo-600 text-white shadow-lg'
                          : slot.is_available
                            ? 'border-gray-300 dark:border-gray-600 hover:border-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20'
                            : 'border-gray-200 dark:border-gray-700 opacity-50 cursor-not-allowed'
                        "
                      >
                        {{ slot.start_time }}<br>{{ slot.end_time }}
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Step 3: Confirmation & Notes -->
            <div v-if="bookingStep === 3" class="space-y-6">
              <!-- Back Button -->
              <button
                @click="bookingStep = 2"
                class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 flex items-center gap-1"
              >
                <ArrowLeft class="w-4 h-4" />
                Change Time
              </button>

              <!-- Appointment Summary -->
              <div class="space-y-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Appointment Summary / ملخص الموعد</h3>

                <!-- Summary Card -->
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
                  <!-- Child Info (if selected) -->
                  <div v-if="selectedChild" class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <Users class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" />
                    <div>
                      <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Child</div>
                      <div class="text-gray-900 dark:text-white font-semibold">{{ selectedChild.name }} ({{ getChildAge(selectedChild.date_of_birth) }} years)</div>
                    </div>
                  </div>

                  <!-- Provider Info -->
                  <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <Stethoscope class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" />
                    <div>
                      <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Specialist / الأخصائي</div>
                      <div class="text-gray-900 dark:text-white font-semibold">{{ selectedProvider?.title }} {{ selectedProvider?.user.name }}</div>
                    </div>
                  </div>

                  <!-- Location Info -->
                  <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                    <MapPin class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" />
                    <div>
                      <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Location / المكان</div>
                      <div class="text-gray-900 dark:text-white font-semibold">{{ selectedCity?.name_ar }}, {{ selectedProvince?.name_ar }}</div>
                    </div>
                  </div>

                  <!-- Date & Time Info -->
                  <div class="flex items-start gap-3">
                    <Calendar class="w-5 h-5 text-indigo-600 flex-shrink-0 mt-0.5" />
                    <div>
                      <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Date & Time / التاريخ والوقت</div>
                      <div class="text-gray-900 dark:text-white font-semibold">{{ formatDate(selectedDate) }}</div>
                      <div class="text-gray-900 dark:text-white font-semibold">{{ selectedSlot?.start_time }} - {{ selectedSlot?.end_time }}</div>
                    </div>
                  </div>
                </div>

                <!-- Notes Field -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    <MessageSquare class="w-4 h-4 inline mr-1" />
                    Additional Notes (Optional) / ملاحظات إضافية
                  </label>
                  <Textarea
                    v-model="bookingNotes"
                    placeholder="Add any notes about your appointment, symptoms, or special requirements..."
                    class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800 transition-all min-h-32"
                  />
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="sticky bottom-0 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 px-6 py-4">
            <div v-if="bookingStep === 2 && selectedSlot" class="flex items-center justify-between gap-4">
              <div class="text-sm text-gray-600 dark:text-gray-400">
                <div class="font-medium text-gray-900 dark:text-white">{{ formatDate(selectedDate) }}</div>
                <div>{{ selectedSlot.start_time }} - {{ selectedSlot.end_time }}</div>
              </div>
              <Button
                @click="bookingStep = 3"
                class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:shadow-lg gap-2"
              >
                <ArrowRight class="w-4 h-4" />
                {{ wTrans('bookings.next') || 'Next' }}
              </Button>
            </div>

            <div v-if="bookingStep === 3" class="flex items-center justify-between gap-4">
              <Button
                @click="bookingStep = 2"
                variant="outline"
                class="gap-2"
              >
                <ArrowLeft class="w-4 h-4" />
                {{ wTrans('bookings.back') || 'Back' }}
              </Button>
              <Button
                @click="submitBooking"
                :disabled="bookingLoading"
                class="bg-gradient-to-r from-green-600 to-emerald-600 hover:shadow-lg gap-2"
              >
                <Loader2 v-if="bookingLoading" class="w-4 h-4 animate-spin" />
                <CheckCircle2 v-else class="w-4 h-4" />
                {{ bookingLoading ? wTrans('bookings.booking') || 'Booking...' : wTrans('bookings.confirm') || 'Confirm Booking' }}
              </Button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Success Confirmation Modal -->
    <Teleport to="body">
      <div
        v-if="showSuccessModal"
        class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4"
        @click.self="showSuccessModal = false"
      >
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-md sm:max-w-lg lg:max-w-2xl w-full animate-in mx-4 sm:mx-auto">
          <!-- Success Header -->
          <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-6 text-center">
            <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
              <CheckCircle2 class="w-10 h-10 text-green-600" />
            </div>
            <h2 class="text-2xl font-bold text-white mb-2">Appointment Booked Successfully!</h2>
            <p class="text-green-100">تم حجز الموعد بنجاح</p>
          </div>

          <!-- Appointment Details -->
          <div class="p-6 space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Appointment Details / تفاصيل الموعد</h3>
            
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-3">
              <!-- Status Badge -->
              <div class="flex items-center justify-between pb-3 border-b border-gray-200 dark:border-gray-700">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Status</span>
                <span :class="['px-3 py-1 rounded-full text-xs font-semibold', getStatusColor(bookedAppointment?.status || 'pending')]">
                  {{ wTrans(`appointments.${bookedAppointment?.status}`) || bookedAppointment?.status || 'Pending' }}
                </span>
              </div>

              <!-- Child Info (if applicable) -->
              <div v-if="bookedAppointment?.child" class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                <Users class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Child / الطفل</div>
                  <div class="text-gray-900 dark:text-white font-semibold">
                    {{ bookedAppointment.child.name }} ({{ getChildAge(bookedAppointment.child.date_of_birth) }} {{ wTrans('bookings.years_old') }})
                  </div>
                </div>
              </div>

              <!-- Provider Info -->
              <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                <Stethoscope class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Specialist / الأخصائي</div>
                  <div class="text-gray-900 dark:text-white font-semibold">
                    {{ bookedAppointment?.provider?.title }} {{ bookedAppointment?.provider?.user?.name }}
                  </div>
                </div>
              </div>

              <!-- Location Info -->
              <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                <MapPin class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Location / المكان</div>
                  <div class="text-gray-900 dark:text-white font-semibold">
                    {{ bookedAppointment?.city?.name_ar }}, {{ bookedAppointment?.province?.name_ar }}
                  </div>
                </div>
              </div>

              <!-- Date & Time Info -->
              <div class="flex items-start gap-3 pb-3 border-b border-gray-200 dark:border-gray-700">
                <Calendar class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Date & Time / التاريخ والوقت</div>
                  <div class="text-gray-900 dark:text-white font-semibold">
                    {{ formatDate(bookedAppointment?.date) }}
                  </div>
                  <div class="text-gray-900 dark:text-white font-semibold">
                    {{ bookedAppointment?.slot?.start_time }} - {{ bookedAppointment?.slot?.end_time }}
                  </div>
                </div>
              </div>

              <!-- Notes (if any) -->
              <div v-if="bookedAppointment?.notes" class="flex items-start gap-3">
                <MessageSquare class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" />
                <div class="flex-1">
                  <div class="text-sm font-medium text-gray-700 dark:text-gray-300">Notes / ملاحظات</div>
                  <div class="text-gray-900 dark:text-white">{{ bookedAppointment.notes }}</div>
                </div>
              </div>
            </div>

            <!-- Info Message -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
              <div class="flex gap-3">
                <AlertCircle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                <div class="text-sm text-blue-800 dark:text-blue-300">
                  <p class="font-medium mb-1">Important Information</p>
                  <p>Your appointment is currently <strong>{{ bookedAppointment?.status || 'pending' }}</strong>. You will receive a notification once it is confirmed by the specialist.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal Footer -->
          <div class="border-t border-gray-200 dark:border-gray-700 px-6 py-4 flex gap-3">
            <Button
              @click="showSuccessModal = false; router.reload()"
              class="flex-1 bg-gradient-to-r from-green-600 to-emerald-600 hover:shadow-lg gap-2"
            >
              <CheckCircle2 class="w-4 h-4" />
              View My Appointments
            </Button>
            <Button
              @click="showSuccessModal = false"
              variant="outline"
              class="gap-2"
            >
              Close
            </Button>
          </div>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>


