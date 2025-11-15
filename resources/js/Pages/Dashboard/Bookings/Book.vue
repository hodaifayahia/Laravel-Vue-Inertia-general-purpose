<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { Calendar, Clock, User, Stethoscope, CheckCircle2, Award, Users } from 'lucide-vue-next'
import { wTrans } from 'laravel-vue-i18n'
import AppLayout from '@/layouts/AppLayout.vue'
import * as specializationsRoutes from '@/routes/specializations'
import * as providersRoutes from '@/routes/providers'
import * as appointmentsRoutes from '@/routes/appointments'
import * as providerScheduleRoutes from '@/routes/provider/schedule'

interface Specialization {
  id: number
  name: string
  description: string | null
  slug: string
  icon: string | null
  is_active: boolean
  active_providers_count: number
}

interface Provider {
  id: number
  user_id: number
  specialization_id: number
  bio: string | null
  years_experience: number
  slot_duration: number
  is_available: boolean
  user: {
    id: number
    name: string
    email: string
    avatar: string | null
  }
  specialization: Specialization
}

interface TimeSlot {
  start_time: string
  end_time: string
  is_available: boolean
}

interface Child {
  id: number
  name: string
  date_of_birth: string
  gender: string
  medical_notes: string | null
}

// State
const step = ref(1)
const selectedChild = ref<Child | null>(null)
const selectedSpecialization = ref<Specialization | null>(null)
const selectedProvider = ref<Provider | null>(null)
const selectedDate = ref('')
const selectedSlot = ref<TimeSlot | null>(null)
const notes = ref('')

const children = ref<Child[]>([])
const specializations = ref<Specialization[]>([])
const providers = ref<Provider[]>([])
const availableSlots = ref<TimeSlot[]>([])

const loading = ref(false)

// Computed
const minDate = computed(() => {
  const today = new Date()
  return today.toISOString().split('T')[0]
})

const maxDate = computed(() => {
  const maxDate = new Date()
  maxDate.setMonth(maxDate.getMonth() + 3)
  return maxDate.toISOString().split('T')[0]
})

const canProceed = computed(() => {
  if (step.value === 1) return selectedChild.value !== null
  if (step.value === 2) return selectedSpecialization.value !== null
  if (step.value === 3) return selectedProvider.value !== null
  if (step.value === 4) return selectedDate.value !== ''
  if (step.value === 5) return selectedSlot.value !== null
  return false
})

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

// Functions
const loadChildren = async () => {
  loading.value = true
  try {
    const response = await fetch('/api/children')
    if (!response.ok) throw new Error('Failed to load children')
    const data = await response.json()
    children.value = data
  } catch (error) {
    console.error('Failed to load children:', error)
    children.value = []
  } finally {
    loading.value = false
  }
}

const selectChild = (child: Child) => {
  selectedChild.value = child
  step.value = 2
  loadSpecializations()
}

const loadSpecializations = async () => {
  loading.value = true
  try {
    const response = await fetch(specializationsRoutes.active.url())
    const data = await response.json()
    specializations.value = data
  } catch (error) {
    console.error('Failed to load specializations:', error)
  } finally {
    loading.value = false
  }
}

const selectSpecialization = (specialization: Specialization) => {
  selectedSpecialization.value = specialization
  step.value = 3
  loadProviders()
}

const loadProviders = async () => {
  if (!selectedSpecialization.value) return
  
  loading.value = true
  try {
    const response = await fetch(providersRoutes.bySpecialization.url(selectedSpecialization.value.id))
    const data = await response.json()
    providers.value = data
  } catch (error) {
    console.error('Failed to load providers:', error)
  } finally {
    loading.value = false
  }
}

const selectProvider = (provider: Provider) => {
  selectedProvider.value = provider
  step.value = 4
}

const onDateChange = () => {
  if (selectedDate.value && selectedProvider.value) {
    loadAvailableSlots()
  }
}

const loadAvailableSlots = async () => {
  if (!selectedProvider.value || !selectedDate.value) return
  
  loading.value = true
  try {
    const response = await fetch(
      `/providers/${selectedProvider.value.id}/slots?date=${selectedDate.value}`
    )
    const data = await response.json()
    availableSlots.value = data.slots
    step.value = 5
  } catch (error) {
    console.error('Failed to load slots:', error)
  } finally {
    loading.value = false
  }
}

const selectSlot = (slot: TimeSlot) => {
  selectedSlot.value = slot
}

const bookAppointment = () => {
  if (!selectedChild.value || !selectedProvider.value || !selectedDate.value || !selectedSlot.value) return

  router.post(appointmentsRoutes.store.url(), {
    child_id: selectedChild.value.id,
    provider_profile_id: selectedProvider.value.id,
    appointment_date: selectedDate.value,
    start_time: selectedSlot.value.start_time,
    end_time: selectedSlot.value.end_time,
    notes: notes.value,
  })
}

const goBack = () => {
  if (step.value > 1) {
    step.value--
  }
}

const reset = () => {
  step.value = 1
  selectedChild.value = null
  selectedSpecialization.value = null
  selectedProvider.value = null
  selectedDate.value = ''
  selectedSlot.value = null
  notes.value = ''
  specializations.value = []
  providers.value = []
  availableSlots.value = []
}

// Load children on mount
onMounted(() => {
  loadChildren()
})
</script>

<template>
  <AppLayout>
    <Head :title="wTrans('bookings.book_appointment')" />

    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ wTrans('bookings.book_appointment') }}</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">{{ wTrans('bookings.book_appointment') }}</p>
      </div>

      <!-- Steps Indicator -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div
            v-for="s in 5"
            :key="s"
            class="flex-1 flex items-center"
            :class="{ 'justify-end': s === 5 }"
          >
            <div
              class="flex items-center justify-center w-10 h-10 rounded-full font-semibold transition-all"
              :class="
                s <= step
                  ? 'bg-gradient-to-r from-indigo-500 to-purple-600 text-white'
                  : 'bg-gray-200 dark:bg-gray-700 text-gray-500'
              "
            >
              {{ s }}
            </div>
            <div v-if="s < 5" class="flex-1 h-1 mx-2" :class="s < step ? 'bg-indigo-500' : 'bg-gray-200 dark:bg-gray-700'" />
          </div>
        </div>
        <div class="flex justify-between mt-2 text-sm">
          <span :class="step >= 1 ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">Child</span>
          <span :class="step >= 2 ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">Specialization</span>
          <span :class="step >= 3 ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">Provider</span>
          <span :class="step >= 4 ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">Date</span>
          <span :class="step >= 5 ? 'text-gray-900 dark:text-white font-medium' : 'text-gray-500'">Time</span>
        </div>
      </div>

      <!-- Step 1: Select Child -->
      <div v-if="step === 1" class="space-y-4">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Which child is this appointment for?</h2>
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
        <div v-else-if="children.length === 0" class="text-center py-12">
          <Users class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <p class="text-gray-600 dark:text-gray-400 mb-4">You don't have any children added yet.</p>
          <Link
            href="/profile/children"
            class="inline-block px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all"
          >
            Add a Child
          </Link>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="child in children"
            :key="child.id"
            @click="selectChild(child)"
            class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all border-2 border-transparent hover:border-indigo-500 text-left"
          >
            <div class="flex items-center space-x-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-lg font-bold">
                  {{ child.name.charAt(0).toUpperCase() }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ child.name }}</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                  {{ getChildAge(child.date_of_birth) }} years old · {{ child.gender }}
                </p>
                <p v-if="child.medical_notes" class="mt-1 text-xs text-gray-500 dark:text-gray-500 line-clamp-1">
                  {{ child.medical_notes }}
                </p>
              </div>
            </div>
          </button>
        </div>
      </div>

      <!-- Step 2: Select Specialization -->
      <div v-if="step === 2" class="space-y-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select a Specialization</h2>
          <button @click="goBack" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
            ← Back
          </button>
        </div>
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <button
            v-for="spec in specializations"
            :key="spec.id"
            @click="selectSpecialization(spec)"
            class="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-all border-2 border-transparent hover:border-indigo-500 text-left"
          >
            <div class="flex items-start space-x-4">
              <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center">
                  <Stethoscope class="w-6 h-6 text-white" />
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ spec.name }}</h3>
                <p v-if="spec.description" class="mt-1 text-sm text-gray-600 dark:text-gray-400 line-clamp-2">
                  {{ spec.description }}
                </p>
                <p class="mt-2 text-sm text-indigo-600 dark:text-indigo-400">
                  {{ spec.active_providers_count }} providers available
                </p>
              </div>
            </div>
          </button>
        </div>
      </div>

      <!-- Step 3: Select Provider -->
      <div v-if="step === 3" class="space-y-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select a Provider</h2>
          <button @click="goBack" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
            ← Back
          </button>
        </div>
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
        <div v-else-if="providers.length === 0" class="text-center py-12">
          <p class="text-gray-600 dark:text-gray-400">No providers available for this specialization.</p>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="provider in providers"
            :key="provider.id"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-md hover:shadow-xl transition-all border border-gray-200 dark:border-gray-700 overflow-hidden"
          >
            <!-- Provider Header -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
              <div class="flex items-center space-x-4">
                <div class="w-20 h-20 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center text-white text-3xl font-bold border-2 border-white/30">
                  {{ provider.user.name.charAt(0).toUpperCase() }}
                </div>
                <div class="flex-1">
                  <h3 class="text-xl font-bold">{{ provider.user.name }}</h3>
                  <p class="text-sm opacity-90">{{ provider.specialization.name }}</p>
                </div>
              </div>
            </div>

            <!-- Provider Details -->
            <div class="p-6 space-y-4">
              <!-- Experience Badge -->
              <div class="flex items-center space-x-2">
                <Award class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                <span class="text-sm font-medium text-gray-900 dark:text-white">
                  {{ provider.years_experience }} years of experience
                </span>
              </div>

              <!-- Session Duration -->
              <div class="flex items-center space-x-2">
                <Clock class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ provider.slot_duration }} min sessions
                </span>
              </div>

              <!-- Bio -->
              <p v-if="provider.bio" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-3">
                {{ provider.bio }}
              </p>
              <p v-else class="text-sm text-gray-500 dark:text-gray-500 italic">
                No bio available
              </p>

              <!-- Action Buttons -->
              <div class="pt-4 space-y-2">
                <button
                  @click="selectProvider(provider)"
                  class="w-full px-4 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all font-semibold"
                >
                  Select Provider
                </button>
                <Link
                  :href="`/providers/${provider.id}/details`"
                  class="w-full px-4 py-2 border-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 transition-all font-medium text-center block"
                >
                  View Full Profile
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Step 4: Select Date -->
      <div v-if="step === 4" class="space-y-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select a Date</h2>
          <button @click="goBack" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
            ← Back
          </button>
        </div>
        <div class="max-w-md mx-auto">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
            <div class="flex items-center space-x-3 mb-4">
              <Calendar class="w-6 h-6 text-indigo-600" />
              <label class="text-lg font-medium text-gray-900 dark:text-white">Choose appointment date</label>
            </div>
            <input
              v-model="selectedDate"
              type="date"
              :min="minDate"
              :max="maxDate"
              @change="onDateChange"
              class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
            />
          </div>
        </div>
      </div>

      <!-- Step 5: Select Time Slot -->
      <div v-if="step === 5" class="space-y-4">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select a Time Slot</h2>
          <button @click="step = 4" class="text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
            ← Back
          </button>
        </div>
        <div v-if="loading" class="text-center py-12">
          <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        </div>
        <div v-else-if="availableSlots.length === 0" class="text-center py-12">
          <p class="text-gray-600 dark:text-gray-400">No available slots for this date.</p>
        </div>
        <div v-else>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
            <button
              v-for="(slot, index) in availableSlots"
              :key="index"
              @click="selectSlot(slot)"
              :disabled="!slot.is_available"
              class="p-4 rounded-lg border-2 transition-all"
              :class="
                !slot.is_available
                  ? 'bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-400 cursor-not-allowed'
                  : selectedSlot === slot
                  ? 'bg-gradient-to-r from-indigo-500 to-purple-600 border-indigo-600 text-white'
                  : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 hover:border-indigo-500 text-gray-900 dark:text-white'
              "
            >
              <div class="flex items-center justify-center space-x-2">
                <Clock class="w-4 h-4" />
                <span class="font-medium">{{ slot.start_time }}</span>
              </div>
            </button>
          </div>

          <!-- Notes and Confirm -->
          <div v-if="selectedSlot" class="max-w-2xl mx-auto space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-6">
              <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                Additional Notes (Optional)
              </label>
              <textarea
                v-model="notes"
                rows="4"
                placeholder="Any specific requirements or notes for the provider..."
                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
              />
            </div>

            <!-- Summary -->
            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
              <h3 class="text-lg font-semibold mb-4 flex items-center">
                <CheckCircle2 class="w-5 h-5 mr-2" />
                Appointment Summary
              </h3>
              <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="opacity-90">Child:</span>
                  <span class="font-medium">{{ selectedChild?.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-90">Specialization:</span>
                  <span class="font-medium">{{ selectedSpecialization?.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-90">Provider:</span>
                  <span class="font-medium">{{ selectedProvider?.user.name }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-90">Date:</span>
                  <span class="font-medium">{{ selectedDate }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="opacity-90">Time:</span>
                  <span class="font-medium">{{ selectedSlot.start_time }} - {{ selectedSlot.end_time }}</span>
                </div>
              </div>
            </div>

            <div class="flex space-x-4">
              <button
                @click="reset"
                class="flex-1 px-6 py-3 border-2 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-all"
              >
                Cancel
              </button>
              <button
                @click="bookAppointment"
                class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-lg hover:shadow-lg transition-all font-semibold"
              >
                Confirm Booking
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
