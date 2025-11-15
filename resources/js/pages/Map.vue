<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
import { useThreeJsGlobe } from '@/composables/useThreeJsGlobe'
import { useScrollAnimations, useCardHoverEffect } from '@/composables/useAnimations'
import { MapPin, X, Phone, Mail, Calendar, Award, Star, Globe, Sparkles, Zap } from 'lucide-vue-next'
import NavigationHeader from '@/components/NavigationHeader.vue'

/**
 * INTERACTIVE MAP PAGE WITH 3D GLOBE
 * Shows all doctor locations on a map with clickable markers and 3D globe animation
 */

// Initialize Three.js globe animation
useThreeJsGlobe('three-globe-container')

// Initialize scroll animations
useScrollAnimations('.animate-on-scroll')

/**
 * INTERACTIVE MAP PAGE
 * Shows all doctor locations on a map with clickable markers
 */

interface Doctor {
  id: number
  name: string
  title: string
  specialty: string
  photo: string | null
  city_name: string
  province_name: string
  years_experience: number
  consultation_fee: number | null
  latitude?: number
  longitude?: number
}

const doctors = ref<Doctor[]>([])
const loading = ref(true)
const selectedDoctor = ref<Doctor | null>(null)
const mapContainer = ref<HTMLElement | null>(null)
let map: L.Map | null = null
let markers: L.Marker[] = []

// Default center of Algeria (Algiers)
const ALGERIA_CENTER: [number, number] = [36.7538, 3.0588]
const DEFAULT_ZOOM = 6

const fetchDoctors = async () => {
  try {
    const response = await fetch('/api/doctors/public')
    const data = await response.json()
    doctors.value = data.doctors || []
  } catch (error) {
    console.error('Failed to fetch doctors:', error)
  } finally {
    loading.value = false
  }
}

const initializeMap = () => {
  if (!mapContainer.value) return

  // Create map
  map = L.map(mapContainer.value).setView(ALGERIA_CENTER, DEFAULT_ZOOM)

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19
  }).addTo(map)

  // Add markers for doctors with coordinates
  addMarkers()
}

const addMarkers = () => {
  if (!map) return

  // Clear existing markers
  markers.forEach(marker => marker.remove())
  markers = []

  // Custom icon
  const customIcon = L.divIcon({
    className: 'custom-marker',
    html: `<div class="bg-indigo-600 text-white rounded-full w-10 h-10 flex items-center justify-center shadow-lg border-2 border-white">
      <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/>
        <circle cx="12" cy="10" r="3"/>
      </svg>
    </div>`,
    iconSize: [40, 40],
    iconAnchor: [20, 40]
  })

  doctors.value.forEach(doctor => {
    // Use provided coordinates or generate random ones for demo
    const lat = doctor.latitude || (ALGERIA_CENTER[0] + (Math.random() - 0.5) * 10)
    const lng = doctor.longitude || (ALGERIA_CENTER[1] + (Math.random() - 0.5) * 10)

    const marker = L.marker([lat, lng], { icon: customIcon })
      .addTo(map!)
      .on('click', () => {
        selectedDoctor.value = doctor
        map!.setView([lat, lng], 12, { animate: true })
      })

    markers.push(marker)
  })
}

const closePopup = () => {
  selectedDoctor.value = null
}

onMounted(async () => {
  await fetchDoctors()
  initializeMap()
})

onUnmounted(() => {
  if (map) {
    map.remove()
    map = null
  }
})
</script>

<template>
  <Head title="Doctor Locations Map - Dysgraphia Support" />

  <div class="relative min-h-screen bg-gradient-to-b from-indigo-50 via-white to-purple-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
    <!-- Navigation -->
    <NavigationHeader />

    <!-- Hero Section with 3D Globe -->
    <section class="relative py-20 overflow-hidden">
      <!-- Three.js Globe Animation -->
      <div id="three-globe-container" class="absolute inset-0 opacity-30"></div>

      <!-- Animated Background -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-10 left-10 w-32 h-32 bg-indigo-400 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute bottom-10 right-10 w-40 h-40 bg-purple-400 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-36 h-36 bg-pink-400 rounded-full blur-3xl animate-pulse" style="animation-delay: 0.5s;"></div>
      </div>

      <div class="container mx-auto px-4 relative z-10">
        <div class="max-w-4xl mx-auto text-center">
          <!-- Badge -->
          <div class="inline-flex items-center gap-2 px-6 py-3 bg-white/90 backdrop-blur-sm rounded-full mb-8 animate-fade-in border border-indigo-200">
            <Globe class="w-5 h-5 text-indigo-600" />
            <span class="text-sm font-semibold text-gray-900">Interactive Location Map</span>
          </div>

          <h1 class="text-6xl md:text-7xl font-black text-gray-900 dark:text-white mb-6 animate-fade-in">
            Find Specialists
            <span class="block mt-2 text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600">
              Across Algeria
            </span>
          </h1>

          <p class="text-xl md:text-2xl text-gray-700 dark:text-gray-300 mb-12 animate-fade-in-delay max-w-3xl mx-auto leading-relaxed">
            Discover dysgraphia specialists in your area. Our interactive map shows all verified professionals
            across all 58 provinces of Algeria.
          </p>

          <!-- Stats Cards -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-12 animate-fade-in-delay-2">
            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover-lift animate-on-scroll">
              <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full flex items-center justify-center">
                  <Users class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">{{ doctors.length }}</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Specialists</div>
            </div>

            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover-lift animate-on-scroll">
              <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center">
                  <MapPin class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">58</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Provinces</div>
            </div>

            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover-lift animate-on-scroll">
              <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-red-500 rounded-full flex items-center justify-center">
                  <Award class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">100%</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Verified</div>
            </div>

            <div class="bg-white/90 dark:bg-gray-800/90 backdrop-blur-sm rounded-2xl p-6 shadow-lg hover-lift animate-on-scroll">
              <div class="flex items-center justify-center mb-4">
                <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-orange-500 rounded-full flex items-center justify-center">
                  <Sparkles class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="text-3xl font-bold text-gray-900 dark:text-white mb-1">24/7</div>
              <div class="text-sm text-gray-600 dark:text-gray-400">Available</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Map Section -->
    <section class="relative pb-20">
      <div class="container mx-auto px-4">
        <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl overflow-hidden">
          <!-- Map Header -->
          <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                  <MapPin class="w-6 h-6 text-white" />
                </div>
                <div>
                  <h2 class="text-2xl font-bold text-white">Interactive Map</h2>
                  <p class="text-indigo-100">Click on markers to view specialist details</p>
                </div>
              </div>
              <Link
                href="/doctors"
                class="px-6 py-3 bg-white text-indigo-600 rounded-full font-semibold hover:shadow-lg transition-all flex items-center gap-2"
              >
                <Users class="w-4 h-4" />
                List View
              </Link>
            </div>
          </div>

          <!-- Map Container -->
          <div class="relative">
            <!-- Loading Overlay -->
            <div v-if="loading" class="absolute inset-0 bg-gray-100 dark:bg-gray-900 z-10 flex items-center justify-center">
              <div class="text-center">
                <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-indigo-600 border-t-transparent mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Loading specialists...</p>
              </div>
            </div>

            <!-- Map -->
            <div ref="mapContainer" class="w-full h-96 md:h-[600px]"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Doctor Info Popup -->
    <transition name="slide-up">
      <div
        v-if="selectedDoctor"
        class="fixed bottom-0 left-0 right-0 md:left-auto md:right-4 md:bottom-4 md:w-96 bg-white dark:bg-gray-800 rounded-t-3xl md:rounded-2xl shadow-2xl z-30 overflow-hidden animate-on-scroll"
      >
        <!-- Close Button -->
        <button
          @click="closePopup"
          class="absolute top-4 right-4 p-2 bg-gray-100 dark:bg-gray-700 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors z-10"
        >
          <X class="w-5 h-5" />
        </button>

        <!-- Doctor Photo -->
        <div class="h-48 overflow-hidden">
          <img
            :src="selectedDoctor.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(selectedDoctor.name) + '&size=400&background=4f46e5&color=fff'"
            :alt="selectedDoctor.name"
            class="w-full h-full object-cover"
          />
        </div>

        <!-- Doctor Info -->
        <div class="p-6">
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            {{ selectedDoctor.title }} {{ selectedDoctor.name }}
          </h3>

          <div class="space-y-3 mb-6">
            <div class="flex items-center gap-2 text-indigo-600">
              <Award class="w-5 h-5" />
              <span>{{ selectedDoctor.specialty }}</span>
            </div>

            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
              <MapPin class="w-5 h-5" />
              <span>{{ selectedDoctor.city_name }}, {{ selectedDoctor.province_name }}</span>
            </div>

            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
              <Star class="w-5 h-5 text-yellow-500" />
              <span>{{ selectedDoctor.years_experience }} years experience</span>
            </div>

            <div v-if="selectedDoctor.consultation_fee" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
              <Calendar class="w-5 h-5" />
              <span>From {{ selectedDoctor.consultation_fee }} DZD</span>
            </div>
          </div>

          <div class="flex gap-2">
            <Link
              :href="`/doctors/${selectedDoctor.id}`"
              class="flex-1 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white text-center rounded-lg font-semibold hover:shadow-lg transition-all"
            >
              View Full Profile
            </Link>
            <Link
              :href="`/appointments?doctor=${selectedDoctor.id}`"
              class="px-6 py-3 bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors flex items-center gap-2"
            >
              <Calendar class="w-5 h-5" />
            </Link>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<style>
/* Leaflet map styles */
.leaflet-container {
  font-family: inherit;
}

.custom-marker {
  background: transparent;
  border: none;
}

/* Popup animation */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s ease-out;
}

.slide-up-enter-from {
  transform: translateY(100%);
}

.slide-up-leave-to {
  transform: translateY(100%);
}
</style>
