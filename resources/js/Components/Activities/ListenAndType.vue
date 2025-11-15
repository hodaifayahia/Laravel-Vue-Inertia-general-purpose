<script setup>
import { ref, computed } from 'vue';
import { Volume2, CheckCircle2, XCircle } from 'lucide-vue-next';
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

const userInput = ref('');
const hasPlayed = ref(false);
const showFeedback = ref(false);
const isPlaying = ref(false);

const correctAnswer = computed(() => {
    return props.item.options?.correct_answer || 
           props.item.options?.correct_spelling || 
           props.item.options?.pattern ||
           'hello';
});

const audioUrl = computed(() => {
    return props.item.content_data?.audio_url;
});

const isCorrect = computed(() => {
    return userInput.value.toLowerCase().trim() === correctAnswer.value.toLowerCase().trim();
});

const playAudio = () => {
    if (audioUrl.value) {
        isPlaying.value = true;
        const audio = new Audio(audioUrl.value);
        audio.play();
        audio.onended = () => {
            isPlaying.value = false;
        };
    } else {
        // Fallback: use speech synthesis
        const utterance = new SpeechSynthesisUtterance(correctAnswer.value);
        utterance.rate = 0.8;
        speechSynthesis.speak(utterance);
    }
    hasPlayed.value = true;
};

const handleComplete = () => {
    if (showFeedback.value) return;
    showFeedback.value = true;
    
    setTimeout(() => {
        emit('complete', {
            typed_text: userInput.value,
            correct_text: correctAnswer.value,
            is_correct: isCorrect.value,
            points_earned: isCorrect.value ? props.item.max_points : Math.floor(props.item.max_points * 0.5),
        });
    }, 1500);
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-8">
            <Volume2 class="w-12 h-12 mx-auto mb-4 text-indigo-600 dark:text-indigo-400" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ item.prompt_text }}
            </h3>
        </div>

        <!-- Audio Player -->
        <div class="text-center mb-10">
            <button
                @click="playAudio"
                :disabled="isPlaying"
                :class="[
                    'p-10 rounded-full transition-all duration-300 transform shadow-2xl',
                    isPlaying
                        ? 'bg-gradient-to-br from-purple-600 to-pink-600 scale-95 animate-pulse'
                        : 'bg-gradient-to-br from-indigo-600 to-purple-600 hover:scale-110 hover:shadow-3xl',
                ]"
            >
                <Volume2 class="w-20 h-20 text-white" />
            </button>
            <p class="mt-6 text-lg text-gray-600 dark:text-gray-400 font-medium">
                {{ hasPlayed ? t.playAgain : t.listen }}
            </p>
        </div>

        <!-- Input Field -->
        <div class="mb-8">
            <input
                v-model="userInput"
                type="text"
                :disabled="showFeedback"
                class="w-full p-6 text-2xl text-center border-4 border-indigo-300 dark:border-indigo-700 rounded-2xl focus:ring-4 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white transition-all font-mono"
                :placeholder="t.typeHere"
                @keyup.enter="handleComplete"
            />
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="mb-8 text-center animate-fade-in">
            <div v-if="isCorrect" class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Perfect! You got it right! 🎉</span>
            </div>
            <div v-else class="inline-flex items-center gap-3 bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 px-8 py-4 rounded-full text-lg font-semibold">
                <XCircle class="w-7 h-7" />
                <span>The answer was: "{{ correctAnswer }}"</span>
            </div>
        </div>

        <!-- Submit Button -->
            <button
                @click="handleComplete"
                :disabled="!userInput.trim() || !hasPlayed || showFeedback"
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white py-5 px-8 rounded-2xl font-semibold text-xl transition-all duration-200 transform hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed flex items-center justify-center gap-3"
            >
                <CheckCircle2 class="w-6 h-6" />
                {{ t.submit }}
            </button>        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} points • Listen carefully and type what you hear!
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
