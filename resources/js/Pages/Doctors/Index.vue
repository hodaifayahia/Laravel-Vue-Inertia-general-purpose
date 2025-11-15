<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { useScrollAnimations, useCardHoverEffect, useStaggerAnimation, useFloatingAnimation, usePulseAnimation } from '@/composables/useAnimations'
import { useThreeJsParticles } from '@/composables/useThreeJsParticles'
import { wTrans } from 'laravel-vue-i18n'
import BookingModal from '@/components/BookingModal.vue'
import SearchableSelect from '@/components/SearchableSelect.vue'
import NavigationHeader from '@/components/NavigationHeader.vue'
import {
  Search,
  MapPin,
  Star,
  Award,
  Filter,
  X,
  Calendar,
  DollarSign,
  Users,
  Map as MapIcon,
  List,
  Sparkles,
  Zap,
  Globe
} from 'lucide-vue-next'

/**
 * DOCTOR LISTING PAGE
 * Full searchable and filterable list of all specialists
 */

interface Doctor {
  id: number
  name: string
  title: string
  specialty: string
  bio: string | null
  photo: string | null
  city_name: string
  province_name: string
  years_experience: number
  consultation_fee: number | null
  latitude?: number
  longitude?: number
}

// State
const doctors = ref<Doctor[]>([])
const loading = ref(true)
const searchQuery = ref('')
const selectedProvince = ref('all')
const selectedCity = ref('all')
const selectedSpecialty = ref('Dysgraphia') // Always Dysgraphia
const minExperience = ref(0)
const showBookingModal = ref(false)
const selectedDoctor = ref<Doctor | null>(null)
const sortBy = ref('name')
const viewMode = ref<'grid' | 'list' | 'map'>('grid')

// GSAP Card Hover Effects
const { handleMouseMove, handleMouseLeave } = useCardHoverEffect()

// Filters
const provinces = ref<string[]>([])
const allCities = ref<any[]>([])
const specialties = ref<string[]>([])
const citiesByProvince = ref<{ [key: string]: string[] }>({})

// Computed
const filteredCities = computed(() => {
  if (selectedProvince.value === 'all') {
    return allCities.value.map((c: any) => c.name_en || c.name).sort()
  }
  return citiesByProvince.value[selectedProvince.value] || []
})

const filteredDoctors = computed(() => {
  let result = [...doctors.value]

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    result = result.filter(doc => 
      doc.name.toLowerCase().includes(query) ||
      doc.specialty.toLowerCase().includes(query) ||
      doc.city_name.toLowerCase().includes(query) ||
      doc.province_name.toLowerCase().includes(query)
    )
  }

  // Province filter
  if (selectedProvince.value !== 'all') {
    result = result.filter(doc => doc.province_name === selectedProvince.value)
  }

  // City filter
  if (selectedCity.value !== 'all') {
    result = result.filter(doc => doc.city_name === selectedCity.value)
  }

  // Specialty filter - only Dysgraphia
  if (selectedSpecialty.value !== 'all') {
    result = result.filter(doc => doc.specialty === selectedSpecialty.value)
  } else {
    // Always filter to only show Dysgraphia specialists
    result = result.filter(doc => doc.specialty === 'Dysgraphia')
  }

  // Experience filter
  if (minExperience.value > 0) {
    result = result.filter(doc => doc.years_experience >= minExperience.value)
  }

  // Sorting
  result.sort((a, b) => {
    switch (sortBy.value) {
      case 'experience':
        return b.years_experience - a.years_experience
      case 'name':
        return a.name.localeCompare(b.name)
      case 'city':
        return a.city_name.localeCompare(b.city_name)
      default:
        return 0
    }
  })

  return result
})

const activeFiltersCount = computed(() => {
  let count = 0
  if (selectedProvince.value !== 'all') count++
  if (selectedCity.value !== 'all') count++
  // specialty is always Dysgraphia, so don't count it
  if (minExperience.value > 0) count++
  return count
})

// Methods
const fetchLocations = async () => {
  try {
    const response = await fetch('/locations', {
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      }
    })
    const data = await response.json()
    
    // Extract provinces
    provinces.value = data.provinces?.map((p: any) => p.name_en || p.name).sort() || []
    
    // Store all cities and build province-city mapping
    allCities.value = data.cities || []
    const cityMap: { [key: string]: string[] } = {}
    
    data.cities?.forEach((city: any) => {
      const cityName = city.name_en || city.name
      const provinceName = city.province_id ? 
        (data.provinces?.find((p: any) => p.id === city.province_id)?.name_en || '') : ''
      
      if (provinceName) {
        if (!cityMap[provinceName]) {
          cityMap[provinceName] = []
        }
        if (!cityMap[provinceName].includes(cityName)) {
          cityMap[provinceName].push(cityName)
        }
      }
    })
    
    // Sort cities within each province
    Object.keys(cityMap).forEach(province => {
      cityMap[province].sort()
    })
    
    citiesByProvince.value = cityMap
    
    // Extract specialties
    specialties.value = data.specializations?.map((s: any) => s.name || s.name_en).sort() || []
    
    console.log('Locations fetched:', { provinces: provinces.value, citiesByProvince: citiesByProvince.value, specialties: specialties.value })
  } catch (error) {
    console.error('Failed to fetch locations:', error)
  }
}

const fetchDoctors = async () => {
  try {
    const response = await fetch('/api/doctors/public')
    const data = await response.json()
    doctors.value = data.doctors || []
  } catch (error) {
    console.error('Failed to fetch doctors:', error)
  }
}

const loadData = async () => {
  loading.value = true
  try {
    await Promise.all([fetchLocations(), fetchDoctors()])
  } finally {
    loading.value = false
  }
}

// Watch for province changes to reset city selection
watch(selectedProvince, () => {
  selectedCity.value = 'all'
})

const clearFilters = () => {
  selectedProvince.value = 'all'
  selectedCity.value = 'all'
  // Keep selectedSpecialty as 'Dysgraphia'
  minExperience.value = 0
  searchQuery.value = ''
}

const openBookingModal = (doctor: Doctor) => {
  selectedDoctor.value = doctor
  showBookingModal.value = true
}

const closeBookingModal = () => {
  showBookingModal.value = false
  selectedDoctor.value = null
}

const handleBooking = (bookingData: any) => {
  // User is authenticated, redirect to booking page with doctor and date/time info
  const query = new URLSearchParams({
    doctor: bookingData.doctor_id,
    date: bookingData.date,
    time: bookingData.start_time
  })
  window.location.href = `/book?${query.toString()}`
}

onMounted(() => {
  loadData()

  // Initialize Three.js animations
  useThreeJsParticles('doctors-particles-container')

  // Initialize GSAP animations
  useScrollAnimations('.doctor-card')
  useScrollAnimations('.filter-section')
  useScrollAnimations('.hero-section')
  useStaggerAnimation('.stagger-item')
  useFloatingAnimation('.floating-icon')
  usePulseAnimation('.pulse-badge')
})
</script>

<template>
  <Head :title="wTrans('specialists.page_title')" />

  <div class="min-h-screen bg-gradient-to-br from-rose-50 via-amber-50 to-emerald-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 relative">
    <!-- Navigation Header -->
    <NavigationHeader />

    <!-- Add padding to account for fixed nav -->
    <div class="pt-20"></div>
    <!-- Three.js Particles Background -->
    <div id="doctors-particles-container" class="fixed inset-0 opacity-20 dark:opacity-30 pointer-events-none z-0"></div>

    <!-- Floating decorative elements -->
    <div class="floating-icon fixed top-20 left-10 w-4 h-4 bg-indigo-400 rounded-full opacity-60"></div>
    <div class="floating-icon fixed top-40 right-20 w-6 h-6 bg-purple-400 rounded-full opacity-40"></div>
    <div class="floating-icon fixed bottom-32 left-16 w-3 h-3 bg-pink-400 rounded-full opacity-50"></div>
    <div class="floating-icon fixed bottom-20 right-10 w-5 h-5 bg-emerald-400 rounded-full opacity-45"></div>
    <!-- Hero Section -->
    <section class="hero-section relative bg-gradient-to-r from-rose-400 via-amber-400 to-emerald-400 text-white py-20 overflow-hidden z-10">
      <!-- Organic background shapes -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-10 left-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 right-10 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
      </div>
      <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
          <!-- Floating Icons -->
          <div class="absolute -top-10 left-10 floating-icon">
            <Sparkles class="w-12 h-12 text-yellow-300 opacity-60" />
          </div>
          <div class="absolute -top-5 right-20 floating-icon">
            <Zap class="w-10 h-10 text-purple-300 opacity-60" />
          </div>
          <div class="absolute top-20 left-1/4 floating-icon">
            <Globe class="w-8 h-8 text-blue-300 opacity-40" />
          </div>
          
          <h1 class="text-5xl md:text-7xl font-light text-white mb-6 animate-on-scroll tracking-tight">
            {{ wTrans('specialists.page_heading') }}
          </h1>
          <p class="text-lg md:text-xl text-white/90 mb-8 animate-on-scroll font-light max-w-2xl mx-auto">
            {{ wTrans('specialists.page_subtitle') }}
          </p>
          
          <!-- Quick Search -->
          <div class="relative max-w-2xl mx-auto stagger-item">
            <Search class="absolute left-4 top-1/2 transform -translate-y-1/2 w-6 h-6 text-gray-400" />
            <input 
              v-model="searchQuery"
              type="text" 
              :placeholder="wTrans('specialists.search_placeholder')"
              class="w-full pl-14 pr-4 py-4 rounded-full text-gray-900 text-lg focus:outline-none focus:ring-4 focus:ring-indigo-300"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <div class="container mx-auto px-4 py-12">
      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Filters Sidebar -->
        <aside class="filter-section lg:w-80 flex-shrink-0">
          <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl shadow-sm p-6 sticky top-24 border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-light text-gray-900 dark:text-white flex items-center gap-2">
                <Filter class="w-6 h-6" />
                {{ wTrans('specialists.filters_title') }}
                <span v-if="activeFiltersCount > 0" class="pulse-badge ml-2 px-2 py-1 bg-indigo-600 text-white text-sm rounded-full">
                  {{ activeFiltersCount }}
                </span>
              </h2>
              <button 
                v-if="activeFiltersCount > 0"
                @click="clearFilters"
                class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold flex items-center gap-1"
              >
                <X class="w-4 h-4" />
                {{ wTrans('specialists.clear_filters') }}
              </button>
            </div>

            <!-- Province Filter -->
            <div class="stagger-item mb-6">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ wTrans('specialists.province_label') }}
              </label>
              <SearchableSelect
                v-model="selectedProvince"
                :options="['all', ...provinces]"
                :placeholder="wTrans('specialists.province_all')"
              />
            </div>

            <!-- City Filter -->
            <div class="stagger-item mb-6">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ wTrans('specialists.city_label') }}
              </label>
              <SearchableSelect
                v-model="selectedCity"
                :options="['all', ...filteredCities]"
                :disabled="selectedProvince === 'all' || filteredCities.length === 0"
                :placeholder="wTrans('specialists.city_all')"
              />
            </div>

            <!-- Experience Filter -->
            <div class="stagger-item mb-6">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ wTrans('specialists.experience_label_short') }}: {{ minExperience }} years
              </label>
              <input 
                v-model.number="minExperience"
                type="range" 
                min="0" 
                max="30" 
                step="5"
                class="w-full"
              />
            </div>

            <!-- Sort By -->
            <div class="stagger-item">
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                {{ wTrans('specialists.sort_by_label') }}
              </label>
              <select 
                v-model="sortBy"
                class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-indigo-500 transition-colors"
              >
                <option value="name">{{ wTrans('specialists.sort_name') }}</option>
                <option value="experience">{{ wTrans('specialists.sort_experience') }}</option>
                <option value="city">{{ wTrans('specialists.sort_city') }}</option>
              </select>
            </div>
          </div>
        </aside>

        <!-- Results Section -->
        <main class="flex-1">
          <!-- View Mode Toggle & Results Count -->
          <div class="flex items-center justify-between mb-6">
            <div class="text-gray-700 dark:text-gray-300">
              <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ filteredDoctors.length }}</span>
              <span class="ml-2">{{ wTrans('specialists.specialists_found') }}</span>
            </div>

            <div class="flex items-center gap-2 bg-white dark:bg-gray-800 rounded-lg p-1 shadow-md">
              <button 
                @click="viewMode = 'grid'"
                :class="[
                  'px-4 py-2 rounded-md transition-colors',
                  viewMode === 'grid' 
                    ? 'bg-indigo-600 text-white' 
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <Users class="w-5 h-5" />
              </button>
              <button 
                @click="viewMode = 'list'"
                :class="[
                  'px-4 py-2 rounded-md transition-colors',
                  viewMode === 'list' 
                    ? 'bg-indigo-600 text-white' 
                    : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <List class="w-5 h-5" />
              </button>
              <Link 
                href="/map"
                class="px-4 py-2 rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
              >
                <MapIcon class="w-5 h-5" />
              </Link>
            </div>
          </div>

          <!-- Loading State -->
          <div v-if="loading" class="text-center py-20">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-indigo-600 border-t-transparent"></div>
            <p class="mt-4 text-gray-600 dark:text-gray-400">{{ wTrans('specialists.loading') }}</p>
          </div>

          <!-- Empty State -->
          <div v-else-if="filteredDoctors.length === 0" class="text-center py-20">
            <Search class="w-20 h-20 mx-auto text-gray-400 mb-4" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ wTrans('specialists.no_results') }}</h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">{{ wTrans('specialists.no_results_try') }}</p>
            <button 
              @click="clearFilters"
              class="px-6 py-3 bg-indigo-600 text-white rounded-lg font-semibold hover:bg-indigo-700 transition-colors"
            >
              {{ wTrans('specialists.clear_filters') }}
            </button>
          </div>

          <!-- Grid View -->
          <div v-else-if="viewMode === 'grid'" class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
            <div 
              v-for="doctor in filteredDoctors" 
              :key="doctor.id"
              class="doctor-card animate-on-scroll bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden group border border-gray-100 dark:border-gray-700"
              @mousemove="handleMouseMove($event, $el)"
              @mouseleave="handleMouseLeave($el)"
            >
              <div class="aspect-square overflow-hidden">
                <img 
                  :src="doctor.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor.name) + '&size=400&background=4f46e5&color=fff'"
                  :alt="doctor.name"
                  class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                />
              </div>
              <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">
                  {{ doctor.title }} {{ doctor.name }}
                </h3>
                <div class="flex items-center gap-2 text-indigo-600 mb-3">
                  <Award class="w-4 h-4 pulse-element" />
                  <span class="text-sm">{{ doctor.specialty }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mb-3">
                  <MapPin class="w-4 h-4" />
                  <span class="text-sm">{{ doctor.city_name }}, {{ doctor.province_name }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mb-4">
                  <Star class="w-4 h-4 text-yellow-500 pulse-element" />
                  <span class="text-sm">{{ doctor.years_experience }} years experience</span>
                </div>
                <div v-if="doctor.consultation_fee" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 mb-4">
                  <DollarSign class="w-4 h-4" />
                  <span class="text-sm">{{ doctor.consultation_fee }} DZD</span>
                </div>
                <div class="flex gap-2">
                  <Link 
                    :href="`/doctors/${doctor.id}`"
                    class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center rounded-lg font-semibold hover:shadow-lg transition-all"
                  >
                    {{ wTrans('specialists.view_profile') }}
                  </Link>
                  <button 
                    @click="openBookingModal(doctor)"
                    class="px-4 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                  >
                    <Calendar class="w-5 h-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- List View -->
          <div v-else-if="viewMode === 'list'" class="space-y-4">
            <div 
              v-for="doctor in filteredDoctors" 
              :key="doctor.id"
              class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg hover:shadow-2xl transition-all p-6 flex flex-col md:flex-row gap-6"
            >
              <div class="w-32 h-32 flex-shrink-0 rounded-xl overflow-hidden">
                <img 
                  :src="doctor.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor.name) + '&size=200&background=4f46e5&color=fff'"
                  :alt="doctor.name"
                  class="w-full h-full object-cover"
                />
              </div>
              <div class="flex-1">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                  {{ doctor.title }} {{ doctor.name }}
                </h3>
                <div class="flex flex-wrap gap-4 mb-3">
                  <div class="flex items-center gap-2 text-indigo-600">
                    <Award class="w-4 h-4" />
                    <span class="text-sm">{{ doctor.specialty }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <MapPin class="w-4 h-4" />
                    <span class="text-sm">{{ doctor.city_name }}, {{ doctor.province_name }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <Star class="w-4 h-4 text-yellow-500" />
                    <span class="text-sm">{{ doctor.years_experience }} years</span>
                  </div>
                  <div v-if="doctor.consultation_fee" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                    <DollarSign class="w-4 h-4" />
                    <span class="text-sm">{{ doctor.consultation_fee }} DZD</span>
                  </div>
                </div>
                <p v-if="doctor.bio" class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                  {{ doctor.bio }}
                </p>
              </div>
              <div class="flex md:flex-col gap-2 md:w-40">
                <Link 
                  :href="`/doctors/${doctor.id}`"
                  class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                  {{ wTrans('specialists.view_profile') }}
                </Link>
                <button 
                  @click="openBookingModal(doctor)"
                  class="flex-1 px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white text-center rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center justify-center gap-2"
                >
                  <Calendar class="w-5 h-5" />
                  {{ wTrans('specialists.book_appointment') }}
                </button>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- Booking Modal -->
    <BookingModal 
      v-if="selectedDoctor"
      :doctor="selectedDoctor"
      :is-open="showBookingModal"
      @close="closeBookingModal"
      @book="handleBooking"
    />
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* BuildKindly-inspired smooth transitions */
* {
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>
