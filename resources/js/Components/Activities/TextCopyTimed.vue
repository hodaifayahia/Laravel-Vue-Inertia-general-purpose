<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { Timer, CheckCircle2, Keyboard } from 'lucide-vue-next';
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

const userText = ref('');
const startTime = ref(null);
const endTime = ref(null);
const isFinished = ref(false);
const timeElapsed = ref(0);
const timerInterval = ref(null);

const referenceText = computed(() => {
    if (props.item.options?.text) return props.item.options.text;
    if (props.item.content_data?.text) return props.item.content_data.text;
    return 'Type this sample text';
});

const timeLimit = computed(() => props.item.time_limit_seconds || 60);

const accuracy = computed(() => {
    if (!userText.value) return 100;
    const reference = referenceText.value.toLowerCase();
    const typed = userText.value.toLowerCase();
    let correct = 0;
    for (let i = 0; i < Math.min(reference.length, typed.length); i++) {
        if (reference[i] === typed[i]) correct++;
    }
    return Math.round((correct / reference.length) * 100);
});

const wpm = computed(() => {
    const seconds = timeElapsed.value / 1000;
    const words = userText.value.trim().split(/\s+/).length;
    return Math.round((words / seconds) * 60) || 0;
});

onMounted(() => {
    startTime.value = Date.now();
    timerInterval.value = setInterval(() => {
        timeElapsed.value = Date.now() - startTime.value;
    }, 100);
});

watch(isFinished, (finished) => {
    if (finished && timerInterval.value) {
        clearInterval(timerInterval.value);
    }
});

const calculateErrors = () => {
    const reference = referenceText.value.toLowerCase().trim();
    const typed = userText.value.toLowerCase().trim();
    
    let errors = 0;
    const maxLength = Math.max(reference.length, typed.length);
    
    for (let i = 0; i < maxLength; i++) {
        if (reference[i] !== typed[i]) {
            errors++;
        }
    }
    
    return errors;
};

const handleComplete = () => {
    if (isFinished.value) return;
    
    endTime.value = Date.now();
    isFinished.value = true;
    
    const timeTaken = endTime.value - startTime.value;
    const errors = calculateErrors();
    
    setTimeout(() => {
        emit('complete', {
            typed_text: userText.value,
            reference_text: referenceText.value,
            errors_count: errors,
            time_taken_ms: timeTaken,
            exceeded_time_limit: timeTaken > (timeLimit.value * 1000),
            accuracy: accuracy.value,
            wpm: wpm.value,
            points_earned: accuracy.value >= 80 ? props.item.max_points : Math.floor(props.item.max_points * 0.7),
        });
    }, 1000);
};
</script>

<template>
    <div class="max-w-3xl mx-auto">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
            {{ item.prompt_text }}
        </h3>

        <!-- Stats Bar -->
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg text-center">
                <Timer class="w-6 h-6 mx-auto mb-2 text-blue-600 dark:text-blue-400" />
                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                    {{ Math.floor(timeElapsed / 1000) }}s
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Time</div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg text-center">
                <Keyboard class="w-6 h-6 mx-auto mb-2 text-green-600 dark:text-green-400" />
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                    {{ wpm }}
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">WPM</div>
            </div>
            <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg text-center">
                <CheckCircle2 class="w-6 h-6 mx-auto mb-2 text-purple-600 dark:text-purple-400" />
                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                    {{ accuracy }}%
                </div>
                <div class="text-xs text-gray-600 dark:text-gray-400">Accuracy</div>
            </div>
        </div>

        <!-- Reference Text -->
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 p-6 rounded-xl mb-6 border-2 border-indigo-200 dark:border-indigo-800">
            <p class="text-xl text-gray-900 dark:text-white font-mono leading-relaxed">
                {{ referenceText }}
            </p>
        </div>

        <!-- Input Area -->
        <div class="mb-6">
            <textarea
                v-model="userText"
                :disabled="isFinished"
                class="w-full p-6 border-2 border-gray-300 dark:border-gray-600 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:bg-gray-800 dark:text-white font-mono text-xl resize-none transition-all"
                rows="5"
                placeholder="Start typing here..."
                autofocus
            ></textarea>
        </div>

        <!-- Complete Button -->
        <button
            @click="handleComplete"
            :disabled="isFinished || !userText.trim()"
            class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:from-gray-400 disabled:to-gray-400 text-white py-4 px-6 rounded-xl font-semibold text-lg transition-all duration-200 transform hover:scale-105 disabled:scale-100 disabled:cursor-not-allowed shadow-lg"
        >
            {{ isFinished ? '✓ Complete!' : 'I\'m Finished!' }}
        </button>

        <p class="mt-6 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} {{ t.points }}
        </p>
    </div>
</template>
