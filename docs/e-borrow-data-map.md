# e-Borrow Data Map

Source module: `C:\xampp\htdocs\e-campaignv2\e_Borrow`
Source dump: `C:\xampp\htdocs\e-campaignv2\e_borrow.sql`

## Core legacy tables

| Legacy table | Laravel table | Notes |
| --- | --- | --- |
| `borrow_categories` | `borrow_categories` | Equipment/category master |
| `borrow_items` | `borrow_items` | Individual borrowable items |
| `borrow_records` | `borrow_records` | Borrow request and return transaction |
| `borrow_fines` | `borrow_fines` | Fines tied to overdue or damaged returns |
| `borrow_payments` | `borrow_payments` | Payment records for fines |

## Legacy to Laravel field mapping

### `borrow_categories`
- `image_url` -> `image_path`
- add `clinic_id`
- add `is_active`
- add Laravel timestamps

### `borrow_items`
- `type_id` -> `category_id`
- `image_url` -> `image_path`
- add `clinic_id`
- add Laravel timestamps

### `borrow_records`
- `type_id` -> `category_id`
- `borrower_student_id` -> `borrower_user_id`
- `approver_id` -> `approver_staff_id`
- `borrow_date` -> `borrowed_at`
- `return_date` -> `returned_at`
- `attachment_url` -> `attachment_path`
- add `clinic_id`
- add `notes`
- keep `status`, `approval_status`, `fine_status`

### `borrow_fines`
- `transaction_id` -> `borrow_record_id`
- `student_id` -> `user_id`
- add `clinic_id`
- keep `status`

### `borrow_payments`
- `payment_slip_url` -> `payment_slip_path`
- add `clinic_id`

## Laravel relationships

- `BorrowCategory` has many `BorrowItem`
- `BorrowCategory` has many `BorrowRecord`
- `BorrowItem` belongs to `BorrowCategory`
- `BorrowItem` has many `BorrowRecord`
- `BorrowRecord` belongs to `BorrowCategory`
- `BorrowRecord` belongs to `BorrowItem`
- `BorrowRecord` belongs to `User` as `borrower`
- `BorrowRecord` belongs to `Staff` as `lendingStaff`, `approverStaff`, `returnStaff`
- `BorrowRecord` has many `BorrowFine`
- `BorrowFine` belongs to `BorrowRecord`
- `BorrowFine` belongs to `User`
- `BorrowFine` belongs to `Staff` as `createdByStaff`
- `BorrowFine` has many `BorrowPayment`
- `BorrowPayment` belongs to `BorrowFine`
- `BorrowPayment` belongs to `Staff` as `receivedByStaff`

## Suggested MVP flow

1. Catalog of borrow categories and items
2. User borrow request form
3. User borrow history
4. Admin approval queue
5. Admin return dashboard
