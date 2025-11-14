<script setup lang="ts">
import { ref, computed } from 'vue';
import { Upload, X, Camera, User } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';

interface Props {
    currentPhoto?: string;
    maxSizeMb?: number;
    acceptedFormats?: string[];
    label?: string;
}

const props = withDefaults(defineProps<Props>(), {
    currentPhoto: '',
    maxSizeMb: 5,
    acceptedFormats: () => ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'],
    label: 'Profile Photo',
});

const emit = defineEmits<{
    (e: 'photoSelected', file: File): void;
    (e: 'photoRemoved'): void;
}>();

const fileInput = ref<HTMLInputElement | null>(null);
const previewUrl = ref<string>(props.currentPhoto);
const isDragging = ref(false);
const error = ref<string>('');

const acceptAttribute = computed(() => {
    return props.acceptedFormats.join(',');
});

const formatsList = computed(() => {
    return props.acceptedFormats
        .map(format => format.split('/')[1].toUpperCase())
        .join(', ');
});

const validateFile = (file: File): boolean => {
    error.value = '';

    // Check file type
    if (!props.acceptedFormats.includes(file.type)) {
        error.value = `Invalid file format. Please upload ${formatsList.value} files only.`;
        return false;
    }

    // Check file size
    const maxSizeBytes = props.maxSizeMb * 1024 * 1024;
    if (file.size > maxSizeBytes) {
        error.value = `File size exceeds ${props.maxSizeMb}MB. Please choose a smaller file.`;
        return false;
    }

    return true;
};

const handleFileSelect = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];
    
    if (file && validateFile(file)) {
        processFile(file);
    }
    
    // Reset input value to allow selecting the same file again
    if (target) {
        target.value = '';
    }
};

const processFile = (file: File) => {
    const reader = new FileReader();
    
    reader.onload = (e) => {
        previewUrl.value = e.target?.result as string;
        emit('photoSelected', file);
    };
    
    reader.readAsDataURL(file);
};

const handleDragOver = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = () => {
    isDragging.value = false;
};

const handleDrop = (event: DragEvent) => {
    event.preventDefault();
    isDragging.value = false;
    
    const file = event.dataTransfer?.files[0];
    if (file && validateFile(file)) {
        processFile(file);
    }
};

const removePhoto = () => {
    previewUrl.value = '';
    error.value = '';
    emit('photoRemoved');
};

const triggerFileInput = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="space-y-4">
        <Label v-if="label">{{ label }}</Label>
        
        <Card>
            <CardContent class="pt-6">
                <!-- Preview -->
                <div v-if="previewUrl" class="space-y-4">
                    <div class="flex justify-center">
                        <div class="relative">
                            <img 
                                :src="previewUrl" 
                                alt="Photo preview" 
                                class="h-40 w-40 rounded-lg object-cover border-2 border-border"
                            />
                            <Button
                                @click="removePhoto"
                                variant="destructive"
                                size="icon"
                                class="absolute -top-2 -right-2 h-8 w-8 rounded-full"
                            >
                                <X class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <Button @click="triggerFileInput" variant="outline">
                            <Camera class="h-4 w-4 mr-2" />
                            Change Photo
                        </Button>
                    </div>
                </div>

                <!-- Upload Area -->
                <div 
                    v-else
                    @dragover="handleDragOver"
                    @dragleave="handleDragLeave"
                    @drop="handleDrop"
                    @click="triggerFileInput"
                    :class="[
                        'border-2 border-dashed rounded-lg p-8 text-center cursor-pointer transition-colors',
                        isDragging 
                            ? 'border-primary bg-primary/5' 
                            : 'border-border hover:border-primary hover:bg-accent'
                    ]"
                >
                    <div class="flex flex-col items-center gap-3">
                        <div class="h-16 w-16 rounded-full bg-secondary flex items-center justify-center">
                            <User class="h-8 w-8 text-muted-foreground" />
                        </div>
                        
                        <div>
                            <p class="font-medium">
                                <span class="text-primary">Click to upload</span> or drag and drop
                            </p>
                            <p class="text-sm text-muted-foreground mt-1">
                                {{ formatsList }} (Max {{ maxSizeMb }}MB)
                            </p>
                        </div>
                        
                        <Upload class="h-5 w-5 text-muted-foreground" />
                    </div>
                </div>

                <!-- Hidden file input -->
                <input
                    ref="fileInput"
                    type="file"
                    :accept="acceptAttribute"
                    @change="handleFileSelect"
                    class="hidden"
                />
            </CardContent>
        </Card>

        <!-- Error message -->
        <Alert v-if="error" variant="destructive">
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <!-- Info text -->
        <p class="text-xs text-muted-foreground">
            Recommended: Square image, at least 400x400 pixels for best quality
        </p>
    </div>
</template>
