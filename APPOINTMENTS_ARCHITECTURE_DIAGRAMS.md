# Appointments System - Architecture & Flow Diagrams

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     APPOINTMENTS SYSTEM                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────┐         ┌──────────────┐   ┌──────────────┐  │
│  │   Patient    │         │   Provider   │   │    Admin     │  │
│  └──────┬───────┘         └──────┬───────┘   └──────┬───────┘  │
│         │ can-book                │ book-sys          │ manage   │
│         │                         │                   │ bookings │
│         └────────────┬────────────┴───────────────────┘         │
│                      │                                          │
│         ┌────────────▼─────────────┐                            │
│         │  AppointmentController   │                            │
│         │  - index()               │                            │
│         │  - create()              │                            │
│         │  - store()               │                            │
│         │  - show()                │                            │
│         │  - cancel()              │                            │
│         │  - updateStatus()        │                            │
│         │  - destroy()             │  ◄─ NEW                    │
│         └────────────┬─────────────┘                            │
│                      │                                          │
│         ┌────────────▼──────────────────┐                       │
│         │   Appointments Table          │                       │
│         │                               │                       │
│         │ - id                          │                       │
│         │ - provider_profile_id         │                       │
│         │ - user_id                     │                       │
│         │ - appointment_date            │                       │
│         │ - start_time / end_time       │                       │
│         │ - status (5 states)           │                       │
│         │ - notes                       │                       │
│         │ - reminders_sent              │                       │
│         └────────────┬──────────────────┘                       │
│                      │                                          │
│         ┌────────────▼─────────────────────┐                    │
│         │  Vue Components                  │                    │
│         │  - Index.vue (list + filters)    │                    │
│         │  - Show.vue (details)            │                    │
│         │  - actions (cancel/confirm)      │                    │
│         │  - delete (NEW - admin)          │                    │
│         └──────────────────────────────────┘                    │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

---

## User Role Flow

```
                         START
                          │
                          ▼
                   Authentication
                          │
            ┌─────────────┼─────────────┐
            │             │             │
            ▼             ▼             ▼
        Patient       Provider         Admin
            │             │             │
     ┌──────┴──────┐ ┌────┴────┐ ┌─────┴─────┐
     │             │ │         │ │           │
     ▼             ▼ ▼         ▼ ▼           ▼
    View        Create    View      Confirm  View All  Filter
    Own         Apt       Own       Decline  Delete    Manage
    Apts                  Apts      Mark Co
            │                        │
            │             ┌──────────┼──────────┐
            │             │          │          │
            ▼             ▼          ▼          ▼
          View          View      Cancel     Delete
         Details       Details    Others     Anyone
                │        │          │
                └────────┼──────────┘
                         │
                         ▼
                      Store in DB
                         │
                         ▼
                      Update UI
                         │
                         ▼
                      Show Result
```

---

## Appointment Status Flow

```
                    ┌─────────────┐
                    │   PENDING   │
                    │  (Yellow)   │
                    └──────┬──────┘
                           │
                ┌──────────┼──────────┐
                │          │          │
                ▼          ▼          ▼
           CONFIRMED   CANCELLED   (expired)
           (Green)      (Red)
                │          │
                ▼          │
           COMPLETED       │
           (Blue)          │
                │          │
                └────┬─────┘
                     │
                     ▼
                  NO_SHOW
                  (Gray)
                
Status Transitions:
  pending → confirmed (by provider)
  pending → cancelled (by patient/provider)
  confirmed → completed (by provider)
  confirmed → cancelled (by patient/provider)
  * → no_show (system/manual)
  Any → DELETE (by admin)
```

---

## API Endpoint Flow

```
REQUEST FLOW:

HTTP Request
    │
    ▼
Route (bookings.php)
    │
    ├─── Middleware Check
    │    └─── Permission Verification
    │
    ▼
AppointmentController
    │
    ├─── index() ────► Query DB with Filters
    ├─── create() ───► Show Form
    ├─── store() ────► Save to DB
    ├─── show() ─────► Get Details
    ├─── cancel() ───► Update Status
    ├─── updateStatus()─► Update Status
    └─── destroy() ──► Delete (NEW)
    │
    ▼
Inertia Response / Redirect
    │
    ▼
Vue Component Renders
    │
    ├─── Index.vue (List)
    ├─── Show.vue (Details)
    └─── Filter Panel (Admin)
    │
    ▼
User Sees Updated UI
```

---

## Database Relationship Diagram

```
┌──────────────────┐
│  Users Table     │
│  - id (PK)       │
│  - name          │
│  - email         │
│  - avatar        │
└────────┬─────────┘
         │
         │ (1-to-many)
         │
    ┌────┴────┐
    │          │
    ▼          ▼
┌──────────────────────────┐   ┌──────────────────┐
│  ProviderProfiles Table  │   │ Appointments Table│
│  - id (PK)              │   │ - id (PK)         │
│  - user_id (FK)         │◄──┤ - provider_profile│
│  - specialization_id    │   │   _id (FK)        │
│  - city_id              │   │ - user_id (FK)    │
│  - is_available         │   │ - appointment_    │
└─────────────────────────┘   │   date            │
                              │ - start_time      │
                              │ - end_time        │
                              │ - status          │
                              │ - notes           │
                              └───────────────────┘

Relationships:
- User has many ProviderProfiles (if provider)
- User has many Appointments (if patient)
- ProviderProfile has many Appointments
- Appointment belongs to User (patient)
- Appointment belongs to ProviderProfile
```

---

## Filtering Logic Flow

```
Admin clicks Filter Button
        │
        ▼
Filter Panel Opens
        │
        ├─ Status Filter
        │  └─ pending/confirmed/cancelled/completed/no_show
        │
        ├─ Date Range
        │  └─ from_date AND to_date
        │
        ├─ Specialization
        │  └─ medical_field_slug
        │
        └─ City
           └─ city_id

Click "Apply Filters"
        │
        ▼
Build Query Params
        │
        ├─ status = "confirmed"
        ├─ date_from = "2025-12-01"
        ├─ date_to = "2025-12-31"
        ├─ specialization = "cardiology"
        └─ city = 1
        │
        ▼
Send GET Request
        │
        ▼
AppointmentController@index()
        │
        ├─ WHERE status = "confirmed"
        ├─ WHERE date >= 2025-12-01
        ├─ WHERE date <= 2025-12-31
        ├─ WHERE spec = "cardiology"
        └─ WHERE city = 1
        │
        ▼
Return Filtered Results
        │
        ▼
Vue Updates List
        │
        ▼
Show Active Filter Count
```

---

## Permission-Based Action Visibility

```
PATIENT LOGGED IN:
  ├─ View own appointments ✓
  ├─ Create new appointment ✓
  ├─ Cancel own pending/confirmed ✓
  ├─ View details ✓
  ├─ Confirm appointment ✗
  ├─ Delete appointment ✗
  └─ Use filters ✗

PROVIDER LOGGED IN:
  ├─ View own appointments ✓
  ├─ Create new appointment ✗
  ├─ Cancel own appointments ✗
  ├─ Confirm pending ✓
  ├─ Decline pending ✓
  ├─ Mark completed ✓
  ├─ View details ✓
  ├─ Delete appointment ✗
  └─ Use filters ✗

ADMIN LOGGED IN:
  ├─ View ALL appointments ✓
  ├─ Create new appointment ✗
  ├─ Cancel appointment ✗
  ├─ Confirm appointment ✗
  ├─ Decline appointment ✗
  ├─ Mark completed ✗
  ├─ View details ✓
  ├─ Delete ANY appointment ✓
  └─ Use filters ✓
```

---

## Component Hierarchy

```
AppLayout
  │
  ├─ Head (Inertia Meta)
  │
  └─ Dashboard/Bookings/Appointments/
      │
      ├─ Index.vue ◄─ MAIN LIST VIEW
      │   ├─ Header
      │   │  ├─ Title (role-specific)
      │   │  ├─ Filter Button (admin)
      │   │  └─ Book Button (patient)
      │   │
      │   ├─ Filter Panel (v-if admin)
      │   │  ├─ Status Select
      │   │  ├─ Date Range Inputs
      │   │  ├─ Specialization Select
      │   │  ├─ City Select
      │   │  ├─ Apply Button
      │   │  └─ Clear Button
      │   │
      │   ├─ Appointments List
      │   │  └─ Appointment Card
      │   │     ├─ Avatar & Name
      │   │     ├─ Status Badge
      │   │     ├─ Date/Time Info
      │   │     ├─ Contact Info
      │   │     ├─ Notes
      │   │     └─ Action Buttons
      │   │        ├─ View Details
      │   │        ├─ Cancel (patient)
      │   │        ├─ Confirm (provider)
      │   │        ├─ Decline (provider)
      │   │        ├─ Mark Complete (provider)
      │   │        └─ Delete (admin) ◄─ NEW
      │   │
      │   └─ Pagination (if > 20 items)
      │
      └─ Show.vue ◄─ DETAIL VIEW
          ├─ Back Button
          │
          ├─ Header
          │  ├─ Name
          │  └─ Status Badge
          │
          ├─ Details Grid
          │  ├─ Date & Time
          │  ├─ Contact Info
          │  └─ Provider Details (if patient)
          │
          ├─ Notes Section
          │
          └─ Action Buttons
             ├─ Cancel (patient)
             ├─ Confirm (provider)
             ├─ Decline (provider)
             ├─ Mark Complete (provider)
             └─ Delete (admin) ◄─ NEW
```

---

## Delete Flow Diagram

```
Admin clicks Delete Button
        │
        ▼
Confirmation Dialog
"Are you sure? This action cannot be undone."
        │
    ┌───┴────┐
    │        │
Cancel      Confirm
    │        │
    ▼        ▼
 Close    Send DELETE Request
          /appointments/{id}
          │
          ▼
   AppointmentController@destroy()
          │
    ┌─────┴─────┐
    │           │
 Check Auth  Check Permission
 (Guard)     (manage bookings)
    │           │
    └─────┬─────┘
          │
    ┌─────┴─────────┐
    │               │
Authorized   Unauthorized
    │               │
    ▼               ▼
Delete        Return 403
Record        Forbidden
    │
    ▼
Redirect Back
    │
    ▼
Show Success Message
"Appointment deleted successfully!"
    │
    ▼
Remove from List
    │
    ▼
UI Updates
```

---

## Data Flow: Create to Delete

```
STEP 1: CREATE
Patient Books → Form Submitted → POST /appointments → Store in DB
        │
        └─► Status: pending
        └─► Provider sees notification
        
STEP 2: CONFIRM
Provider Reviews → Confirm Clicked → POST /status → Update to confirmed
        │
        └─► Patient notified (optional)
        └─► Can now be marked complete
        
STEP 3: COMPLETE
Provider After Meeting → Mark Complete → POST /status → Update to completed
        │
        └─► Appointment is done
        └─► Can be reviewed (future)
        
STEP 4: DELETE (ANY TIME)
Admin Reviews → Delete Clicked → DELETE /appointments/{id} → Remove from DB
        │
        └─► Confirmation required
        └─► Completely removed
        └─► Cannot be recovered

ALTERNATIVE FLOWS:
Patient Cancel → POST /cancel → Status: cancelled
Provider Decline → POST /status?status=cancelled → Status: cancelled
System Mark NoShow → Manual/Auto → Status: no_show
```

---

## Security Check Flow

```
Request Received
    │
    ▼
Authenticate User
    ├─ Not Authenticated? → Redirect to Login
    │
    ▼
Check Route Permission
    ├─ can-book? → Allow patient actions
    ├─ book-sys? → Allow provider actions
    ├─ manage bookings? → Allow admin actions
    └─ No permission? → 403 Forbidden
    │
    ▼
Check Resource Authorization
    ├─ Patient: Own appointment only
    ├─ Provider: Their assigned only
    ├─ Admin: All appointments
    └─ Unauthorized? → 403 Forbidden
    │
    ▼
Execute Action
    │
    ▼
Return Result
```

---

## Summary

This system provides:
- ✅ Clear separation of concerns
- ✅ Role-based access control
- ✅ Proper authorization checks
- ✅ Efficient database queries
- ✅ User-friendly UI with filters
- ✅ Multiple action capabilities
- ✅ Secure deletion mechanism
- ✅ Responsive design
- ✅ Error handling
- ✅ Status tracking

All flows are implemented and production-ready!

