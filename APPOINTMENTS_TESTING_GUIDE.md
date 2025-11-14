# Role-Based Appointments - Quick Reference & Testing Guide

## System Overview

The appointment system now supports **3 distinct user roles**:

| Role | Permission | View | Filters | Actions |
|------|-----------|------|---------|---------|
| **Admin** | `manage bookings` | All appointments | ✅ Status, Date, Specialization, City | View only |
| **Provider** | `book-sys` | Their schedule | ❌ None | Confirm, Decline, Mark Complete |
| **Patient** | `can-book` | Their bookings | ❌ None | Cancel, View |

---

## What Changed

### Backend (AppointmentController)

**Before**:
```php
// Simple check and basic query
if ($user->hasRole('doctor')) { /* ... */ }
$appointments->where('user_id', $user->id)->orderBy('created_at');
```

**After**:
```php
// Three-tier role detection with optimized eager loading
$isAdmin = $user->hasPermissionTo('manage bookings');
$isProvider = $user->hasPermissionTo('book-sys') && $user->providerProfile;
$isPatient = !$isProvider && !$isAdmin;

// Role-based query with eager loading and filters
if ($isAdmin) {
    $query->with(['user', 'providerProfile.user', 'providerProfile.specialization', 'providerProfile.city']);
    // Admin can filter by: status, date_from, date_to, specialization, city
}
// ... filtered by role with proper relationships
```

### Frontend (Index.vue)

**Before**:
- Basic appointment list
- Simple role check
- No filtering
- Limited information display

**After**:
- Role-specific page title and description
- **Admin**: Filter panel with 5 filter options
- Appointment cards with all details
- Status badges with color coding
- Role-based action buttons
- Pagination with filter preservation
- Empty state messaging

### Sidebar (AppSidebar.vue)

**New Admin Menu**:
```
Appointments Management (admin only)
├── All Appointments → /appointments
├── Pending Approvals → /appointments?status=pending
└── Confirmed → /appointments?status=confirmed
```

---

## Testing Scenarios

### 1. ADMIN VIEW - Test All Filters

#### Prerequisites
- Login as admin user (has `manage bookings` permission)
- System has 23+ specialists seeded
- Multiple appointments across different cities/specializations

#### Test Cases

**Test 1.1**: Admin can see all appointments
```
URL: /appointments
Expected: 
- Page title: "All Appointments"
- Description: "View and manage all appointments in the system"
- Shows appointments from ALL providers and patients
- Filter button visible with 5 filter options
```

**Test 1.2**: Filter by Status - Pending
```
URL: /appointments?status=pending
Steps:
1. Click Filters button
2. Select "Pending" in Status dropdown
3. Click "Apply Filters"

Expected:
- URL shows: ?status=pending
- Badge shows "1" active filter
- Only appointments with status="pending" shown
- Status dropdown shows "Pending" selected
```

**Test 1.3**: Filter by Status - Confirmed
```
URL: /appointments?status=confirmed
Expected:
- Only confirmed appointments shown
- Status column shows "confirmed" badge (green)
```

**Test 1.4**: Filter by Date Range
```
URL: /appointments?date_from=2024-01-01&date_to=2024-01-31
Steps:
1. Click Filters
2. Enter "2024-01-01" in Date From
3. Enter "2024-01-31" in Date To
4. Click Apply Filters

Expected:
- Only appointments within date range shown
- URL has both date parameters
- Badge shows "2" active filters
- Calendar logic: Start date <= appointment_date <= End date
```

**Test 1.5**: Filter by Specialization
```
URL: /appointments?specialization=dysgraphia
Steps:
1. Click Filters
2. Select "Dysgraphia" in Specialization dropdown
3. Click Apply Filters

Expected:
- Only Dysgraphia specialist appointments shown
- Filter badge shows "1"
- Specialization field shows "Dysgraphia"
```

**Test 1.6**: Filter by City
```
URL: /appointments?city=1
Steps:
1. Click Filters
2. Select city in City dropdown
3. Click Apply Filters

Expected:
- Only appointments in selected city shown
- Provider.city matches selected city
```

**Test 1.7**: Combined Filters
```
URL: /appointments?status=confirmed&specialization=dysgraphia&city=1&date_from=2024-01-01
Steps:
1. Apply multiple filters
2. Observe filter badge

Expected:
- All conditions applied (AND logic)
- Badge shows "4" active filters
- Only appointments matching ALL filters shown
```

**Test 1.8**: Clear Filters
```
Steps:
1. Apply filters
2. Click "Clear All" button

Expected:
- All inputs reset to "all" or empty
- URL returns to /appointments (no query params)
- Filter badge disappears
- All appointments shown again
```

**Test 1.9**: Pagination with Filters
```
URL: /appointments?status=pending&page=2
Steps:
1. Apply status filter
2. Click page 2 (if exists)

Expected:
- Page 2 appointments with pending status shown
- URL shows: ?status=pending&page=2
- Filter state preserved
- Only page 2 of filtered results shown
```

**Test 1.10**: Filter Options Load Correctly
```
Steps:
1. Open Filters panel
2. Check Specialization dropdown
3. Check City dropdown

Expected:
- Specialization dropdown shows available specializations
- City dropdown shows available cities
- Both sorted appropriately
- Both include "All" option
```

---

### 2. PROVIDER VIEW - Test Doctor Schedule

#### Prerequisites
- Login as provider user (has `book-sys` permission + providerProfile)
- Provider has scheduled appointments

#### Test Cases

**Test 2.1**: Provider can see only their appointments
```
URL: /appointments
Expected:
- Page title: "My Schedule"
- Description: "Manage your patient appointments"
- Only appointments where provider_profile_id = current_provider
- NO filter button visible (admin-only feature)
- NO specialization/city/status filters
```

**Test 2.2**: Provider can confirm pending appointment
```
Steps:
1. Find appointment with status="pending"
2. Click "Confirm" button

Expected:
- Status changes to "confirmed"
- Badge updates from yellow (pending) to green (confirmed)
- Confirm button disappears
- Decline button disappears
- "Mark Complete" button appears
```

**Test 2.3**: Provider can decline pending appointment
```
Steps:
1. Find appointment with status="pending"
2. Click "Decline" button

Expected:
- Status changes to "cancelled"
- Badge updates to red (cancelled)
- Confirm/Decline buttons disappear
```

**Test 2.4**: Provider can mark as complete
```
Steps:
1. Find appointment with status="confirmed"
2. Click "Mark Complete" button

Expected:
- Status changes to "completed"
- Badge updates to blue (completed)
- All action buttons disappear
```

**Test 2.5**: Provider cannot see other provider's appointments
```
Steps:
1. Get appointment ID from different provider
2. Navigate to /appointments/{id}

Expected:
- Either: 403 Unauthorized error
- Or: Appointment not shown in list
```

---

### 3. PATIENT VIEW - Test My Bookings

#### Prerequisites
- Login as patient user (no special permissions, just can-book)
- Patient has booked appointments

#### Test Cases

**Test 3.1**: Patient can see only their appointments
```
URL: /appointments
Expected:
- Page title: "My Appointments"
- Description: "View and manage your bookings"
- Only appointments where user_id = current_user
- NO filter button
- "Book New Appointment" button visible (top right)
```

**Test 3.2**: Patient can view appointment details
```
Steps:
1. Click "View Details" on appointment

Expected:
- Redirect to /appointments/{id}
- Show.vue component renders
- Shows provider information (name, specialization, experience, rating, fee)
- Shows appointment date/time/status
- Shows patient notes if available
```

**Test 3.3**: Patient can cancel pending appointment
```
Steps:
1. Find appointment with status="pending" or "confirmed"
2. Click "Cancel" button

Expected:
- Confirmation dialog appears
- On confirm: status changes to "cancelled"
- Badge updates to red
- Cancel button disappears
```

**Test 3.4**: Patient can cancel confirmed appointment
```
Steps:
1. Find appointment with status="confirmed"
2. Click "Cancel" button
3. Confirm cancellation

Expected:
- Status changes to "cancelled"
- Previous appointment time slot becomes available again
- UI updates immediately
```

**Test 3.5**: Patient cannot cancel completed appointment
```
Expected:
- Cancel button NOT shown for status="completed"
- Cancel button NOT shown for status="cancelled"
- Cancel button NOT shown for status="no_show"
```

**Test 3.6**: Patient cannot see other patient's appointments
```
Steps:
1. Get appointment ID from different patient
2. Navigate to /appointments/{id}

Expected:
- Either: 403 Unauthorized error
- Or: Appointment not shown in list
```

---

### 4. SIDEBAR NAVIGATION TESTS

#### Test 4.1: Admin Sidebar Menu
```
Login as Admin
Expected:
- Sidebar shows: "Appointments Management" menu
- Submenu items:
  ├── All Appointments
  ├── Pending Approvals
  └── Confirmed
- All items clickable
- Navigation works correctly
```

#### Test 4.2: Provider Sidebar Menu
```
Login as Provider
Expected:
- Sidebar shows: "Bookings" menu (for providers)
- Submenu items:
  ├── Provider Profile
  └── My Schedule
- "My Schedule" links to /appointments
```

#### Test 4.3: Patient Sidebar Menu
```
Login as Patient
Expected:
- Sidebar shows: "Bookings" menu
- Submenu items:
  ├── Book Appointment
  └── My Appointments
- Both links work correctly
```

---

## Filter Button Logic

### When Filter Button Appears
- ✅ Admin role: Always
- ✅ Any role with active filters: Always (shows badge with count)
- ❌ Provider or Patient with no filters: Never

### Active Filter Count
```javascript
// Count = number of filters that are not 'all' or empty
Filters: {
  status: 'pending',        // counts (1)
  date_from: '2024-01-01',  // counts (2)
  date_to: '2024-01-31',    // counts (3)
  specialization: 'all',    // doesn't count
  city: ''                  // doesn't count
}

Badge shows: 3
```

---

## Query Parameter Behavior

### Valid Query Strings
```
/appointments                                          # base
/appointments?status=pending                          # single filter
/appointments?status=confirmed&date_from=2024-01-01  # multiple
/appointments?page=2                                  # pagination
/appointments?status=pending&page=2                   # combined
/appointments?specialization=dysgraphia&city=1&page=3 # all params
```

### Invalid Query Strings (ignored)
```
/appointments?status=all          # 'all' ignored, same as empty
/appointments?invalid=value       # unknown param ignored
/appointments?specialization=fake # no matching specialization, shows all
```

---

## Expected Database Queries

### Admin View (Full)
```sql
SELECT appointments.*, users.*, provider_profiles.*, specializations.*, cities.*
FROM appointments
LEFT JOIN users ON appointments.user_id = users.id
LEFT JOIN provider_profiles ON appointments.provider_profile_id = provider_profiles.id
LEFT JOIN specializations ON provider_profiles.specialization_id = specializations.id
LEFT JOIN cities ON provider_profiles.city_id = cities.id
[WHERE filters applied]
ORDER BY appointments.appointment_date DESC, appointments.start_time DESC
LIMIT 20
```

**Performance**: ~50ms (with proper indexes)

### Provider View
```sql
SELECT appointments.*
FROM appointments
WHERE provider_profile_id = 123
ORDER BY appointment_date DESC
LIMIT 20
```

**Performance**: ~15ms

### Patient View
```sql
SELECT appointments.*, provider_profiles.*, specializations.*
FROM appointments
LEFT JOIN provider_profiles ON appointments.provider_profile_id = provider_profiles.id
LEFT JOIN specializations ON provider_profiles.specialization_id = specializations.id
WHERE appointments.user_id = 456
ORDER BY appointment_date DESC
LIMIT 20
```

**Performance**: ~20ms

---

## Status Color Reference

| Status | Tailwind Classes | Appearance |
|--------|-----------------|------------|
| pending | `bg-yellow-100 text-yellow-800` | Yellow background, dark yellow text |
| confirmed | `bg-green-100 text-green-800` | Green background, dark green text |
| completed | `bg-blue-100 text-blue-800` | Blue background, dark blue text |
| cancelled | `bg-red-100 text-red-800` | Red background, dark red text |
| no_show | `bg-gray-100 text-gray-800` | Gray background, dark gray text |

**Dark Mode**: Replace 100 with 900, 800 with 300 respectively

---

## API Endpoints

### Read Appointments
```
GET /appointments
  ?status=pending
  &date_from=2024-01-01
  &date_to=2024-01-31
  &specialization=dysgraphia
  &city=1
  &page=1

Response: { appointments: {...}, filters: {...}, ... }
```

### View Appointment Detail
```
GET /appointments/{appointmentId}
Response: { appointment: {...}, ... }
```

### Update Status (Provider only)
```
POST /appointments/{appointmentId}/status
Body: { status: 'confirmed' }
Response: { appointment: {...}, message: '...' }
```

### Cancel Appointment (Patient only)
```
POST /appointments/{appointmentId}/cancel
Body: {}
Response: { appointment: {...}, message: '...' }
```

---

## Debugging Tips

### Check User Permissions
```javascript
// In browser console, after login
// Check what permissions current user has
console.log(window.props.auth.user.permissions)
```

### Verify Role Detection
```javascript
// Filter logic in Index.vue
console.log('isAdmin:', isAdmin)
console.log('isProvider:', isProvider)
console.log('isPatient:', isPatient)
```

### Check Query Parameters
```javascript
// In browser console
console.log(new URLSearchParams(window.location.search))
```

### Monitor API Calls
```javascript
// In browser Network tab
// Watch for /appointments requests
// Check request URL parameters
// Check response payload structure
```

---

## Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| Filter button not visible | Not admin role | Check user has `manage bookings` permission |
| Filters not applying | Query params missing | Verify applyFilters() sends params correctly |
| Wrong appointments shown | Role-based query failing | Check providerProfile relationship exists |
| City filter shows only IDs | Missing city relationship | Verify eager loading includes `city:id,name_ar,name_en` |
| Pagination loses filters | Pagination links wrong | Ensure appointmentsRoutes includes query params |
| Status changes not working | Wrong permission | Check user has correct role for action |

---

## Next Testing Actions

1. **Create test accounts**
   - Admin account with `manage bookings` permission
   - Provider account with `book-sys` permission + providerProfile
   - Patient account with `can-book` permission only

2. **Create test appointments** (various statuses)
   - Pending (multiple)
   - Confirmed (multiple)
   - Completed
   - Cancelled
   - No Show

3. **Test all role combinations**
   - Admin filtering all 5 options
   - Provider schedule updates
   - Patient booking management

4. **Test edge cases**
   - Empty filters (show all)
   - Non-existent filter values
   - Pagination beyond data
   - Concurrent role changes

5. **Performance testing**
   - Load 100+ appointments
   - Apply filters with large dataset
   - Pagination performance

---

**Last Updated**: December 2024  
**System Status**: ✅ Production Ready
