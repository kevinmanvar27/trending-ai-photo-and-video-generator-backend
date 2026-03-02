# Template Coins System - Flow Diagram

## User Flow: Using a Template

```
┌─────────────────────────────────────────────────────────────────┐
│                    FLUTTER APP (User Side)                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ 1. Browse Templates
                              ▼
                    ┌──────────────────┐
                    │  GET /templates  │
                    └──────────────────┘
                              │
                              │ Response: Templates with coins_required
                              ▼
                    ┌──────────────────┐
                    │  User selects    │
                    │  template with   │
                    │  10 coins req.   │
                    └──────────────────┘
                              │
                              │ 2. Upload image + template_id
                              ▼
                    ┌──────────────────┐
                    │ POST /submissions│
                    │ + Authorization  │
                    └──────────────────┘
                              │
┌─────────────────────────────┼─────────────────────────────┐
│                    BACKEND (Laravel)                       │
│                             │                              │
│                             ▼                              │
│                  ┌──────────────────┐                      │
│                  │  Authenticate    │                      │
│                  │  User            │                      │
│                  └──────────────────┘                      │
│                             │                              │
│                             ▼                              │
│                  ┌──────────────────┐                      │
│                  │  Check Active    │                      │
│                  │  Subscription?   │                      │
│                  └──────────────────┘                      │
│                             │                              │
│                    ┌────────┴────────┐                     │
│                    │                 │                     │
│                   NO                YES                    │
│                    │                 │                     │
│                    ▼                 ▼                     │
│           ┌──────────────┐  ┌──────────────┐              │
│           │ Return 403   │  │ Check Coins  │              │
│           │ No Sub Error │  │ Required     │              │
│           └──────────────┘  └──────────────┘              │
│                                     │                      │
│                        ┌────────────┴────────────┐        │
│                        │                         │        │
│                 coins_required > 0?       coins = 0       │
│                        │                         │        │
│                       YES                       NO        │
│                        │                         │        │
│                        ▼                         │        │
│              ┌──────────────────┐                │        │
│              │ Has Enough Coins?│                │        │
│              └──────────────────┘                │        │
│                        │                         │        │
│               ┌────────┴────────┐                │        │
│               │                 │                │        │
│              NO                YES               │        │
│               │                 │                │        │
│               ▼                 ▼                │        │
│      ┌──────────────┐  ┌──────────────┐         │        │
│      │ Return 403   │  │ Deduct Coins │         │        │
│      │ Insufficient │  │ from Sub     │         │        │
│      └──────────────┘  └──────────────┘         │        │
│                                │                 │        │
│                                └─────────────────┘        │
│                                         │                 │
│                                         ▼                 │
│                              ┌──────────────────┐         │
│                              │ Create           │         │
│                              │ Submission       │         │
│                              └──────────────────┘         │
│                                         │                 │
│                                         ▼                 │
│                              ┌──────────────────┐         │
│                              │ Increment        │         │
│                              │ Usage Count      │         │
│                              └──────────────────┘         │
│                                         │                 │
└─────────────────────────────────────────┼─────────────────┘
                                          │
                                          │ Return Success
                                          ▼
                              ┌──────────────────┐
                              │ Response:        │
                              │ - submission     │
                              │ - coins_deducted │
                              │ - remaining_coins│
                              └──────────────────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────────────────────┐
│                    FLUTTER APP (User Side)                      │
│                                                                 │
│                  Show Success + Remaining Coins                 │
└─────────────────────────────────────────────────────────────────┘
```

## Database Tables Involved

```
┌─────────────────────────────────────────────────────────────────┐
│                    image_prompt_templates                       │
├─────────────────────────────────────────────────────────────────┤
│ id                                                              │
│ title                                                           │
│ type (image/video)                                              │
│ description                                                     │
│ prompt                                                          │
│ reference_image_path                                            │
│ is_active                                                       │
│ usage_count                                                     │
│ coins_required  ← NEW FIELD                                     │
│ created_at                                                      │
│ updated_at                                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ template_id (FK)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    user_image_submissions                       │
├─────────────────────────────────────────────────────────────────┤
│ id                                                              │
│ user_id (FK)                                                    │
│ template_id (FK)                                                │
│ original_image_path                                             │
│ processed_image_path                                            │
│ output_type                                                     │
│ status                                                          │
│ error_message                                                   │
│ processing_time                                                 │
│ started_at                                                      │
│ completed_at                                                    │
│ created_at                                                      │
│ updated_at                                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ user_id (FK)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                       users                                     │
├─────────────────────────────────────────────────────────────────┤
│ id                                                              │
│ name                                                            │
│ email                                                           │
│ password                                                        │
│ role                                                            │
│ ...                                                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ user_id (FK)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    user_subscriptions                           │
├─────────────────────────────────────────────────────────────────┤
│ id                                                              │
│ user_id (FK)                                                    │
│ subscription_plan_id (FK)                                       │
│ started_at                                                      │
│ expires_at                                                      │
│ status (active/cancelled/expired)                               │
│ coins_used  ← INCREMENTED WHEN TEMPLATE USED                    │
│ cancelled_at                                                    │
│ created_at                                                      │
│ updated_at                                                      │
└─────────────────────────────────────────────────────────────────┘
                              │
                              │ subscription_plan_id (FK)
                              ▼
┌─────────────────────────────────────────────────────────────────┐
│                    subscription_plans                           │
├─────────────────────────────────────────────────────────────────┤
│ id                                                              │
│ name                                                            │
│ description                                                     │
│ price                                                           │
│ duration_type                                                   │
│ duration_value                                                  │
│ coins  ← TOTAL COINS IN PLAN                                    │
│ features                                                        │
│ is_active                                                       │
│ created_at                                                      │
│ updated_at                                                      │
└─────────────────────────────────────────────────────────────────┘
```

## Coins Calculation

```
User's Remaining Coins = Plan Total Coins - Coins Used

Example:
- Plan: 100 coins
- Used: 35 coins
- Remaining: 65 coins

When user uses template with 10 coins:
- Coins Used: 35 + 10 = 45
- Remaining: 100 - 45 = 55
```

## Error Scenarios

```
Scenario 1: No Active Subscription
┌──────────────────────────────────┐
│ User has no active subscription  │
│ OR subscription expired          │
└──────────────────────────────────┘
                │
                ▼
        ┌──────────────┐
        │ Return 403   │
        │ Error        │
        └──────────────┘

Scenario 2: Insufficient Coins
┌──────────────────────────────────┐
│ Template requires: 10 coins      │
│ User has: 5 coins                │
└──────────────────────────────────┘
                │
                ▼
        ┌──────────────┐
        │ Return 403   │
        │ Error        │
        └──────────────┘

Scenario 3: Template Inactive
┌──────────────────────────────────┐
│ Template is_active = false       │
└──────────────────────────────────┘
                │
                ▼
        ┌──────────────┐
        │ Return 400   │
        │ Error        │
        └──────────────┘

Scenario 4: Success
┌──────────────────────────────────┐
│ ✓ Active subscription            │
│ ✓ Enough coins                   │
│ ✓ Template active                │
└──────────────────────────────────┘
                │
                ▼
        ┌──────────────┐
        │ Deduct coins │
        │ Create sub   │
        │ Return 201   │
        └──────────────┘
```

## API Response Flow

```
Request: POST /api/submissions
{
  "template_id": 1,
  "original_image": [file],
  "output_type": "image"
}

                │
                ▼
        ┌──────────────┐
        │   Process    │
        └──────────────┘
                │
    ┌───────────┴───────────┐
    │                       │
   FAIL                  SUCCESS
    │                       │
    ▼                       ▼
┌──────────┐         ┌──────────┐
│ 400/403  │         │   201    │
│ 500      │         │          │
└──────────┘         └──────────┘
    │                       │
    ▼                       ▼
{                       {
  "success": false,       "success": true,
  "message": "...",       "message": "...",
  "coins_required": 10,   "data": {...},
  "coins_available": 5    "coins_deducted": 10,
}                         "remaining_coins": 90
                        }
```

---

**Visual Guide Created:** March 2, 2026  
**Status:** Complete ✅
