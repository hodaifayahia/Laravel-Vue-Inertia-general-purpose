<script setup>
import { ref, computed, onMounted } from 'vue';
import { Shuffle, CheckCircle2, RotateCcw } from 'lucide-vue-next';
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

const scrambledLetters = ref([]);
const selectedIndices = ref([]);
const startTime = ref(null);
const showFeedback = ref(false);

const correctWord = computed(() => {
    return props.item.options?.correct_word || 'word';
});

const scrambleWord = (word) => {
    const letters = word.split('');
    for (let i = letters.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [letters[i], letters[j]] = [letters[j], letters[i]];
    }
    return letters;
};

onMounted(() => {
    if (props.item.content_data?.scrambled_word) {
        scrambledLetters.value = props.item.content_data.scrambled_word.split('');
    } else {
        scrambledLetters.value = scrambleWord(correctWord.value.toUpperCase());
    }
    startTime.value = Date.now();
});

const currentWord = computed(() => {
    return selectedIndices.value.map(i => scrambledLetters.value[i]).join('');
});

const isCorrect = computed(() => {
    return currentWord.value.toLowerCase() === correctWord.value.toLowerCase();
});

const handleLetterClick = (index) => {
    if (selectedIndices.value.includes(index)) {
        selectedIndices.value = selectedIndices.value.filter(i => i !== index);
    } else {
        selectedIndices.value.push(index);
    }
};

const clearWord = () => {
    selectedIndices.value = [];
};

const handleComplete = () => {
    showFeedback.value = true;
    const endTime = Date.now();
    
    setTimeout(() => {
        emit('complete', {
            unscrambled_word: currentWord.value,
            correct_word: correctWord.value,
            is_correct: isCorrect.value,
            time_taken_ms: endTime - startTime.value,
            points_earned: isCorrect.value ? props.item.max_points : Math.floor(props.item.max_points * 0.5),
        });
    }, 1500);
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-8 text-center">
            {{ item.prompt_text }}
        </h3>

        <!-- Hint Image -->
        <div v-if="item.content_data?.hint_image_url" class="text-center mb-8">
            <img
                :src="item.content_data.hint_image_url"
                alt="Hint"
                class="inline-block max-w-xs rounded-xl shadow-lg"
            />
        </div>

        <!-- Current Word Display -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-8 rounded-2xl mb-8 text-center border-2 border-indigo-200 dark:border-indigo-800">
            <Shuffle class="w-8 h-8 mx-auto mb-3 text-indigo-600 dark:text-indigo-400" />
            <p class="text-4xl font-bold text-indigo-600 dark:text-indigo-400 min-h-[4rem] tracking-wider">
                {{ currentWord || '_____' }}
            </p>
        </div>

        <!-- Scrambled Letters -->
        <div class="flex flex-wrap justify-center gap-4 mb-8">
            <button
                v-for="(letter, index) in scrambledLetters"
                :key="index"
                @click="handleLetterClick(index)"
                :disabled="showFeedback"
                :class="[
                    'w-20 h-20 text-3xl font-bold rounded-xl transition-all duration-300 transform shadow-lg',
                    selectedIndices.includes(index)
                        ? 'bg-gradient-to-br from-indigo-600 to-purple-600 text-white scale-95 ring-4 ring-indigo-400'
                        : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white border-2 border-gray-300 dark:border-gray-600 hover:scale-110 hover:border-indigo-500'
                ]"
            >
                {{ letter }}
            </button>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="mb-6 text-center animate-fade-in">
            <div v-if="isCorrect" class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Perfect! You unscrambled it! 🎉</span>
            </div>
            <div v-else class="inline-flex items-center gap-3 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 px-8 py-4 rounded-full text-lg font-semibold">
                <span>Good try! The answer was: {{ correctWord }}</span>
            </div>
        </div>

        <!-- Controls -->
        <div class="flex gap-4">
            <button
                @click="clearWord"
                :disabled="showFeedback"
                class="flex-1 bg-gray-500 hover:bg-gray-600 disabled:bg-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 flex items-center justify-center gap-2"
            >
                <RotateCcw class="w-5 h-5" />
                {{ t.clear }}
            </button>
            <button
                @click="handleComplete"
                :disabled="!currentWord || showFeedback"
                class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 flex items-center justify-center gap-2"
            >
                <CheckCircle2 class="w-5 h-5" />
                {{ t.submit }}
            </button>
        </div>

        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400 text-center">
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
