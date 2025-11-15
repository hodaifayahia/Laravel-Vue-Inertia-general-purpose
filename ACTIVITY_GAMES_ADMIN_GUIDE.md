# Activity Games Admin Management Guide

## Overview
The admin dashboard provides complete control over activity games and task configuration for the dysgraphia platform.

## Accessing Admin Panel

1. **Navigate to Admin Activities**: `/admin/activities`
2. **Login Required**: Must have admin role with proper permissions
3. **Features Available**:
   - Create new activities/games
   - Edit existing activities
   - Manage activity items (tasks)
   - View player attempts and results
   - Add/remove tasks from games

---

## Creating a New Activity/Game

### Steps:
1. Click **"Create Activity"** button from the Activities list
2. Fill in the following information:
   - **Title**: Name of the game (e.g., "Memory Match")
   - **Description**: Brief description of what the game does
   - **Duration**: Estimated time in minutes (5-300)
   - **Difficulty Level**: Beginner / Intermediate / Advanced
   - **Age Range**: Minimum and maximum age (optional)
   - **Active**: Toggle to make visible/hidden to users

3. Click **Save**

---

## Managing Activity Items (Tasks)

### Add a Task to a Game:
1. Go to **Edit Activity** page
2. In the **Activity Items** section, select:
   - **Game Type**: Choose from 10 game types (see list below)
   - **Prompt Text**: Instructions/question for the player
   - **Max Points**: Points awarded for completion (0-1000)
   - **Time Limit**: Optional time limit in seconds
3. Click **"Add Item"** button

### Game Types Available:

| Game Type | Description |
|-----------|-------------|
| **Emoji Choice** | Self-report emotional check-in with emojis |
| **Timed Typing Test** | Measure typing speed and accuracy |
| **Shape Drawing** | Player replicates shapes on canvas |
| **Path Tracing** | Trace a pre-drawn path with mouse/finger |
| **Connect the Dots** | Traditional dot-to-dot drawing |
| **Find the Different One** | Spot the odd one out |
| **Simple Puzzle** | Drag-and-drop puzzle pieces |
| **What's Missing** | Identify missing elements in image |
| **Listen and Type** | Audio prompt with text transcription |
| **Unscramble the Word** | Rearrange letters to form words |

### Edit a Task:
1. Click **Edit** next to the task in the items list
2. Modify the fields as needed
3. Save changes

### Delete a Task:
1. Click the **Delete** (trash icon) button next to the task
2. Confirm deletion

---

## Viewing Player Results

### Access Results:
1. Click **"View Results"** button from Activities list
2. See all player attempts with:
   - Player name / guest session
   - Activity played
   - Time spent
   - Score/completion status
   - Consultation needs

### Detailed Results:
1. Click on any attempt to view detailed breakdown
2. See scores for each task
3. Add admin notes for consultation recommendations

---

## Task Configuration Options

### For Each Task:
- **Prompt Text**: What the player sees (question/instruction)
- **Max Points**: Points possible for this task
- **Time Limit**: Optional duration in seconds
- **Item Type**: The game mechanics type

### Example Configurations:

**Beginner Activity - Shape Recognition**
- Duration: 10 minutes
- Tasks:
  1. Shape Drawing (30 sec, 10 pts)
  2. Find Different Shape (30 sec, 10 pts)
  3. Shape Memory (60 sec, 20 pts)

**Intermediate Activity - Fine Motor Skills**
- Duration: 15 minutes
- Tasks:
  1. Path Tracing (60 sec, 25 pts)
  2. Connect Dots (90 sec, 25 pts)
  3. Text Copy (timed) (120 sec, 25 pts)

**Advanced Activity - Hand-Eye Coordination**
- Duration: 20 minutes
- Tasks:
  1. Listen and Type (120 sec, 33 pts)
  2. Simple Puzzle (150 sec, 33 pts)
  3. Unscramble Word (120 sec, 34 pts)

---

## Best Practices

1. **Age Appropriate**: Set min/max ages for the activity
2. **Progressive Difficulty**: Structure tasks from easy to hard
3. **Time Limits**: Set realistic limits based on expected skill level
4. **Point Distribution**: Make total points meaningful (usually 100, 50, or 25)
5. **Clear Instructions**: Keep prompt text simple and understandable
6. **Testing**: Test activities before making active
7. **Monitoring**: Regularly check player results for feedback

---

## Database Models

### Activity
- title, description, estimated_duration_minutes
- difficulty_level (beginner/intermediate/advanced)
- min_age, max_age, is_active, order

### ActivityItem
- activity_id, item_type, prompt_text
- max_points, time_limit_seconds, order
- content_data (JSON), options (JSON)

### ActivityAttempt
- user_id, guest_session_id, activity_id
- status (in_progress/completed)
- started_at, completed_at, final_score

### Result
- activity_attempt_id, activity_item_id
- result_data (JSON), points_awarded
- time_taken_ms, is_correct

---

## API Endpoints (For Reference)

**Public API** (no auth required):
- `GET /api/activities` - List all active activities
- `GET /api/activities/{id}` - Get activity details
- `POST /api/activities/{id}/start` - Start an attempt

**Admin Routes** (auth required):
- `GET /admin/activities` - List activities
- `GET /admin/activities/create` - Create form
- `POST /admin/activities` - Store new activity
- `GET /admin/activities/{id}/edit` - Edit form
- `PUT /admin/activities/{id}` - Update activity
- `POST /admin/activities/{id}/items` - Add task
- `DELETE /admin/activities/items/{id}` - Remove task
- `GET /admin/activities/attempts` - View attempts
- `GET /admin/activities/attempts/{id}` - Attempt details

---

## Troubleshooting

**Can't see Activities menu?**
- Ensure you have admin role
- Check if "view activities" permission is assigned

**Task won't save?**
- Verify all required fields are filled
- Check max_points is between 0-1000
- Ensure prompt_text is not empty

**No player data showing?**
- Verify activity is marked as "Active"
- Check that attempts exist in database
- Guest sessions should be tracked via localStorage

---

## Support

For technical issues or questions about specific game types, refer to the game component documentation in:
`resources/js/Components/Activities/`

Each game type has its own Vue component with detailed configuration options.
