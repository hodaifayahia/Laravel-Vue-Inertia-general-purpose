<script setup>
import { ref, computed } from 'vue';
import { CheckCircle2, XCircle } from 'lucide-vue-next';
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

const emit = defineEmits(['complete']);

const selectedOption = ref(null);
const showFeedback = ref(false);

const t = computed(() => useGameTranslation(props.language));

// Check if this is a color recognition task
const displayColor = computed(() => props.item.content_data?.display_color);
const displayShape = computed(() => props.item.content_data?.display_shape || 'circle');

// Check if there's a visual question (like math with emojis or counting)
const visualQuestion = computed(() => props.item.content_data?.visual);
const questionText = computed(() => props.item.content_data?.question);
const objectsToCount = computed(() => props.item.content_data?.objects);

// Parse options from item
const emojis = computed(() => {
    if (props.item.options?.emojis) {
        return props.item.options.emojis;
    }
    // Default emojis if not specified
    return ['😊', '😢', '😡', '😴', '🤔', '😍'];
});

const textOptions = computed(() => {
    // Use translations if available
    const translations = props.item.options?.translations;
    if (translations) {
        const langKey = props.language === 'en' ? 'en' : props.language === 'fr' ? 'fr' : 'ar';
        return translations[langKey] || props.item.options.options;
    }
    
    if (props.item.options?.options) {
        return props.item.options.options;
    }
    if (props.item.options?.colors) {
        return props.item.options.colors;
    }
    return null;
});

const correctAnswer = computed(() => props.item.options?.correct);

const isCorrect = computed(() => {
    if (!correctAnswer.value || !selectedOption.value) return true;
    return selectedOption.value === correctAnswer.value;
});

const handleSelection = (option) => {
    selectedOption.value = option;
    showFeedback.value = true;
    
    // Emit result after short delay for visual feedback
    setTimeout(() => {
        const correct = correctAnswer.value ? option === correctAnswer.value : true;
        emit('complete', {
            selected_option: option,
            is_correct: correct,
            points_earned: correct ? props.item.max_points : Math.floor(props.item.max_points / 2),
        });
    }, 1200);
};
</script>

<template>
    <div class="text-center max-w-3xl mx-auto">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">
            {{ item.prompt_text }}
        </h3>

        <!-- Color Display -->
        <div v-if="displayColor" class="flex justify-center mb-8">
            <div 
                :class="[
                    'w-48 h-48 shadow-2xl border-4 border-gray-300 dark:border-gray-600',
                    displayShape === 'square' ? 'rounded-2xl' : 'rounded-full'
                ]"
                :style="{ backgroundColor: displayColor }"
            ></div>
        </div>

        <!-- Visual Question (Math/Counting with emojis) -->
        <div v-if="visualQuestion || objectsToCount" class="mb-8">
            <div v-if="questionText" class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">
                {{ questionText }}
            </div>
            <div class="text-6xl mb-6 leading-relaxed">
                {{ visualQuestion || objectsToCount }}
            </div>
        </div>

        <!-- Emoji Options -->
        <div v-if="emojis && !textOptions" class="flex flex-wrap justify-center gap-6 mt-10">
            <button
                v-for="(emoji, index) in emojis"
                :key="index"
                @click="handleSelection(emoji)"
                :disabled="showFeedback"
                :class="[
                    'text-7xl p-8 rounded-2xl transition-all duration-300 transform hover:scale-110 shadow-lg',
                    selectedOption === emoji 
                        ? 'bg-gradient-to-br from-indigo-500 to-purple-600 ring-4 ring-indigo-400 scale-110' 
                        : 'bg-white dark:bg-gray-700 hover:shadow-xl',
                    showFeedback && selectedOption !== emoji ? 'opacity-50 scale-95' : '',
                ]"
            >
                {{ emoji }}
            </button>
        </div>

        <!-- Text/Color Options -->
        <div v-else-if="textOptions" class="grid grid-cols-2 gap-4 max-w-xl mx-auto mt-10">
            <button
                v-for="(option, index) in textOptions"
                :key="index"
                @click="handleSelection(props.item.options.options ? props.item.options.options[index] : option)"
                :disabled="showFeedback"
                :class="[
                    'py-6 px-8 rounded-xl text-xl font-semibold transition-all duration-300 transform hover:scale-105',
                    selectedOption === (props.item.options.options ? props.item.options.options[index] : option)
                        ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white ring-4 ring-green-400 scale-105' 
                        : 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white border-2 border-gray-300 dark:border-gray-600 hover:border-indigo-500',
                    showFeedback && selectedOption !== (props.item.options.options ? props.item.options.options[index] : option) ? 'opacity-50' : '',
                ]"
            >
                {{ option }}
            </button>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="mt-8 animate-fade-in">
            <div v-if="isCorrect" class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-6 py-3 rounded-full">
                <CheckCircle2 class="w-6 h-6" />
                <span class="font-semibold">{{ t.excellent }} 🎉</span>
            </div>
            <div v-else class="inline-flex items-center gap-3 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 px-6 py-3 rounded-full">
                <XCircle class="w-6 h-6" />
                <span class="font-semibold">{{ t.goodTry }}</span>
            </div>
        </div>

        <p class="mt-10 text-sm text-gray-500 dark:text-gray-400">
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
