# GulfTP Form Testing Guide

## Overview
All 4 forms submit to **one unified database table** (`form_responses`) and are identified by a `form_type` field.

**No need for 4 separate databases** - one unified table handles all submissions efficiently with filtering.

---

## Database Schema
All submissions stored in `form_responses` table with fields:
- `form_type` - Identifies which form (Contact, Subscription, Appointment, Search)
- `first_name`, `last_name` - Submitter name
- `email`, `phone` - Contact info
- `company` - Organization
- `service` - For Contact form (what service needed)
- `country` - For Contact form (which country)
- `users` - For Subscription form (number of users)
- `message` - Additional message
- `form_data` - Full JSON of all submitted data
- `ip_address` - Submitter IP
- `created_at` - Submission timestamp

---

## Form 1: Contact Form
**File:** `contact.html`  
**Form Type:** `Contact`  
**Unique Fields:** `service`, `country`

### Test Steps:
1. Go to `/contact.html`
2. Fill in:
   - First Name: "John"
   - Last Name: "Doe"
   - Email: "john@example.com"
   - Phone: "+971 50 123 4567"
   - Company: "ABC Consulting"
   - Service: "GCC Benchmark Search"
   - Country: "UAE"
   - Message: "Need help with transfer pricing"
3. Submit form
4. Verify:
   - Success message appears
   - Check admin dashboard - should see entry with form_type = "Contact"

---

## Form 2: Subscription Form
**File:** `buy-subscription.html`  
**Form Type:** `Subscription`  
**Unique Fields:** `users` (number of users like "1-3", "4-10", "10+")

### Test Steps:
1. Go to `/buy-subscription.html`
2. Fill in:
   - First Name: "Jane"
   - Last Name: "Smith"
   - Company: "XYZ Corp"
   - Email: "jane@xyz.com"
   - Expected Users: "4-10 Users"
   - Requirements: "Need enterprise license"
3. Submit form
4. Verify:
   - Success message appears
   - Check admin dashboard - should see entry with form_type = "Subscription"

---

## Form 3: Book Appointment Form
**File:** `book-an-appointment.html`  
**Form Type:** `Appointment`  
**Unique Fields:** None (basic fields only)

### Test Steps:
1. Go to `/book-an-appointment.html`
2. Fill in:
   - Full Name: "Ahmed"
   - Email: "ahmed@company.com"
   - Organisation: "Tech Solutions"
   - Mobile: "+971 55 987 6543"
3. Submit form
4. Verify:
   - Success message appears
   - Check admin dashboard - should see entry with form_type = "Appointment"

---

## Form 4: Update Search Form
**File:** `update-a-search.html`  
**Form Type:** `Appointment` (or could be customized to "Search")  
**Unique Fields:** `fileUpload` (file attachment)

### Test Steps:
1. Go to `/update-a-search.html`
2. Fill in:
   - Upload Previous Search: Select any PDF/DOCX file
   - Full Name: "Sara"
   - Email: "sara@firm.com"
   - Organisation: "Legal Firm LLC"
   - Mobile: "+971 45 123 4567"
3. Submit form
4. Verify:
   - Success message appears
   - Check admin dashboard - should see entry with form_type = "Appointment"

---

## Admin Dashboard Access

1. Go to `/admin/login.php`
2. Login with:
   - Username: `admin`
   - Password: `admin@2024`
3. Dashboard shows:
   - Statistics (Total, This Month, This Week, Today)
   - Filter by form type dropdown
   - Table of all submissions
4. Click "View" to see full submission details

---

## Filtering in Admin Dashboard

In the admin dashboard filter dropdown, you can filter by:
- **All Types** - Shows all submissions
- **Contact / Service Inquiry** - Only "Contact" forms
- **Subscription Request** - Only "Subscription" forms
- **Book Appointment** - Only "Appointment" forms
- **Update Search** - For future Search-specific forms
- **Payment** - For payment submissions

---

## How Form Type is Determined

In `submit-form.php`:

```php
if (isset($data['service']) && isset($data['country'])) {
    $form_type = 'Contact';
} elseif (isset($data['users'])) {
    $form_type = 'Subscription';
} else {
    $form_type = 'Appointment'; // Default for basic forms
}
```

---

## Email Notifications

When a form is submitted:

1. **Admin receives:** HTML-formatted email to `admin@gulftp.com` with:
   - All submitted data
   - Form type
   - Submission timestamp
   - Submitter's IP address

2. **User receives:** Confirmation email to their email address with:
   - Thank you message
   - Confirmation of submission
   - Next steps (24-48 hours response time)

---

## Data Storage

All submissions are stored in the `form_responses` database table:

| Column | Value |
|--------|-------|
| form_type | Contact / Subscription / Appointment |
| first_name | "John" |
| last_name | "Doe" |
| email | "john@example.com" |
| phone | "+971 50 123 4567" |
| company | "ABC Consulting" |
| service | "GCC Benchmark Search" (Contact only) |
| country | "UAE" (Contact only) |
| users | "4-10" (Subscription only) |
| message | "Additional notes" |
| ip_address | "192.168.x.x" |
| created_at | 2026-06-07 12:30:45 |

---

## Testing Checklist

- [ ] Contact form submits with form_type = "Contact"
- [ ] Subscription form submits with form_type = "Subscription"
- [ ] Appointment form submits with form_type = "Appointment"
- [ ] Update Search form submits with form_type = "Appointment"
- [ ] Admin dashboard receives all submissions
- [ ] Filter by Contact shows only contact forms
- [ ] Filter by Subscription shows only subscription forms
- [ ] Filter by Appointment shows only appointment/search forms
- [ ] Email sent to admin@gulftp.com
- [ ] Confirmation email sent to user's email
- [ ] View Submission page shows all details correctly
- [ ] Success message displays on form after submission

---

## Files Modified

- ✅ `config.php` - Unified database table creation
- ✅ `submit-form.php` - Improved form type detection
- ✅ `payment-handler.php` - Updated for unified table
- ✅ `admin/dashboard.php` - Updated filter labels
- ✅ `admin/login.php` - Admin authentication
- ✅ `admin/view-submission.php` - View individual submissions
- ✅ `admin/logout.php` - Logout handler
- ✅ `.env` - Configuration values
- ✅ `contact.html` - Submits as "Contact"
- ✅ `buy-subscription.html` - Submits as "Subscription"
- ✅ `book-an-appointment.html` - Submits as "Appointment"
- ✅ `update-a-search.html` - Submits as "Appointment"

---

## Conclusion

**Single Unified Database is Better Because:**
1. ✅ Easier to manage and query
2. ✅ Better for analytics and reporting
3. ✅ Less code complexity
4. ✅ Better performance with single indexed table
5. ✅ Easy filtering by form type
6. ✅ Scalable for thousands of submissions
7. ✅ Single admin dashboard for all forms

No need to create 4 separate databases!
