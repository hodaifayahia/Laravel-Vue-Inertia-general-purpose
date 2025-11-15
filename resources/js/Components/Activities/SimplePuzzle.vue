<script setup>
import { ref, onMounted, computed } from 'vue';
import { Puzzle, CheckCircle2 } from 'lucide-vue-next';
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

const pieces = ref([]);
const placedPieces = ref([]);
const draggedPiece = ref(null);
const startTime = ref(null);
const showFeedback = ref(false);

const totalPieces = computed(() => {
    return props.item.options?.pieces || 6;
});

const difficulty = computed(() => {
    return props.item.options?.difficulty || 'easy';
});

// Generate puzzle pieces with colors/shapes
const generatePieces = () => {
    // Check if shapes are provided in content_data
    if (props.item.content_data?.shapes) {
        const shapesData = props.item.content_data.shapes;
        return shapesData.map((shapeData, i) => ({
            id: i,
            shape: shapeData.emoji,
            shapeType: shapeData.shape,
            color: shapeData.color,
            placed: false,
            correctSlot: i,
        })).sort(() => Math.random() - 0.5);
    }
    
    // Default puzzle pieces
    const shapes = ['🔴', '🔵', '🟢', '🟡', '🟣', '🟤', '⭐', '❤️', '🔶', '⬛', '🔷', '🔺'];
    const piecesArray = [];
    
    for (let i = 0; i < totalPieces.value && i < shapes.length; i++) {
        piecesArray.push({
            id: i,
            shape: shapes[i],
            placed: false,
            correctSlot: i,
        });
    }
    
    // Shuffle pieces
    return piecesArray.sort(() => Math.random() - 0.5);
};

const containers = computed(() => {
    if (props.item.content_data?.containers) {
        return props.item.content_data.containers;
    }
    return [];
});

onMounted(() => {
    pieces.value = generatePieces();
    startTime.value = Date.now();
});

const startDrag = (piece) => {
    if (!piece.placed) {
        draggedPiece.value = piece;
    }
};

const allowDrop = (e) => {
    e.preventDefault();
};

const drop = (slotIndex) => {
    if (draggedPiece.value && !draggedPiece.value.placed) {
        const piece = pieces.value.find(p => p.id === draggedPiece.value.id);
        if (piece) {
            piece.placed = true;
            
            // For shape sorting, check if piece's shape type matches container
            let isCorrect = piece.correctSlot === slotIndex;
            if (containers.value.length > 0 && piece.shapeType) {
                // Match piece shape type to container
                const containerType = containers.value[slotIndex % containers.value.length];
                isCorrect = piece.shapeType === containerType;
            }
            
            placedPieces.value.push({
                pieceId: piece.id,
                slotIndex: slotIndex,
                isCorrect: isCorrect,
            });
            
            // Check if all pieces are placed
            if (placedPieces.value.length === pieces.value.length) {
                handleComplete();
            }
        }
    }
    draggedPiece.value = null;
};

const handleComplete = () => {
    showFeedback.value = true;
    const endTime = Date.now();
    const timeTaken = endTime - startTime.value;
    const correctPlacements = placedPieces.value.filter(p => p.isCorrect).length;
    const accuracy = Math.round((correctPlacements / pieces.value.length) * 100);
    
    setTimeout(() => {
        emit('complete', {
            placed_pieces: placedPieces.value,
            time_taken_ms: timeTaken,
            accuracy: accuracy,
            points_earned: accuracy >= 70 ? props.item.max_points : Math.floor(props.item.max_points * 0.6),
        });
    }, 1500);
};
</script>

<template>
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-8">
            <Puzzle class="w-12 h-12 mx-auto mb-4 text-indigo-600 dark:text-indigo-400" />
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ item.prompt_text }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                {{ placedPieces.length }} / {{ pieces.length }} pieces placed
            </p>
        </div>

        <!-- Drop Zones -->
        <div v-if="containers.length > 0" class="mb-8">
            <!-- Shape Sorting Containers -->
            <div class="grid grid-cols-3 gap-6">
                <div v-for="(containerType, containerIndex) in containers" :key="'container-' + containerIndex" class="flex flex-col items-center">
                    <!-- Container Label -->
                    <div class="mb-3 px-4 py-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
                        <span class="text-lg font-semibold text-indigo-700 dark:text-indigo-300 capitalize">
                            {{ containerType }}
                        </span>
                    </div>
                    
                    <!-- Drop Zone for this container type -->
                    <div class="w-full min-h-[200px] border-4 border-dashed border-indigo-400 dark:border-indigo-600 rounded-2xl p-4 bg-indigo-50 dark:bg-indigo-900/20 flex flex-wrap gap-2 justify-center items-start">
                        <template v-for="placed in placedPieces" :key="'placed-' + placed.pieceId">
                            <div
                                v-if="Math.floor(placed.slotIndex / (totalPieces / containers.length)) === containerIndex"
                                @dragover="allowDrop"
                                @drop="drop(placed.slotIndex)"
                                class="text-5xl"
                            >
                                {{ pieces.find(p => p.id === placed.pieceId)?.shape }}
                            </div>
                        </template>
                        
                        <!-- Empty slots in this container -->
                        <div
                            v-for="slot in Math.ceil(totalPieces / containers.length)"
                            :key="'slot-' + containerIndex + '-' + slot"
                            @dragover="allowDrop"
                            @drop="drop(containerIndex * Math.ceil(totalPieces / containers.length) + slot - 1)"
                            v-show="!placedPieces.some(p => p.slotIndex === containerIndex * Math.ceil(totalPieces / containers.length) + slot - 1)"
                            class="w-20 h-20 border-2 border-dashed border-gray-400 dark:border-gray-600 rounded-xl flex items-center justify-center bg-white dark:bg-gray-700 hover:border-indigo-500 hover:bg-indigo-100 dark:hover:bg-indigo-800/30 transition-all"
                        >
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Default Grid (no containers) -->
        <div v-else class="grid grid-cols-3 gap-4 mb-8 p-6 bg-gray-100 dark:bg-gray-800 rounded-2xl">
            <div
                v-for="slotIndex in totalPieces"
                :key="'slot-' + slotIndex"
                @dragover="allowDrop"
                @drop="drop(slotIndex - 1)"
                class="aspect-square border-4 border-dashed border-gray-400 dark:border-gray-600 rounded-xl flex items-center justify-center text-6xl bg-white dark:bg-gray-700 transition-all hover:border-indigo-500 hover:bg-indigo-50 dark:hover:bg-indigo-900/20"
            >
                <template v-for="placed in placedPieces" :key="'placed-' + placed.pieceId">
                    <span v-if="placed.slotIndex === slotIndex - 1">
                        {{ pieces.find(p => p.id === placed.pieceId)?.shape }}
                    </span>
                </template>
            </div>
        </div>

        <!-- Puzzle Pieces -->
        <div class="flex flex-wrap justify-center gap-4 p-6 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 rounded-2xl">
            <div
                v-for="piece in pieces"
                :key="'piece-' + piece.id"
                v-show="!piece.placed"
                draggable="true"
                @dragstart="startDrag(piece)"
                class="w-24 h-24 bg-white dark:bg-gray-700 border-4 border-indigo-500 rounded-xl flex items-center justify-center text-6xl cursor-move hover:scale-110 transition-all shadow-lg hover:shadow-2xl"
            >
                {{ piece.shape }}
            </div>
        </div>

        <!-- Feedback -->
        <div v-if="showFeedback" class="mt-8 text-center animate-fade-in">
            <div class="inline-flex items-center gap-3 bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400 px-8 py-4 rounded-full text-lg font-semibold">
                <CheckCircle2 class="w-7 h-7" />
                <span>Puzzle Complete! 🧩</span>
            </div>
        </div>

        <p class="mt-8 text-sm text-gray-500 dark:text-gray-400 text-center">
            {{ item.max_points }} points • Drag and drop pieces to their correct positions!
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
