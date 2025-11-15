<?php

namespace Database\Seeders;

use App\Models\Activity;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $activities = [
            [
                'title' => 'Memory Match',
                'description' => 'Test your memory skills by matching pairs of cards. Flip cards and find matching pairs to complete the game!',
                'estimated_duration_minutes' => 10,
                'is_active' => true,
                'order' => 1,
                'difficulty_level' => 'beginner',
                'min_age' => 4,
                'max_age' => 12,
            ],
            [
                'title' => 'Color Recognition',
                'description' => 'Learn colors through fun interactive activities. Click on objects of the requested color!',
                'estimated_duration_minutes' => 5,
                'is_active' => true,
                'order' => 2,
                'difficulty_level' => 'beginner',
                'min_age' => 3,
                'max_age' => 8,
            ],
            [
                'title' => 'Shape Sorter',
                'description' => 'Sort shapes into the correct categories. Drag and drop shapes to their matching containers!',
                'estimated_duration_minutes' => 8,
                'is_active' => true,
                'order' => 3,
                'difficulty_level' => 'beginner',
                'min_age' => 4,
                'max_age' => 10,
            ],
            [
                'title' => 'Number Counting',
                'description' => 'Practice counting with fun animations. Count objects and select the correct number!',
                'estimated_duration_minutes' => 7,
                'is_active' => true,
                'order' => 4,
                'difficulty_level' => 'beginner',
                'min_age' => 4,
                'max_age' => 9,
            ],
            [
                'title' => 'Letter Tracing',
                'description' => 'Learn to write letters by tracing them on screen. Follow the arrows to practice proper letter formation!',
                'estimated_duration_minutes' => 15,
                'is_active' => true,
                'order' => 5,
                'difficulty_level' => 'beginner',
                'min_age' => 5,
                'max_age' => 10,
            ],
            [
                'title' => 'Pattern Recognition',
                'description' => 'Complete the pattern by selecting the next shape or color. Develops logical thinking skills!',
                'estimated_duration_minutes' => 10,
                'is_active' => true,
                'order' => 6,
                'difficulty_level' => 'intermediate',
                'min_age' => 6,
                'max_age' => 12,
            ],
            [
                'title' => 'Word Building',
                'description' => 'Build simple words by arranging letters in the correct order. Great for early reading skills!',
                'estimated_duration_minutes' => 12,
                'is_active' => true,
                'order' => 7,
                'difficulty_level' => 'intermediate',
                'min_age' => 6,
                'max_age' => 11,
            ],
            [
                'title' => 'Math Challenge',
                'description' => 'Solve simple addition and subtraction problems. Practice mental math in a fun way!',
                'estimated_duration_minutes' => 10,
                'is_active' => true,
                'order' => 8,
                'difficulty_level' => 'intermediate',
                'min_age' => 7,
                'max_age' => 12,
            ],
            [
                'title' => 'Puzzle Master',
                'description' => 'Complete jigsaw puzzles with varying difficulty levels. Drag pieces to complete the picture!',
                'estimated_duration_minutes' => 15,
                'is_active' => true,
                'order' => 9,
                'difficulty_level' => 'intermediate',
                'min_age' => 6,
                'max_age' => 14,
            ],
            [
                'title' => 'Rhythm Game',
                'description' => 'Follow the musical rhythm by tapping at the right time. Improves timing and coordination!',
                'estimated_duration_minutes' => 8,
                'is_active' => true,
                'order' => 10,
                'difficulty_level' => 'intermediate',
                'min_age' => 5,
                'max_age' => 12,
            ],
            [
                'title' => 'Story Sequencing',
                'description' => 'Arrange story scenes in the correct order. Develops comprehension and logical thinking!',
                'estimated_duration_minutes' => 12,
                'is_active' => true,
                'order' => 11,
                'difficulty_level' => 'intermediate',
                'min_age' => 7,
                'max_age' => 13,
            ],
            [
                'title' => 'Maze Navigator',
                'description' => 'Guide the character through the maze to reach the goal. Develops problem-solving skills!',
                'estimated_duration_minutes' => 10,
                'is_active' => true,
                'order' => 12,
                'difficulty_level' => 'advanced',
                'min_age' => 8,
                'max_age' => 15,
            ],
            [
                'title' => 'Logic Puzzles',
                'description' => 'Solve challenging logic puzzles and brain teasers. Perfect for developing critical thinking!',
                'estimated_duration_minutes' => 20,
                'is_active' => true,
                'order' => 13,
                'difficulty_level' => 'advanced',
                'min_age' => 9,
                'max_age' => 16,
            ],
            [
                'title' => 'Drawing Board',
                'description' => 'Free drawing canvas with various colors and tools. Express creativity and practice fine motor skills!',
                'estimated_duration_minutes' => 20,
                'is_active' => true,
                'order' => 14,
                'difficulty_level' => 'beginner',
                'min_age' => 3,
                'max_age' => 15,
            ],
            [
                'title' => 'Coding Basics',
                'description' => 'Learn basic programming concepts through visual block coding. Move the character by arranging commands!',
                'estimated_duration_minutes' => 25,
                'is_active' => true,
                'order' => 15,
                'difficulty_level' => 'advanced',
                'min_age' => 8,
                'max_age' => 16,
            ],
        ];

        foreach ($activities as $activity) {
            Activity::create($activity);
        }
    }
}
