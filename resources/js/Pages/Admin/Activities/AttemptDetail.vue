<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/app/AppSidebarLayout.vue';
import { ChevronLeft, MessageSquare, CheckCircle, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    attempt: Object,
});

const notes = ref(attempt.admin_notes || '');

const getItemTypeLabel = (type) => {
    const labels = {
        emoji_choice: '😊 Emoji Choice',
        text_copy_timed: '⌨️ Typing Test',
        shape_copy_canvas: '🎨 Shape Drawing',
        trace_the_path: '✏️ Path Tracing',
        dot_to_dot: '🔵 Connect Dots',
        find_the_different_one: '🔍 Find Different',
        simple_puzzle_drag: '🧩 Puzzle',
        whats_missing: '❓ What\'s Missing',
        listen_and_type: '🎧 Listen & Type',
        unscramble_the_word: '🔤 Unscramble',
    };
    return labels[type] || type;
};
</script>

<template>
    <Head :title="`Attempt Results - ${attempt.activity.title}`" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex items-center gap-4">
                    <Link 
                        :href="route('admin.activities.attempts')"
                        class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100"
                    >
                        <ChevronLeft class="w-5 h-5" />
                        Back to Attempts
                    </Link>
                </div>

                <div class="grid grid-cols-3 gap-6">
                    <!-- Main Results Card -->
                    <div class="col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ attempt.activity.title }}</h2>
                            <p class="text-gray-600 dark:text-gray-400">{{ attempt.activity.description }}</p>
                        </div>

                        <!-- Score Overview -->
                        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/30 dark:to-blue-800/30 p-4 rounded-lg">
                                    <div class="text-blue-600 dark:text-blue-400 text-sm font-semibold">Final Score</div>
                                    <div class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-2">{{ attempt.final_score || 0 }}</div>
                                </div>
                                <div class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/30 dark:to-purple-800/30 p-4 rounded-lg">
                                    <div class="text-purple-600 dark:text-purple-400 text-sm font-semibold">Items Completed</div>
                                    <div class="text-3xl font-bold text-purple-900 dark:text-purple-100 mt-2">{{ attempt.results.length }}</div>
                                </div>
                                <div :class="{
                                    'bg-gradient-to-br p-4 rounded-lg': true,
                                    'from-red-50 to-red-100 dark:from-red-900/30 dark:to-red-800/30': attempt.consultation_needed,
                                    'from-green-50 to-green-100 dark:from-green-900/30 dark:to-green-800/30': !attempt.consultation_needed,
                                }">
                                    <div :class="{
                                        'text-sm font-semibold': true,
                                        'text-red-600 dark:text-red-400': attempt.consultation_needed,
                                        'text-green-600 dark:text-green-400': !attempt.consultation_needed,
                                    }">
                                        Consultation
                                    </div>
                                    <div :class="{
                                        'text-3xl font-bold mt-2': true,
                                        'text-red-900 dark:text-red-100': attempt.consultation_needed,
                                        'text-green-900 dark:text-green-100': !attempt.consultation_needed,
                                    }">
                                        {{ attempt.consultation_needed ? 'Needed' : 'OK' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Results -->
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Item Results</h3>
                            <div class="space-y-3">
                                <div v-for="(result, index) in attempt.results" :key="result.id" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg">
                                    <div class="flex items-start justify-between mb-2">
                                        <div>
                                            <div class="font-semibold text-gray-900 dark:text-white">
                                                {{ index + 1 }}. {{ getItemTypeLabel(result.activity_item.item_type) }}
                                            </div>
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ result.activity_item.prompt_text }}</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <div class="text-right">
                                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                                    {{ result.points_awarded }}/{{ result.activity_item.max_points }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                                    {{ Math.round(result.time_taken_ms / 1000) }}s
                                                </div>
                                            </div>
                                            <div class="ml-2">
                                                <CheckCircle v-if="result.is_correct" class="w-6 h-6 text-green-500" />
                                                <XCircle v-else class="w-6 h-6 text-red-500" />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Result Data Preview -->
                                    <div v-if="result.result_data" class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                                        <div class="text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 p-2 rounded font-mono overflow-auto max-h-24">
                                            <pre>{{ JSON.stringify(result.result_data, null, 2) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar - User Info & Timestamps -->
                    <div class="space-y-6">
                        <!-- User Info -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">User Info</h3>
                            <div class="space-y-3">
                                <div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Account</div>
                                    <div class="text-sm text-gray-900 dark:text-white mt-1">
                                        {{ attempt.user ? attempt.user.name : 'Guest' }}
                                    </div>
                                </div>
                                <div v-if="attempt.child">
                                    <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Child</div>
                                    <div class="text-sm text-gray-900 dark:text-white mt-1">{{ attempt.child.name }}</div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Session ID</div>
                                    <div class="text-xs text-gray-900 dark:text-white mt-1 font-mono break-all">
                                        {{ attempt.guest_session_id || attempt.user_id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Timestamps -->
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Timeline</h3>
                            <div class="space-y-3 text-sm">
                                <div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Started</div>
                                    <div class="text-gray-900 dark:text-white mt-1">
                                        {{ new Date(attempt.started_at).toLocaleString() }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Completed</div>
                                    <div class="text-gray-900 dark:text-white mt-1">
                                        {{ attempt.completed_at ? new Date(attempt.completed_at).toLocaleString() : 'In Progress' }}
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold">Status</div>
                                    <div class="mt-1">
                                        <span :class="{
                                            'px-2 py-1 rounded text-xs font-semibold': true,
                                            'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300': attempt.status === 'completed',
                                            'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300': attempt.status === 'in_progress',
                                            'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300': attempt.status === 'started',
                                        }">
                                            {{ attempt.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
