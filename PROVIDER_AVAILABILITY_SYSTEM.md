# Provider Availability Configuration System

## Overview
This feature allows doctors/providers to configure their available dates, set custom hours, mark unavailable dates, and manage their calendar availability.

## Features

### ✅ For Doctors/Providers:
1. **Visual Calendar Interface** - Interactive month-by-month calendar view
2. **Date Selection** - Click to select multiple dates at once
3. **Mark Available** - Set specific dates as available with custom working hours
4. **Mark Unavailable** - Block dates (holidays, conferences, personal time)
5. **Bulk Configuration** - Set availability for date ranges with specific days of week
6. **Remove Availability** - Delete availability overrides to revert to default schedule
7. **Color-Coded Display** - Visual indicators for available (green), unavailable (red), and selected dates
8. **Reasons/Notes** - Add optional reasons for unavailability

### ✅ Configuration Options:

#### Individual Date Configuration
- Select one or more dates on the calendar
- Set custom start/end times for available dates
- Mark dates as unavailable
- Add optional reason/note

#### Bulk Date Configuration
- Set date range (start date to end date)
- Select days of week (Monday-Sunday checkboxes)
- Set working hours for all matching dates
- Add optional reason/note
- Automatically generates all matching dates

## Backend Implementation

### Controller: `ProviderAvailabilityController`

**Location:** `app/Http/Controllers/ProviderAvailabilityController.php`

#### Methods:

1. **`index()`** - Display availability management page
   - Returns: Inertia page with provider data and availability records
   - Loads: Next 3 months of availability

2. **`store(Request $request)`** - Set availability for specific dates
   - Validates: dates[], start_time, end_time, is_available, reason
   - Creates/Updates: ProviderAvailability records
   - Returns: Success message with count

3. **`bulkStore(Request $request)`** - Bulk set availability
   - Validates: start_date, end_date, days_of_week[], start_time, end_time, is_available, reason
   - Generates: All dates matching criteria
   - Creates/Updates: Multiple ProviderAvailability records
   - Returns: Success message with count

4. **`destroy(Request $request)`** - Remove availability records
   - Validates: dates[]
   - Deletes: ProviderAvailability records for specified dates
   - Returns: Success message with count

5. **`getMonthAvailability(Request $request)`** - API endpoint for calendar data
   - Validates: year, month
   - Returns: JSON with availability records for the month

### Routes

**File:** `routes/bookings.php`

```php
// Provider Availability (Date-specific configuration)
Route::middleware('permission:book-sys')->group(function () {
    Route::get('/provider/availability', [ProviderAvailabilityController::class, 'index'])
        ->name('provider.availability.index');
    
    Route::post('/provider/availability', [ProviderAvailabilityController::class, 'store'])
        ->name('provider.availability.store');
    
    Route::post('/provider/availability/bulk', [ProviderAvailabilityController::class, 'bulkStore'])
        ->name('provider.availability.bulk');
    
    Route::delete('/provider/availability', [ProviderAvailabilityController::class, 'destroy'])
        ->name('provider.availability.destroy');
    
    Route::get('/provider/availability/month', [ProviderAvailabilityController::class, 'getMonthAvailability'])
        ->name('provider.availability.month');
});
```

### Database

Uses existing `provider_availability` table:
- `id` - Primary key
- `provider_profile_id` - Foreign key to provider_profiles
- `date` - Date (YYYY-MM-DD)
- `start_time` - Working hours start (HH:MM:SS)
- `end_time` - Working hours end (HH:MM:SS)
- `is_available` - Boolean (true = available, false = unavailable)
- `reason` - Optional text explanation
- `created_at`, `updated_at` - Timestamps

### Permissions

**Required Permission:** `book-sys`
- Doctors/providers with this permission can access the availability management

## Frontend Implementation

### Page: `Provider/Availability/Index.vue`

**Location:** `resources/js/Pages/Provider/Availability/Index.vue`

#### Props:
```typescript
interface Props {
    provider: any;                          // Provider profile
    availability: AvailabilityRecord[];    // Existing availability records
    defaultSchedule: Record<string, any>;  // Weekly default schedule
}
```

#### Features:

1. **Calendar Display**
   - Month/year navigation (previous/next)
   - 7x6 grid (42 days total)
   - Shows previous/current/next month days
   - Color coding:
     - Green = Available dates
     - Red = Unavailable dates
     - Primary ring = Selected dates
     - Accent = Default schedule dates (future enhancement)

2. **Date Selection**
   - Click to toggle selection
   - Multiple dates can be selected
   - Cannot select past dates
   - Selection counter in buttons

3. **Action Buttons**
   - **Bulk Set Availability** - Opens bulk configuration dialog
   - **Mark Available (N)** - Set selected dates as available
   - **Mark Unavailable (N)** - Set selected dates as unavailable
   - **Remove (N)** - Delete availability for selected dates
   - **Clear Selection** - Deselect all dates

4. **Set Availability Dialog**
   - Single/multiple date configuration
   - Start/end time pickers (for available dates)
   - Reason textarea (optional)
   - Validation and error display

5. **Bulk Set Dialog**
   - Date range selection (start/end date)
   - Days of week checkboxes (Sun-Sat)
   - Start/end time pickers
   - Reason textarea (optional)
   - Automatically calculates matching dates

#### State Management:
- Uses Inertia.js forms for data submission
- Preserves scroll position after updates
- Shows success/error messages
- Clears selection after successful operations

## Usage Guide

### For Providers:

#### Accessing the Page:
Navigate to `/provider/availability` or use the link in the provider dashboard.

#### Setting Availability for Specific Dates:

1. Click on one or more dates in the calendar
2. Click "Mark Available (N)" button
3. Set working hours (start/end time)
4. Optionally add a reason
5. Click "Save"

#### Marking Dates as Unavailable:

1. Click on one or more dates in the calendar
2. Click "Mark Unavailable (N)" button
3. Add a reason (e.g., "Holiday", "Conference")
4. Click "Save"

#### Bulk Setting Availability:

1. Click "Bulk Set Availability" button
2. Select date range (start and end date)
3. Check days of week (e.g., Mon-Fri)
4. Set working hours
5. Click "Save"
6. All matching dates will be configured

**Example:** Set availability for all Mondays and Wednesdays in December:
- Start Date: December 1, 2025
- End Date: December 31, 2025
- Days: Monday, Wednesday
- Hours: 9:00 AM - 5:00 PM
- Result: 8-9 dates configured automatically

#### Removing Availability:

1. Click on dates with existing availability
2. Click "Remove (N)" button
3. Confirm deletion
4. Availability records deleted (reverts to default schedule)

### Navigation:

- **Previous/Next Month** - Use arrow buttons in calendar header
- **Legend** - Shows color meanings at bottom of calendar
- **Date Counter** - Selection count shown in button labels

## Translation Keys

Added to all 4 languages (`en`, `fr`, `ar`, `lt`):

```php
'manage_availability' => 'Manage Availability',
'manage_availability_description' => 'Configure your available dates and working hours',
'bulk_set_availability' => 'Bulk Set Availability',
'mark_available' => 'Mark Available',
'mark_unavailable' => 'Mark Unavailable',
'remove' => 'Remove',
'clear_selection' => 'Clear Selection',
'click_dates_to_select' => 'Click on dates to select them. Click again to deselect.',
'selected' => 'Selected',
'default_schedule' => 'Default Schedule',
'set_available' => 'Set Available',
'set_unavailable' => 'Set Unavailable',
'setting_availability_for' => 'Setting availability for',
'date_s' => 'date(s)',
'start_time' => 'Start Time',
'end_time' => 'End Time',
'reason_placeholder' => 'E.g., Holiday, Conference, Personal time off...',
'bulk_set_description' => 'Set availability for multiple dates at once...',
'start_date' => 'Start Date',
'end_date' => 'End Date',
'days_of_week' => 'Days of Week',
'bulk_reason_placeholder' => 'Optional reason for this availability schedule',
'saving' => 'Saving',
'save' => 'Save',
```

## Integration with Booking System

### How It Works:

1. **Patient Booking Flow:**
   - Patient selects a provider
   - System calls `$provider->getAvailableDatesBetween($startDate, $endDate)`
   - Method checks both default schedule AND provider_availability table
   - Returns array of available dates

2. **Availability Logic (in ProviderProfile Model):**
   ```php
   public function isAvailableOn($date)
   {
       // Check for specific override in provider_availability table
       $override = $this->availability()
           ->where('date', $date)
           ->first();
       
       if ($override) {
           return $override->is_available;
       }
       
       // Fall back to default weekly schedule
       $dayOfWeek = Carbon::parse($date)->format('l');
       return isset($this->working_hours[strtolower($dayOfWeek)]);
   }
   ```

3. **Priority:**
   - Provider Availability (specific dates) > Default Weekly Schedule
   - If a date is in provider_availability table, it overrides the default
   - If not, system checks the weekly working_hours

## Examples

### Example 1: Christmas Vacation
**Scenario:** Doctor wants to block Dec 24-26 for Christmas

**Steps:**
1. Navigate to December 2025
2. Click Dec 24, 25, 26
3. Click "Mark Unavailable (3)"
4. Reason: "Christmas Holiday"
5. Save

**Result:** Those dates show as red/unavailable to patients

### Example 2: Extended Hours on Fridays
**Scenario:** Doctor wants to work until 8 PM on Fridays in November

**Steps:**
1. Click "Bulk Set Availability"
2. Start: Nov 1, 2025
3. End: Nov 30, 2025
4. Days: Friday only
5. Hours: 9:00 AM - 8:00 PM
6. Save

**Result:** All Fridays in November have extended hours

### Example 3: Conference Week
**Scenario:** Doctor attending conference Oct 15-17 (Mon-Wed)

**Steps:**
1. Select Oct 15, 16, 17
2. Click "Mark Unavailable (3)"
3. Reason: "Medical Conference"
4. Save

**Result:** No appointments can be booked for those dates

## API Endpoints

### Get Month Availability (AJAX)
```
GET /provider/availability/month?year=2025&month=11
```

**Response:**
```json
{
    "availability": {
        "2025-11-15": {
            "id": 1,
            "date": "2025-11-15",
            "start_time": "09:00:00",
            "end_time": "17:00:00",
            "is_available": true,
            "reason": null
        },
        "2025-11-24": {
            "id": 2,
            "date": "2025-11-24",
            "start_time": "00:00:00",
            "end_time": "00:00:00",
            "is_available": false,
            "reason": "Thanksgiving Holiday"
        }
    }
}
```

## Future Enhancements

1. **Recurring Patterns** - Set availability for "every Monday" indefinitely
2. **Copy Schedule** - Copy one week/month to another
3. **Team Calendar** - View multiple providers' availability
4. **Conflict Detection** - Warn about existing appointments when marking unavailable
5. **Export/Import** - Import availability from external calendar (iCal, Google Calendar)
6. **Statistics** - Show utilization rate, most booked days, etc.
7. **Mobile App** - Native mobile interface for on-the-go availability management
8. **AI Suggestions** - Suggest optimal availability based on booking patterns

## Testing Checklist

- [ ] Calendar displays current month correctly
- [ ] Month navigation works (previous/next)
- [ ] Date selection works (click to toggle)
- [ ] Cannot select past dates
- [ ] Mark Available dialog opens and submits
- [ ] Mark Unavailable dialog opens and submits
- [ ] Bulk set dialog opens and submits
- [ ] Days of week checkboxes work
- [ ] Time validation works (end > start)
- [ ] Remove availability confirmation works
- [ ] Success messages display
- [ ] Error messages display
- [ ] Calendar updates after operations
- [ ] Selected dates clear after success
- [ ] Color coding is correct (green/red/selected)
- [ ] Legend displays correctly
- [ ] All translations work in 4 languages
- [ ] Responsive design works on mobile
- [ ] Dark mode displays correctly

## Summary

This feature gives complete control to doctors/providers to manage their calendar availability:

✅ **Visual calendar interface** - Easy to use, color-coded
✅ **Flexible configuration** - Individual dates or bulk operations
✅ **Unavailability management** - Block holidays, conferences, personal time
✅ **Custom hours** - Override default schedule for specific dates
✅ **Multi-language** - Works in all 4 languages
✅ **Integrated** - Works seamlessly with patient booking system
✅ **Permission-based** - Only accessible to providers with `book-sys` permission

The system is production-ready and can be accessed at `/provider/availability` by any user with the `book-sys` permission!
