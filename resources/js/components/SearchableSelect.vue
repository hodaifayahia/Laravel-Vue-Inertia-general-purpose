<template>
  <div class="relative">
    <div class="relative">
      <input
        type="text"
        :value="displayValue"
        :placeholder="placeholder"
        @input="handleInput"
        @focus="isOpen = true"
        @blur="closeDropdown"
        :disabled="disabled"
        class="w-full px-4 py-3 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 focus:border-indigo-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
      />
      <ChevronDown v-if="!disabled" class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" />
    </div>

    <!-- Dropdown List -->
    <div
      v-if="isOpen && !disabled"
      class="absolute top-full left-0 right-0 mt-1 bg-white dark:bg-gray-900 border-2 border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 max-h-64 overflow-y-auto"
    >
      <div
        v-if="filteredOptions.length === 0"
        class="px-4 py-3 text-gray-500 dark:text-gray-400"
      >
        No options found
      </div>

      <div
        v-for="option in filteredOptions"
        :key="option"
        @click="selectOption(option)"
        class="px-4 py-3 cursor-pointer hover:bg-indigo-100 dark:hover:bg-indigo-900/30 transition-colors"
        :class="{ 'bg-indigo-100 dark:bg-indigo-900/30': option === modelValue }"
      >
        {{ option }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { ChevronDown } from 'lucide-vue-next'

interface Props {
  modelValue: string
  options: string[]
  placeholder?: string
  disabled?: boolean
}

interface Emits {
  (e: 'update:modelValue', value: string): void
}

const props = withDefaults(defineProps<Props>(), {
  placeholder: 'Select an option...',
  disabled: false,
})

const emit = defineEmits<Emits>()

const searchQuery = ref('')
const isOpen = ref(false)

const displayValue = computed(() => {
  return searchQuery.value || props.modelValue || ''
})

const filteredOptions = computed(() => {
  if (!searchQuery.value) {
    return props.options
  }
  return props.options.filter(option =>
    option.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  searchQuery.value = target.value
}

const selectOption = (option: string) => {
  emit('update:modelValue', option)
  searchQuery.value = ''
  isOpen.value = false
}

const closeDropdown = () => {
  setTimeout(() => {
    isOpen.value = false
    searchQuery.value = ''
  }, 200)
}
</script>
