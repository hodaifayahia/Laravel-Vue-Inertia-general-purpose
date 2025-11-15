<script setup>
import { ref, onMounted, computed } from 'vue';
import { Brush, Eraser, RotateCcw, CheckCircle2, Palette } from 'lucide-vue-next';
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

const canvas = ref(null);
const ctx = ref(null);
const isDrawing = ref(false);
const startTime = ref(null);
const endTime = ref(null);
const currentColor = ref('#4F46E5');
const lineWidth = ref(4);
const showFeedback = ref(false);

const colors = ['#4F46E5', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6', '#EC4899', '#000000'];

onMounted(() => {
    if (canvas.value) {
        ctx.value = canvas.value.getContext('2d');
        ctx.value.strokeStyle = currentColor.value;
        ctx.value.lineWidth = lineWidth.value;
        ctx.value.lineCap = 'round';
        ctx.value.lineJoin = 'round';
    }
    startTime.value = Date.now();
});

const getCoordinates = (e) => {
    const rect = canvas.value.getBoundingClientRect();
    const scaleX = canvas.value.width / rect.width;
    const scaleY = canvas.value.height / rect.height;
    
    if (e.touches && e.touches[0]) {
        return {
            x: (e.touches[0].clientX - rect.left) * scaleX,
            y: (e.touches[0].clientY - rect.top) * scaleY,
        };
    }
    return {
        x: (e.clientX - rect.left) * scaleX,
        y: (e.clientY - rect.top) * scaleY,
    };
};

const startDrawing = (e) => {
    e.preventDefault();
    isDrawing.value = true;
    const coords = getCoordinates(e);
    ctx.value.beginPath();
    ctx.value.moveTo(coords.x, coords.y);
};

const draw = (e) => {
    e.preventDefault();
    if (!isDrawing.value) return;
    const coords = getCoordinates(e);
    ctx.value.lineTo(coords.x, coords.y);
    ctx.value.stroke();
};

const stopDrawing = (e) => {
    e.preventDefault();
    isDrawing.value = false;
};

const clearCanvas = () => {
    ctx.value.clearRect(0, 0, canvas.value.width, canvas.value.height);
};

const changeColor = (color) => {
    currentColor.value = color;
    ctx.value.strokeStyle = color;
};

const changeLineWidth = (width) => {
    lineWidth.value = width;
    ctx.value.lineWidth = width;
};

const handleComplete = () => {
    if (showFeedback.value) return;
    
    endTime.value = Date.now();
    showFeedback.value = true;
    const drawingData = canvas.value.toDataURL('image/png');
    
    setTimeout(() => {
        emit('complete', {
            drawing_svg: drawingData,
            time_taken_ms: endTime.value - startTime.value,
            points_earned: props.item.max_points,
        });
    }, 1500);
};
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
            {{ item.prompt_text }}
        </h3>

        <!-- Reference Shape -->
        <div v-if="item.content_data?.reference_image_url" class="mb-6 text-center">
            <div class="inline-block bg-gray-100 dark:bg-gray-800 p-4 rounded-xl border-2 border-indigo-300 dark:border-indigo-700">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Copy this shape:</p>
                <img
                    :src="item.content_data.reference_image_url"
                    alt="Shape to copy"
                    class="max-w-xs"
                />
            </div>
        </div>

        <!-- Color Palette -->
        <div class="mb-4 flex items-center justify-center gap-3 flex-wrap">
            <Palette class="w-5 h-5 text-gray-600 dark:text-gray-400" />
            <button
                v-for="color in colors"
                :key="color"
                @click="changeColor(color)"
                :class="[
                    'w-10 h-10 rounded-full border-4 transition-all duration-200 transform hover:scale-110',
                    currentColor === color ? 'border-white ring-4 ring-indigo-500 scale-110' : 'border-gray-300',
                ]"
                :style="{ backgroundColor: color }"
            ></button>
            
            <!-- Line Width -->
            <div class="flex items-center gap-2 ml-4">
                <Brush class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                <button
                    v-for="width in [2, 4, 8]"
                    :key="width"
                    @click="changeLineWidth(width)"
                    :class="[
                        'px-3 py-1 rounded-lg text-sm font-medium transition-all',
                        lineWidth === width
                            ? 'bg-indigo-600 text-white'
                            : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300',
                    ]"
                >
                    {{ width }}px
                </button>
            </div>
        </div>

        <!-- Drawing Canvas -->
        <div class="mb-6 text-center">
            <canvas
                ref="canvas"
                width="800"
                height="500"
                @mousedown="startDrawing"
                @mousemove="draw"
                @mouseup="stopDrawing"
                @mouseleave="stopDrawing"
                @touchstart="startDrawing"
                @touchmove="draw"
                @touchend="stopDrawing"
                class="border-4 border-indigo-500 rounded-2xl cursor-crosshair bg-white inline-block max-w-full shadow-2xl"
                style="touch-action: none;"
            ></canvas>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="mb-6 text-center animate-fade-in">
            <div class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Great drawing! 🎨</span>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex gap-4">
            <button
                @click="clearCanvas"
                :disabled="showFeedback"
                class="flex-1 bg-gray-500 hover:bg-gray-600 disabled:bg-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 flex items-center justify-center gap-2"
            >
                <RotateCcw class="w-5 h-5" />
                {{ t.clear }}
            </button>
            <button
                @click="handleComplete"
                :disabled="showFeedback"
                class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 flex items-center justify-center gap-2"
            >
                <CheckCircle2 class="w-5 h-5" />
                {{ t.finished }}
            </button>
        </div>

        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} points • Draw on the canvas using your mouse or finger!
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

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}
</style>
