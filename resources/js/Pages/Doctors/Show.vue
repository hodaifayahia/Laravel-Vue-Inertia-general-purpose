<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { 
  MapPin, 
  Star, 
  Award, 
  Calendar,
  Phone,
  Mail,
  Clock,
  DollarSign,
  CheckCircle,
  ArrowLeft,
  Share2,
  Heart
} from 'lucide-vue-next'

/**
 * INDIVIDUAL DOCTOR PROFILE PAGE
 * Detailed information about a specific specialist
 */

interface Props {
  id: string
}

const props = defineProps<Props>()

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
  email?: string
  phone?: string
}

const doctor = ref<Doctor | null>(null)
const loading = ref(true)
const isFavorite = ref(false)

const fetchDoctor = async () => {
  try {
    const response = await fetch('/api/doctors/public')
    const data = await response.json()
    const allDoctors = data.doctors || []
    doctor.value = allDoctors.find((d: Doctor) => d.id === parseInt(props.id)) || null
  } catch (error) {
    console.error('Failed to fetch doctor:', error)
  } finally {
    loading.value = false
  }
}

const toggleFavorite = () => {
  isFavorite.value = !isFavorite.value
}

const shareProfile = async () => {
  if (navigator.share) {
    try {
      await navigator.share({
        title: `${doctor.value?.title} ${doctor.value?.name}`,
        text: `Check out ${doctor.value?.name}, a ${doctor.value?.specialty} specialist`,
        url: window.location.href
      })
    } catch (err) {
      console.log('Share failed:', err)
    }
  } else {
    // Fallback: copy to clipboard
    navigator.clipboard.writeText(window.location.href)
    alert('Link copied to clipboard!')
  }
}

const bookAppointment = () => {
  router.visit(`/appointments?doctor=${props.id}`)
}

onMounted(() => {
  fetchDoctor()
})
</script>

<template>
  <Head :title="`${doctor?.title} ${doctor?.name} - Dysgraphia Support`" />

  <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white dark:from-gray-900 dark:to-gray-800">
    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-indigo-600 border-t-transparent mb-4"></div>
        <p class="text-gray-600 dark:text-gray-400">Loading profile...</p>
      </div>
    </div>

    <!-- Not Found -->
    <div v-else-if="!doctor" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Doctor Not Found</h2>
        <Link href="/doctors" class="text-indigo-600 hover:underline">
          Back to Doctor Listing
        </Link>
      </div>
    </div>

    <!-- Profile Content -->
    <div v-else>
      <!-- Hero Section -->
      <section class="relative bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
        <div class="container mx-auto px-4 py-8">
          <Link 
            href="/doctors"
            class="inline-flex items-center gap-2 text-white/80 hover:text-white mb-6 transition-colors"
          >
            <ArrowLeft class="w-5 h-5" />
            Back to Specialists
          </Link>

          <div class="flex flex-col md:flex-row gap-8 items-start">
            <!-- Photo -->
            <div class="w-48 h-48 rounded-2xl overflow-hidden shadow-2xl flex-shrink-0">
              <img 
                :src="doctor.photo || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(doctor.name) + '&size=400&background=fff&color=4f46e5'"
                :alt="doctor.name"
                class="w-full h-full object-cover"
              />
            </div>

            <!-- Info -->
            <div class="flex-1">
              <h1 class="text-4xl md:text-5xl font-bold mb-4">
                {{ doctor.title }} {{ doctor.name }}
              </h1>

              <div class="flex flex-wrap gap-6 mb-6">
                <div class="flex items-center gap-2">
                  <Award class="w-5 h-5" />
                  <span>{{ doctor.specialty }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <MapPin class="w-5 h-5" />
                  <span>{{ doctor.city_name }}, {{ doctor.province_name }}</span>
                </div>
                <div class="flex items-center gap-2">
                  <Star class="w-5 h-5 text-yellow-400" />
                  <span>{{ doctor.years_experience }} years experience</span>
                </div>
              </div>

              <div class="flex gap-3">
                <button 
                  @click="bookAppointment"
                  class="px-8 py-3 bg-white text-indigo-600 rounded-lg font-semibold hover:bg-gray-100 transition-colors flex items-center gap-2"
                >
                  <Calendar class="w-5 h-5" />
                  Book Appointment
                </button>
                <button 
                  @click="toggleFavorite"
                  :class="[
                    'px-4 py-3 rounded-lg transition-colors',
                    isFavorite 
                      ? 'bg-pink-500 text-white' 
                      : 'bg-white/20 text-white hover:bg-white/30'
                  ]"
                >
                  <Heart :class="['w-5 h-5', isFavorite && 'fill-current']" />
                </button>
                <button 
                  @click="shareProfile"
                  class="px-4 py-3 bg-white/20 text-white rounded-lg hover:bg-white/30 transition-colors"
                >
                  <Share2 class="w-5 h-5" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Main Content -->
      <div class="container mx-auto px-4 py-12">
        <div class="grid lg:grid-cols-3 gap-8">
          <!-- Left Column - Details -->
          <div class="lg:col-span-2 space-y-8">
            <!-- About -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">About</h2>
              <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
                {{ doctor.bio || `${doctor.title} ${doctor.name} is an experienced ${doctor.specialty} specialist with ${doctor.years_experience} years of experience helping individuals with dysgraphia. Located in ${doctor.city_name}, ${doctor.province_name}, they provide comprehensive assessment and therapy services.` }}
              </p>
            </div>

            <!-- Expertise -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Expertise</h2>
              <div class="grid md:grid-cols-2 gap-4">
                <div class="flex items-start gap-3">
                  <CheckCircle class="w-6 h-6 text-green-500 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Dysgraphia Assessment</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Comprehensive evaluation and diagnosis</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="w-6 h-6 text-green-500 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Therapy Sessions</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Individualized treatment plans</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="w-6 h-6 text-green-500 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Family Support</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Guidance for parents and caregivers</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <CheckCircle class="w-6 h-6 text-green-500 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Progress Monitoring</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Regular tracking and adjustments</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Education & Credentials -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Education & Credentials</h2>
              <div class="space-y-4">
                <div class="flex items-start gap-3">
                  <Award class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Licensed Specialist</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Certified in {{ doctor.specialty }}</p>
                  </div>
                </div>
                <div class="flex items-start gap-3">
                  <Award class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-1" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">Professional Experience</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ doctor.years_experience }}+ years of practice</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Booking Info -->
          <div class="lg:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 sticky top-8">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Contact Information</h2>

              <div class="space-y-6 mb-8">
                <div class="flex items-start gap-3">
                  <MapPin class="w-6 h-6 text-indigo-600 flex-shrink-0" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Location</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ doctor.city_name }}, {{ doctor.province_name }}</p>
                  </div>
                </div>

                <div v-if="doctor.phone" class="flex items-start gap-3">
                  <Phone class="w-6 h-6 text-indigo-600 flex-shrink-0" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Phone</h3>
                    <a :href="`tel:${doctor.phone}`" class="text-indigo-600 hover:underline">
                      {{ doctor.phone }}
                    </a>
                  </div>
                </div>

                <div v-if="doctor.email" class="flex items-start gap-3">
                  <Mail class="w-6 h-6 text-indigo-600 flex-shrink-0" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Email</h3>
                    <a :href="`mailto:${doctor.email}`" class="text-indigo-600 hover:underline break-all">
                      {{ doctor.email }}
                    </a>
                  </div>
                </div>

                <div v-if="doctor.consultation_fee" class="flex items-start gap-3">
                  <DollarSign class="w-6 h-6 text-indigo-600 flex-shrink-0" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Consultation Fee</h3>
                    <p class="text-gray-600 dark:text-gray-400">{{ doctor.consultation_fee }} DZD</p>
                  </div>
                </div>

                <div class="flex items-start gap-3">
                  <Clock class="w-6 h-6 text-indigo-600 flex-shrink-0" />
                  <div>
                    <h3 class="font-semibold text-gray-900 dark:text-white mb-1">Availability</h3>
                    <p class="text-gray-600 dark:text-gray-400">By appointment</p>
                  </div>
                </div>
              </div>

              <button 
                @click="bookAppointment"
                class="w-full py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg transition-all flex items-center justify-center gap-2"
              >
                <Calendar class="w-5 h-5" />
                Book Appointment Now
              </button>

              <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-4">
                Available appointments will be shown in the booking form
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
