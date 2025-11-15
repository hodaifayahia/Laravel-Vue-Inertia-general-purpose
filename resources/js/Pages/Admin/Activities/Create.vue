<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/layouts/app/AppSidebarLayout.vue';
import { ChevronLeft, Save, Plus, Trash2 } from 'lucide-vue-next';
import { ref } from 'vue';

defineProps({
    itemTypes: Object,
});

const form = useForm({
    title: '',
    description: '',
    estimated_duration_minutes: 15,
    difficulty_level: 'beginner',
    min_age: null,
    max_age: null,
    is_active: true,
    order: 0,
});

const items = ref([]);
const newItem = ref({
    item_type: 'emoji_choice',
    prompt_text: '',
    max_points: 100,
    time_limit_seconds: null,
    order: 0,
    content_data: {},
    options: {},
});

const addItem = () => {
    items.value.push({
        ...newItem.value,
        id: Date.now(),
    });
    newItem.value = {
        item_type: 'emoji_choice',
        prompt_text: '',
        max_points: 100,
        time_limit_seconds: null,
        order: 0,
        content_data: {},
        options: {},
    };
};

const removeItem = (index) => {
    items.value.splice(index, 1);
};

const submit = () => {
    form.post(route('admin.activities.store'), {
        onFinish: () => {
            form.reset();
            items.value = [];
        },
    });
};

const getItemTypeLabel = (type) => {
    const labels = {
        emoji_choice: 'Emoji Choice',
        text_copy_timed: 'Timed Typing',
        shape_copy_canvas: 'Shape Drawing',
        trace_the_path: 'Path Tracing',
        dot_to_dot: 'Connect Dots',
        find_the_different_one: 'Find Different',
        simple_puzzle_drag: 'Puzzle',
        whats_missing: 'What\'s Missing',
        listen_and_type: 'Listen & Type',
        unscramble_the_word: 'Unscramble',
    };
    return labels[type] || type;
};
</script>

<template>
    <Head title="Create Activity" />

    <AuthenticatedLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
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

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <!-- Activity Form Section -->
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Create New Activity</h2>

                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Activity Title
                                </label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    placeholder="e.g., Letter Recognition Game"
                                />
                                <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
                            </div>

                            <!-- Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Description
                                </label>
                                <textarea
                                    v-model="form.description"
                                    rows="4"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    placeholder="Describe what this activity helps with..."
                                ></textarea>
                            </div>

                            <!-- Duration and Difficulty -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Duration (minutes)
                                    </label>
                                    <input
                                        v-model.number="form.estimated_duration_minutes"
                                        type="number"
                                        min="1"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Difficulty Level
                                    </label>
                                    <select
                                        v-model="form.difficulty_level"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Age Range -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Min Age
                                    </label>
                                    <input
                                        v-model.number="form.min_age"
                                        type="number"
                                        min="1"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Max Age
                                    </label>
                                    <input
                                        v-model.number="form.max_age"
                                        type="number"
                                        min="1"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    />
                                </div>
                            </div>

                            <!-- Active Status -->
                            <div class="flex items-center gap-3">
                                <input
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="w-4 h-4 text-indigo-600 border-gray-300 rounded"
                                />
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Active (visible to users)
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex gap-4">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50 font-medium transition-colors"
                                >
                                    <Save class="w-5 h-5" />
                                    {{ form.processing ? 'Creating...' : 'Create Activity' }}
                                </button>
                                <Link
                                    :href="route('admin.activities.index')"
                                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 font-medium transition-colors"
                                >
                                    Cancel
                                </Link>
                            </div>
                        </form>
                    </div>

                    <!-- Activity Items Section -->
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Activity Items (Optional)</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Add individual game items to this activity. You can also add items after creating the activity.
                        </p>

                        <!-- New Item Form -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg mb-6">
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Game Type
                                    </label>
                                    <select
                                        v-model="newItem.item_type"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                    >
                                        <option value="emoji_choice">Emoji Choice</option>
                                        <option value="text_copy_timed">Timed Typing Test</option>
                                        <option value="shape_copy_canvas">Shape Drawing</option>
                                        <option value="trace_the_path">Path Tracing</option>
                                        <option value="dot_to_dot">Connect the Dots</option>
                                        <option value="find_the_different_one">Find the Different One</option>
                                        <option value="simple_puzzle_drag">Simple Puzzle</option>
                                        <option value="whats_missing">What's Missing</option>
                                        <option value="listen_and_type">Listen and Type</option>
                                        <option value="unscramble_the_word">Unscramble the Word</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Prompt Text
                                    </label>
                                    <textarea
                                        v-model="newItem.prompt_text"
                                        rows="2"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                        placeholder="e.g., How are you feeling today?"
                                    ></textarea>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Max Points
                                        </label>
                                        <input
                                            v-model.number="newItem.max_points"
                                            type="number"
                                            min="0"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Time Limit (seconds)
                                        </label>
                                        <input
                                            v-model.number="newItem.time_limit_seconds"
                                            type="number"
                                            min="0"
                                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-indigo-500"
                                            placeholder="Leave empty for no limit"
                                        />
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    @click="addItem"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors"
                                >
                                    <Plus class="w-5 h-5" />
                                    Add Item
                                </button>
                            </div>
                        </div>

                        <!-- Items List -->
                        <div v-if="items.length > 0" class="space-y-3">
                            <h4 class="font-medium text-gray-900 dark:text-white">Items to be added:</h4>
                            <div v-for="(item, index) in items" :key="item.id" class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg flex justify-between items-start">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 text-xs font-semibold text-white bg-indigo-600 rounded">
                                            {{ getItemTypeLabel(item.item_type) }}
                                        </span>
                                    </div>
                                    <p class="text-gray-900 dark:text-white font-medium">{{ item.prompt_text || '(No prompt)' }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Max Points: {{ item.max_points }}
                                        <span v-if="item.time_limit_seconds">| Time Limit: {{ item.time_limit_seconds }}s</span>
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    @click="removeItem(index)"
                                    class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                >
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p>No items added yet. You can add items after creating the activity.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
