<script setup lang="ts">
import { computed } from 'vue';
import { Clock, CheckCircle2 } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';

interface TimeSlot {
    start_time: string;
    end_time: string;
    is_available: boolean;
}

interface Props {
    slots: TimeSlot[];
    selectedSlot?: string;
    selectedDate?: string;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    selectedSlot: '',
    selectedDate: '',
    loading: false,
});

const emit = defineEmits<{
    (e: 'slotSelected', slot: TimeSlot): void;
}>();

const formatTime = (time: string) => {
    const [hours, minutes] = time.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const displayHour = hour === 0 ? 12 : hour > 12 ? hour - 12 : hour;
    return `${displayHour}:${minutes} ${ampm}`;
};

const morningSlots = computed(() => {
    return props.slots.filter(slot => {
        const hour = parseInt(slot.start_time.split(':')[0]);
        return hour < 12;
    });
});

const afternoonSlots = computed(() => {
    return props.slots.filter(slot => {
        const hour = parseInt(slot.start_time.split(':')[0]);
        return hour >= 12 && hour < 17;
    });
});

const eveningSlots = computed(() => {
    return props.slots.filter(slot => {
        const hour = parseInt(slot.start_time.split(':')[0]);
        return hour >= 17;
    });
});

const selectSlot = (slot: TimeSlot) => {
    if (slot.is_available) {
        emit('slotSelected', slot);
    }
};

const isSelected = (slot: TimeSlot) => {
    return props.selectedSlot === slot.start_time;
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Clock class="h-5 w-5" />
                {{ $t('bookings.available_slots') }}
            </CardTitle>
            <p v-if="selectedDate" class="text-sm text-muted-foreground">
                {{ new Date(selectedDate).toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
            </p>
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"></div>
                <p class="mt-2 text-sm text-muted-foreground">{{ $t('bookings.loading') }}...</p>
            </div>

            <div v-else-if="!selectedDate" class="text-center py-8 text-muted-foreground">
                <Clock class="h-12 w-12 mx-auto mb-2 opacity-50" />
                <p>{{ $t('bookings.select_date') }}</p>
            </div>

            <div v-else-if="slots.length === 0" class="text-center py-8 text-muted-foreground">
                <Clock class="h-12 w-12 mx-auto mb-2 opacity-50" />
                <p>{{ $t('bookings.no_slots_available') }}</p>
            </div>

            <ScrollArea v-else class="h-[400px]">
                <div class="space-y-4">
                    <!-- Morning Slots -->
                    <div v-if="morningSlots.length > 0">
                        <div class="flex items-center gap-2 mb-2">
                            <Badge variant="outline">{{ $t('bookings.morning') }}</Badge>
                            <span class="text-xs text-muted-foreground">
                                {{ morningSlots.filter(s => s.is_available).length }} {{ $t('bookings.slot_available') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                v-for="slot in morningSlots"
                                :key="slot.start_time"
                                :variant="isSelected(slot) ? 'default' : 'outline'"
                                :disabled="!slot.is_available"
                                @click="selectSlot(slot)"
                                class="justify-between"
                            >
                                <span>{{ formatTime(slot.start_time) }}</span>
                                <CheckCircle2 v-if="isSelected(slot)" class="h-4 w-4" />
                                <Badge v-else-if="!slot.is_available" variant="destructive" class="text-xs">
                                    {{ $t('bookings.slot_taken') }}
                                </Badge>
                            </Button>
                        </div>
                    </div>

                    <!-- Afternoon Slots -->
                    <div v-if="afternoonSlots.length > 0">
                        <div class="flex items-center gap-2 mb-2">
                            <Badge variant="outline">{{ $t('bookings.afternoon') }}</Badge>
                            <span class="text-xs text-muted-foreground">
                                {{ afternoonSlots.filter(s => s.is_available).length }} {{ $t('bookings.slot_available') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                v-for="slot in afternoonSlots"
                                :key="slot.start_time"
                                :variant="isSelected(slot) ? 'default' : 'outline'"
                                :disabled="!slot.is_available"
                                @click="selectSlot(slot)"
                                class="justify-between"
                            >
                                <span>{{ formatTime(slot.start_time) }}</span>
                                <CheckCircle2 v-if="isSelected(slot)" class="h-4 w-4" />
                                <Badge v-else-if="!slot.is_available" variant="destructive" class="text-xs">
                                    {{ $t('bookings.slot_taken') }}
                                </Badge>
                            </Button>
                        </div>
                    </div>

                    <!-- Evening Slots -->
                    <div v-if="eveningSlots.length > 0">
                        <div class="flex items-center gap-2 mb-2">
                            <Badge variant="outline">{{ $t('bookings.evening') }}</Badge>
                            <span class="text-xs text-muted-foreground">
                                {{ eveningSlots.filter(s => s.is_available).length }} {{ $t('bookings.slot_available') }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <Button
                                v-for="slot in eveningSlots"
                                :key="slot.start_time"
                                :variant="isSelected(slot) ? 'default' : 'outline'"
                                :disabled="!slot.is_available"
                                @click="selectSlot(slot)"
                                class="justify-between"
                            >
                                <span>{{ formatTime(slot.start_time) }}</span>
                                <CheckCircle2 v-if="isSelected(slot)" class="h-4 w-4" />
                                <Badge v-else-if="!slot.is_available" variant="destructive" class="text-xs">
                                    {{ $t('bookings.slot_taken') }}
                                </Badge>
                            </Button>
                        </div>
                    </div>
                </div>
            </ScrollArea>
        </CardContent>
    </Card>
</template>
