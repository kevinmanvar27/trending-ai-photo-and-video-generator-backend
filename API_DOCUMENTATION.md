# API Documentation

## Base URL
```
http://your-domain.com/api
```

## Authentication
Most endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:
```
Authorization: Bearer {your_token}
```

---

## Table of Contents
1. [Authentication](#authentication-endpoints)
2. [User Profile](#user-profile)
3. [Contact](#contact-endpoints)
4. [Activity Tracking](#activity-tracking-endpoints)
5. [Templates](#template-endpoints)
6. [Image Submissions](#image-submission-endpoints)
7. [Subscriptions](#subscription-endpoints)
8. [Settings](#settings-endpoints)

---

## Authentication Endpoints

### 1. Register User
**Endpoint:** `POST /register`

**Request Body:**
```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123"
}
```

**Validation:**
- name: required, string, max 255
- email: required, valid email, unique
- password: required, min 8 characters

**Success Response (201):**
```json
{
  "success": true,
  "message": "Registration successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz..."
  }
}
```

---

### 2. Login User
**Endpoint:** `POST /login`

**Request Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "token": "2|abcdefghijklmnopqrstuvwxyz..."
  }
}
```

---

### 3. Logout
**Endpoint:** `POST /logout`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Logged out successfully"
}
```

---

## User Profile

### 4. Get Profile
**Endpoint:** `GET /profile`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "user",
    "active_subscription": {
      "plan": {
        "name": "Premium"
      }
    }
  }
}
```

---

### 5. Update Profile
**Endpoint:** `PUT /profile`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "John Updated",
  "email": "john.updated@example.com",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Validation:**
- name: optional, string, max 255
- email: optional, valid email, unique (excluding current user)
- password: optional, min 8 characters, must be confirmed
- password_confirmation: required if password is provided

**Success Response (200):**
```json
{
  "success": true,
  "message": "Profile updated successfully",
  "data": {
    "id": 1,
    "name": "John Updated",
    "email": "john.updated@example.com",
    "role": "user"
  }
}
```

---

## Contact Endpoints

### 6. Sync Device Contacts
**Endpoint:** `POST /contacts`

**Headers:** `Authorization: Bearer {token}`

**Description:** Sync device contacts from Flutter app to database. Accepts bulk contacts and prevents duplicates.

**Request Body:**
```json
{
  "contacts": [
    {
      "name": "John Doe",
      "phone_number": "+1234567890",
      "email": "john@example.com"
    },
    {
      "name": "Jane Smith",
      "phone_number": "+0987654321",
      "email": null
    },
    {
      "name": null,
      "phone_number": "+1122334455",
      "email": null
    }
  ]
}
```

**Validation:**
- contacts: required, array, min 1 item
- contacts.*.name: optional, string, max 255
- contacts.*.phone_number: required, string, max 20
- contacts.*.email: optional, valid email, max 255

**Success Response (201):**
```json
{
  "success": true,
  "message": "Contacts synced successfully",
  "data": {
    "total_received": 3,
    "synced": 3,
    "skipped": 0,
    "errors": []
  }
}
```

**Partial Success Response (201):**
```json
{
  "success": true,
  "message": "Contacts synced successfully",
  "data": {
    "total_received": 5,
    "synced": 4,
    "skipped": 1,
    "errors": [
      {
        "index": 2,
        "phone_number": "+invalid",
        "error": "Database constraint violation"
      }
    ]
  }
}
```

**Error Response (422):**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "contacts": ["The contacts field is required."],
    "contacts.0.phone_number": ["The contacts.0.phone_number field is required."]
  }
}
```

---

### 7. Get Synced Contacts
**Endpoint:** `GET /contacts`

**Headers:** `Authorization: Bearer {token}`

**Description:** Retrieve all synced contacts for the authenticated user with pagination.

**Query Parameters:**
- page: optional, default 1
- per_page: optional, default 50

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "name": "John Doe",
        "phone_number": "+1234567890",
        "email": "john@example.com",
        "is_synced": true,
        "created_at": "2026-03-02T06:15:00.000000Z",
        "updated_at": "2026-03-02T06:15:00.000000Z"
      },
      {
        "id": 2,
        "user_id": 1,
        "name": "Jane Smith",
        "phone_number": "+0987654321",
        "email": null,
        "is_synced": true,
        "created_at": "2026-03-02T06:15:00.000000Z",
        "updated_at": "2026-03-02T06:15:00.000000Z"
      }
    ],
    "per_page": 50,
    "total": 2
  }
}
```

---

### 8. Delete All Contacts
**Endpoint:** `DELETE /contacts`

**Headers:** `Authorization: Bearer {token}`

**Description:** Delete all synced contacts for the authenticated user.

**Success Response (200):**
```json
{
  "success": true,
  "message": "All contacts deleted successfully",
  "data": {
    "deleted_count": 150
  }
}
```

---

## Activity Tracking Endpoints

### 9. Start Session
**Endpoint:** `POST /activity/start`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "device_type": "mobile"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Session started",
  "data": {
    "session_id": 123
  }
}
```

---

### 7. Start Session
**Endpoint:** `POST /activity/start`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "device_type": "mobile"
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Session started",
  "data": {
    "session_id": 123
  }
}
```

---

### 9. Start Session
**Endpoint:** `POST /activity/end`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "session_id": 123
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Session ended",
  "data": {
    "duration": 3600
  }
}
```

---

### 10. Activity History
**Endpoint:** `GET /activity/history?page=1`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 123,
        "session_start": "2026-03-02T03:00:00.000000Z",
        "session_end": "2026-03-02T04:00:00.000000Z",
        "duration": 3600
      }
    ],
    "per_page": 20
  }
}
```

---

## Template Endpoints

### 11. Get All Templates
**Endpoint:** `GET /templates`

**Query Parameters:**
- type: image|video
- is_active: true|false
- sort_by: created_at|usage_count
- sort_order: asc|desc

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Cartoon Effect",
      "type": "image",
      "description": "Transform to cartoon",
      "prompt": "Convert to cartoon style",
      "usage_count": 150,
      "coins_required": 10,
      "is_active": true
    }
  ]
}
```

---

### 12. Get Template
**Endpoint:** `GET /templates/{id}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Cartoon Effect",
    "type": "image",
    "prompt": "Convert to cartoon style",
    "coins_required": 10,
    "reference_image_url": "http://example.com/storage/templates/ref.jpg"
  }
}
```

---

### 13. Create Template
**Endpoint:** `POST /templates`

**Headers:** `Authorization: Bearer {token}`

**Request Body (multipart/form-data):**
```
title: "Anime Style"
type: "image"
description: "Transform to anime"
prompt: "Convert to anime art style"
reference_image: [file]
coins_required: 15
is_active: true
```

**Validation:**
- title: required, max 255
- type: required, image|video
- prompt: required
- coins_required: optional, integer, min 0
- reference_image: optional, max 10MB

**Success Response (201):**
```json
{
  "success": true,
  "message": "Template created successfully",
  "data": {
    "id": 5,
    "title": "Anime Style",
    "coins_required": 15
  }
}
```

---

### 14. Update Template
**Endpoint:** `PUT /templates/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "title": "Updated Title",
  "prompt": "Updated prompt",
  "coins_required": 20,
  "is_active": false
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Template updated successfully"
}
```

---

### 15. Delete Template
**Endpoint:** `DELETE /templates/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Template deleted successfully"
}
```

---

### 16. Toggle Template Status
**Endpoint:** `POST /templates/{id}/toggle-active`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Template status updated"
}
```

---

### 17. Popular Templates
**Endpoint:** `GET /templates/popular?limit=10`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Cartoon Effect",
      "usage_count": 500
    }
  ]
}
```

---

## Image Prompt Endpoints

### 18. Get All Prompts
**Endpoint:** `GET /prompts`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- status: pending|processing|completed|failed
- file_type: image|video
- sort_by: created_at
- sort_order: desc|asc
- per_page: 15

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "prompt": "Make it a painting",
        "status": "completed",
        "processing_time": 12.5
      }
    ]
  }
}
```

---

### 19. Get Prompt
**Endpoint:** `GET /prompts/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "prompt": "Make it a painting",
    "status": "completed"
  }
}
```

---

### 20. Create Prompt
**Endpoint:** `POST /prompts`

**Headers:** `Authorization: Bearer {token}`

**Request Body (multipart/form-data):**
```
original_image: [file]
prompt: "Transform to watercolor"
output_type: "image"
file_type: "image"
```

**Validation:**
- original_image: required, max 10MB
- prompt: required, max 1000
- output_type: optional, image|video

**Success Response (201):**
```json
{
  "success": true,
  "message": "Prompt created successfully",
  "data": {
    "id": 10,
    "status": "pending"
  }
}
```

---

### 21. Process Prompt
**Endpoint:** `POST /prompts/{id}/process`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Image processed successfully",
  "data": {
    "id": 10,
    "status": "completed",
    "processing_time": 15.3
  }
}
```

---

### 22. Update Prompt Status
**Endpoint:** `PUT /prompts/{id}/status`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "completed",
  "processing_time": 12.5
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Prompt updated successfully"
}
```

---

### 23. Delete Prompt
**Endpoint:** `DELETE /prompts/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Prompt deleted successfully"
}
```

---

### 24. Prompt Statistics
**Endpoint:** `GET /prompts/statistics`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_prompts": 50,
    "completed": 45,
    "pending": 2,
    "processing": 1,
    "failed": 2,
    "average_processing_time": 14.2
  }
}
```

---

### 25. Recent Prompts
**Endpoint:** `GET /prompts/recent?limit=10`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "prompt": "Make it artistic",
      "status": "completed"
    }
  ]
}
```

---

## Image Submission Endpoints

### 26. Get All Submissions
**Endpoint:** `GET /submissions`

**Headers:** `Authorization: Bearer {token}`

**Query Parameters:**
- status: pending|processing|completed|failed
- output_type: image|video
- template_id: number
- per_page: 15

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "template_id": 5,
        "status": "completed",
        "template": {
          "title": "Cartoon Effect"
        }
      }
    ]
  }
}
```

---

### 27. Get Submission
**Endpoint:** `GET /submissions/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "template_id": 5,
    "status": "completed"
  }
}
```

---

### 28. Create Submission
**Endpoint:** `POST /submissions`

**Headers:** `Authorization: Bearer {token}`

**Description:** Creates a new image submission using a template. Automatically deducts required coins from user's active subscription.

**Request Body (multipart/form-data):**
```
template_id: 5
original_image: [file]
output_type: "image"
```

**Validation:**
- template_id: required, exists
- original_image: required, max 10MB
- output_type: required, image|video

**Success Response (201):**
```json
{
  "success": true,
  "message": "Submission created successfully",
  "data": {
    "id": 20,
    "status": "pending"
  },
  "coins_deducted": 10,
  "remaining_coins": 90
}
```

**Error Responses:**

**No Active Subscription (403):**
```json
{
  "success": false,
  "message": "You need an active subscription to use templates"
}
```

**Insufficient Coins (403):**
```json
{
  "success": false,
  "message": "Insufficient coins. You need 10 coins but have 5 coins remaining.",
  "coins_required": 10,
  "coins_available": 5
}
```

**Template Not Available (400):**
```json
{
  "success": false,
  "message": "This template is not available"
}
```

---

### 29. Update Submission Status
**Endpoint:** `PUT /submissions/{id}/status`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "status": "completed",
  "processing_time": 10.5
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Submission updated successfully"
}
```

---

### 30. Delete Submission
**Endpoint:** `DELETE /submissions/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Submission deleted successfully"
}
```

---

### 31. Submission Statistics
**Endpoint:** `GET /submissions/statistics`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "total_submissions": 100,
    "completed": 95,
    "images_generated": 80,
    "videos_generated": 15,
    "average_processing_time": 12.8
  }
}
```

---

### 32. Recent Submissions
**Endpoint:** `GET /submissions/recent?limit=10`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 20,
      "status": "completed",
      "template": {
        "title": "Cartoon Effect"
      }
    }
  ]
}
```

---

## Subscription Endpoints

### 33. Get All Plans
**Endpoint:** `GET /subscription-plans`

**Query Parameters:**
- is_active: true|false
- sort_by: price
- sort_order: asc|desc

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Basic",
      "price": 9.99,
      "duration_type": "month",
      "duration_value": 1,
      "features": ["Feature 1", "Feature 2"],
      "is_active": true
    }
  ]
}
```

---

### 34. Get Plan
**Endpoint:** `GET /subscription-plans/{id}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Basic",
    "price": 9.99
  }
}
```

---

### 35. Create Plan
**Endpoint:** `POST /subscription-plans`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Premium",
  "description": "Premium features",
  "price": 19.99,
  "duration_type": "month",
  "duration_value": 1,
  "features": ["Feature 1", "Feature 2"],
  "is_active": true
}
```

**Validation:**
- name: required, max 255
- price: required, numeric, min 0
- duration_type: required, day|week|month|year
- duration_value: required, integer, min 1

**Success Response (201):**
```json
{
  "success": true,
  "message": "Plan created successfully",
  "data": {
    "id": 5,
    "name": "Premium"
  }
}
```

---

### 36. Update Plan
**Endpoint:** `PUT /subscription-plans/{id}`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "name": "Premium Plus",
  "price": 24.99
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Plan updated successfully"
}
```

---

### 37. Delete Plan
**Endpoint:** `DELETE /subscription-plans/{id}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Plan deleted successfully"
}
```

---

### 38. Subscribe
**Endpoint:** `POST /subscriptions`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "subscription_plan_id": 1
}
```

**Success Response (201):**
```json
{
  "success": true,
  "message": "Subscription created successfully",
  "data": {
    "id": 10,
    "started_at": "2026-03-02T04:00:00.000000Z",
    "expires_at": "2026-04-02T04:00:00.000000Z",
    "status": "active"
  }
}
```

---

### 39. My Subscription
**Endpoint:** `GET /subscriptions/my-subscription`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "status": "active",
    "expires_at": "2026-04-02T04:00:00.000000Z",
    "plan": {
      "name": "Basic"
    }
  }
}
```

---

### 40. Subscription History
**Endpoint:** `GET /subscriptions/history`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "status": "active",
      "plan": {
        "name": "Basic"
      }
    }
  ]
}
```

---

### 41. Cancel Subscription
**Endpoint:** `POST /subscriptions/cancel`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Subscription cancelled successfully",
  "data": {
    "status": "cancelled",
    "cancelled_at": "2026-03-02T04:00:00.000000Z"
  }
}
```

---

## Settings Endpoints

### 42. Get All Settings
**Endpoint:** `GET /settings`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "key": "app_name",
      "value": "My App",
      "type": "text",
      "group": "general"
    }
  ]
}
```

---

### 44. Get Settings by Group
**Endpoint:** `GET /settings/group/{group}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "key": "api_key",
      "value": "xxx",
      "group": "api"
    }
  ]
}
```

---

### 44. Get Setting
**Endpoint:** `GET /settings/{key}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "key": "app_name",
    "value": "My App"
  }
}
```

---

### 45. Create/Update Setting
**Endpoint:** `POST /settings`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "key": "app_name",
  "value": "My Awesome App",
  "type": "text",
  "group": "general"
}
```

**Validation:**
- key: required, max 255
- value: required
- type: optional, text|number|boolean|json

**Success Response (200):**
```json
{
  "success": true,
  "message": "Setting saved successfully"
}
```

---

### 46. Bulk Update Settings
**Endpoint:** `POST /settings/bulk-update`

**Headers:** `Authorization: Bearer {token}`

**Request Body:**
```json
{
  "settings": [
    {
      "key": "app_name",
      "value": "New Name",
      "type": "text"
    },
    {
      "key": "max_uploads",
      "value": 100,
      "type": "number"
    }
  ]
}
```

**Success Response (200):**
```json
{
  "success": true,
  "message": "Settings updated successfully"
}
```

---

### 47. Delete Setting
**Endpoint:** `DELETE /settings/{key}`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Setting deleted successfully"
}
```

---

### 48. Clear Cache
**Endpoint:** `POST /settings/clear-cache`

**Headers:** `Authorization: Bearer {token}`

**Success Response (200):**
```json
{
  "success": true,
  "message": "Cache cleared successfully"
}
```

---

## Error Responses

### 401 Unauthorized
```json
{
  "message": "Unauthenticated."
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "Unauthorized"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found"
}
```

### 422 Validation Error
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field": ["Error message"]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to process request",
  "error": "Error details"
}
```

---

## Notes

- All timestamps in UTC (ISO 8601)
- All responses in JSON
- File uploads use multipart/form-data
- Max image size: 10MB
- Max processed file: 50MB
- Default pagination: 15-20 items
- Tokens via Laravel Sanctum
- Rate limiting applies

