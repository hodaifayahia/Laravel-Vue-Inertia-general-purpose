<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

interface Props {
  class?: string;
}

const props = defineProps<Props>();

const scrollAreaClass = computed(() => 
  cn('relative overflow-auto', props.class)
);
</script>

<template>
  <div :class="scrollAreaClass">
    <slot />
  </div>
</template>
