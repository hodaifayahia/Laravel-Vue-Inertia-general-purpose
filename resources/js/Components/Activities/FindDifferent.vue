<script setup>
import { ref, computed } from 'vue';
import { Eye, CheckCircle2, XCircle } from 'lucide-vue-next';
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

const selectedItem = ref(null);
const showFeedback = ref(false);

// Generate sample items if no images provided
const items = computed(() => {
    // Use custom items from content_data if available
    if (props.item.content_data?.items && props.item.content_data.items.length > 0) {
        const itemsData = props.item.content_data.items;
        const correctIndex = props.item.options?.correct_index || 3;
        return itemsData.map((item, idx) => ({
            id: idx,
            type: 'emoji',
            content: item,
            isDifferent: idx === correctIndex,
        }));
    }
    
    if (props.item.options?.images && props.item.options.images.length > 0) {
        return props.item.options.images.map((img, idx) => ({
            id: idx,
            type: 'image',
            content: img,
        }));
    }
    
    // Generate default shape/emoji items
    const shapes = ['🔵', '🔵', '🔵', '🔴', '🔵', '🔵'];
    const correctIndex = props.item.options?.correct_index || 3;
    
    return shapes.map((shape, idx) => ({
        id: idx,
        type: 'emoji',
        content: shape,
        isDifferent: idx === correctIndex,
    }));
});

const correctIndex = computed(() => {
    return props.item.options?.correct_index || 
           props.item.options?.different_one || 
           items.value.findIndex(item => item.isDifferent);
});

const isCorrect = computed(() => selectedItem.value === correctIndex.value);

const handleSelection = (index) => {
    selectedItem.value = index;
    showFeedback.value = true;
    
    setTimeout(() => {
        const correct = index === correctIndex.value;
        emit('complete', {
            selected_item: index,
            correct_item: correctIndex.value,
            is_correct: correct,
            points_earned: correct ? props.item.max_points : Math.floor(props.item.max_points * 0.3),
        });
    }, 1500);
};
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <Eye class="w-12 h-12 mx-auto mb-4 text-indigo-600 dark:text-indigo-400" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ item.prompt_text }}
            </h3>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
            <button
                v-for="(gameItem, index) in items"
                :key="index"
                @click="handleSelection(index)"
                :disabled="showFeedback"
                :class="[
                    'p-8 rounded-2xl border-4 transition-all duration-300 transform hover:scale-105 shadow-lg',
                    selectedItem === index
                        ? isCorrect 
                            ? 'border-green-500 bg-green-50 dark:bg-green-900/20 scale-105 ring-4 ring-green-400'
                            : 'border-red-500 bg-red-50 dark:bg-red-900/20 scale-105 ring-4 ring-red-400'
                        : 'border-gray-300 dark:border-gray-600 hover:border-indigo-500 bg-white dark:bg-gray-800',
                    showFeedback && selectedItem !== index ? 'opacity-50' : '',
                ]"
            >
                <div v-if="gameItem.type === 'image'" class="w-full h-40 flex items-center justify-center">
                    <img
                        :src="gameItem.content"
                        alt="Option"
                        class="max-w-full max-h-full object-contain"
                    />
                </div>
                <div v-else class="text-7xl text-center">
                    {{ gameItem.content }}
                </div>
            </button>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="text-center animate-fade-in">
            <div v-if="isCorrect" class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Excellent! You found the different one! 🎯</span>
            </div>
            <div v-else class="inline-flex items-center gap-3 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 px-8 py-4 rounded-full text-lg font-semibold">
                <XCircle class="w-7 h-7" />
                <span>Not quite! Keep practicing!</span>
            </div>
        </div>

        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} {{ t.points }}
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
