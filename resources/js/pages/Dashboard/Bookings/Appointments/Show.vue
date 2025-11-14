<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3'
import { Calendar, Clock, User, MapPin, FileText, CheckCircle2, XCircle, Clock3, ArrowLeft, Phone, Mail, Award } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import * as appointmentsRoutes from '@/routes/appointments'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

interface User {
  id: number
  name: string
  email: string
  avatar: string | null
}

interface Specialization {
  id: number
  name: string
}

interface ProviderProfile {
  id: number
  user: User
  specialization: Specialization
  years_experience: number
  rating: number
  total_reviews: number
  consultation_fee: number
  phone?: string
}

interface Appointment {
  id: number
  appointment_date: string
  start_time: string
  end_time: string
  status: 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show'
  notes: string | null
  user: User
  providerProfile: ProviderProfile
}

interface Props {
  appointment: Appointment
  isProvider: boolean
  isAdmin?: boolean
}

const props = defineProps<Props>()

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

const cancelAppointment = () => {
  if (confirm('Are you sure you want to cancel this appointment?')) {
    router.post(appointmentsRoutes.cancel.url(props.appointment.id))
  }
}

const updateStatus = (status: string) => {
  router.post(appointmentsRoutes.updateStatus.url(props.appointment.id), { status })
}

const deleteAppointment = () => {
  if (confirm('Are you sure you want to permanently delete this appointment? This action cannot be undone.')) {
    router.delete(appointmentsRoutes.destroy.url(props.appointment.id))
  }
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('ar-DZ', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  })
}
</script>

<template>
  <AppLayout>
    <Head :title="`Appointment - ${appointment.id}`" />

    <div class="container mx-auto px-4 py-8 max-w-4xl">
      <!-- Back Button -->
      <Link
        :href="appointmentsRoutes.index.url()"
        class="inline-flex items-center text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 mb-6 transition-colors"
      >
        <ArrowLeft class="w-5 h-5 mr-2" />
        Back to Appointments
      </Link>

      <!-- Appointment Header -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-6">
        <div class="flex items-start justify-between mb-6">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
              {{ isProvider ? appointment.user?.name : appointment.providerProfile?.user?.name }}
            </h1>
            <p v-if="!isProvider && appointment.providerProfile" class="text-lg text-gray-600 dark:text-gray-400">
              {{ appointment.providerProfile.specialization?.name }}
            </p>
          </div>
          <span
            class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold"
            :class="getStatusColor(appointment.status)"
          >
            <component :is="getStatusIcon(appointment.status)" class="w-5 h-5 mr-2" />
            {{ appointment.status.charAt(0).toUpperCase() + appointment.status.slice(1) }}
          </span>
        </div>

        <!-- Appointment Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Left Column -->
          <div class="space-y-6">
            <!-- Date & Time -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Calendar class="w-5 h-5 text-indigo-600" />
                  Date & Time
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Date</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ formatDate(appointment.appointment_date) }}
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Time</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ appointment.start_time }} - {{ appointment.end_time }}
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Contact Info -->
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <User class="w-5 h-5 text-indigo-600" />
                  {{ isProvider ? 'Patient' : 'Provider' }} Information
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <div class="flex items-center space-x-2">
                    <Mail class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    <div>
                      <p class="text-sm text-gray-600 dark:text-gray-400">Email</p>
                      <p class="text-gray-900 dark:text-white">
                        {{ isProvider ? appointment.user?.email : appointment.providerProfile?.user?.email }}
                      </p>
                    </div>
                  </div>
                  <div v-if="isProvider && appointment.providerProfile?.phone" class="flex items-center space-x-2">
                    <Phone class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    <div>
                      <p class="text-sm text-gray-600 dark:text-gray-400">Phone</p>
                      <p class="text-gray-900 dark:text-white">{{ appointment.providerProfile?.phone }}</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Right Column -->
          <div class="space-y-6">
            <!-- Provider Details (only for patients) -->
            <Card v-if="!isProvider && appointment.providerProfile">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <Award class="w-5 h-5 text-indigo-600" />
                  Provider Details
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Experience</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ appointment.providerProfile?.years_experience ?? 'N/A' }} years
                    </p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Rating</p>
                    <div class="flex items-center">
                      <span class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ appointment.providerProfile?.rating ?? 'N/A' }}
                      </span>
                      <span class="text-sm text-gray-600 dark:text-gray-400 ml-2">
                        ({{ appointment.providerProfile?.total_reviews ?? 0 }} reviews)
                      </span>
                    </div>
                  </div>
                  <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Consultation Fee</p>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                      {{ appointment.providerProfile?.consultation_fee ?? 'N/A' }} DZD
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Notes Section -->
            <Card v-if="appointment.notes">
              <CardHeader>
                <CardTitle class="flex items-center gap-2">
                  <FileText class="w-5 h-5 text-indigo-600" />
                  Notes
                </CardTitle>
              </CardHeader>
              <CardContent>
                <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                  {{ appointment.notes }}
                </p>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-wrap gap-4 justify-center md:justify-start">
        <!-- Patient Actions -->
        <Button
          v-if="(!isProvider || isAdmin) && ['pending', 'confirmed'].includes(appointment.status)"
          @click="cancelAppointment"
          variant="destructive"
          class="px-6 py-3"
        >
          Cancel Appointment
        </Button>

        <!-- Provider Actions -->
        <Button
          v-if="(isProvider || isAdmin) && appointment.status === 'pending'"
          @click="updateStatus('confirmed')"
          class="px-6 py-3 bg-green-600 hover:bg-green-700"
        >
          Confirm Appointment
        </Button>
        <Button
          v-if="(isProvider || isAdmin) && appointment.status === 'pending'"
          @click="updateStatus('cancelled')"
          variant="destructive"
          class="px-6 py-3"
        >
          Decline Appointment
        </Button>
        <Button
          v-if="(isProvider || isAdmin) && appointment.status === 'confirmed'"
          @click="updateStatus('completed')"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700"
        >
          Mark as Completed
        </Button>
        <Button
          v-if="(isProvider || isAdmin) && appointment.status === 'confirmed'"
          @click="updateStatus('no_show')"
          class="px-6 py-3 bg-orange-600 hover:bg-orange-700"
        >
          Mark as No Show
        </Button>

        <!-- Admin Actions -->
        <Button
          v-if="isAdmin"
          @click="deleteAppointment"
          variant="destructive"
          class="px-6 py-3"
        >
          Delete Appointment
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
