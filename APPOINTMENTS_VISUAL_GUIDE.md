# 🎨 Appointment Management System - Visual Feature Guide

## 📱 UI Components Overview

### **1. Status Dropdown Component**

```
┌──────────────────────────────────────────────────┐
│  Appointment Card                                 │
├──────────────────────────────────────────────────┤
│                                                   │
│  👤 Dr. Ahmed Smith          🟡 PENDING          │
│  Cardiology • Algiers                             │
│                                                   │
│  📅 Nov 7, 2025              ⏱️  10:00 - 11:00   │
│  Patient: John Doe                                │
│                                                   │
├──────────────────────────────────────────────────┤
│  Actions:                                         │
│  [View Details] [▼ Change Status...    ]         │
│                  └─ Confirmed            │
│                  └─ Cancelled            │
│                  └─ (Other valid options)│
└──────────────────────────────────────────────────┘
```

**Mobile Version:**
```
┌──────────────────────────────────────┐
│ Dr. Ahmed Smith    🟡 PENDING        │
├──────────────────────────────────────┤
│ Cardiology • Algiers                 │
│ Nov 7, 2025 • 10:00 - 11:00         │
│ Patient: John Doe                    │
├──────────────────────────────────────┤
│ [View] [▼ Status...] [Delete]       │
└──────────────────────────────────────┘
```

---

### **2. Confirmation Modal Component**

```
      Click to change status
              ↓
        ┌─────────────────────────────┐
        │ ⚠️  Change Status            │
        ├─────────────────────────────┤
        │                             │
        │  Are you sure you want to   │
        │  change the status to       │
        │  Confirmed?                 │
        │                             │
        │ [Cancel]        [Confirm]   │
        └─────────────────────────────┘
```

**Dark Mode:**
```
        ┌─────────────────────────────┐
        │ ⚠️  Change Status        ░░░  │
        ├─────────────────────────────┤
        │ (Dark background)           │
        │ Text in light color         │
        │ Are you sure?               │
        │                             │
        │ [Dark] [Highlight]          │
        └─────────────────────────────┘
```

---

### **3. Pagination Component**

#### **Few Pages (≤7)**
```
[← Previous] [1] [2] [3 ●] [4] [5] [Next →]
                        ↑
                   Current page (highlighted)
```

#### **Many Pages (>7)**
```
[← Previous] [1] ... [4] [5 ●] [6] ... [20] [Next →]
                     └─────┬─────┘
                    Visible page range
```

#### **Pagination Info**
```
Showing 41-60 of 200 appointments
   ↑            ↑     ↑
 Range info   Total Count

Page navigation below:
[← Previous] [1] ... [3] [4 ●] [5] ... [10] [Next →]
```

---

### **4. Filter Panel Component**

```
┌─────────────────────────────────────────────┐
│ 🔍 Filters [3]  ← Active filter count       │
├─────────────────────────────────────────────┤
│                                             │
│  Status          Specialization    City    │
│  [All ▼]         [All ▼]          [All ▼] │
│                                             │
│  Date From       Date To                   │
│  [pick date]     [pick date]               │
│                                             │
│  [Apply Filters]  [Clear All]              │
├─────────────────────────────────────────────┤
```

**With Selections:**
```
┌─────────────────────────────────────────────┐
│ 🔍 Filters [3]                              │
├─────────────────────────────────────────────┤
│                                             │
│  Status              Specialization  City  │
│  [Pending ▼]         [Cardiology ▼]  [1▼] │
│                                             │
│  Date From           Date To                │
│  [2025-11-01]        [2025-11-30]          │
│                                             │
│  [Apply Filters]     [Clear All]           │
├─────────────────────────────────────────────┤
```

---

### **5. Statistics Dashboard (Admin)**

```
┌──────────────────────────────────────────────────────────┐
│ 📊 All Appointments Statistics                           │
├──────────────────────────────────────────────────────────┤
│                                                           │
│ ┌─────────┐ ┌─────────┐ ┌─────────┐ ┌─────────┐        │
│ │ Total   │ │Pending  │ │Confirmed│ │Completed│        │
│ │  150    │ │   25    │ │   80    │ │   35   │        │
│ └─────────┘ └─────────┘ └─────────┘ └─────────┘        │
│      📅        🟡         🟢          🔵               │
│                                                           │
│ ┌─────────┐                                              │
│ │Cancelled│                                              │
│ │   10    │                                              │
│ └─────────┘                                              │
│      🔴                                                  │
│                                                           │
└──────────────────────────────────────────────────────────┘
```

---

### **6. Admin Table View (Desktop)**

```
┌──────────────────────────────────────────────────────────────┐
│ Patient         │ Provider       │ Specialization │ Status   │
├──────────────────────────────────────────────────────────────┤
│ 👤 John Doe     │ 👤 Dr. Ahmed   │ Cardiology    │ 🟡 PENDING │
│ john@email.com  │ ahmed@email    │               │          │
├──────────────────────────────────────────────────────────────┤
│ 👤 Jane Smith   │ 👤 Dr. Fatima  │ Neurology     │ 🟢 CONFIRMED│
│ jane@email.com  │ fatima@email   │               │          │
├──────────────────────────────────────────────────────────────┤
│                        Actions: [👁️] [▼] [🗑️]              │
└──────────────────────────────────────────────────────────────┘
     ↑                                    ↑    ↑    ↑
   Patient                             View Change Delete
```

---

### **7. Admin Card View (Mobile)**

```
┌──────────────────────────────┐
│ John Doe      🟡 PENDING     │
│ Patient                      │
├──────────────────────────────┤
│ Provider: Dr. Ahmed          │
│ Specialization: Cardiology   │
│ Date: Nov 7, 2025            │
│ Time: 10:00 - 11:00          │
│ Location: Algiers            │
├──────────────────────────────┤
│ [View] [▼ Status] [Delete]  │
└──────────────────────────────┘
     ↑       ↑        ↑
   Compact layout for mobile
```

---

## 🎨 Status Color Legend

```
┌───────────┬──────────┬─────────────────────────────┐
│ Status    │ Color    │ Meaning                     │
├───────────┼──────────┼─────────────────────────────┤
│ Pending   │ 🟡 YELLOW│ Waiting for provider action │
│ Confirmed │ 🟢 GREEN │ Appointment confirmed      │
│ Completed │ 🔵 BLUE  │ Appointment finished       │
│ Cancelled │ 🔴 RED   │ Appointment cancelled      │
│ No Show   │ ⚪ GRAY  │ Didn't show up             │
└───────────┴──────────┴─────────────────────────────┘
```

---

## 🔄 User Interaction Flows

### **Flow 1: Provider Confirms Appointment**

```
START
  ↓
View Appointments List
  ↓
Find Pending Appointment
  ↓
Click "Change Status..." Dropdown
  ↓
Select "Confirmed"
  ↓
Confirmation Modal Appears
  ↓
Review Message
  ↓
Click "Confirm"
  ↓
Status Updates to Green ✓
  ↓
Success Notification
  ↓
END
```

### **Flow 2: Admin Filters & Exports**

```
START
  ↓
Go to Appointments
  ↓
Click "Filters" Button
  ↓
Select Filter Options
├─ Status: Pending
├─ Specialization: Cardiology
├─ City: Algiers
└─ Date Range: Nov 1-30
  ↓
Click "Apply Filters"
  ↓
Table Updates with Results
  ↓
Review Statistics
  ↓
Click "Export" Button
  ↓
CSV Downloads
  ↓
END
```

### **Flow 3: Patient Cancels Appointment**

```
START
  ↓
View My Appointments
  ↓
Find Confirmed Appointment
  ↓
Click "Cancel" Button
  ↓
Confirmation Modal
  ↓
Click "Cancel Appointment"
  ↓
Status Changes to Red ✗
  ↓
Provider Notified (optional)
  ↓
END
```

---

## 📊 Data Export Format

### **CSV Output Example**

```csv
Patient,Provider,Specialization,Date,Time,Status,Location
John Doe,Dr. Ahmed Smith,Cardiology,2025-11-07,10:00 - 11:00,Confirmed,Algiers
Jane Smith,Dr. Fatima Johnson,Neurology,2025-11-07,14:00 - 15:00,Pending,Constantine
Ahmed Ali,Dr. Sara Hassan,Dermatology,2025-11-08,09:00 - 10:00,Completed,Oran
```

**Opens in:**
- Excel ✓
- Google Sheets ✓
- LibreOffice Calc ✓
- Any text editor ✓

---

## 🎯 Permission Matrix

```
┌────────────────┬──────────┬──────────┬────────┐
│ Action         │ Patient  │ Provider │ Admin  │
├────────────────┼──────────┼──────────┼────────┤
│ View Own       │    ✓     │    ✓     │   ✓    │
│ View All       │    ✗     │    ✗     │   ✓    │
│ Change Status  │    ✗     │    ✓*    │   ✓    │
│ Cancel         │    ✓**   │    ✗     │   ✓    │
│ Delete         │    ✗     │    ✗     │   ✓    │
│ Export         │    ✗     │    ✗     │   ✓    │
│ View Stats     │    ✗     │    ✗     │   ✓    │
└────────────────┴──────────┴──────────┴────────┘

* Only valid transitions shown
** Only pending/confirmed appointments
```

---

## 🔍 Responsive Breakpoints

```
Desktop (1024px+)
├─ Full table layout
├─ Side-by-side elements
├─ Full filter panel
└─ Optimal spacing

Tablet (768px - 1023px)
├─ Adapted table/cards
├─ Stacked layout
├─ Touch-friendly sizes
└─ Maintained features

Mobile (<768px)
├─ Card layout
├─ Single column
├─ Large touch targets
└─ Full functionality
```

---

## 🌓 Dark Mode Variants

### **Light Mode**
```
Background: White (#FFFFFF)
Text: Dark Gray (#111827)
Accent: Indigo (#4F46E5)
Card: White with shadow
```

### **Dark Mode**
```
Background: Dark Gray (#111827)
Text: White (#F9FAFB)
Accent: Indigo (#6366F1)
Card: Gray-800 with shadow
```

---

## ⌨️ Keyboard Navigation

```
Tab          → Move between elements
Shift+Tab    → Move back
Enter        → Activate button/select
Space        → Toggle dropdown
Escape       → Close modal/dropdown
Arrow Keys   → Navigate select options
```

---

## 📲 Mobile Touch Targets

```
Minimum size: 44x44 pixels (Apple standard)
Spacing: 8px minimum between targets
Button height: 44-56px
Input height: 44-48px
Dropdown height: 40-48px
```

---

## 🎬 Animation & Transitions

```
Modal Fade-In: 200ms
Dropdown Open: 150ms
Status Update: 300ms
Page Transition: Inertia (smooth)
Hover Effects: 200ms transition
```

---

## 📋 Form Validation

```
Status Change:
├─ Show only valid transitions ✓
├─ Prevent invalid attempts ✓
└─ Confirm before action ✓

Date Filters:
├─ Date From ≤ Date To ✓
├─ Dates in valid format ✓
└─ No future dates (usually) ✓

Export:
├─ At least 1 appointment ✓
├─ Valid CSV format ✓
└─ Proper file naming ✓
```

---

## 🎨 Component Styling

### **Primary Colors**
- Indigo: #4F46E5 (actions, highlights)
- Purple: #9333EA (gradients)
- Green: #22C55E (success, confirmed)
- Yellow: #EAB308 (warning, pending)
- Red: #EF4444 (danger, cancelled)
- Blue: #3B82F6 (info, completed)
- Gray: #6B7280 (neutral, no show)

### **Spacing Scale**
- xs: 4px
- sm: 8px
- md: 16px
- lg: 24px
- xl: 32px
- 2xl: 48px

### **Border Radius**
- sm: 4px (inputs)
- md: 8px (cards)
- lg: 12px (modals)
- full: 50% (avatars)

---

This visual guide complements the technical documentation and user quick start guide. Refer to it when:
- Designing additional features
- Training users on the interface
- Troubleshooting layout issues
- Planning mobile improvements
