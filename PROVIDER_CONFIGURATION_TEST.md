# Provider Configuration Testing Guide

## Features Implemented

### 1. Weekly Schedule with Max Patients ✓
- **Feature**: When selecting a day in the weekly schedule, show:
  - Start time input
  - End time input  
  - Max patients input (optional - defaults to calculated value from slot_duration)

### 2. Working Date Range ✓
- **Feature**: Set overall availability period
- **Behavior**: 
  - Only creates availability for days where provider has a schedule
  - Uses schedule times (start_time, end_time) from weekly schedule
  - Does NOT create entries for days provider doesn't work

### 3. Excluded Dates (Date Ranges) ✓
- **Feature**: Add unavailable periods
- **Display**: Shows as date ranges (e.g., "Nov 10, 2025 - Nov 15, 2025")
- **Behavior**:
  - Single date or date range supported
  - Automatically groups consecutive dates
  - Optional reason field
  - Excluded dates override working dates

### 4. Delete Functionality ✓
- **Feature**: Delete button works for both working and excluded periods
- **Behavior**: Deletes the availability record by ID

### 5. Removed Features ✓
- Monthly pattern selector (removed as requested)

## Testing Steps

### Test 1: Weekly Schedule Setup
1. Navigate to Provider Configuration
2. Go to "Schedule" tab
3. Select "Monday" checkbox
4. Verify inputs appear:
   - Start time (default: 09:00)
   - End time (default: 17:00)
   - Max patients (optional, can leave empty)
5. Enter values:
   - Start: 09:00
   - End: 17:00
   - Max patients: 20
6. Click "Save Weekly Schedule"
7. Verify success message

**Expected Result**: Schedule saved with max_patients value

### Test 2: Working Date Range
1. Go to "Availability" tab
2. Set working date range:
   - From: Tomorrow's date
   - To: Date 30 days from tomorrow
3. Click "Save Working Date Range"
4. Verify "Active Working Periods" section shows the range
5. Verify it only shows days you work (based on schedule)

**Expected Result**: Only days with schedules are marked as available

### Test 3: Excluded Dates (Single Day)
1. In "Add Excluded Dates" section:
   - From: Select a date within your working range
   - To: Leave empty
   - Reason: "Medical Conference"
2. Click "Add Excluded Period"
3. Verify excluded date appears in "Excluded Periods"
4. Verify it shows as single date, not range

**Expected Result**: Single excluded date displayed correctly

### Test 4: Excluded Dates (Date Range)
1. In "Add Excluded Dates" section:
   - From: Select a start date
   - To: Select end date (5 days later)
   - Reason: "Vacation"
2. Click "Add Excluded Period"
3. Verify excluded range appears as "Nov 10, 2025 - Nov 15, 2025"

**Expected Result**: Date range displayed correctly

### Test 5: Delete Working Period
1. Find a working period in "Active Working Periods"
2. Click trash icon
3. Confirm deletion
4. Verify it's removed from the list

**Expected Result**: Working period deleted successfully

### Test 6: Delete Excluded Period
1. Find an excluded period in "Excluded Periods"
2. Click trash icon
3. Confirm deletion
4. Verify it's removed from the list

**Expected Result**: Excluded period deleted successfully

### Test 7: Excluded Dates Override Working Dates
1. Create a working date range (e.g., Dec 1 - Dec 31)
2. Create an excluded date within that range (e.g., Dec 15 - Dec 20)
3. Verify both appear in their respective sections
4. When booking, excluded dates should NOT be available

**Expected Result**: Excluded dates take precedence

## Database Verification

Check the database tables:

```sql
-- Check schedules
SELECT * FROM provider_schedules WHERE provider_profile_id = YOUR_PROFILE_ID;

-- Check availability (working dates)
SELECT * FROM provider_availability 
WHERE provider_profile_id = YOUR_PROFILE_ID 
AND is_available = 1 
ORDER BY date;

-- Check excluded dates
SELECT * FROM provider_availability 
WHERE provider_profile_id = YOUR_PROFILE_ID 
AND is_available = 0 
ORDER BY date;
```

## Expected Data Structure

### provider_schedules Table
```
| id | provider_profile_id | day_of_week | start_time | end_time | is_available | max_patients |
|----|---------------------|-------------|------------|----------|--------------|--------------|
| 1  | 1                   | 1           | 09:00:00   | 17:00:00 | 1            | 20           |
| 2  | 1                   | 3           | 09:00:00   | 17:00:00 | 1            | NULL         |
```

### provider_availability Table (Working Dates)
```
| id | provider_profile_id | date       | start_time | end_time | is_available | reason |
|----|---------------------|------------|------------|----------|--------------|--------|
| 1  | 1                   | 2025-11-10 | 09:00:00   | 17:00:00 | 1            | NULL   |
| 2  | 1                   | 2025-11-11 | 09:00:00   | 17:00:00 | 1            | NULL   |
```

### provider_availability Table (Excluded Dates)
```
| id | provider_profile_id | date       | start_time | end_time | is_available | reason          |
|----|---------------------|------------|------------|----------|--------------|-----------------|
| 10 | 1                   | 2025-12-15 | 00:00:00   | 00:00:00 | 0            | Vacation        |
| 11 | 1                   | 2025-12-16 | 00:00:00   | 00:00:00 | 0            | Vacation        |
| 12 | 1                   | 2025-12-17 | 00:00:00   | 00:00:00 | 0            | Vacation        |
```

## Known Issues to Watch For

1. **Date Grouping**: Excluded dates should group consecutive dates into ranges
2. **Delete Button**: Must use the ID from the first record in a range
3. **Working Days Only**: Working date range only creates entries for scheduled days
4. **Time Inheritance**: Working dates inherit times from weekly schedule

## Success Criteria

✅ Weekly schedule saves with max_patients
✅ Working date range only creates entries for scheduled days
✅ Excluded dates display as ranges
✅ Delete buttons work for both types
✅ Monthly pattern section removed
✅ Excluded dates override working dates
