<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Calendar, Clock, MapPin, User, Stethoscope, CheckCircle2, Award, Smile, Frown, ArrowRight, ArrowLeft, ChevronDown, Building2, Users } from 'lucide-vue-next'
import { wTrans } from 'laravel-vue-i18n'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Textarea } from '@/components/ui/textarea'
import { Label } from '@/components/ui/label'

interface Child {
  id: number
  name: string
  date_of_birth: string
  gender: string
  medical_notes: string | null
}

interface Province {
  id: number
  name_en: string
  name_ar: string
  code: string
}

interface City {
  id: number
  name_en: string
  name_ar: string
  province_id: number
}

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
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
  user: User
}

interface TimeSlot {
  start_time: string
  end_time: string
  is_available: boolean
}

interface AvailableDate {
  date: string
  has_slots: boolean
}

interface Props {
  provinces: Province[]
  cities: City[]
  success?: boolean
}

const props = defineProps<Props>()

// State
const step = ref(props.success ? 5 : 1)
const selectedChild = ref<Child | null>(null)
const selectedProvince = ref<Province | null>(null)
const selectedCity = ref<City | null>(null)
const selectedProvider = ref<Provider | null>(null)
const selectedDate = ref('')
const selectedSlot = ref<TimeSlot | null>(null)
const notes = ref('')

const children = ref<Child[]>([])
const providers = ref<Provider[]>([])
const availableDates = ref<AvailableDate[]>([])
const availableSlots = ref<TimeSlot[]>([])
const loading = ref(false)

// Dropdown state
const showChildDropdown = ref(false)
const showProvinceDropdown = ref(false)
const showCityDropdown = ref(false)
const showProviderDropdown = ref(false)
const provinceSearch = ref('')
const citySearch = ref('')
const providerSearch = ref('')

// Computed
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

const filteredCities = computed(() => {
  if (!selectedProvince.value) return []
  return props.cities.filter(city => city.province_id === selectedProvince.value!.id)
})

const calendarDates = computed(() => {
  if (!selectedDate.value) return []
  
  const selected = new Date(selectedDate.value)
  const year = selected.getFullYear()
  const month = selected.getMonth()
  
  const firstDay = new Date(year, month, 1)
  const lastDay = new Date(year, month + 1, 0)
  
  const dates = []
  const startPadding = firstDay.getDay()
  
  // Add padding for days before month starts
  for (let i = 0; i < startPadding; i++) {
    dates.push(null)
  }
  
  // Add actual days
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
  return date.toLocaleDateString('ar-DZ', { month: 'long', year: 'numeric' })
})

// Dropdown computed properties
const filteredProvinces = computed(() => {
  if (!provinceSearch.value) return props.provinces
  const search = provinceSearch.value.toLowerCase()
  return props.provinces.filter(province =>
    province.name_ar.toLowerCase().includes(search) ||
    province.name_en.toLowerCase().includes(search)
  )
})

const filteredCitiesBySearch = computed(() => {
  const cities = filteredCities.value
  if (!citySearch.value) return cities
  const search = citySearch.value.toLowerCase()
  return cities.filter(city =>
    city.name_ar.toLowerCase().includes(search) ||
    city.name_en.toLowerCase().includes(search)
  )
})

const filteredProvidersBySearch = computed(() => {
  if (!providerSearch.value) return providers.value
  const search = providerSearch.value.toLowerCase()
  return providers.value.filter(provider =>
    provider.user.name.toLowerCase().includes(search) ||
    (provider.title && provider.title.toLowerCase().includes(search)) ||
    (provider.bio && provider.bio.toLowerCase().includes(search))
  )
})

// Functions
const loadChildren = async () => {
  try {
    console.log('Loading children...')
    const response = await fetch('/api/children', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    })
    
    console.log('Response status:', response.status)
    
    if (!response.ok) {
      const errorText = await response.text()
      console.error('API error response:', errorText)
      throw new Error(`Failed to load children: ${response.status}`)
    }
    
    const data = await response.json()
    console.log('Children loaded:', data)
    children.value = data
  } catch (error) {
    console.error('Failed to load children:', error)
    children.value = []
  }
}

const selectChild = (child: Child) => {
  selectedChild.value = child
  showChildDropdown.value = false
}

const selectProvince = (province: Province) => {
  selectedProvince.value = province
  selectedCity.value = null
  selectedProvider.value = null
  selectedDate.value = ''
  selectedSlot.value = null
  showProvinceDropdown.value = false
  provinceSearch.value = ''
  // Auto-load providers if city is already selected
  if (selectedCity.value) {
    loadProviders()
  }
}

const selectCity = async (city: City) => {
  selectedCity.value = city
  selectedProvider.value = null
  selectedDate.value = ''
  selectedSlot.value = null
  showCityDropdown.value = false
  citySearch.value = ''
  await loadProviders()
}

const loadProviders = async () => {
  if (!selectedCity.value) return

  loading.value = true
  try {
    const response = await fetch(`/api/providers?city_id=${selectedCity.value.id}&specialization=dysgraphia`)
    const data = await response.json()
    providers.value = data
  } catch (error) {
    console.error('Failed to load providers:', error)
  } finally {
    loading.value = false
  }
}

const selectProvider = async (provider: Provider) => {
  selectedProvider.value = provider
  selectedDate.value = ''
  selectedSlot.value = null
  showProviderDropdown.value = false
  providerSearch.value = ''

  // Set initial date to today or tomorrow
  const tomorrow = new Date()
  tomorrow.setDate(tomorrow.getDate() + 1)
  selectedDate.value = tomorrow.toISOString().split('T')[0]

  await loadAvailableDates()
}

const loadAvailableDates = async () => {
  if (!selectedProvider.value || !selectedDate.value) return
  
  loading.value = true
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
    loading.value = false
  }
}

const selectDate = async (dateStr: string) => {
  selectedDate.value = dateStr
  selectedSlot.value = null
  await loadAvailableSlots()
}

const loadAvailableSlots = async () => {
  if (!selectedProvider.value || !selectedDate.value) return
  
  loading.value = true
  try {
    const response = await fetch(`/api/providers/${selectedProvider.value.id}/slots?date=${selectedDate.value}`)
    const data = await response.json()
    availableSlots.value = data.slots
  } catch (error) {
    console.error('Failed to load slots:', error)
  } finally {
    loading.value = false
  }
}

const selectSlot = (slot: TimeSlot) => {
  selectedSlot.value = slot
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

const bookAppointment = async () => {
  if (!selectedProvider.value || !selectedDate.value || !selectedSlot.value) return

  loading.value = true
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
        notes: notes.value,
      })
    })

    const data = await response.json()

    if (!response.ok) {
      console.error('Booking failed:', data)
      const errorMessage = data.message || data.error || 'Failed to book appointment. Please try again.'
      alert(errorMessage)
      loading.value = false
      return
    }

    // Show success message
    step.value = 5

    // Redirect to appointments page after 2 seconds
    setTimeout(() => {
      router.visit('/appointments')
    }, 2000)
  } catch (error) {
    console.error('Error booking appointment:', error)
    alert('An error occurred. Please try again.')
    loading.value = false
  }
}

const resetBooking = () => {
  step.value = 1
  selectedChild.value = null
  selectedProvince.value = null
  selectedCity.value = null
  selectedProvider.value = null
  selectedDate.value = ''
  selectedSlot.value = null
  notes.value = ''
  closeDropdowns()
}

const formatDate = (dateString: string): string => {
  const date = new Date(dateString)
  return date.toLocaleDateString('ar-DZ', { 
    weekday: 'long',
    year: 'numeric', 
    month: 'long', 
    day: 'numeric' 
  })
}

// Close dropdowns when clicking outside
const closeDropdowns = () => {
  showChildDropdown.value = false
  showProvinceDropdown.value = false
  showCityDropdown.value = false
  showProviderDropdown.value = false
  provinceSearch.value = ''
  citySearch.value = ''
  providerSearch.value = ''
}

// Load children on component mount
onMounted(() => {
  loadChildren()
})

// Watch for step changes to close dropdowns
watch(step, () => {
  closeDropdowns()
})
</script>

<template>
  <AppLayout>
    <Head :title="wTrans('bookings.book_appointment')" />

    <div class="container mx-auto px-4 py-8 max-w-7xl">
      <!-- Header -->
      <div class="mb-8 text-center">
        <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-2">
          {{ wTrans('bookings.book_appointment') }}
        </h1>
        <p class="text-lg text-gray-600 dark:text-gray-400">
          {{ wTrans('bookings.book_appointment') }}
        </p>
      </div>

      <!-- Success Page -->
      <div v-if="step === 5" class="max-w-2xl mx-auto">
        <Card class="border-2 border-green-500">
          <CardContent class="pt-12 pb-12 text-center">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full mb-6">
              <Smile class="w-12 h-12 text-white" />
            </div>
            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
              {{ wTrans('bookings.booked_successfully') }}
            </h2>
            <p class="text-xl text-gray-600 dark:text-gray-400 mb-8">
              {{ wTrans('bookings.booked_successfully') }}
            </p>

            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950 dark:to-purple-950 rounded-xl p-6 mb-8">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-right">
                <div v-if="selectedChild">
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">الطفل / Child</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ selectedChild.name }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">الطبيب / Doctor</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedProvider?.title }} {{ selectedProvider?.user.name }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">التاريخ / Date</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(selectedDate) }}</p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">الوقت / Time</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedSlot?.start_time }} - {{ selectedSlot?.end_time }}
                  </p>
                </div>
                <div>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">الموقع / Location</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedCity?.name_ar }}، {{ selectedProvince?.name_ar }}
                  </p>
                </div>
              </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-4 justify-center">
              <Button
                @click="router.visit('/appointments')"
                class="px-8 py-3 bg-gradient-to-r from-indigo-500 to-purple-600"
              >
                <CheckCircle2 class="w-5 h-5 ml-2" />
                {{ wTrans('bookings.my_appointments') }}
              </Button>
              <Button
                @click="resetBooking()"
                variant="outline"
                class="px-8 py-3"
              >
                {{ wTrans('bookings.book_appointment') }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Single Page Booking Form -->
      <div v-else class="space-y-8">
        <!-- Selection Cards Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Child Selection -->
          <Card class="border-2 border-purple-200 dark:border-purple-800" @click="closeDropdowns">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-lg">
                <Users class="w-5 h-5 text-purple-600" />
                {{ wTrans('bookings.select_child') }}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="relative">
                <button
                  @click.stop="showChildDropdown = !showChildDropdown"
                  class="w-full p-4 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-right flex items-center justify-between hover:border-purple-500 transition-all"
                  :class="{ 'border-purple-500 bg-purple-50 dark:bg-purple-900': selectedChild }"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0">
                      <Users class="w-5 h-5 text-white" />
                    </div>
                    <div v-if="selectedChild" class="text-right">
                      <div class="font-semibold text-gray-900 dark:text-white">{{ selectedChild.name }}</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">{{ getChildAge(selectedChild.date_of_birth) }} years old</div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                      اختر الطفل / Select Child
                    </div>
                  </div>
                  <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showChildDropdown }" />
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-if="showChildDropdown"
                  class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-xl max-h-80 overflow-y-auto"
                  @click.stop
                >
                  <div v-if="children.length === 0" class="p-4 text-center text-gray-500">
                    <p>لا توجد أطفال / No children added</p>
                  </div>
                  <div v-else class="max-h-64 overflow-y-auto">
                    <button
                      v-for="child in children"
                      :key="child.id"
                      @click="selectChild(child)"
                      class="w-full p-4 text-right hover:bg-purple-50 dark:hover:bg-purple-900 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    >
                      <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold text-xs">
                        {{ child.name.charAt(0).toUpperCase() }}
                      </div>
                      <div class="flex-1 text-left">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ child.name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ getChildAge(child.date_of_birth) }} years • {{ child.gender }}</div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Province Selection -->
          <Card class="border-2 border-indigo-200 dark:border-indigo-800" @click="closeDropdowns">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-lg">
                <MapPin class="w-5 h-5 text-indigo-600" />
                الولاية / Province
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="relative">
                <button
                  @click.stop="showProvinceDropdown = !showProvinceDropdown"
                  class="w-full p-4 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-right flex items-center justify-between hover:border-indigo-500 transition-all"
                  :class="{ 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900': selectedProvince }"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                      <MapPin class="w-5 h-5 text-white" />
                    </div>
                    <div v-if="selectedProvince" class="text-right">
                      <div class="font-semibold text-gray-900 dark:text-white">{{ selectedProvince.name_ar }}</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">{{ selectedProvince.name_en }}</div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                      اختر الولاية / Select Province
                    </div>
                  </div>
                  <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showProvinceDropdown }" />
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-if="showProvinceDropdown"
                  class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-xl max-h-80 overflow-y-auto"
                  @click.stop
                >
                  <div class="p-2">
                    <input
                      v-model="provinceSearch"
                      type="text"
                      placeholder="البحث عن ولاية... / Search provinces..."
                      class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg text-right focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 dark:focus:ring-indigo-800"
                    />
                  </div>
                  <div class="max-h-64 overflow-y-auto">
                    <button
                      v-for="province in filteredProvinces"
                      :key="province.id"
                      @click="selectProvince(province)"
                      class="w-full p-4 text-right hover:bg-indigo-50 dark:hover:bg-indigo-900 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    >
                      <div class="w-8 h-8 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <MapPin class="w-4 h-4 text-white" />
                      </div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ province.name_ar }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ province.name_en }}</div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- City Selection -->
          <Card class="border-2 border-green-200 dark:border-green-800" @click="closeDropdowns">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-lg">
                <Building2 class="w-5 h-5 text-green-600" />
                المدينة / City
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="relative">
                <button
                  @click.stop="showCityDropdown = !showCityDropdown"
                  :disabled="!selectedProvince"
                  class="w-full p-4 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-right flex items-center justify-between hover:border-green-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="{ 'border-green-500 bg-green-50 dark:bg-green-900': selectedCity }"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                      <Building2 class="w-5 h-5 text-white" />
                    </div>
                    <div v-if="selectedCity" class="text-right">
                      <div class="font-semibold text-gray-900 dark:text-white">{{ selectedCity.name_ar }}</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">{{ selectedCity.name_en }}</div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                      اختر المدينة / Select City
                    </div>
                  </div>
                  <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showCityDropdown }" />
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-if="showCityDropdown"
                  class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-xl max-h-80 overflow-y-auto"
                  @click.stop
                >
                  <div class="p-2">
                    <input
                      v-model="citySearch"
                      type="text"
                      placeholder="البحث عن مدينة... / Search cities..."
                      class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg text-right focus:border-green-500 focus:ring-2 focus:ring-green-200 dark:focus:ring-green-800"
                    />
                  </div>
                  <div class="max-h-64 overflow-y-auto">
                    <button
                      v-for="city in filteredCitiesBySearch"
                      :key="city.id"
                      @click="selectCity(city)"
                      class="w-full p-4 text-right hover:bg-green-50 dark:hover:bg-green-900 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    >
                      <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <Building2 class="w-4 h-4 text-white" />
                      </div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ city.name_ar }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">{{ city.name_en }}</div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Provider Selection -->
          <Card class="border-2 border-purple-200 dark:border-purple-800" @click="closeDropdowns">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-lg">
                <User class="w-5 h-5 text-purple-600" />
                الأخصائي / Specialist
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div class="relative">
                <button
                  @click.stop="showProviderDropdown = !showProviderDropdown"
                  :disabled="!selectedCity"
                  class="w-full p-4 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl text-right flex items-center justify-between hover:border-purple-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  :class="{ 'border-purple-500 bg-purple-50 dark:bg-purple-900': selectedProvider }"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0">
                      <User class="w-5 h-5 text-white" />
                    </div>
                    <div v-if="selectedProvider" class="text-right">
                      <div class="font-semibold text-gray-900 dark:text-white">{{ selectedProvider.title }} {{ selectedProvider.user.name }}</div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">Dysgraphia Specialist</div>
                    </div>
                    <div v-else class="text-gray-500 dark:text-gray-400">
                      اختر الأخصائي / Select Specialist
                    </div>
                  </div>
                  <ChevronDown class="w-5 h-5 text-gray-400 transition-transform" :class="{ 'rotate-180': showProviderDropdown }" />
                </button>

                <!-- Dropdown Menu -->
                <div
                  v-if="showProviderDropdown"
                  class="absolute z-50 w-full mt-2 bg-white dark:bg-gray-800 border-2 border-gray-300 dark:border-gray-600 rounded-xl shadow-xl max-h-80 overflow-y-auto"
                  @click.stop
                >
                  <div class="p-2">
                    <input
                      v-model="providerSearch"
                      type="text"
                      placeholder="البحث عن أخصائي... / Search specialists..."
                      class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg text-right focus:border-purple-500 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-800"
                    />
                  </div>
                  <div class="max-h-64 overflow-y-auto">
                    <div v-if="loading" class="text-center py-8">
                      <div class="inline-block animate-spin rounded-full h-6 w-6 border-b-2 border-purple-600"></div>
                    </div>
                    <div v-else-if="filteredProvidersBySearch.length === 0" class="text-center py-8 text-gray-500">
                      <Frown class="w-8 h-8 mx-auto mb-2 opacity-50" />
                      <p class="text-sm">لا يوجد أخصائيون متاحون</p>
                      <p class="text-xs">No specialists available</p>
                    </div>
                    <button
                      v-else
                      v-for="provider in filteredProvidersBySearch"
                      :key="provider.id"
                      @click="selectProvider(provider)"
                      class="w-full p-4 text-right hover:bg-purple-50 dark:hover:bg-purple-900 transition-colors flex items-center gap-3 border-b border-gray-100 dark:border-gray-700 last:border-b-0"
                    >
                      <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-600 rounded-lg flex items-center justify-center flex-shrink-0">
                        <User class="w-4 h-4 text-white" />
                      </div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ provider.title }} {{ provider.user.name }}</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                          {{ provider.years_experience }} سنوات خبرة • {{ provider.consultation_fee }} دج
                        </div>
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Date & Time Selection -->
        <div v-if="selectedProvider" class="space-y-6">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2 text-xl">
                <Calendar class="w-6 h-6 text-indigo-600" />
                اختر التاريخ والوقت / Select Date & Time
              </CardTitle>
              <CardDescription>
                اختر موعداً مع {{ selectedProvider.title }} {{ selectedProvider.user.name }}
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Calendar -->
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <Button @click="previousMonth" variant="outline" size="sm">
                      <ArrowRight class="w-4 h-4" />
                    </Button>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ currentMonthName }}</h3>
                    <Button @click="nextMonth" variant="outline" size="sm">
                      <ArrowLeft class="w-4 h-4" />
                    </Button>
                  </div>

                  <div class="grid grid-cols-7 gap-2 mb-4">
                    <div v-for="day in ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت']" :key="day" class="text-center text-sm font-semibold text-gray-600 dark:text-gray-400">
                      {{ day.slice(0, 3) }}
                    </div>
                  </div>

                  <div class="grid grid-cols-7 gap-2">
                    <div
                      v-for="(date, index) in calendarDates"
                      :key="index"
                      @click="date && date.isAvailable ? selectDate(date.date) : null"
                      class="aspect-square flex items-center justify-center rounded-lg text-sm font-medium transition-all"
                      :class="{
                        'cursor-pointer hover:bg-indigo-50 dark:hover:bg-indigo-900': date && date.isAvailable,
                        'bg-gradient-to-r from-green-500 to-emerald-600 text-white': date && date.isAvailable && !date.isSelected,
                        'bg-gradient-to-r from-indigo-500 to-purple-600 text-white': date && date.isSelected,
                        'text-gray-400 cursor-not-allowed': date && !date.isAvailable,
                        'border-2 border-indigo-500': date && date.isToday,
                      }"
                    >
                      {{ date?.day }}
                    </div>
                  </div>

                  <div class="mt-4 flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                      <div class="w-4 h-4 bg-gradient-to-r from-green-500 to-emerald-600 rounded"></div>
                      <span class="text-gray-600 dark:text-gray-400">متاح / Available</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <div class="w-4 h-4 bg-gray-300 dark:bg-gray-600 rounded"></div>
                      <span class="text-gray-600 dark:text-gray-400">غير متاح / Not Available</span>
                    </div>
                  </div>
                </div>

                <!-- Time Slots -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <Clock class="w-5 h-5 text-indigo-600" />
                    الأوقات المتاحة / Available Times
                  </h3>

                  <div v-if="!selectedDate" class="text-center py-12 text-gray-500">
                    <Calendar class="w-12 h-12 mx-auto mb-4 opacity-50" />
                    <p>اختر تاريخاً من التقويم</p>
                    <p class="text-sm">Select a date from calendar</p>
                  </div>

                  <div v-else-if="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
                  </div>

                  <div v-else-if="availableSlots.length === 0" class="text-center py-12">
                    <Frown class="w-16 h-16 text-gray-400 mx-auto mb-4" />
                    <p class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
                      لا توجد مواعيد متاحة
                    </p>
                    <p class="text-gray-500">No appointments available for this date</p>
                  </div>

                  <div v-else class="space-y-2">
                    <button
                      v-for="slot in availableSlots"
                      :key="`${slot.start_time}-${slot.end_time}`"
                      @click="selectSlot(slot)"
                      :disabled="!slot.is_available"
                      class="w-full p-4 rounded-lg border-2 transition-all text-right"
                      :class="selectedSlot === slot
                        ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white border-indigo-500'
                        : slot.is_available
                          ? 'border-gray-300 hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900'
                          : 'border-gray-200 opacity-50 cursor-not-allowed'"
                    >
                      <div class="flex items-center justify-between">
                        <span class="font-semibold text-lg">{{ slot.start_time }} - {{ slot.end_time }}</span>
                        <CheckCircle2 v-if="selectedSlot === slot" class="w-5 h-5" />
                      </div>
                    </button>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Booking Confirmation -->
        <div v-if="selectedSlot" class="max-w-4xl mx-auto space-y-6">
          <Card>
            <CardHeader>
              <CardTitle class="text-xl">ملاحظات إضافية / Additional Notes</CardTitle>
            </CardHeader>
            <CardContent>
              <Textarea
                v-model="notes"
                placeholder="أضف أي ملاحظات أو تفاصيل مهمة... / Add any important notes or details..."
                rows="4"
                class="text-right"
              />
            </CardContent>
          </Card>

          <Card class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-950 dark:to-purple-950 border-2 border-indigo-500">
            <CardHeader>
              <CardTitle class="text-2xl">ملخص الموعد / Appointment Summary</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="text-right">
                  <p class="text-sm text-gray-600 dark:text-gray-400">الطبيب / Doctor</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedProvider?.title }} {{ selectedProvider?.user.name }}
                  </p>
                  <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ selectedProvider?.years_experience }} سنوات خبرة • {{ selectedProvider?.consultation_fee }} دج
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600 dark:text-gray-400">الموقع / Location</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedCity?.name_ar }}، {{ selectedProvince?.name_ar }}
                  </p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600 dark:text-gray-400">التاريخ / Date</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ formatDate(selectedDate) }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm text-gray-600 dark:text-gray-400">الوقت / Time</p>
                  <p class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ selectedSlot.start_time }} - {{ selectedSlot.end_time }}
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>

          <Button
            @click="bookAppointment"
            class="w-full py-6 text-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:shadow-xl transition-all"
          >
            <CheckCircle2 class="w-6 h-6 mr-2" />
            تأكيد الحجز / Confirm Booking
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
