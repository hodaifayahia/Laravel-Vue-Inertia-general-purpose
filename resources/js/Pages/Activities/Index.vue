<script setup>
import { ref, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import axios from 'axios';
import { Gamepad2 } from 'lucide-vue-next';
import * as activitiesRoutes from '@/routes/activities';

const activities = ref([]);
const loading = ref(true);

const fetchActivities = async () => {
    try {
        const response = await axios.get('/api/activities');
        activities.value = response.data;
    } catch (error) {
        console.error('Error fetching activities:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchActivities();
});

const getDifficultyColor = (level) => {
    const colors = {
        beginner: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300',
        intermediate: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300',
        advanced: 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300',
    };
    return colors[level] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300';
};
</script>

<template>
    <Head title="Activity Games" />
    
    <AppLayout>
        <div class="py-12 bg-gradient-to-b from-white to-gray-50 dark:from-gray-900 dark:to-gray-800">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-12 text-center">
                    <div class="inline-flex items-center gap-2 mb-4">
                        <Gamepad2 class="w-8 h-8 text-indigo-600 dark:text-indigo-400" />
                        <h1 class="text-4xl font-bold text-gray-900 dark:text-white">
                            Activity Games
                        </h1>
                    </div>
                    <p class="mt-2 text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                        Play fun and engaging games designed to help with learning and skill development. 
                        All games are free and no scores are recorded - just pure fun!
                    </p>
                </div>

                <!-- Loading State -->
                <div v-if="loading" class="flex justify-center py-12">
                    <div class="animate-spin rounded-full h-16 w-16 border-4 border-indigo-200 dark:border-indigo-900 border-t-4 border-t-indigo-600 dark:border-t-indigo-400"></div>
                </div>

                <!-- Activities Grid -->
                <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="activity in activities"
                        :key="activity.id"
                        class="group bg-white dark:bg-gray-800 rounded-lg shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600"
                    >
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    {{ activity.title }}
                                </h3>
                                <span
                                    :class="getDifficultyColor(activity.difficulty_level)"
                                    class="px-3 py-1 rounded-full text-xs font-semibold capitalize ml-2 flex-shrink-0"
                                >
                                    {{ activity.difficulty_level }}
                                </span>
                            </div>

                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">
                                {{ activity.description }}
                            </p>

                            <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400 mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span>{{ activity.estimated_duration_minutes }} min</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span>{{ activity.activity_items_count || 0 }} tasks</span>
                                </div>
                            </div>

                            <Link
                                :href="activitiesRoutes.play(activity.id)"
                                class="block w-full bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white text-center py-3 px-4 rounded-lg transition-all duration-200 font-semibold"
                            >
                                Start Playing 🎯
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!loading && activities.length === 0" class="text-center py-12">
                    <Gamepad2 class="mx-auto h-12 w-12 text-gray-400 mb-4" />
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">No activities available</h3>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">Check back soon for fun games!</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
