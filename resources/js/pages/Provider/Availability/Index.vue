<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { store, bulk, destroy } from '@/routes/provider/availability';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Label } from '@/components/ui/label';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Separator } from '@/components/ui/separator';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    Calendar,
    Clock,
    Plus,
    Trash2,
    Check,
    X,
    ChevronLeft,
    ChevronRight,
    AlertCircle,
} from 'lucide-vue-next';

interface AvailabilityRecord {
    id: number;
    date: string;
    start_time: string;
    end_time: string;
    is_available: boolean;
    reason?: string;
}

interface Props {
    provider: any;
    availability: AvailabilityRecord[];
    defaultSchedule: Record<string, any>;
}

const props = defineProps<Props>();

const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const selectedDates = ref<string[]>([]);
const showDialog = ref(false);
const showBulkDialog = ref(false);
const dialogMode = ref<'available' | 'unavailable'>('available');

const form = useForm({
    dates: [] as string[],
    start_time: '09:00',
    end_time: '17:00',
    is_available: true,
    reason: '',
});

const bulkForm = useForm({
    start_date: '',
    end_date: '',
    days_of_week: [] as number[],
    start_time: '09:00',
    end_time: '17:00',
    is_available: true,
    reason: '',
});

const daysOfWeek = [
    { value: 0, label: 'Sunday' },
    { value: 1, label: 'Monday' },
    { value: 2, label: 'Tuesday' },
    { value: 3, label: 'Wednesday' },
    { value: 4, label: 'Thursday' },
    { value: 5, label: 'Friday' },
    { value: 6, label: 'Saturday' },
];

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const calendarDays = computed(() => {
    const firstDay = new Date(currentYear.value, currentMonth.value, 1);
    const lastDay = new Date(currentYear.value, currentMonth.value + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    const days: Array<{
        date: number;
        fullDate: string;
        isCurrentMonth: boolean;
        isPast: boolean;
        isSelected: boolean;
        availability?: AvailabilityRecord;
    }> = [];

    // Add previous month days
    const prevMonthLastDay = new Date(currentYear.value, currentMonth.value, 0).getDate();
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        const date = prevMonthLastDay - i;
        const prevMonth = currentMonth.value === 0 ? 11 : currentMonth.value - 1;
        const prevYear = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value;
        const fullDate = `${prevYear}-${String(prevMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
        
        days.push({
            date,
            fullDate,
            isCurrentMonth: false,
            isPast: new Date(fullDate) < new Date(new Date().setHours(0, 0, 0, 0)),
            isSelected: selectedDates.value.includes(fullDate),
        });
    }

    // Add current month days
    for (let date = 1; date <= daysInMonth; date++) {
        const fullDate = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
        const availability = props.availability.find(a => a.date === fullDate);
        
        days.push({
            date,
            fullDate,
            isCurrentMonth: true,
            isPast: new Date(fullDate) < new Date(new Date().setHours(0, 0, 0, 0)),
            isSelected: selectedDates.value.includes(fullDate),
            availability,
        });
    }

    // Add next month days to complete the grid
    const remainingDays = 42 - days.length; // 6 rows x 7 days
    for (let date = 1; date <= remainingDays; date++) {
        const nextMonth = currentMonth.value === 11 ? 0 : currentMonth.value + 1;
        const nextYear = currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value;
        const fullDate = `${nextYear}-${String(nextMonth + 1).padStart(2, '0')}-${String(date).padStart(2, '0')}`;
        
        days.push({
            date,
            fullDate,
            isCurrentMonth: false,
            isPast: new Date(fullDate) < new Date(new Date().setHours(0, 0, 0, 0)),
            isSelected: selectedDates.value.includes(fullDate),
        });
    }

    return days;
});

const previousMonth = () => {
    if (currentMonth.value === 0) {
        currentMonth.value = 11;
        currentYear.value--;
    } else {
        currentMonth.value--;
    }
};

const nextMonth = () => {
    if (currentMonth.value === 11) {
        currentMonth.value = 0;
        currentYear.value++;
    } else {
        currentMonth.value++;
    }
};

const toggleDateSelection = (day: any) => {
    if (!day.isCurrentMonth || day.isPast) return;

    const index = selectedDates.value.indexOf(day.fullDate);
    if (index > -1) {
        selectedDates.value.splice(index, 1);
    } else {
        selectedDates.value.push(day.fullDate);
    }
};

const clearSelection = () => {
    selectedDates.value = [];
};

const openAvailableDialog = () => {
    if (selectedDates.value.length === 0) return;
    
    dialogMode.value = 'available';
    form.dates = [...selectedDates.value];
    form.is_available = true;
    form.start_time = '09:00';
    form.end_time = '17:00';
    form.reason = '';
    showDialog.value = true;
};

const openUnavailableDialog = () => {
    if (selectedDates.value.length === 0) return;
    
    dialogMode.value = 'unavailable';
    form.dates = [...selectedDates.value];
    form.is_available = false;
    form.start_time = '00:00';
    form.end_time = '00:00';
    form.reason = '';
    showDialog.value = true;
};

const openBulkDialog = () => {
    bulkForm.reset();
    bulkForm.start_date = new Date().toISOString().split('T')[0];
    bulkForm.end_date = new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
    bulkForm.days_of_week = [1, 2, 3, 4, 5]; // Mon-Fri by default
    bulkForm.start_time = '09:00';
    bulkForm.end_time = '17:00';
    bulkForm.is_available = true;
    showBulkDialog.value = false;
    showBulkDialog.value = true;
};

const submitAvailability = () => {
    form.post(store().url, {
        preserveScroll: true,
        onSuccess: () => {
            showDialog.value = false;
            clearSelection();
            form.reset();
        },
    });
};

const submitBulkAvailability = () => {
    bulkForm.post(bulk().url, {
        preserveScroll: true,
        onSuccess: () => {
            showBulkDialog.value = false;
            bulkForm.reset();
        },
    });
};

const removeAvailability = () => {
    if (selectedDates.value.length === 0) return;

    if (confirm(`Are you sure you want to remove availability for ${selectedDates.value.length} date(s)?`)) {
        router.delete(destroy().url, {
            data: { dates: selectedDates.value },
            preserveScroll: true,
            onSuccess: () => {
                clearSelection();
            },
        });
    }
};

const formatTime = (time: string) => {
    if (!time) return '';
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
};
</script>

<template>
    <Head title="Manage Availability" />

    <AppLayout>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold">{{ $t('bookings.manage_availability') }}</h1>
                <p class="text-muted-foreground mt-2">
                    {{ $t('bookings.manage_availability_description') }}
                </p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3 mb-6">
                <Button @click="openBulkDialog" variant="default">
                    <Plus class="h-4 w-4 mr-2" />
                    {{ $t('bookings.bulk_set_availability') }}
                </Button>

                <Button 
                    @click="openAvailableDialog" 
                    variant="outline"
                    :disabled="selectedDates.length === 0"
                >
                    <Check class="h-4 w-4 mr-2" />
                    {{ $t('bookings.mark_available') }} ({{ selectedDates.length }})
                </Button>

                <Button 
                    @click="openUnavailableDialog" 
                    variant="outline"
                    :disabled="selectedDates.length === 0"
                >
                    <X class="h-4 w-4 mr-2" />
                    {{ $t('bookings.mark_unavailable') }} ({{ selectedDates.length }})
                </Button>

                <Button 
                    @click="removeAvailability" 
                    variant="destructive"
                    :disabled="selectedDates.length === 0"
                >
                    <Trash2 class="h-4 w-4 mr-2" />
                    {{ $t('bookings.remove') }} ({{ selectedDates.length }})
                </Button>

                <Button 
                    v-if="selectedDates.length > 0"
                    @click="clearSelection" 
                    variant="ghost"
                >
                    {{ $t('bookings.clear_selection') }}
                </Button>
            </div>

            <!-- Calendar -->
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle class="flex items-center gap-2">
                                <Calendar class="h-5 w-5" />
                                {{ monthNames[currentMonth] }} {{ currentYear }}
                            </CardTitle>
                            <CardDescription class="mt-2">
                                {{ $t('bookings.click_dates_to_select') }}
                            </CardDescription>
                        </div>
                        <div class="flex gap-2">
                            <Button @click="previousMonth" variant="outline" size="icon">
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <Button @click="nextMonth" variant="outline" size="icon">
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <!-- Day headers -->
                    <div class="grid grid-cols-7 gap-2 mb-2">
                        <div 
                            v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" 
                            :key="day"
                            class="text-center font-medium text-sm text-muted-foreground"
                        >
                            {{ day }}
                        </div>
                    </div>

                    <!-- Calendar grid -->
                    <div class="grid grid-cols-7 gap-2">
                        <button
                            v-for="day in calendarDays"
                            :key="day.fullDate"
                            @click="toggleDateSelection(day)"
                            :disabled="!day.isCurrentMonth || day.isPast"
                            :class="[
                                'aspect-square p-2 rounded-md text-sm font-medium transition-colors relative',
                                !day.isCurrentMonth && 'text-muted-foreground/50 cursor-not-allowed',
                                day.isPast && day.isCurrentMonth && 'text-muted-foreground/50 cursor-not-allowed',
                                day.isCurrentMonth && !day.isPast && 'hover:bg-accent cursor-pointer',
                                day.isSelected && 'ring-2 ring-primary',
                                day.availability?.is_available && 'bg-green-100 dark:bg-green-900/30 border-2 border-green-500',
                                day.availability && !day.availability.is_available && 'bg-red-100 dark:bg-red-900/30 border-2 border-red-500',
                            ]"
                        >
                            <span>{{ day.date }}</span>
                            
                            <!-- Availability indicator -->
                            <div v-if="day.availability" class="absolute bottom-1 left-1/2 -translate-x-1/2">
                                <div 
                                    :class="[
                                        'h-1 w-1 rounded-full',
                                        day.availability.is_available ? 'bg-green-600' : 'bg-red-600'
                                    ]"
                                />
                            </div>
                        </button>
                    </div>

                    <!-- Legend -->
                    <Separator class="my-4" />
                    <div class="flex flex-wrap gap-4 text-sm">
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded border-2 border-green-500 bg-green-100 dark:bg-green-900/30" />
                            <span>{{ $t('bookings.available') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded border-2 border-red-500 bg-red-100 dark:bg-red-900/30" />
                            <span>{{ $t('bookings.unavailable') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded ring-2 ring-primary bg-background" />
                            <span>{{ $t('bookings.selected') }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="h-4 w-4 rounded bg-accent" />
                            <span>{{ $t('bookings.default_schedule') }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Set Availability Dialog -->
            <Dialog v-model:open="showDialog">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {{ dialogMode === 'available' ? $t('bookings.set_available') : $t('bookings.set_unavailable') }}
                        </DialogTitle>
                        <DialogDescription>
                            {{ $t('bookings.setting_availability_for') }} {{ form.dates.length }} {{ $t('bookings.date_s') }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div v-if="dialogMode === 'available'" class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>{{ $t('bookings.start_time') }}</Label>
                                <Input v-model="form.start_time" type="time" class="mt-2" />
                            </div>
                            <div>
                                <Label>{{ $t('bookings.end_time') }}</Label>
                                <Input v-model="form.end_time" type="time" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label>{{ $t('bookings.reason') }} ({{ $t('bookings.optional') }})</Label>
                            <Textarea 
                                v-model="form.reason" 
                                :placeholder="dialogMode === 'unavailable' ? $t('bookings.reason_placeholder') : ''"
                                class="mt-2"
                                rows="3"
                            />
                        </div>

                        <Alert v-if="form.errors.dates">
                            <AlertCircle class="h-4 w-4" />
                            <AlertDescription>{{ form.errors.dates }}</AlertDescription>
                        </Alert>
                    </div>

                    <DialogFooter>
                        <Button @click="showDialog = false" variant="outline" :disabled="form.processing">
                            {{ $t('bookings.cancel') }}
                        </Button>
                        <Button @click="submitAvailability" :disabled="form.processing">
                            {{ form.processing ? $t('bookings.saving') : $t('bookings.save') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            <!-- Bulk Set Availability Dialog -->
            <Dialog v-model:open="showBulkDialog">
                <DialogContent class="max-w-2xl">
                    <DialogHeader>
                        <DialogTitle>{{ $t('bookings.bulk_set_availability') }}</DialogTitle>
                        <DialogDescription>
                            {{ $t('bookings.bulk_set_description') }}
                        </DialogDescription>
                    </DialogHeader>

                    <div class="space-y-4 py-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>{{ $t('bookings.start_date') }}</Label>
                                <Input v-model="bulkForm.start_date" type="date" class="mt-2" />
                            </div>
                            <div>
                                <Label>{{ $t('bookings.end_date') }}</Label>
                                <Input v-model="bulkForm.end_date" type="date" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label>{{ $t('bookings.days_of_week') }}</Label>
                            <div class="grid grid-cols-2 gap-3 mt-2">
                                <div 
                                    v-for="day in daysOfWeek" 
                                    :key="day.value"
                                    class="flex items-center gap-2"
                                >
                                    <Checkbox 
                                        :id="`day-${day.value}`"
                                        :checked="bulkForm.days_of_week.includes(day.value)"
                                        @update:checked="(checked) => {
                                            if (checked) {
                                                bulkForm.days_of_week.push(day.value);
                                            } else {
                                                const index = bulkForm.days_of_week.indexOf(day.value);
                                                if (index > -1) bulkForm.days_of_week.splice(index, 1);
                                            }
                                        }"
                                    />
                                    <Label :for="`day-${day.value}`" class="cursor-pointer">
                                        {{ day.label }}
                                    </Label>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <Label>{{ $t('bookings.start_time') }}</Label>
                                <Input v-model="bulkForm.start_time" type="time" class="mt-2" />
                            </div>
                            <div>
                                <Label>{{ $t('bookings.end_time') }}</Label>
                                <Input v-model="bulkForm.end_time" type="time" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <Label>{{ $t('bookings.reason') }} ({{ $t('bookings.optional') }})</Label>
                            <Textarea 
                                v-model="bulkForm.reason" 
                                :placeholder="$t('bookings.bulk_reason_placeholder')"
                                class="mt-2"
                                rows="3"
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button @click="showBulkDialog = false" variant="outline" :disabled="bulkForm.processing">
                            {{ $t('bookings.cancel') }}
                        </Button>
                        <Button @click="submitBulkAvailability" :disabled="bulkForm.processing || bulkForm.days_of_week.length === 0">
                            {{ bulkForm.processing ? $t('bookings.saving') : $t('bookings.save') }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
