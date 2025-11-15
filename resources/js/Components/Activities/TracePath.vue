<script setup>
import { ref, onMounted, computed } from 'vue';
import { PenTool, CheckCircle2, RotateCcw } from 'lucide-vue-next';
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
const isTracing = ref(false);
const pathPoints = ref([]);
const startTime = ref(null);
const showFeedback = ref(false);

// Generate a simple path if none provided
const generatePath = () => {
    const letter = props.item.options?.letter || 'A';
    // Simple path for demonstration
    return [
        { x: 100, y: 300 },
        { x: 250, y: 100 },
        { x: 400, y: 300 },
        { x: 250, y: 200 },
        { x: 350, y: 200 },
    ];
};

onMounted(() => {
    if (canvas.value) {
        ctx.value = canvas.value.getContext('2d');
        drawReferencePath();
    }
    startTime.value = Date.now();
});

const drawReferencePath = () => {
    const path = props.item.content_data?.path_coordinates || generatePath();
    if (path.length < 2) return;
    
    // Draw dotted guide path
    ctx.value.strokeStyle = '#D1D5DB';
    ctx.value.lineWidth = 12;
    ctx.value.lineCap = 'round';
    ctx.value.setLineDash([15, 10]);
    
    ctx.value.beginPath();
    ctx.value.moveTo(path[0].x, path[0].y);
    for (let i = 1; i < path.length; i++) {
        ctx.value.lineTo(path[i].x, path[i].y);
    }
    ctx.value.stroke();
    ctx.value.setLineDash([]);
    
    // Draw start point
    ctx.value.fillStyle = '#10B981';
    ctx.value.beginPath();
    ctx.value.arc(path[0].x, path[0].y, 15, 0, 2 * Math.PI);
    ctx.value.fill();
    
    // Draw end point
    ctx.value.fillStyle = '#EF4444';
    ctx.value.beginPath();
    ctx.value.arc(path[path.length - 1].x, path[path.length - 1].y, 15, 0, 2 * Math.PI);
    ctx.value.fill();
};

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

const startTracing = (e) => {
    e.preventDefault();
    isTracing.value = true;
    const coords = getCoordinates(e);
    
    pathPoints.value.push(coords);
    
    ctx.value.strokeStyle = '#4F46E5';
    ctx.value.lineWidth = 6;
    ctx.value.lineCap = 'round';
    ctx.value.lineJoin = 'round';
    ctx.value.beginPath();
    ctx.value.moveTo(coords.x, coords.y);
};

const trace = (e) => {
    e.preventDefault();
    if (!isTracing.value) return;
    const coords = getCoordinates(e);
    
    pathPoints.value.push(coords);
    ctx.value.lineTo(coords.x, coords.y);
    ctx.value.stroke();
};

const stopTracing = (e) => {
    e.preventDefault();
    isTracing.value = false;
};

const resetCanvas = () => {
    ctx.value.clearRect(0, 0, canvas.value.width, canvas.value.height);
    pathPoints.value = [];
    drawReferencePath();
};

const handleComplete = () => {
    if (showFeedback.value) return;
    showFeedback.value = true;
    const endTime = Date.now();
    
    setTimeout(() => {
        emit('complete', {
            traced_path: pathPoints.value,
            time_taken_ms: endTime - startTime.value,
            points_earned: props.item.max_points,
        });
    }, 1500);
};
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-6">
            <PenTool class="w-12 h-12 mx-auto mb-4 text-indigo-600 dark:text-indigo-400" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ item.prompt_text }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                Follow the dotted line from 🟢 start to 🔴 end
            </p>
        </div>

        <div class="text-center mb-6">
            <canvas
                ref="canvas"
                width="800"
                height="500"
                @mousedown="startTracing"
                @mousemove="trace"
                @mouseup="stopTracing"
                @mouseleave="stopTracing"
                @touchstart="startTracing"
                @touchmove="trace"
                @touchend="stopTracing"
                class="border-4 border-indigo-500 rounded-2xl cursor-crosshair bg-white inline-block max-w-full shadow-2xl"
                style="touch-action: none;"
            ></canvas>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="mb-6 text-center animate-fade-in">
            <div class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Great tracing! ✏️</span>
            </div>
        </div>

        <div class="flex gap-4">
            <button
                @click="resetCanvas"
                :disabled="showFeedback"
                class="flex-1 bg-gray-500 hover:bg-gray-600 disabled:bg-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 flex items-center justify-center gap-2"
            >
                <RotateCcw class="w-5 h-5" />
                {{ t.startOver }}
            </button>
            <button
                @click="handleComplete"
                :disabled="showFeedback || pathPoints.length < 5"
                class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 flex items-center justify-center gap-2"
            >
                <CheckCircle2 class="w-5 h-5" />
                {{ t.finished }}
            </button>
        </div>

        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} points • Trace along the dotted path!
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
