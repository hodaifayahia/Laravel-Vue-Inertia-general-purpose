<script setup>
import { ref, computed } from 'vue';
import { Circle, CheckCircle2 } from 'lucide-vue-next';
import { useGameTranslation } from '@/composables/useGameTranslation';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
    language: {
        type: String,
        default: 'en',
    },
});

const t = computed(() => useGameTranslation(props.language));

const emit = defineEmits(['complete']);

const tappedSequence = ref([]);
const wrongTaps = ref(0);
const showFeedback = ref(false);

// Generate dots in a pattern if none provided
const generateDots = () => {
    const count = 10;
    const dots = [];
    const centerX = 300;
    const centerY = 250;
    const radius = 150;
    
    for (let i = 0; i < count; i++) {
        const angle = (i / count) * 2 * Math.PI - Math.PI / 2;
        dots.push({
            number: i + 1,
            x: centerX + radius * Math.cos(angle),
            y: centerY + radius * Math.sin(angle),
        });
    }
    return dots;
};

const dots = computed(() => props.item.content_data?.dots || generateDots());
const nextDotIndex = ref(0);
const lines = ref([]);

const handleDotClick = (dotNumber) => {
    const expectedDot = dots.value[nextDotIndex.value];
    
    if (dotNumber === expectedDot.number) {
        // Correct dot clicked
        tappedSequence.value.push(dotNumber);
        
        // Draw line to previous dot
        if (nextDotIndex.value > 0) {
            const prevDot = dots.value[nextDotIndex.value - 1];
            lines.value.push({
                x1: prevDot.x,
                y1: prevDot.y,
                x2: expectedDot.x,
                y2: expectedDot.y,
            });
        }
        
        nextDotIndex.value++;
        
        // Check if complete
        if (nextDotIndex.value >= dots.value.length) {
            handleComplete();
        }
    } else {
        wrongTaps.value++;
    }
};

const handleComplete = () => {
    showFeedback.value = true;
    
    setTimeout(() => {
        emit('complete', {
            tapped_sequence: tappedSequence.value,
            wrong_taps: wrongTaps.value,
            accuracy: Math.round(((dots.value.length) / (dots.value.length + wrongTaps.value)) * 100),
            points_earned: wrongTaps.value <= 2 ? props.item.max_points : Math.floor(props.item.max_points * 0.7),
        });
    }, 1500);
};

const getDotStyle = (dot) => {
    return {
        left: `${dot.x}px`,
        top: `${dot.y}px`,
    };
};
</script>

<template>
    <div class="relative">
        <!-- Header with icon and title -->
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl">
                <Circle :size="24" class="text-white" />
            </div>
            <div>
                <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ item.prompt_text }}
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ t.progress }}: {{ nextDotIndex }}/{{ dots.length }}
                </p>
            </div>
            <div class="ml-auto text-right">
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ t.points }}</div>
                <div class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                    {{ item.max_points }}
                </div>
            </div>
        </div>

        <!-- Progress bar -->
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mb-6">
            <div 
                class="bg-gradient-to-r from-purple-500 to-pink-600 h-2 rounded-full transition-all duration-300"
                :style="{ width: `${(nextDotIndex / dots.length) * 100}%` }"
            ></div>
        </div>

        <!-- Dot-to-Dot Canvas -->
        <div class="relative w-full h-[500px] bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-800 dark:to-gray-700 rounded-2xl border-2 border-purple-200 dark:border-purple-800 mb-6 overflow-hidden">
            <!-- SVG for lines -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none">
                <line
                    v-for="(line, index) in lines"
                    :key="index"
                    :x1="line.x1"
                    :y1="line.y1"
                    :x2="line.x2"
                    :y2="line.y2"
                    stroke="url(#lineGradient)"
                    stroke-width="3"
                    class="animate-draw"
                />
                <defs>
                    <linearGradient id="lineGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:rgb(168,85,247);stop-opacity:1" />
                        <stop offset="100%" style="stop-color:rgb(219,39,119);stop-opacity:1" />
                    </linearGradient>
                </defs>
            </svg>

            <!-- Dots -->
            <button
                v-for="dot in dots"
                :key="dot.number"
                @click="handleDotClick(dot.number)"
                :style="getDotStyle(dot)"
                :class="[
                    'absolute w-12 h-12 rounded-full font-bold text-white transition-all duration-300 transform hover:scale-110 shadow-lg',
                    'flex items-center justify-center',
                    tappedSequence.includes(dot.number)
                        ? 'bg-gradient-to-br from-green-400 to-green-600 scale-90'
                        : dot.number === dots[nextDotIndex]?.number
                        ? 'bg-gradient-to-br from-purple-500 to-pink-600 animate-bounce'
                        : 'bg-gradient-to-br from-gray-400 to-gray-500'
                ]"
                :disabled="tappedSequence.includes(dot.number)"
            >
                {{ dot.number }}
            </button>
        </div>

        <!-- Next dot hint -->
        <div v-if="nextDotIndex < dots.length" class="text-center mb-4">
            <div class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-100 to-pink-100 dark:from-purple-900/30 dark:to-pink-900/30 rounded-full">
                <Circle :size="20" class="text-purple-600 dark:text-purple-400 animate-pulse" />
                <p class="text-lg font-medium text-gray-700 dark:text-gray-300">
                    {{ t.next }}: <span class="font-bold text-purple-600 dark:text-purple-400">{{ dots[nextDotIndex]?.number }}</span>
                </p>
            </div>
        </div>

        <!-- Wrong taps indicator -->
        <div v-if="wrongTaps > 0" class="text-center">
            <p class="text-sm text-red-500 dark:text-red-400">
                {{ t.mistakes }}: {{ wrongTaps }}
            </p>
        </div>

        <!-- Completion feedback -->
        <div
            v-if="showFeedback"
            class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 animate-fadeIn"
        >
            <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl text-center animate-scaleIn">
                <CheckCircle2 :size="64" class="text-green-500 mx-auto mb-4 animate-bounce" />
                <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    {{ t.perfectWork }}
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    {{ t.accuracy }}: {{ Math.round(((dots.length) / (dots.length + wrongTaps)) * 100) }}%
                </p>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes draw {
    from {
        stroke-dashoffset: 1000;
    }
    to {
        stroke-dashoffset: 0;
    }
}

.animate-draw {
    stroke-dasharray: 1000;
    animation: draw 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0.5);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.animate-scaleIn {
    animation: scaleIn 0.3s ease-out;
}
</style>
