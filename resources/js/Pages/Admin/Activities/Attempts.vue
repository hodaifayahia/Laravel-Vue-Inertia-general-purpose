<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/app/AppSidebarLayout.vue';
import { ChevronLeft, Eye, MessageSquare } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    attempts: Object,
});

const selectedAttempt = ref(null);
</script>

<template>
    <Head title="Activity Attempts" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center gap-4">
                    <Link 
                        :href="route('admin.activities.index')"
                        class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        <ChevronLeft class="w-5 h-5" />
                        Back to Activities
                    </Link>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Activity Attempts & Results</h2>

                        <!-- Results Table -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                                    <tr>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Activity</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">User</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Score</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Status</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Consultation</th>
                                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Date</th>
                                        <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    <tr v-for="attempt in attempts.data" :key="attempt.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900 dark:text-white">{{ attempt.activity.title }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ attempt.id }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-gray-900 dark:text-white">
                                                {{ attempt.user ? attempt.user.name : 'Guest' }}
                                            </div>
                                            <div v-if="attempt.child" class="text-xs text-gray-500 dark:text-gray-400">
                                                Child: {{ attempt.child.name }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-lg text-gray-900 dark:text-white">
                                                {{ attempt.final_score || 0 }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span 
                                                :class="{
                                                    'px-2 py-1 rounded-full text-xs font-semibold': true,
                                                    'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': attempt.status === 'completed',
                                                    'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': attempt.status === 'in_progress',
                                                    'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300': attempt.status === 'started',
                                                }"
                                            >
                                                {{ attempt.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span 
                                                :class="{
                                                    'px-2 py-1 rounded-full text-xs font-semibold': true,
                                                    'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300': attempt.consultation_needed,
                                                    'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': !attempt.consultation_needed,
                                                }"
                                            >
                                                {{ attempt.consultation_needed ? 'Needed' : 'Not Needed' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                            {{ new Date(attempt.completed_at || attempt.created_at).toLocaleDateString() }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <Link 
                                                :href="route('admin.activities.attempts.show', attempt.id)"
                                                class="inline-flex items-center gap-1 px-3 py-2 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-900/20 rounded transition-colors"
                                            >
                                                <Eye class="w-4 h-4" />
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Info -->
                        <div v-if="attempts.data.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p>No attempts yet.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
