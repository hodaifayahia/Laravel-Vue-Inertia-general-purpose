<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

interface Props {
    availableDates: string[]; // Array of available dates in YYYY-MM-DD format
    selectedDate?: string;
    minDate?: string; // Minimum date that can be selected
    maxDate?: string; // Maximum date that can be selected
}

const props = withDefaults(defineProps<Props>(), {
    selectedDate: '',
    minDate: new Date().toISOString().split('T')[0],
    maxDate: '2025-12-01',
});

const emit = defineEmits<{
    (e: 'dateSelected', date: string): void;
}>();

const currentMonth = ref(new Date().getMonth());
const currentYear = ref(new Date().getFullYear());
const internalSelectedDate = ref(props.selectedDate);

watch(() => props.selectedDate, (newVal) => {
    internalSelectedDate.value = newVal;
});

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const daysInMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    return new Date(currentYear.value, currentMonth.value, 1).getDay();
});

const calendarDays = computed(() => {
    const days: Array<{ date: string; day: number; isCurrentMonth: boolean; isAvailable: boolean; isPast: boolean; isSelected: boolean; isToday: boolean }> = [];
    const today = new Date().toISOString().split('T')[0];
    
    // Previous month days
    const prevMonthDays = new Date(currentYear.value, currentMonth.value, 0).getDate();
    for (let i = firstDayOfMonth.value - 1; i >= 0; i--) {
        const day = prevMonthDays - i;
        const month = currentMonth.value === 0 ? 11 : currentMonth.value - 1;
        const year = currentMonth.value === 0 ? currentYear.value - 1 : currentYear.value;
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        days.push({
            date: dateStr,
            day,
            isCurrentMonth: false,
            isAvailable: false,
            isPast: dateStr < props.minDate,
            isSelected: dateStr === internalSelectedDate.value,
            isToday: dateStr === today,
        });
    }
    
    // Current month days
    for (let day = 1; day <= daysInMonth.value; day++) {
        const dateStr = `${currentYear.value}-${String(currentMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isAvailable = props.availableDates.includes(dateStr);
        const isPast = dateStr < props.minDate || (props.maxDate && dateStr > props.maxDate);
        
        days.push({
            date: dateStr,
            day,
            isCurrentMonth: true,
            isAvailable,
            isPast,
            isSelected: dateStr === internalSelectedDate.value,
            isToday: dateStr === today,
        });
    }
    
    // Next month days to fill the grid
    const remainingDays = 42 - days.length; // 6 rows * 7 days
    for (let day = 1; day <= remainingDays; day++) {
        const month = currentMonth.value === 11 ? 0 : currentMonth.value + 1;
        const year = currentMonth.value === 11 ? currentYear.value + 1 : currentYear.value;
        const dateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        days.push({
            date: dateStr,
            day,
            isCurrentMonth: false,
            isAvailable: false,
            isPast: dateStr < props.minDate || (props.maxDate && dateStr > props.maxDate),
            isSelected: dateStr === internalSelectedDate.value,
            isToday: dateStr === today,
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

const selectDate = (dayInfo: any) => {
    if (!dayInfo.isPast && dayInfo.isAvailable && dayInfo.isCurrentMonth) {
        internalSelectedDate.value = dayInfo.date;
        emit('dateSelected', dayInfo.date);
    }
};

const getDayClasses = (dayInfo: any) => {
    const classes = ['day-cell'];
    
    if (!dayInfo.isCurrentMonth) {
        classes.push('other-month');
    }
    
    if (dayInfo.isPast) {
        classes.push('past-date');
    } else if (dayInfo.isAvailable) {
        classes.push('available');
    } else {
        classes.push('unavailable');
    }
    
    if (dayInfo.isSelected) {
        classes.push('selected');
    }
    
    if (dayInfo.isToday) {
        classes.push('today');
    }
    
    return classes.join(' ');
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <Button variant="ghost" size="icon" @click="previousMonth">
                    <ChevronLeft class="h-4 w-4" />
                </Button>
                <CardTitle>{{ monthNames[currentMonth] }} {{ currentYear }}</CardTitle>
                <Button variant="ghost" size="icon" @click="nextMonth">
                    <ChevronRight class="h-4 w-4" />
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <div class="calendar-grid">
                <!-- Weekday headers -->
                <div class="weekday-header">Sun</div>
                <div class="weekday-header">Mon</div>
                <div class="weekday-header">Tue</div>
                <div class="weekday-header">Wed</div>
                <div class="weekday-header">Thu</div>
                <div class="weekday-header">Fri</div>
                <div class="weekday-header">Sat</div>
                
                <!-- Calendar days -->
                <button
                    v-for="dayInfo in calendarDays"
                    :key="dayInfo.date"
                    :class="getDayClasses(dayInfo)"
                    @click="selectDate(dayInfo)"
                    :disabled="dayInfo.isPast || !dayInfo.isAvailable || !dayInfo.isCurrentMonth"
                >
                    {{ dayInfo.day }}
                </button>
            </div>
            
            <!-- Legend -->
            <div class="mt-4 flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-green-500"></div>
                    <span>{{ $t('bookings.available') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full bg-gray-300"></div>
                    <span>{{ $t('bookings.unavailable') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 rounded-full border-2 border-primary"></div>
                    <span>{{ $t('bookings.selected') }}</span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

<style scoped>
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 0.25rem;
}

.weekday-header {
    text-align: center;
    font-weight: 600;
    padding: 0.5rem;
    color: hsl(var(--muted-foreground));
    font-size: 0.875rem;
}

.day-cell {
    aspect-ratio: 1;
    padding: 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    border: 2px solid transparent;
    cursor: pointer;
}

.day-cell.other-month {
    color: hsl(var(--muted-foreground));
    opacity: 0.4;
}

.day-cell.past-date {
    color: hsl(var(--muted-foreground));
    opacity: 0.5;
    cursor: not-allowed;
}

.day-cell.available {
    background-color: hsl(var(--accent));
    color: hsl(var(--accent-foreground));
}

.day-cell.available:hover:not(:disabled) {
    background-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    transform: scale(1.05);
}

.day-cell.unavailable {
    background-color: hsl(var(--muted));
    color: hsl(var(--muted-foreground));
    cursor: not-allowed;
}

.day-cell.selected {
    background-color: hsl(var(--primary));
    color: hsl(var(--primary-foreground));
    border-color: hsl(var(--primary));
    font-weight: 700;
}

.day-cell.today {
    border-color: hsl(var(--ring));
}

.day-cell:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
</style>
