<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { X, Calendar, Clock, User, Mail, Phone } from 'lucide-vue-next'

interface Props {
  doctor: any
  isOpen: boolean
}

interface Emits {
  (e: 'close'): void
  (e: 'book', data: any): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const page = usePage()
const isAuthenticated = computed(() => !!page.props.auth?.user)

const selectedDate = ref('')
const selectedSlot = ref('')
const availableDates = ref<any[]>([])
const availableSlots = ref<any[]>([])
const loading = ref(false)
const slotsLoading = ref(false)
const bookingMessage = ref('')
const fullName = ref('')
const email = ref('')
const phone = ref('')

const fetchAvailableDates = async () => {
  if (!props.doctor?.id) return
  
  try {
    loading.value = true
    const response = await fetch(`/api/doctors/public/${props.doctor.id}/available-dates`)
    const data = await response.json()
    
    if (data.success) {
      availableDates.value = data.dates
      selectedDate.value = ''
      availableSlots.value = []
    }
  } catch (error) {
    console.error('Failed to fetch available dates:', error)
  } finally {
    loading.value = false
  }
}

const fetchAvailableSlots = async () => {
  if (!props.doctor?.id || !selectedDate.value) return
  
  try {
    slotsLoading.value = true
    const response = await fetch(`/api/doctors/public/${props.doctor.id}/slots/${selectedDate.value}`)
    const data = await response.json()
    
    if (data.success) {
      availableSlots.value = data.slots.filter((slot: any) => slot.is_available)
      selectedSlot.value = ''
    }
  } catch (error) {
    console.error('Failed to fetch available slots:', error)
  } finally {
    slotsLoading.value = false
  }
}

const handleDateChange = () => {
  fetchAvailableSlots()
}

const handleBooking = () => {
  if (!selectedDate.value || !selectedSlot.value) {
    bookingMessage.value = 'Please select a date and time slot'
    return
  }

  if (!isAuthenticated.value) {
    bookingMessage.value = 'Please log in or create an account to book an appointment'
    return
  }

  emit('book', {
    doctor_id: props.doctor.id,
    date: selectedDate.value,
    slot: selectedSlot.value,
    start_time: selectedSlot.value.split(' - ')[0]
  })
}

const handleClose = () => {
  selectedDate.value = ''
  selectedSlot.value = ''
  bookingMessage.value = ''
  emit('close')
}

// Fetch dates when modal opens
watch(() => props.isOpen, (newVal: boolean) => {
  if (newVal) {
    fetchAvailableDates()
  }
}, { immediate: true })
</script>

<template>
  <!-- Modal Overlay -->
  <div v-if="isOpen" class="fixed inset-0 bg-black/50 z-40 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
      <!-- Header -->
      <div class="sticky top-0 bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 flex items-center justify-between border-b border-gray-200 dark:border-gray-700">
        <div>
          <h2 class="text-2xl font-bold">Book an Appointment</h2>
          <p class="text-indigo-100">{{ doctor?.title }} {{ doctor?.name }}</p>
        </div>
        <button 
          @click="handleClose"
          class="p-2 hover:bg-white/20 rounded-lg transition-colors"
        >
          <X class="w-6 h-6" />
        </button>
      </div>

      <!-- Content -->
      <div class="p-6 space-y-6">
        <!-- Doctor Info -->
        <div class="flex gap-4 bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
          <img 
            :src="doctor?.photo || `https://ui-avatars.com/api/?name=${encodeURIComponent(doctor?.name)}&size=100&background=4f46e5&color=fff`"
            :alt="doctor?.name"
            class="w-20 h-20 rounded-lg object-cover"
          />
          <div class="flex-1">
            <h3 class="font-bold text-gray-900 dark:text-white">{{ doctor?.specialty }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ doctor?.years_experience }} years experience</p>
            <p v-if="doctor?.consultation_fee" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 mt-1">
              {{ doctor?.consultation_fee }} DZD per consultation
            </p>
          </div>
        </div>

        <!-- Authentication Check -->
        <div v-if="!isAuthenticated" class="bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-300 dark:border-amber-700 rounded-lg p-4">
          <p class="text-amber-900 dark:text-amber-200 mb-3">You need to log in to book an appointment</p>
          <div class="flex gap-2">
            <Link 
              href="/login"
              class="flex-1 px-4 py-2 bg-amber-600 text-white rounded-lg font-semibold hover:bg-amber-700 text-center transition-colors"
            >
              Login
            </Link>
            <Link 
              href="/register"
              class="flex-1 px-4 py-2 bg-white dark:bg-gray-800 text-amber-600 dark:text-amber-400 border-2 border-amber-300 dark:border-amber-700 rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 text-center transition-colors"
            >
              Register
            </Link>
          </div>
        </div>

        <!-- Booking Form -->
        <div v-if="isAuthenticated" class="space-y-4">
          <!-- Select Date -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <Calendar class="w-4 h-4 inline mr-2" />
              Select Date
            </label>
            <select 
              v-model="selectedDate"
              @change="handleDateChange"
              :disabled="loading || availableDates.length === 0"
              class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <option value="">{{ loading ? 'Loading dates...' : 'Choose a date' }}</option>
              <option v-for="date in availableDates" :key="date.date" :value="date.date">
                {{ date.display }} ({{ date.day }})
              </option>
            </select>
          </div>

          <!-- Select Time Slot -->
          <div>
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <Clock class="w-4 h-4 inline mr-2" />
              Select Time
            </label>
            <div v-if="!selectedDate" class="text-sm text-gray-500 dark:text-gray-400 py-3">
              Please select a date first
            </div>
            <div v-else-if="slotsLoading" class="text-sm text-gray-500 dark:text-gray-400 py-3">
              Loading time slots...
            </div>
            <div v-else-if="availableSlots.length === 0" class="text-sm text-red-600 dark:text-red-400 py-3">
              No available slots for this date
            </div>
            <select 
              v-else
              v-model="selectedSlot"
              class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:border-indigo-500 transition-colors"
            >
              <option value="">Choose a time slot</option>
              <option v-for="slot in availableSlots" :key="slot.start_time" :value="`${slot.start_time} - ${slot.end_time}`">
                {{ slot.start_time }} - {{ slot.end_time }}
              </option>
            </select>
          </div>

          <!-- Error Message -->
          <div v-if="bookingMessage" class="text-sm text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 p-3 rounded-lg">
            {{ bookingMessage }}
          </div>
        </div>

        <!-- Booking Summary -->
        <div v-if="selectedDate && selectedSlot && isAuthenticated" class="bg-indigo-50 dark:bg-indigo-900/20 border-2 border-indigo-300 dark:border-indigo-700 rounded-lg p-4">
          <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Booking Summary</h3>
          <div class="space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Doctor:</span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ doctor?.title }} {{ doctor?.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Date:</span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ selectedDate }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600 dark:text-gray-400">Time:</span>
              <span class="font-semibold text-gray-900 dark:text-white">{{ selectedSlot }}</span>
            </div>
            <div v-if="doctor?.consultation_fee" class="flex justify-between pt-2 border-t border-indigo-200 dark:border-indigo-700">
              <span class="text-gray-600 dark:text-gray-400">Fee:</span>
              <span class="font-bold text-indigo-600 dark:text-indigo-400">{{ doctor?.consultation_fee }} DZD</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="sticky bottom-0 bg-gray-50 dark:bg-gray-800 p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
        <button 
          @click="handleClose"
          class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg font-semibold hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
        >
          Cancel
        </button>
        <button 
          v-if="isAuthenticated"
          @click="handleBooking"
          :disabled="!selectedDate || !selectedSlot"
          class="flex-1 px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-semibold hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
        >
          Book Appointment
        </button>
      </div>
    </div>
  </div>
</template>
