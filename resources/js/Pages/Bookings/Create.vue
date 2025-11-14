<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DoctorProfile from '@/components/Bookings/DoctorProfile.vue';
import AvailabilityCalendar from '@/components/Bookings/AvailabilityCalendar.vue';
import TimeSlotSelector from '@/components/Bookings/TimeSlotSelector.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { CheckCircle2, AlertCircle, Calendar, Clock, User } from 'lucide-vue-next';

interface TimeSlot {
    start_time: string;
    end_time: string;
    is_available: boolean;
}

interface Props {
    provider: any;
    availableDates: string[];
    children?: any[];
}

const props = defineProps<Props>();

const step = ref<'profile' | 'datetime' | 'confirm'>(props.availableDates.length > 0 ? 'datetime' : 'profile');
const selectedDate = ref<string>('');
const selectedSlot = ref<TimeSlot | null>(null);
const selectedChild = ref<number | null>(null);
const notes = ref<string>('');
const timeSlots = ref<TimeSlot[]>([]);
const loadingSlots = ref(false);
const submitting = ref(false);
const error = ref<string>('');

const minDate = computed(() => {
    const today = new Date();
    return today.toISOString().split('T')[0];
});

const maxDate = computed(() => {
    const today = new Date();
    const advanceDays = props.provider.advance_booking_days || 30;
    const maxDate = new Date(today.setDate(today.getDate() + advanceDays));
    return maxDate.toISOString().split('T')[0];
});

const canProceedToConfirm = computed(() => {
    return selectedDate.value && selectedSlot.value && (!props.children || selectedChild.value);
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

const formatTime = (time: string) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
};

// Watch for date selection to fetch time slots
watch(selectedDate, async (newDate) => {
    if (!newDate) {
        timeSlots.value = [];
        selectedSlot.value = null;
        return;
    }

    loadingSlots.value = true;
    error.value = '';

    try {
        const response = await fetch(`/api/providers/${props.provider.id}/slots?date=${newDate}`);
        const data = await response.json();
        
        if (response.ok) {
            timeSlots.value = data.slots || [];
        } else {
            error.value = data.message || 'Failed to load time slots';
            timeSlots.value = [];
        }
    } catch (err) {
        error.value = 'Failed to load time slots. Please try again.';
        timeSlots.value = [];
    } finally {
        loadingSlots.value = false;
    }
});

const handleDateSelected = (date: string) => {
    selectedDate.value = date;
    selectedSlot.value = null;
};

const handleSlotSelected = (slot: TimeSlot) => {
    selectedSlot.value = slot;
};

const goToDateTimeSelection = () => {
    step.value = 'datetime';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const goToConfirmation = () => {
    if (canProceedToConfirm.value) {
        step.value = 'confirm';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
};

const goBackToDateTime = () => {
    step.value = 'datetime';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const goBackToProfile = () => {
    step.value = 'profile';
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const submitBooking = async () => {
    if (!canProceedToConfirm.value) return;

    submitting.value = true;
    error.value = '';

    try {
        router.post('/appointments', {
            provider_profile_id: props.provider.id,
            child_id: selectedChild.value,
            appointment_date: selectedDate.value,
            start_time: selectedSlot.value!.start_time,
            end_time: selectedSlot.value!.end_time,
            notes: notes.value,
        }, {
            onSuccess: () => {
                // Redirect handled by controller
            },
            onError: (errors) => {
                error.value = errors.message || 'Failed to book appointment. Please try again.';
                submitting.value = false;
            },
        });
    } catch (err) {
        error.value = 'An unexpected error occurred. Please try again.';
        submitting.value = false;
    }
};
</script>

<template>
    <Head :title="$t('bookings.book_appointment')" />

    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Progress Steps -->
            <div class="mb-8">
                <div class="flex items-center justify-center gap-4">
                    <div class="flex items-center gap-2">
                        <div 
                            :class="[
                                'flex items-center justify-center h-10 w-10 rounded-full border-2',
                                step === 'profile' ? 'border-primary bg-primary text-primary-foreground' : 'border-muted bg-background'
                            ]"
                        >
                            <User class="h-5 w-5" />
                        </div>
                        <span :class="['font-medium', step === 'profile' ? 'text-foreground' : 'text-muted-foreground']">
                            {{ $t('bookings.doctor_profile') }}
                        </span>
                    </div>

                    <Separator class="w-12" />

                    <div class="flex items-center gap-2">
                        <div 
                            :class="[
                                'flex items-center justify-center h-10 w-10 rounded-full border-2',
                                step === 'datetime' ? 'border-primary bg-primary text-primary-foreground' : 
                                ['confirm'].includes(step) ? 'border-primary bg-primary text-primary-foreground' :
                                'border-muted bg-background'
                            ]"
                        >
                            <Calendar class="h-5 w-5" />
                        </div>
                        <span :class="['font-medium', ['datetime', 'confirm'].includes(step) ? 'text-foreground' : 'text-muted-foreground']">
                            {{ $t('bookings.select_date_time') }}
                        </span>
                    </div>

                    <Separator class="w-12" />

                    <div class="flex items-center gap-2">
                        <div 
                            :class="[
                                'flex items-center justify-center h-10 w-10 rounded-full border-2',
                                step === 'confirm' ? 'border-primary bg-primary text-primary-foreground' : 'border-muted bg-background'
                            ]"
                        >
                            <CheckCircle2 class="h-5 w-5" />
                        </div>
                        <span :class="['font-medium', step === 'confirm' ? 'text-foreground' : 'text-muted-foreground']">
                            {{ $t('bookings.confirm') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            <Alert v-if="error" variant="destructive" class="mb-6">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>{{ error }}</AlertDescription>
            </Alert>

            <!-- Step 1: Doctor Profile -->
            <div v-if="step === 'profile'">
                <DoctorProfile 
                    :provider="provider" 
                    :show-booking-button="false"
                />
                
                <div class="flex justify-center mt-8">
                    <Button @click="goToDateTimeSelection" size="lg">
                        {{ $t('bookings.continue') }}
                        <Calendar class="ml-2 h-5 w-5" />
                    </Button>
                </div>
            </div>

            <!-- Step 2: Date & Time Selection -->
            <div v-if="step === 'datetime'">
                <div class="grid lg:grid-cols-2 gap-6">
                    <AvailabilityCalendar
                        :available-dates="availableDates"
                        :selected-date="selectedDate"
                        :min-date="minDate"
                        :max-date="maxDate"
                        @date-selected="handleDateSelected"
                    />

                    <TimeSlotSelector
                        :slots="timeSlots"
                        :selected-slot="selectedSlot?.start_time"
                        :selected-date="selectedDate"
                        :loading="loadingSlots"
                        @slot-selected="handleSlotSelected"
                    />
                </div>

                <div class="flex justify-between mt-8">
                    <Button @click="goBackToProfile" variant="outline">
                        {{ $t('bookings.back') }}
                    </Button>
                    <Button 
                        @click="goToConfirmation" 
                        :disabled="!canProceedToConfirm"
                    >
                        {{ $t('bookings.continue') }}
                        <CheckCircle2 class="ml-2 h-5 w-5" />
                    </Button>
                </div>
            </div>

            <!-- Step 3: Confirmation -->
            <div v-if="step === 'confirm'">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ $t('bookings.confirm_appointment') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <!-- Doctor Summary -->
                        <div>
                            <h3 class="font-semibold mb-2">{{ $t('bookings.doctor') }}</h3>
                            <div class="flex items-center gap-3">
                                <img 
                                    v-if="provider.user.photo" 
                                    :src="provider.user.photo" 
                                    :alt="provider.user.name"
                                    class="h-12 w-12 rounded-lg object-cover"
                                />
                                <div>
                                    <p class="font-medium">{{ provider.title || 'Dr.' }} {{ provider.user.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ provider.specialty }}</p>
                                </div>
                            </div>
                        </div>

                        <Separator />

                        <!-- Date & Time Summary -->
                        <div class="grid sm:grid-cols-2 gap-4">
                            <div>
                                <h3 class="font-semibold mb-2 flex items-center gap-2">
                                    <Calendar class="h-4 w-4" />
                                    {{ $t('bookings.date') }}
                                </h3>
                                <p>{{ formatDate(selectedDate) }}</p>
                            </div>
                            <div>
                                <h3 class="font-semibold mb-2 flex items-center gap-2">
                                    <Clock class="h-4 w-4" />
                                    {{ $t('bookings.time') }}
                                </h3>
                                <p>{{ formatTime(selectedSlot!.start_time) }} - {{ formatTime(selectedSlot!.end_time) }}</p>
                            </div>
                        </div>

                        <Separator />

                        <!-- Child Selection (if applicable) -->
                        <div v-if="children && children.length > 0">
                            <Label>{{ $t('bookings.select_child') }}</Label>
                            <select 
                                v-model="selectedChild" 
                                class="w-full mt-2 px-3 py-2 border rounded-md"
                                required
                            >
                                <option :value="null">{{ $t('bookings.select_child') }}</option>
                                <option 
                                    v-for="child in children" 
                                    :key="child.id" 
                                    :value="child.id"
                                >
                                    {{ child.name }} ({{ child.age }} {{ $t('bookings.years_old') }})
                                </option>
                            </select>
                        </div>

                        <Separator />

                        <!-- Notes -->
                        <div>
                            <Label for="notes">{{ $t('bookings.notes') }} ({{ $t('bookings.optional') }})</Label>
                            <Textarea 
                                id="notes"
                                v-model="notes" 
                                :placeholder="$t('bookings.notes_placeholder')"
                                class="mt-2"
                                rows="4"
                            />
                        </div>

                        <!-- Consultation Fee -->
                        <div v-if="provider.consultation_fee" class="bg-secondary/50 p-4 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="font-medium">{{ $t('bookings.consultation_fee') }}</span>
                                <span class="text-2xl font-bold">${{ provider.consultation_fee }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <div class="flex justify-between mt-8">
                    <Button @click="goBackToDateTime" variant="outline" :disabled="submitting">
                        {{ $t('bookings.back') }}
                    </Button>
                    <Button 
                        @click="submitBooking" 
                        :disabled="submitting || !canProceedToConfirm"
                        size="lg"
                    >
                        <span v-if="submitting">{{ $t('bookings.submitting') }}...</span>
                        <span v-else>{{ $t('bookings.confirm_book') }}</span>
                    </Button>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
