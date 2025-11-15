# Activities System - Quick Start Guide

## 🚀 One-Minute Setup

### Step 1: Create Your First Activity
```
1. Go to: /admin/activities/create
2. Fill in details:
   - Title: "Emoji Feelings"
   - Description: "Express how you feel with emojis"
   - Duration: 10 minutes
   - Difficulty: Beginner
3. Add an item:
   - Type: Emoji Choice
   - Prompt: "How are you feeling?"
   - Max Points: 100
4. Click "Create Activity"
```

### Step 2: Play the Game
```
1. Go to: /activities
2. Find "Emoji Feelings" card
3. Click "Start Playing 🎯"
4. Select an emoji
5. Watch it complete
6. See "Great job!" message
```

### Step 3: View Results
```
1. Go to: /admin/activities/attempts
2. Find your attempt in the table
3. Click "View" button
4. See detailed breakdown:
   - Score
   - Time taken
   - Result data
   - Status
```

---

## 📋 Test Scenarios by Game Type

### Scenario 1: Emoji Choice
**File**: Create.vue → Add Item → Type "emoji_choice"
- Prompt: "How do you feel?"
- Points: 100
- Expected: User picks emoji, counts as complete

### Scenario 2: Timed Typing
**File**: Create.vue → Add Item → Type "text_copy_timed"
- Prompt: "Type the word 'dysgraphia'"
- Time Limit: 30 seconds
- Expected: User types, errors counted, score calculated

### Scenario 3: Shape Drawing
**File**: Create.vue → Add Item → Type "shape_copy_canvas"
- Prompt: "Draw a circle"
- Expected: Canvas opens, user draws, auto-completes

### Scenario 4: Connect Dots
**File**: Create.vue → Add Item → Type "dot_to_dot"
- Prompt: "Click dots in order 1→2→3"
- Expected: User clicks dots sequentially

### Scenario 5: Find Different
**File**: Create.vue → Add Item → Type "find_the_different_one"
- Prompt: "Find the different one"
- Expected: Grid shows items, user clicks different one

---

## 🎯 All Routes at a Glance

### Public Routes
```
/                           Home page (has Activities preview)
/activities                 Full activities listing
/activities/{id}/play       Play a specific game
```

### Admin Routes
```
/admin/activities                      Management dashboard
/admin/activities/create               Create new activity form
/admin/activities/{id}/edit            Edit activity & add items
/admin/activities/attempts             View all attempts/results
/admin/activities/attempts/{id}        View single attempt detail
```

### API Routes (used by frontend)
```
GET    /api/activities
GET    /api/activities/{id}
POST   /api/activities/{id}/start
GET    /api/activities/attempts/{id}/items
POST   /api/activities/attempts/{id}/submit
POST   /api/activities/attempts/{id}/complete
```

---

## 🎮 10 Game Types Available

1. **emoji_choice** - Pick an emoji
2. **text_copy_timed** - Type with time limit
3. **shape_copy_canvas** - Draw on canvas
4. **trace_the_path** - Trace a line
5. **dot_to_dot** - Connect dots in order
6. **find_the_different_one** - Spot the difference
7. **simple_puzzle_drag** - Drag puzzle pieces
8. **whats_missing** - Identify missing element
9. **listen_and_type** - Hear audio, type response
10. **unscramble_the_word** - Arrange letters into word

---

## 📊 Data Model

### Activity
```php
- id (primary key)
- title (string)
- description (text)
- estimated_duration_minutes (int)
- difficulty_level (enum: beginner|intermediate|advanced)
- min_age (int, nullable)
- max_age (int, nullable)
- is_active (boolean)
- order (int)
- timestamps & soft_deletes
```

### ActivityItem
```php
- id
- activity_id (FK)
- item_type (enum: 10 types)
- prompt_text (string)
- content_data (json)
- options (json)
- max_points (int)
- time_limit_seconds (int, nullable)
- order (int)
- timestamps & soft_deletes
```

### ActivityAttempt
```php
- id
- user_id (nullable)
- guest_session_id (string)
- activity_id (FK)
- child_id (nullable)
- final_score (int, nullable)
- consultation_needed (boolean)
- status (enum: started|in_progress|completed)
- started_at (timestamp)
- completed_at (timestamp, nullable)
- admin_notes (text, nullable)
- timestamps & soft_deletes
```

### Result
```php
- id
- activity_attempt_id (FK)
- activity_item_id (FK)
- result_data (json)
- points_awarded (int)
- time_taken_ms (int)
- is_correct (boolean)
- timestamps
```

---

## 🔍 Common Tasks

### Create Activity with Multiple Items
```
1. /admin/activities/create
2. Fill activity details
3. Add Item #1: emoji_choice
4. Add Item #2: text_copy_timed
5. Add Item #3: shape_copy_canvas
6. Click "Create Activity"
7. Gets redirected to /admin/activities/{id}/edit
8. Can add more items there
```

### Publish Activity
```
1. /admin/activities/{id}/edit
2. Check "Active (visible to users)"
3. Click "Save Changes"
4. Activity now visible in /activities
```

### Remove Activity
```
1. /admin/activities/{id}/edit
2. Click "Delete Activity" button
3. Confirm deletion
```

### Check User Results
```
1. /admin/activities/attempts
2. See table of all attempts
3. Click "View" on any row
4. See detailed results per item
5. View raw result_data (JSON)
```

---

## 🎨 UI Components Used

- **Lucide Vue Next**: All icons (Gamepad2, ChevronLeft, Save, Plus, etc.)
- **Tailwind CSS**: Styling with dark mode support
- **Vue 3**: Composition API with setup
- **Inertia.js**: Page navigation and forms
- **HTML5 Canvas**: Drawing games
- **Audio API**: Listen & type game
- **Axios**: API requests

---

## ✅ Verification Checklist

- [x] Admin pages created (Create, Edit, Attempts, Detail)
- [x] Public pages updated (Index, Game)
- [x] All 10 game types supported
- [x] Routes configured (admin, public, API)
- [x] Database tables migrated
- [x] Admin controller has all methods
- [x] Dark mode support throughout
- [x] Responsive design (mobile/tablet/desktop)
- [x] Guest session management
- [x] Server-side scoring
- [x] Sidebar navigation fixed
- [x] Error handling in place
- [x] Form validation working
- [x] Results display formatted nicely

---

## 🆘 Troubleshooting

### "Activity not found" when accessing /activities/{id}/play
- Check if activity exists in database
- Check if activity.is_active = 1
- Verify activity ID is correct

### Results not showing in /admin/activities/attempts
- Check if activity attempts were created
- Verify database migrations ran successfully
- Check if user_id or guest_session_id exists in attempts

### Game item not rendering
- Check item_type matches one of 10 types exactly
- Verify component is imported in Game.vue
- Check if item has required fields filled

### Sidebar link not working
- Clear browser cache
- Verify AppLayout is imported correctly
- Check route name is 'activities.index'

---

## 📞 Database Setup

All migrations already executed:
```
✓ 2025_11_14_100000_create_activities_table
✓ 2025_11_14_100001_create_activity_items_table
✓ 2025_11_14_100002_create_activity_attempts_table
✓ 2025_11_14_100003_create_results_table
```

Run to verify:
```bash
php artisan migrate:status
```

---

**Ready to use!** Start creating activities and testing games now. 🎮
