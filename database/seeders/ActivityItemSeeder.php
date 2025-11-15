<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityItem;
use Illuminate\Database\Seeder;

class ActivityItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Memory Match - 3 tasks
        $activity = Activity::where('title', 'Memory Match')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'How are you feeling today? Select the emoji that matches your mood',
                'max_points' => 10,
                'order' => 1,
                'options' => [
                    'correct' => 'happy',
                    'emojis' => ['😊', '😢', '😡', '😴'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'simple_puzzle_drag',
                'prompt_text' => 'Match the pairs! Drag cards to find matching pairs',
                'max_points' => 20,
                'time_limit_seconds' => 60,
                'order' => 2,
                'options' => [
                    'pairs' => 8,
                    'difficulty' => 'easy',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'find_the_different_one',
                'prompt_text' => 'Which one is different? Spot the odd one out',
                'max_points' => 10,
                'order' => 3,
                'options' => [
                    'correct_index' => 3,
                ],
            ]);
        }

        // Color Recognition - 2 tasks
        $activity = Activity::where('title', 'Color Recognition')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'What color is this? | Quelle couleur est-ce? | ما هذا اللون؟',
                'max_points' => 15,
                'order' => 1,
                'content_data' => [
                    'display_color' => '#FF0000',
                    'display_shape' => 'circle',
                ],
                'options' => [
                    'correct' => 'red',
                    'options' => ['red', 'blue', 'green', 'yellow'],
                    'translations' => [
                        'en' => ['Red', 'Blue', 'Green', 'Yellow'],
                        'fr' => ['Rouge', 'Bleu', 'Vert', 'Jaune'],
                        'ar' => ['أحمر', 'أزرق', 'أخضر', 'أصفر'],
                    ],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'Select the BLUE color | Sélectionnez la couleur BLEUE | اختر اللون الأزرق',
                'max_points' => 15,
                'order' => 2,
                'content_data' => [
                    'display_color' => '#0000FF',
                    'display_shape' => 'square',
                ],
                'options' => [
                    'correct' => 'blue',
                    'options' => ['red', 'blue', 'green', 'yellow'],
                    'translations' => [
                        'en' => ['Red', 'Blue', 'Green', 'Yellow'],
                        'fr' => ['Rouge', 'Bleu', 'Vert', 'Jaune'],
                        'ar' => ['أحمر', 'أزرق', 'أخضر', 'أصفر'],
                    ],
                ],
            ]);
        }

        // Shape Sorter - 2 tasks
        $activity = Activity::where('title', 'Shape Sorter')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'simple_puzzle_drag',
                'prompt_text' => 'Drag shapes to match | Faites glisser les formes | اسحب الأشكال لتطابق',
                'max_points' => 20,
                'time_limit_seconds' => 60,
                'order' => 1,
                'content_data' => [
                    'shapes' => [
                        ['shape' => 'circle', 'color' => 'red', 'emoji' => '🔴'],
                        ['shape' => 'square', 'color' => 'blue', 'emoji' => '🟦'],
                        ['shape' => 'triangle', 'color' => 'green', 'emoji' => '🔺'],
                        ['shape' => 'circle', 'color' => 'yellow', 'emoji' => '🟡'],
                        ['shape' => 'square', 'color' => 'purple', 'emoji' => '🟪'],
                        ['shape' => 'triangle', 'color' => 'orange', 'emoji' => '🔶'],
                    ],
                    'containers' => ['circle', 'square', 'triangle'],
                ],
                'options' => [
                    'pieces' => 6,
                    'difficulty' => 'easy',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'find_the_different_one',
                'prompt_text' => 'Find the different shape | Trouvez la forme différente | ابحث عن الشكل المختلف',
                'max_points' => 15,
                'order' => 2,
                'content_data' => [
                    'items' => ['🔵', '🔵', '🔵', '🔴', '🔵', '🔵'],
                ],
                'options' => [
                    'correct_index' => 3,
                ],
            ]);
        }

        // Number Counting - 3 tasks
        $activity = Activity::where('title', 'Number Counting')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'How many apples? | Combien de pommes? | كم تفاحة؟',
                'max_points' => 15,
                'order' => 1,
                'content_data' => [
                    'objects' => '🍎🍎🍎🍎🍎',
                    'count' => 5,
                ],
                'options' => [
                    'correct' => '5',
                    'options' => ['3', '4', '5', '6'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'Count the stars | Comptez les étoiles | عد النجوم',
                'max_points' => 15,
                'order' => 2,
                'content_data' => [
                    'objects' => '⭐⭐⭐⭐⭐⭐⭐⭐',
                    'count' => 8,
                ],
                'options' => [
                    'correct' => '8',
                    'options' => ['6', '7', '8', '9'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'unscramble_the_word',
                'prompt_text' => 'Unscramble the number | Démêlez le nombre | فك تشفير الرقم',
                'max_points' => 20,
                'time_limit_seconds' => 45,
                'order' => 3,
                'options' => [
                    'correct_word' => 'seven',
                    'scrambled' => 'vesen',
                ],
            ]);
        }

        // Letter Tracing - 2 tasks
        $activity = Activity::where('title', 'Letter Tracing')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'trace_the_path',
                'prompt_text' => 'Trace the letter A - Follow the path to trace the letter',
                'max_points' => 25,
                'time_limit_seconds' => 60,
                'order' => 1,
                'options' => [
                    'letter' => 'A',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'text_copy_timed',
                'prompt_text' => 'Type the letters shown - Type as fast as you can',
                'max_points' => 25,
                'time_limit_seconds' => 60,
                'order' => 2,
                'options' => [
                    'text' => 'ABC',
                ],
            ]);
        }

        // Pattern Recognition - 2 tasks
        $activity = Activity::where('title', 'Pattern Recognition')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'find_the_different_one',
                'prompt_text' => 'Which pattern continues the sequence? Select the correct next pattern',
                'max_points' => 20,
                'order' => 1,
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'Complete the pattern - Select the shape that completes the pattern',
                'max_points' => 20,
                'order' => 2,
            ]);
        }

        // Word Building - 3 tasks
        $activity = Activity::where('title', 'Word Building')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'unscramble_the_word',
                'prompt_text' => 'Unscramble: TAC | Démêlez: TAC | فك تشفير: TAC',
                'max_points' => 20,
                'time_limit_seconds' => 60,
                'order' => 1,
                'content_data' => [
                    'hint' => '🐱 meow!',
                ],
                'options' => [
                    'correct_word' => 'cat',
                    'scrambled' => 'tac',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'unscramble_the_word',
                'prompt_text' => 'Make a word: GOD | Formez un mot: GOD | كون كلمة: GOD',
                'max_points' => 20,
                'time_limit_seconds' => 60,
                'order' => 2,
                'content_data' => [
                    'hint' => '🐕 woof!',
                ],
                'options' => [
                    'correct_word' => 'dog',
                    'scrambled' => 'god',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'listen_and_type',
                'prompt_text' => 'Type the word | Tapez le mot | اكتب الكلمة',
                'max_points' => 20,
                'time_limit_seconds' => 60,
                'order' => 3,
                'options' => [
                    'correct_answer' => 'hello',
                    'audio_text' => 'hello',
                ],
            ]);
        }

        // Math Challenge - 4 tasks
        $activity = Activity::where('title', 'Math Challenge')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'What is 2 + 3? | Combien font 2 + 3? | كم يساوي 2 + 3؟',
                'max_points' => 20,
                'order' => 1,
                'content_data' => [
                    'question' => '2 + 3 = ?',
                    'visual' => '🍎🍎 + 🍎🍎🍎 = ?',
                ],
                'options' => [
                    'correct' => '5',
                    'options' => ['3', '4', '5', '6'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'What is 5 - 2? | Combien font 5 - 2? | كم يساوي 5 - 2؟',
                'max_points' => 20,
                'order' => 2,
                'content_data' => [
                    'question' => '5 - 2 = ?',
                    'visual' => '⭐⭐⭐⭐⭐ - ⭐⭐ = ?',
                ],
                'options' => [
                    'correct' => '3',
                    'options' => ['2', '3', '4', '7'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'What is 4 × 2? | Combien font 4 × 2? | كم يساوي 4 × 2؟',
                'max_points' => 25,
                'order' => 3,
                'content_data' => [
                    'question' => '4 × 2 = ?',
                    'visual' => '🔵🔵🔵🔵 × 2 = ?',
                ],
                'options' => [
                    'correct' => '8',
                    'options' => ['6', '7', '8', '9'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'What is 10 ÷ 2? | Combien font 10 ÷ 2? | كم يساوي 10 ÷ 2؟',
                'max_points' => 25,
                'order' => 4,
                'content_data' => [
                    'question' => '10 ÷ 2 = ?',
                    'visual' => '🍪🍪🍪🍪🍪🍪🍪🍪🍪🍪 ÷ 2 = ?',
                ],
                'options' => [
                    'correct' => '5',
                    'options' => ['4', '5', '6', '8'],
                ],
            ]);
        }

        // Puzzle Master - 2 tasks
        $activity = Activity::where('title', 'Puzzle Master')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'simple_puzzle_drag',
                'prompt_text' => 'Complete the puzzle - Drag pieces to complete the picture',
                'max_points' => 30,
                'time_limit_seconds' => 120,
                'order' => 1,
                'options' => [
                    'pieces' => 12,
                    'difficulty' => 'medium',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'whats_missing',
                'prompt_text' => 'What is missing from the picture? Identify and tap the missing element',
                'max_points' => 25,
                'time_limit_seconds' => 60,
                'order' => 2,
            ]);
        }

        // Rhythm Game - 2 tasks
        $activity = Activity::where('title', 'Rhythm Game')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'dot_to_dot',
                'prompt_text' => 'Follow the rhythm pattern - Tap the dots in sequence',
                'max_points' => 25,
                'time_limit_seconds' => 90,
                'order' => 1,
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'listen_and_type',
                'prompt_text' => 'Type the rhythm pattern - Transcribe the pattern you hear',
                'max_points' => 25,
                'time_limit_seconds' => 90,
                'order' => 2,
                'options' => [
                    'pattern' => 'TATAT',
                ],
            ]);
        }

        // Story Sequencing - 2 tasks
        $activity = Activity::where('title', 'Story Sequencing')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'simple_puzzle_drag',
                'prompt_text' => 'Arrange the story in order - Drag scenes to put the story in correct order',
                'max_points' => 30,
                'time_limit_seconds' => 120,
                'order' => 1,
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'listen_and_type',
                'prompt_text' => 'What happens next in the story? Type what you think happens next',
                'max_points' => 20,
                'time_limit_seconds' => 90,
                'order' => 2,
            ]);
        }

        // Maze Navigator - 2 tasks
        $activity = Activity::where('title', 'Maze Navigator')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'trace_the_path',
                'prompt_text' => 'Navigate through the maze - Trace the path from start to exit',
                'max_points' => 35,
                'time_limit_seconds' => 120,
                'order' => 1,
                'options' => [
                    'difficulty' => 'medium',
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'dot_to_dot',
                'prompt_text' => 'Connect the dots to escape - Connect all dots to find the exit',
                'max_points' => 30,
                'time_limit_seconds' => 90,
                'order' => 2,
            ]);
        }

        // Logic Puzzles - 2 tasks
        $activity = Activity::where('title', 'Logic Puzzles')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'emoji_choice',
                'prompt_text' => 'Solve the logic puzzle - Select the correct answer',
                'max_points' => 40,
                'order' => 1,
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'unscramble_the_word',
                'prompt_text' => 'Unscramble the puzzle word - Arrange letters to solve the puzzle',
                'max_points' => 40,
                'time_limit_seconds' => 120,
                'order' => 2,
            ]);
        }

        // Drawing Board - 2 tasks
        $activity = Activity::where('title', 'Drawing Board')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'shape_copy_canvas',
                'prompt_text' => 'Draw a circle - Draw a perfect circle',
                'max_points' => 20,
                'time_limit_seconds' => 120,
                'order' => 1,
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'shape_copy_canvas',
                'prompt_text' => 'Draw a house - Draw a simple house',
                'max_points' => 30,
                'time_limit_seconds' => 180,
                'order' => 2,
            ]);
        }

        // Coding Basics - 2 tasks
        $activity = Activity::where('title', 'Coding Basics')->first();
        if ($activity) {
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'simple_puzzle_drag',
                'prompt_text' => 'Arrange the command blocks - Drag blocks to program movement',
                'max_points' => 40,
                'time_limit_seconds' => 150,
                'order' => 1,
                'options' => [
                    'blocks' => ['forward', 'turn', 'repeat'],
                ],
            ]);
            ActivityItem::create([
                'activity_id' => $activity->id,
                'item_type' => 'dot_to_dot',
                'prompt_text' => 'Program the path - Connect dots to create a program path',
                'max_points' => 40,
                'time_limit_seconds' => 150,
                'order' => 2,
            ]);
        }
    }
}
