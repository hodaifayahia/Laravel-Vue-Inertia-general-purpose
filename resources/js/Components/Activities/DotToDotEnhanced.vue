<script setup>
import { ref, computed, onMounted } from 'vue';
import { Circle, CheckCircle2 } from 'lucide-vue-next';

const props = defineProps({
    item: {
        type: Object,
        required: true,
    },
});

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
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-6">
            <Circle class="w-12 h-12 mx-auto mb-4 text-indigo-600 dark:text-indigo-400" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ item.prompt_text }}
            </h3>
            <p class="text-lg text-gray-600 dark:text-gray-400 mt-2">
                Next: Tap number 
                <span class="font-bold text-indigo-600 dark:text-indigo-400 text-2xl">
                    {{ dots[nextDotIndex]?.number }}
                </span>
            </p>
        </div>

        <!-- Game Board -->
        <div class="relative w-full h-[500px] bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-2xl mb-6 border-4 border-indigo-300 dark:border-indigo-700 shadow-2xl overflow-hidden">
            <!-- Lines connecting dots -->
            <svg class="absolute inset-0 w-full h-full pointer-events-none">
                <line
                    v-for="(line, idx) in lines"
                    :key="'line-' + idx"
                    :x1="line.x1"
                    :y1="line.y1"
                    :x2="line.x2"
                    :y2="line.y2"
                    stroke="#4F46E5"
                    stroke-width="4"
                    stroke-linecap="round"
                    class="animate-draw"
                />
            </svg>

            <!-- Dots -->
            <button
                v-for="dot in dots"
                :key="dot.number"
                @click="handleDotClick(dot.number)"
                :style="getDotStyle(dot)"
                :disabled="showFeedback"
                :class="[
                    'absolute -translate-x-1/2 -translate-y-1/2 w-16 h-16 rounded-full font-bold text-xl text-white transition-all duration-300 shadow-lg transform',
                    dot.number === dots[nextDotIndex]?.number
                        ? 'bg-gradient-to-br from-green-500 to-emerald-600 scale-125 animate-pulse ring-4 ring-green-400 z-10'
                        : dot.number < dots[nextDotIndex]?.number
                        ? 'bg-gradient-to-br from-indigo-600 to-purple-600 scale-90'
                        : 'bg-gray-400 hover:bg-gray-500 hover:scale-110',
                ]"
            >
                {{ dot.number }}
            </button>
        </div>

        <!-- Progress -->
        <div class="mb-6">
            <div class="flex justify-between mb-2">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Progress</span>
                <span class="text-sm font-medium text-indigo-600 dark:text-indigo-400">
                    {{ nextDotIndex }} / {{ dots.length }}
                </span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                <div
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 h-3 rounded-full transition-all duration-300"
                    :style="{ width: `${(nextDotIndex / dots.length) * 100}%` }"
                ></div>
            </div>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="text-center animate-fade-in">
            <div class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Perfect! Picture complete! 🎨</span>
            </div>
        </div>

        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} points • Connect the dots in numerical order!
        </p>
    </div>
</template>

<style scoped>
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes draw {
    from {
        stroke-dashoffset: 1000;
    }
    to {
        stroke-dashoffset: 0;
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-draw {
    stroke-dasharray: 1000;
    animation: draw 0.5s ease-out forwards;
}
</style>
