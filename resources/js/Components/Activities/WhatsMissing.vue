<script setup>
import { ref, computed } from 'vue';
import { Search, CheckCircle2, XCircle } from 'lucide-vue-next';
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

const clickPosition = ref(null);
const showFeedback = ref(false);

// Use placeholder image if none provided
const imageUrl = computed(() => {
    return props.item.content_data?.image_url || 'https://via.placeholder.com/600x400?text=Find+What+is+Missing';
});

const missingPosition = computed(() => {
    return props.item.content_data?.missing_position || { x: 300, y: 200 };
});

const isCloseEnough = (click, target) => {
    const distance = Math.sqrt(
        Math.pow(click.x - target.x, 2) + Math.pow(click.y - target.y, 2)
    );
    return distance < 100; // Within 100 pixels
};

const handleImageClick = (e) => {
    if (showFeedback.value) return;
    
    const rect = e.target.getBoundingClientRect();
    const x = e.clientX - rect.left;
    const y = e.clientY - rect.top;
    
    clickPosition.value = { x, y };
    showFeedback.value = true;
    
    const isCorrect = isCloseEnough({ x, y }, missingPosition.value);
    
    setTimeout(() => {
        emit('complete', {
            clicked_position: { x, y },
            missing_item_position: missingPosition.value,
            is_correct: isCorrect,
            points_earned: isCorrect ? props.item.max_points : Math.floor(props.item.max_points * 0.4),
        });
    }, 1500);
};

const isCorrect = computed(() => {
    if (!clickPosition.value) return false;
    return isCloseEnough(clickPosition.value, missingPosition.value);
});
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <Search class="w-12 h-12 mx-auto mb-4 text-indigo-600 dark:text-indigo-400" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ item.prompt_text }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                🔍 Look carefully and click where something is missing!
            </p>
        </div>

        <div class="text-center mb-8 relative inline-block">
            <img
                :src="imageUrl"
                alt="Find what's missing"
                @click="handleImageClick"
                :class="[
                    'rounded-2xl cursor-crosshair border-4 shadow-2xl max-w-full transition-all',
                    showFeedback ? 'border-green-500' : 'border-indigo-500 hover:border-indigo-600',
                ]"
            />
            
            <!-- Click marker -->
            <div
                v-if="clickPosition"
                :style="{
                    position: 'absolute',
                    left: `${clickPosition.x}px`,
                    top: `${clickPosition.y}px`,
                    transform: 'translate(-50%, -50%)',
                }"
                :class="[
                    'w-12 h-12 rounded-full border-4 animate-ping-once',
                    isCorrect ? 'border-green-500 bg-green-500/30' : 'border-red-500 bg-red-500/30',
                ]"
            ></div>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="text-center animate-fade-in">
            <div v-if="isCorrect" class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>You found it! Great job! 🎯</span>
            </div>
            <div v-else class="inline-flex items-center gap-3 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 px-8 py-4 rounded-full text-lg font-semibold">
                <XCircle class="w-7 h-7" />
                <span>Good try! Keep looking!</span>
            </div>
        </div>

        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} points • Click on the area where something is missing
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

@keyframes ping-once {
    0% {
        transform: translate(-50%, -50%) scale(1);
        opacity: 1;
    }
    75%, 100% {
        transform: translate(-50%, -50%) scale(2);
        opacity: 0;
    }
}

.animate-fade-in {
    animation: fade-in 0.3s ease-out;
}

.animate-ping-once {
    animation: ping-once 1s cubic-bezier(0, 0, 0.2, 1);
}
</style>
