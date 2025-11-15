<script setup lang="ts">
import { ref, computed, nextTick } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import {
  Stethoscope,
  User,
  Award,
  Clock,
  Calendar,
  Check,
  X,
  Settings,
  MapPin,
  Phone,
  DollarSign,
  FileText,
  Globe,
  Trophy,
  GraduationCap,
  AlertCircle,
  Plus,
  Trash2
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import * as providerProfileRoutes from '@/routes/provider/profile'
import * as providerScheduleRoutes from '@/routes/provider/schedule'
import * as providerAvailabilityRoutes from '@/routes/provider/availability'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Separator } from '@/components/ui/separator'

interface Specialization {
  id: number
  name: string
  description: string | null
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

interface Schedule {
  id: number
  day_of_week: number
  start_time: string
  end_time: string
  is_available: boolean
  max_patients?: number | null
}

interface ScheduleEntry {
  day_of_week: number
  start_time: string
  end_time: string
  is_available: boolean
  max_patients?: number | null
  label: string
  shortLabel: string
}

interface AvailabilityRecord {
  id: number
  date: string
  end_date?: string
  start_time: string
  end_time: string
  is_available: boolean
  is_range?: boolean
  reason?: string
}

interface Profile {
  id: number
  user_id: number
  specialization_id: number
  province_id?: number
  city_id?: number
  bio: string | null
  years_experience: number
  slot_duration: number
  is_available: boolean
  title?: string
  license_number?: string
  qualifications?: string
  languages?: string
  phone?: string
  office_address?: string
  clinic_name?: string
  consultation_fee?: number
  advance_booking_days?: number
  services_offered?: string
  education?: string
  awards?: string
  website?: string
  social_links?: string
  specialization: Specialization
  province?: Province
  city?: City
}

interface Props {
  profile: Profile | null
  specializations: Specialization[]
  provinces: Province[]
  cities: City[]
  schedules: Schedule[]
  availability: AvailabilityRecord[]
  defaultSchedule: Record<string, any>
}

const props = defineProps<Props>()

// Active tab
const activeTab = ref('profile')

// Profile form
const profileForm = useForm({
  specialization_id: props.profile?.specialization_id || 1, // Default to Dysgraphia (ID: 1)
  province_id: props.profile?.province_id || '',
  city_id: props.profile?.city_id || '',
  bio: props.profile?.bio || '',
  years_experience: props.profile?.years_experience || 0,
  slot_duration: props.profile?.slot_duration || 30,
  is_available: props.profile?.is_available ?? true,
  title: props.profile?.title || 'Dr.',
  license_number: props.profile?.license_number || '',
  qualifications: props.profile?.qualifications || '',
  languages: props.profile?.languages || '',
  phone: props.profile?.phone || '',
  office_address: props.profile?.office_address || '',
  clinic_name: props.profile?.clinic_name || '',
  consultation_fee: props.profile?.consultation_fee || 0,
  advance_booking_days: props.profile?.advance_booking_days || 30,
  services_offered: props.profile?.services_offered || '',
  education: props.profile?.education || '',
  awards: props.profile?.awards || '',
  website: props.profile?.website || '',
  social_links: props.profile?.social_links || '',
})

// Schedule data
const days = [
  { id: 0, name: 'Sunday', short: 'Sun' },
  { id: 1, name: 'Monday', short: 'Mon' },
  { id: 2, name: 'Tuesday', short: 'Tue' },
  { id: 3, name: 'Wednesday', short: 'Wed' },
  { id: 4, name: 'Thursday', short: 'Thu' },
  { id: 5, name: 'Friday', short: 'Fri' },
  { id: 6, name: 'Saturday', short: 'Sat' },
]

const scheduleData = ref<ScheduleEntry[]>(
  days.map((day) => {
    const existing = props.schedules?.find((s) => s.day_of_week === day.id)
    return {
      day_of_week: day.id,
      start_time: existing?.start_time || '09:00',
      end_time: existing?.end_time || '17:00',
      is_available: existing?.is_available ?? false,
      max_patients: existing?.max_patients || null,
      label: day.name,
      shortLabel: day.short,
    }
  })
)

// Working dates form
const workingDatesForm = useForm({
  from_date: '',
  to_date: '',
})

// Excluded dates form
const excludedDateForm = useForm({
  from_date: '',
  to_date: '',
  reason: '',
})

// Filtered cities based on selected province
const filteredCities = computed(() => {
  if (!profileForm.province_id || !props.cities) return []
  const provinceId = typeof profileForm.province_id === 'string' 
    ? parseInt(profileForm.province_id) 
    : profileForm.province_id
  return props.cities.filter(city => city.province_id === provinceId)
})

// Submit profile
const submitProfile = () => {
  profileForm.post(providerProfileRoutes.store.url(), {
    onSuccess: () => {
      // Could show success message
    }
  })
}

// Submit schedule
const submitSchedule = () => {
  const scheduleForm = useForm({
    schedules: scheduleData.value.map((item) => ({
      day_of_week: item.day_of_week,
      start_time: item.start_time,
      end_time: item.end_time,
      is_available: item.is_available,
      max_patients: item.max_patients ?? null,
    }))
  })

  scheduleForm.post(providerScheduleRoutes.bulk.url(), {
    onSuccess: () => {
      // Could show success message
    }
  })
}

// Toggle a day's availability and focus the start time when enabled
const toggleDay = async (index: number) => {
  // Wait for the DOM to update with the new conditional v-if state
  await nextTick()
  
  // Focus the start time input if now available
  const item = scheduleData.value[index]
  if (item?.is_available) {
    const input = document.getElementById(`schedule-start-${item.day_of_week}`) as HTMLInputElement | null
    input?.focus()
  }
}

// Submit working date range
const submitWorkingDates = () => {
  if (!workingDatesForm.from_date || !workingDatesForm.to_date) {
    alert('Please select both start and end dates')
    return
  }
  
  workingDatesForm.post(providerAvailabilityRoutes.store.url(), {
    onSuccess: () => {
      workingDatesForm.reset()
    }
  })
}

// Submit excluded date(s)
const submitExcludedDate = () => {
  if (!excludedDateForm.from_date) {
    alert('Please select a start date')
    return
  }
  
  // If no end date, use start date
  if (!excludedDateForm.to_date) {
    excludedDateForm.to_date = excludedDateForm.from_date
  }
  
  excludedDateForm.post(providerAvailabilityRoutes.exclude.url(), {
    onSuccess: () => {
      excludedDateForm.reset()
    }
  })
}

// Delete availability record
const deleteAvailability = (id: number) => {
  if (confirm('Are you sure you want to delete this availability record?')) {
    const { router } = require('@inertiajs/vue3')
    router.delete(providerAvailabilityRoutes.destroy.url(id), {
      preserveScroll: true,
      onSuccess: () => {
        // Success message handled by backend
      }
    })
  }
}

// Format date for display
const formatDate = (dateString: string): string => {
  if (!dateString) return 'Invalid Date'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return 'Invalid Date'
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: '2-digit' 
  })
}

// Format date range for display
const formatDateRange = (startDate: string, endDate?: string, isRange?: boolean): string => {
  if (!startDate) return 'Invalid Date'
  if (!endDate || !isRange || startDate === endDate) {
    return formatDate(startDate)
  }
  return `${formatDate(startDate)} - ${formatDate(endDate)}`
}

// Calculate average hours per working day
const calculateAverageHours = (): string => {
  const workingDays = scheduleData.value.filter((entry) => entry.is_available)
  if (workingDays.length === 0) return '0.0'
  
  const totalHours = workingDays.reduce((sum: number, entry) => {
    if (!entry.start_time || !entry.end_time) return sum
    try {
      const start = new Date(`2000-01-01 ${entry.start_time}`)
      const end = new Date(`2000-01-01 ${entry.end_time}`)
      if (isNaN(start.getTime()) || isNaN(end.getTime())) return sum
      return sum + (end.getTime() - start.getTime()) / (1000 * 60 * 60)
    } catch {
      return sum
    }
  }, 0)
  
  return (totalHours / workingDays.length).toFixed(1)
}

// Group working periods into ranges
const groupWorkingPeriods = () => {
  const workingPeriods = props.availability?.filter(a => a?.is_available) || []
  if (workingPeriods.length === 0) return []

  // Sort by date
  const sorted = workingPeriods.sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime())
  
  const ranges: Array<{
    start_date: string
    end_date: string
    start_time: string
    end_time: string
    count: number
  }> = []
  
  let currentRange = {
    start_date: sorted[0].date,
    end_date: sorted[0].date,
    start_time: sorted[0].start_time,
    end_time: sorted[0].end_time,
    count: 1
  }
  
  for (let i = 1; i < sorted.length; i++) {
    const current = sorted[i]
    const prev = sorted[i - 1]
    
    // Check if dates are consecutive and times match
    const currentDate = new Date(current.date)
    const prevDate = new Date(prev.date)
    const diffTime = currentDate.getTime() - prevDate.getTime()
    const diffDays = diffTime / (1000 * 3600 * 24)
    
    if (diffDays === 1 && current.start_time === prev.start_time && current.end_time === prev.end_time) {
      // Extend current range
      currentRange.end_date = current.date
      currentRange.count++
    } else {
      // Start new range
      ranges.push({ ...currentRange })
      currentRange = {
        start_date: current.date,
        end_date: current.date,
        start_time: current.start_time,
        end_time: current.end_time,
        count: 1
      }
    }
  }
  
  // Add the last range
  ranges.push(currentRange)
  
  return ranges
}
</script>

<template>
  <AppLayout>
    <Head title="Provider Configuration" />

    <div class="container mx-auto px-4 py-8 max-w-6xl">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white flex items-center gap-3">
          <Settings class="w-8 h-8 text-indigo-600" />
          Provider Configuration
        </h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">
          Manage your complete profile, schedule, and availability in one place
        </p>
      </div>

      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-3">
          <TabsTrigger value="profile" class="flex items-center gap-2">
            <User class="w-4 h-4" />
            Profile
          </TabsTrigger>
          <TabsTrigger value="schedule" class="flex items-center gap-2">
            <Calendar class="w-4 h-4" />
            Schedule
          </TabsTrigger>
          <TabsTrigger value="availability" class="flex items-center gap-2">
            <Clock class="w-4 h-4" />
            Availability
          </TabsTrigger>
        </TabsList>

        <!-- Profile Tab -->
        <TabsContent value="profile" class="space-y-6 mt-6">
          <form @submit.prevent="submitProfile" class="space-y-6">
            <!-- Basic Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="w-5 h-5" />
                  Basic Information
                </CardTitle>
                <CardDescription>
                  Essential details about your practice
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="title">Title</Label>
                    <select
                      v-model="profileForm.title"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="Dr.">Dr.</option>
                      <option value="Prof.">Prof.</option>
                      <option value="Mr.">Mr.</option>
                      <option value="Ms.">Ms.</option>
                    </select>
                  </div>
                  <div>
                    <Label for="specialization">Specialization *</Label>
                    <select
                      v-model="profileForm.specialization_id"
                      required
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="">Select specialization</option>
                      <option
                        v-for="spec in specializations"
                        :key="spec.id"
                        :value="spec.id"
                      >
                        {{ spec.name }}
                      </option>
                    </select>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="province">Province</Label>
                    <select
                      v-model="profileForm.province_id"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                    >
                      <option value="">Select province</option>
                      <option
                        v-for="province in provinces"
                        :key="province.id"
                        :value="province.id"
                      >
                        {{ province.name_ar }} ({{ province.name_en }}) - {{ province.code }}
                      </option>
                    </select>
                  </div>
                  <div>
                    <Label for="city">City</Label>
                    <select
                      v-model="profileForm.city_id"
                      :disabled="!profileForm.province_id"
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white disabled:opacity-50"
                    >
                      <option value="">Select city</option>
                      <option
                        v-for="city in filteredCities"
                        :key="city.id"
                        :value="city.id"
                      >
                        {{ city.name_ar }} ({{ city.name_en }})
                      </option>
                    </select>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Professional Details -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Award class="w-5 h-5" />
                  Professional Details
                </CardTitle>
                <CardDescription>
                  Your qualifications and experience
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="years_experience">Years of Experience *</Label>
                    <Input
                      v-model.number="profileForm.years_experience"
                      type="number"
                      min="0"
                      max="100"
                      required
                    />
                  </div>
                  <div>
                    <Label for="license_number">License Number</Label>
                    <Input v-model="profileForm.license_number" />
                  </div>
                </div>

                <div>
                  <Label for="qualifications">Qualifications</Label>
                  <Textarea
                    v-model="profileForm.qualifications"
                    placeholder="e.g., MD Cardiology, Board Certified, etc."
                    rows="2"
                  />
                </div>

                <div>
                  <Label for="languages">Languages</Label>
                  <Input
                    v-model="profileForm.languages"
                    placeholder="e.g., Arabic, French, English"
                  />
                </div>

                <div>
                  <Label for="bio">Bio</Label>
                  <Textarea
                    v-model="profileForm.bio"
                    placeholder="Describe your experience, approach, and what patients can expect..."
                    rows="4"
                  />
                </div>
              </CardContent>
            </Card>

            <!-- Practice Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <MapPin class="w-5 h-5" />
                  Practice Information
                </CardTitle>
                <CardDescription>
                  Where and how patients can reach you
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="clinic_name">Clinic Name</Label>
                    <Input v-model="profileForm.clinic_name" />
                  </div>
                  <div>
                    <Label for="phone">Phone</Label>
                    <Input v-model="profileForm.phone" />
                  </div>
                </div>

                <div>
                  <Label for="office_address">Office Address</Label>
                  <Textarea v-model="profileForm.office_address" rows="2" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="consultation_fee">Consultation Fee ($)</Label>
                    <Input
                      v-model.number="profileForm.consultation_fee"
                      type="number"
                      min="0"
                      step="0.01"
                    />
                  </div>
                  <div>
                    <Label for="advance_booking_days">Advance Booking Days</Label>
                    <Input
                      v-model.number="profileForm.advance_booking_days"
                      type="number"
                      min="1"
                      max="365"
                    />
                  </div>
                </div>

                <div>
                  <Label for="services_offered">Services Offered</Label>
                  <Textarea
                    v-model="profileForm.services_offered"
                    placeholder="e.g., Consultation, ECG, Blood Tests, etc."
                    rows="3"
                  />
                </div>
              </CardContent>
            </Card>

            <!-- Additional Information -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Globe class="w-5 h-5" />
                  Additional Information
                </CardTitle>
                <CardDescription>
                  Education, awards, and online presence
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div>
                  <Label for="education">Education</Label>
                  <Textarea
                    v-model="profileForm.education"
                    placeholder="Your educational background and certifications"
                    rows="3"
                  />
                </div>

                <div>
                  <Label for="awards">Awards & Recognition</Label>
                  <Textarea
                    v-model="profileForm.awards"
                    placeholder="Professional awards and recognitions"
                    rows="2"
                  />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="website">Website</Label>
                    <Input v-model="profileForm.website" placeholder="https://" />
                  </div>
                  <div>
                    <Label for="social_links">Social Links</Label>
                    <Input
                      v-model="profileForm.social_links"
                      placeholder="Facebook, LinkedIn, etc."
                    />
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Settings -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Settings class="w-5 h-5" />
                  Settings
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <Label for="slot_duration">Appointment Duration *</Label>
                    <select
                      v-model.number="profileForm.slot_duration"
                      required
                      class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white"
                    >
                      <option :value="15">15 minutes</option>
                      <option :value="30">30 minutes</option>
                      <option :value="45">45 minutes</option>
                      <option :value="60">60 minutes</option>
                    </select>
                  </div>
                  <div class="flex items-center space-x-2">
                    <input
                      id="is_available"
                      type="checkbox"
                      v-model="profileForm.is_available"
                      class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer"
                    />
                    <Label for="is_available">Currently accepting appointments</Label>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Submit Button -->
            <div class="flex justify-end">
              <Button
                type="submit"
                :disabled="profileForm.processing"
                class="px-8 py-3"
              >
                {{ profileForm.processing ? 'Saving...' : 'Save Profile' }}
              </Button>
            </div>
          </form>
        </TabsContent>

        <!-- Schedule Tab -->
        <TabsContent value="schedule" class="space-y-6 mt-6">
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Calendar class="w-5 h-5" />
                Weekly Schedule
              </CardTitle>
              <CardDescription>
                Set your regular working hours for each day of the week
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div
                  v-for="(entry, index) in scheduleData"
                  :key="entry.day_of_week"
                  class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800 transition"
                >
                  <div class="flex items-center gap-4 flex-1">
                    <!-- Day Selection -->
                    <div class="flex items-center space-x-3 w-32">
                      <input
                        :id="`day-${entry.day_of_week}`"
                        type="checkbox"
                        v-model="entry.is_available"
                        @change="toggleDay(index)"
                        class="w-4 h-4 rounded border-gray-300 text-indigo-600 cursor-pointer"
                      />
                      <Label :for="`day-${entry.day_of_week}`" class="font-semibold text-gray-700 dark:text-gray-300 cursor-pointer min-w-fit">
                        {{ entry.label }}
                      </Label>
                    </div>

                    <!-- Working Hours -->
                    <div v-if="entry.is_available" class="flex items-center gap-3 flex-1 flex-wrap">
                      <!-- Start Time -->
                      <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">From:</span>
                        <Input
                          v-model="entry.start_time"
                          type="time"
                          class="w-24 px-2 py-2"
                          :id="`schedule-start-${entry.day_of_week}`"
                        />
                      </div>

                      <!-- Separator -->
                      <span class="text-gray-400 dark:text-gray-600">→</span>

                      <!-- End Time -->
                      <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">To:</span>
                        <Input
                          v-model="entry.end_time"
                          type="time"
                          class="w-24 px-2 py-2"
                        />
                      </div>

                      <!-- Max Patients (Optional) -->
                      <div class="flex items-center gap-2">
                        <span class="text-xs font-medium text-gray-600 dark:text-gray-400 whitespace-nowrap">Max Patients:</span>
                        <Input
                          v-model.number="entry.max_patients"
                          type="number"
                          min="1"
                          max="100"
                          placeholder="Auto"
                          class="w-20 px-2 py-2"
                        />
                      </div>

                      <!-- Time Display Badge -->
                      <div class="ml-auto">
                        <Badge variant="outline" class="bg-green-50 dark:bg-green-950 border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
                          <Clock class="w-3 h-3 mr-1" />
                          {{ entry.start_time || '09:00' }} - {{ entry.end_time || '17:00' }}
                        </Badge>
                      </div>
                    </div>

                    <!-- Not Working -->
                    <div v-else class="flex items-center gap-2 ml-auto">
                      <Badge variant="outline" class="bg-gray-100 dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-600 dark:text-gray-400">
                        <X class="w-3 h-3 mr-1" />
                        Not working
                      </Badge>
                    </div>
                  </div>
                </div>
              </div>

              <Separator class="my-6" />

              <!-- Schedule Summary -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="p-3 bg-blue-50 dark:bg-blue-950 rounded-lg border border-blue-200 dark:border-blue-800">
                  <div class="text-xs text-blue-600 dark:text-blue-400 font-semibold">Working Days</div>
                  <div class="text-xl font-bold text-blue-700 dark:text-blue-300">
                    {{ scheduleData.filter((entry: ScheduleEntry) => entry.is_available).length }}/7
                  </div>
                </div>
                <div class="p-3 bg-green-50 dark:bg-green-950 rounded-lg border border-green-200 dark:border-green-800" v-if="scheduleData.filter((entry: ScheduleEntry) => entry.is_available).length > 0">
                  <div class="text-xs text-green-600 dark:text-green-400 font-semibold">Avg Hours/Day</div>
                  <div class="text-xl font-bold text-green-700 dark:text-green-300">
                    {{ calculateAverageHours() }}h
                  </div>
                </div>
              </div>

              <div class="flex justify-end gap-3">
                <Button @click="submitSchedule" class="px-8 py-3">
                  <Check class="w-4 h-4 mr-2" />
                  Save Weekly Schedule
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Availability Tab -->
        <TabsContent value="availability" class="space-y-6 mt-6">
          <!-- Working Dates Range -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Calendar class="w-5 h-5" />
                Working Date Range
              </CardTitle>
              <CardDescription>
                Set your overall availability period (dates when you'll be accepting appointments)
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label for="working_from">Available From</Label>
                  <Input
                    v-model="workingDatesForm.from_date"
                    type="date"
                    :min="new Date().toISOString().split('T')[0]"
                  />
                </div>
                <div>
                  <Label for="working_to">Available Until</Label>
                  <Input
                    v-model="workingDatesForm.to_date"
                    type="date"
                    :min="workingDatesForm.from_date || new Date().toISOString().split('T')[0]"
                  />
                </div>
              </div>
              <Button 
                @click="submitWorkingDates" 
                :disabled="workingDatesForm.processing"
                class="w-full"
              >
                <Check class="w-4 h-4 mr-2" />
                {{ workingDatesForm.processing ? 'Saving...' : 'Save Working Date Range' }}
              </Button>
            </CardContent>
          </Card>

          <!-- Excluded Dates -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Plus class="w-5 h-5" />
                  Add Excluded Dates
                </CardTitle>
                <CardDescription>
                  Mark specific dates when you're unavailable (holidays, conferences, etc.)
                </CardDescription>
              </CardHeader>
              <CardContent>
                <form @submit.prevent="submitExcludedDate" class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <Label for="exclude_from">Unavailable From</Label>
                      <Input
                        v-model="excludedDateForm.from_date"
                        type="date"
                        :min="new Date().toISOString().split('T')[0]"
                        required
                      />
                    </div>
                    <div>
                      <Label for="exclude_to">Unavailable Until (optional)</Label>
                      <Input
                        v-model="excludedDateForm.to_date"
                        type="date"
                        :min="excludedDateForm.from_date || new Date().toISOString().split('T')[0]"
                      />
                      <p class="text-xs text-gray-500 mt-1">Leave empty for single day exclusion</p>
                    </div>
                  </div>                  <div>
                    <Label for="exclude_reason">Reason</Label>
                    <Textarea
                      v-model="excludedDateForm.reason"
                      placeholder="e.g., Conference, Holiday, Vacation, Personal Leave..."
                      rows="2"
                    />
                  </div>

                  <Button
                    type="submit"
                    :disabled="excludedDateForm.processing"
                    class="w-full"
                  >
                    <Plus class="w-4 h-4 mr-2" />
                    {{ excludedDateForm.processing ? 'Adding...' : 'Add Excluded Period' }}
                  </Button>
                </form>
              </CardContent>
            </Card>

            <!-- Current Excluded Dates -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <X class="w-5 h-5" />
                  Excluded Periods
                </CardTitle>
                <CardDescription>
                  Your unavailable dates and periods
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div v-if="!props.availability || props.availability.filter(a => !a?.is_available).length === 0" class="text-center py-8 text-gray-500">
                  <AlertCircle class="w-8 h-8 mx-auto mb-2 opacity-50" />
                  No excluded dates set
                </div>
                <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                  <div
                    v-for="avail in props.availability.filter(a => !a?.is_available)"
                    :key="avail.id"
                    class="flex items-start justify-between p-3 border border-red-200 dark:border-red-900 rounded-lg bg-red-50 dark:bg-red-950/20"
                  >
                    <div class="flex-1">
                      <div class="font-medium text-gray-900 dark:text-white">
                        {{ formatDateRange(avail.date, avail.end_date, avail.is_range) }}
                      </div>
                      <div class="text-sm text-gray-600 dark:text-gray-400">
                        <Badge variant="destructive" class="inline-block mb-1">Unavailable</Badge>
                        <p v-if="avail.reason" class="mt-1">{{ avail.reason }}</p>
                      </div>
                    </div>
                    <Button
                      @click="deleteAvailability(avail.id)"
                      variant="outline"
                      size="sm"
                      class="ml-2"
                    >
                      <Trash2 class="w-4 h-4" />
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Working Date Ranges -->
          <Card>
            <CardHeader>
              <CardTitle class="flex items-center gap-2">
                <Check class="w-5 h-5" />
                Active Working Periods
              </CardTitle>
              <CardDescription>
                Your confirmed available dates and time ranges
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div v-if="!props.availability || props.availability.filter(a => a?.is_available).length === 0" class="text-center py-8 text-gray-500">
                <AlertCircle class="w-8 h-8 mx-auto mb-2 opacity-50" />
                No working periods added yet
              </div>
              <div v-else class="space-y-3 max-h-96 overflow-y-auto">
                <div
                  v-for="range in groupWorkingPeriods()"
                  :key="`${range.start_date}-${range.end_date}`"
                  class="flex items-start justify-between p-3 border border-green-200 dark:border-green-900 rounded-lg bg-green-50 dark:bg-green-950/20"
                >
                  <div class="flex-1">
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ formatDateRange(range.start_date, range.end_date, range.start_date !== range.end_date) }}
                      <span v-if="range.count > 1" class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                        ({{ range.count }} days)
                      </span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">
                      <Badge variant="default" class="inline-block">
                        {{ range.start_time }} - {{ range.end_time }}
                      </Badge>
                    </div>
                  </div>
                  <div class="text-xs text-gray-500 dark:text-gray-400">
                    Working period
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>