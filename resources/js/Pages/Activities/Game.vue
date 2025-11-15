<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head, router, Link, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { ChevronLeft, Trophy, Star } from 'lucide-vue-next';
import * as activitiesRoutes from '@/routes/activities';

// Game Components
import EmojiChoice from '@/Components/Activities/EmojiChoice.vue';
import TextCopyTimed from '@/Components/Activities/TextCopyTimed.vue';
import ShapeCopyCanvas from '@/Components/Activities/ShapeCopyCanvas.vue';
import TracePath from '@/Components/Activities/TracePath.vue';
import DotToDot from '@/Components/Activities/DotToDot.vue';
import FindDifferent from '@/Components/Activities/FindDifferent.vue';
import SimplePuzzle from '@/Components/Activities/SimplePuzzle.vue';
import WhatsMissing from '@/Components/Activities/WhatsMissing.vue';
import ListenAndType from '@/Components/Activities/ListenAndType.vue';
import UnscrambleWord from '@/Components/Activities/UnscrambleWord.vue';

const props = defineProps({
    activityId: {
        type: [String, Number],
        required: true,
    },
});

const page = usePage();

const activity = ref(null);
const attempt = ref(null);
const items = ref([]);
const currentItemIndex = ref(0);
const loading = ref(true);
const guestSessionId = ref(null);
const totalPoints = ref(0);
const currentLanguage = ref('en');
const showNextButton = ref(false);
const lastResultData = ref(null);

// Language translations
const translations = {
    en: {
        backToActivities: 'Back to Activities',
        task: 'Task',
        of: 'of',
        complete: 'complete',
        totalPoints: 'Total Points',
        nextTask: 'Next Task',
        finish: 'Finish Activity',
        greatJob: 'Great job! You completed all activities!',
    },
    fr: {
        backToActivities: 'Retour aux activités',
        task: 'Tâche',
        of: 'de',
        complete: 'terminé',
        totalPoints: 'Points totaux',
        nextTask: 'Tâche suivante',
        finish: 'Terminer l\'activité',
        greatJob: 'Excellent travail ! Vous avez terminé toutes les activités !',
    },
    ar: {
        backToActivities: 'العودة إلى الأنشطة',
        task: 'مهمة',
        of: 'من',
        complete: 'مكتمل',
        totalPoints: 'مجموع النقاط',
        nextTask: 'المهمة التالية',
        finish: 'إنهاء النشاط',
        greatJob: 'عمل رائع! لقد أكملت جميع الأنشطة!',
    },
};

const t = computed(() => translations[currentLanguage.value]);

const currentItem = computed(() => items.value[currentItemIndex.value] || null);
const progress = computed(() => {
    if (items.value.length === 0) return 0;
    return ((currentItemIndex.value / items.value.length) * 100).toFixed(0);
});

onMounted(async () => {
    await loadActivity();
    await startActivity();
});

const loadActivity = async () => {
    try {
        const response = await axios.get(`/api/activities/${props.activityId}`);
        activity.value = response.data;
    } catch (error) {
        console.error('Error loading activity:', error);
    }
};

const startActivity = async () => {
    try {
        // Generate or retrieve guest session ID
        guestSessionId.value = localStorage.getItem('guest_session_id') || 
            `guest_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        localStorage.setItem('guest_session_id', guestSessionId.value);

        const response = await axios.post(`/api/activities/${props.activityId}/start`, {
            child_id: null,
            guest_session_id: guestSessionId.value,
        });
        
        attempt.value = response.data;
        await loadItems();
    } catch (error) {
        console.error('Error starting activity:', error);
    } finally {
        loading.value = false;
    }
};

const loadItems = async () => {
    try {
        const response = await axios.get(`/api/activities/attempts/${attempt.value.id}/items`, {
            headers: {
                'X-Guest-Session-Id': guestSessionId.value,
            },
        });
        items.value = response.data;
    } catch (error) {
        console.error('Error loading items:', error);
    }
};

const handleItemComplete = async (resultData) => {
    try {
        lastResultData.value = resultData;
        
        // Add points earned to total
        if (resultData.points_earned) {
            totalPoints.value += resultData.points_earned;
        }
        
        await axios.post(`/api/activities/attempts/${attempt.value.id}/submit`, {
            activity_item_id: currentItem.value.id,
            result_data: resultData,
            guest_session_id: guestSessionId.value,
        });

        // Show next button instead of auto-advancing
        showNextButton.value = true;
    } catch (error) {
        console.error('Error submitting result:', error);
    }
};

const goToNextTask = () => {
    showNextButton.value = false;
    lastResultData.value = null;
    
    // Move to next item or complete
    if (currentItemIndex.value < items.value.length - 1) {
        currentItemIndex.value++;
    } else {
        completeActivity();
    }
};

const completeActivity = async () => {
    try {
        await axios.post(`/api/activities/attempts/${attempt.value.id}/complete`, {
            guest_session_id: guestSessionId.value,
        });
        
        // Show completion message and redirect
        alert(t.value.greatJob + ` ${totalPoints.value} ${t.value.totalPoints}!`);
        router.visit(activitiesRoutes.index().url);
    } catch (error) {
        console.error('Error completing activity:', error);
    }
};

const changeLanguage = (lang) => {
    currentLanguage.value = lang;
    localStorage.setItem('activity_language', lang);
};
</script>

<template>
    <Head :title="activity?.title || 'Activity Game'" />
    
    <AppLayout>
        <div class="py-12 bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-800 min-h-screen">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-16 w-16 border-4 border-indigo-200 dark:border-indigo-900 border-t-4 border-t-indigo-600 dark:border-t-indigo-400"></div>
                </div>

                <!-- Game Container -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                    <!-- Progress Bar -->
                    <div class="bg-gray-200 dark:bg-gray-700 h-3">
                        <div
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 h-3 transition-all duration-500"
                            :style="{ width: `${progress}%` }"
                        ></div>
                    </div>

                    <!-- Header -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/30 dark:to-purple-900/30">
                        <div class="flex items-center justify-between mb-4">
                            <Link 
                                :href="activitiesRoutes.index().url"
                                class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 transition-colors"
                            >
                                <ChevronLeft class="w-5 h-5" />
                                {{ t.backToActivities }}
                            </Link>
                            
                            <!-- Language Selector -->
                            <div class="flex items-center gap-2">
                                <button
                                    v-for="lang in ['en', 'fr', 'ar']"
                                    :key="lang"
                                    @click="changeLanguage(lang)"
                                    :class="[
                                        'px-3 py-1 rounded-lg text-sm font-semibold transition-all',
                                        currentLanguage === lang
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600'
                                    ]"
                                >
                                    {{ lang.toUpperCase() }}
                                </button>
                            </div>
                        </div>
                        
                        <!-- Points Display -->
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ activity.title }}
                            </h2>
                            <div class="flex items-center gap-3 bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-6 py-2 rounded-full shadow-lg">
                                <Trophy class="w-6 h-6" />
                                <span class="text-xl font-bold">{{ totalPoints }}</span>
                                <Star class="w-5 h-5" />
                            </div>
                        </div>
                        
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                            <span class="font-semibold">{{ t.task }} {{ currentItemIndex + 1 }} {{ t.of }} {{ items.length }}</span>
                            <span class="text-gray-500 dark:text-gray-500 ml-2">({{ progress }}% {{ t.complete }})</span>
                        </p>
                    </div>

                    <!-- Game Content -->
                    <div class="p-6 min-h-[500px] bg-white dark:bg-gray-800">
                        <!-- Emoji Choice -->
                        <EmojiChoice
                            v-if="currentItem?.item_type === 'emoji_choice' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Text Copy Timed -->
                        <TextCopyTimed
                            v-else-if="currentItem?.item_type === 'text_copy_timed' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Shape Copy Canvas -->
                        <ShapeCopyCanvas
                            v-else-if="currentItem?.item_type === 'shape_copy_canvas' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Trace Path -->
                        <TracePath
                            v-else-if="currentItem?.item_type === 'trace_the_path' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Dot to Dot -->
                        <DotToDot
                            v-else-if="currentItem?.item_type === 'dot_to_dot' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Find Different -->
                        <FindDifferent
                            v-else-if="currentItem?.item_type === 'find_the_different_one' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Simple Puzzle -->
                        <SimplePuzzle
                            v-else-if="currentItem?.item_type === 'simple_puzzle_drag' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- What's Missing -->
                        <WhatsMissing
                            v-else-if="currentItem?.item_type === 'whats_missing' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Listen and Type -->
                        <ListenAndType
                            v-else-if="currentItem?.item_type === 'listen_and_type' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Unscramble Word -->
                        <UnscrambleWord
                            v-else-if="currentItem?.item_type === 'unscramble_the_word' && !showNextButton"
                            :item="currentItem"
                            :language="currentLanguage"
                            @complete="handleItemComplete"
                        />

                        <!-- Next Button Screen -->
                        <div v-if="showNextButton" class="text-center py-16">
                            <div class="mb-8 animate-bounce">
                                <div class="inline-flex items-center gap-3 bg-gradient-to-r from-green-400 to-emerald-500 text-white px-10 py-6 rounded-3xl shadow-2xl">
                                    <Trophy class="w-12 h-12" />
                                    <div class="text-left">
                                        <p class="text-lg font-semibold">{{ lastResultData?.is_correct !== false ? 'Perfect!' : 'Good Try!' }}</p>
                                        <p class="text-3xl font-bold">+{{ lastResultData?.points_earned || currentItem?.max_points || 0 }} {{ t.totalPoints }}</p>
                                    </div>
                                    <Star class="w-10 h-10" />
                                </div>
                            </div>

                            <button
                                @click="goToNextTask"
                                class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-6 px-16 rounded-2xl font-bold text-2xl transition-all duration-200 transform hover:scale-105 shadow-2xl"
                            >
                                {{ currentItemIndex < items.length - 1 ? t.nextTask : t.finish }} →
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
