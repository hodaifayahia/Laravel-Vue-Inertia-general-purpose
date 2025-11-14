# System Restructure - Partner & Children Management

## Overview
The system has been restructured to support a medical appointment platform where:
- **Super-admin & Admin**: Can add doctors (name + email)
- **Doctors**: Configure their schedule/availability, chat with partners and admin
- **Partners**: Book appointments for their children, add child information with medical problems/notes, chat with doctors

## Key Changes

### 1. **Roles Updated** (4 roles total)
- **super-admin**: Full system access
- **admin**: Can add/manage doctors, view system, manage bookings
- **doctor**: Can configure schedule, provide medical services, chat with partners
- **partner**: Can book appointments for children, add child info, chat with doctors

**Removed roles**: `manager`, `user`, `viewer`, `patient`

### 2. **New Feature: Children Management**

#### New Model: `Child`
**File**: `app/Models/Child.php`
- Partners can add multiple children
- Each child has: name, date of birth, gender, medical notes
- Children are linked to appointments

#### New Migration: `create_children_table`
**File**: `database/migrations/2025_10_31_000001_create_children_table.php`
- Creates `children` table
- Adds `child_id` foreign key to `appointments` table

#### Updated Models:
- **Appointment**: Added `child_id` and `child()` relationship
- **User**: Added `children()` relationship and helper methods (`isPartner()`, `isDoctor()`)

### 3. **Chat Permissions Updated**
**File**: `database/seeders/ChatPermissionSeeder.php`
- Super-admin & Admin: Can chat with everyone
- Doctors: Can chat with admin, super-admin, and partners
- Partners: Can only chat with doctors (about their children)

### 4. **Booking Permissions Updated**
**File**: `database/seeders/BookingPermissionSeeder.php`
- Super-admin & Admin: Full booking management
- Doctors: Can provide services (`book-sys`) and view bookings
- Partners: Can book appointments (`can-book`)

### 5. **Test Data Updated**
**File**: `database/seeders/BookingTestSeeder.php`
- Updated test users:
  - 3 doctors (Dr. Smith, Dr. Johnson, Dr. Williams)
  - 2 partners (Alice Partner, Bob Partner)
- Updated appointments with child information in notes
- Partners book appointments for their children with medical problems/notes

### 6. **Sidebar Updated**
**File**: `resources/js/components/AppSidebar.vue`
- Removed "Permissions" link from sidebar (keeping it cleaner)

### 7. **Helper Scripts Updated**
- **seed_permissions.php**: Updated to create 4 roles
- **reset-chat-permissions.php**: Updated chat permissions for 4 roles

## User Workflow

### Admin Workflow:
1. Login as admin
2. Add doctors by providing name and email
3. Manage system and bookings
4. Chat with doctors if needed

### Doctor Workflow:
1. Receive email with login credentials
2. Login and configure profile
3. Set working schedule (days and hours)
4. View appointments booked by partners
5. Chat with partners about their children
6. Chat with admin for system issues

### Partner Workflow:
1. Register/Login as partner
2. Add children information (name, DOB, medical notes)
3. Browse available doctors by specialization
4. Check doctor's availability
5. Book appointment for a child
6. Add specific problems/notes for each appointment
7. Chat with doctors about their children's health

## Database Schema Changes

### New Table: `children`
```sql
- id
- partner_id (foreign key to users)
- name
- date_of_birth
- gender (male/female/other)
- medical_notes (text)
- timestamps
```

### Updated Table: `appointments`
```sql
+ child_id (foreign key to children, nullable)
```

## Next Steps to Implement

1. **Create Partner Registration Flow**
   - Allow partners to register
   - Email verification

2. **Create Children Management UI**
   - Partners can add/edit/view their children
   - Form with: name, DOB, gender, medical notes

3. **Update Appointment Booking UI**
   - Select child from partner's children list
   - Add appointment-specific notes about the child's problem
   - Show doctor's availability based on their configured schedule

4. **Create Doctor Schedule Configuration UI**
   - Doctors can set working days
   - Doctors can set working hours
   - Doctors can set slot duration

5. **Update Chat UI**
   - Show child context in doctor-partner chats
   - Filter conversations by child

6. **Admin Dashboard**
   - Create/manage doctor accounts
   - View system statistics
   - Manage appointments

## Test Credentials

After running seeders:
- **Super Admin**: superadmin@test.com / password
- **Admin**: admin@test.com / password
- **Doctor 1**: dr.smith@test.com / password
- **Doctor 2**: dr.johnson@test.com / password
- **Doctor 3**: dr.williams@test.com / password
- **Partner 1**: partner1@test.com / password
- **Partner 2**: partner2@test.com / password

## Commands to Run

```bash
# Run migrations
php artisan migrate

# Run seeders
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=ChatPermissionSeeder
php artisan db:seed --class=BookingPermissionSeeder
php artisan db:seed --class=BookingTestSeeder

# Or run all seeders
php artisan db:seed

# Reset chat permissions (if needed)
php reset-chat-permissions.php

# Seed permissions (standalone)
php seed_permissions.php
```
