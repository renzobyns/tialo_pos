# Installment Report - Card View Redesign

This document outlines the plan to transform the Installment Report from a flat table into a visual, card-based interface with a detailed drill-down modal.

## 1. Core Concept

*   **View Type:** Grid of Cards (Visual, Dashboard-style).
*   **Entity:** Each card represents one **Installment Transaction** (one customer's purchase), not a single monthly payment.
*   **Interaction:** Clicking a card opens a detailed modal showing the full payment schedule and allowing actions (Mark as Paid).

## 2. UI Design

### A. The Card (Main View)
Each card will display high-level summary info to give an instant status update:
*   **Header:** Customer Name (Bold) & Contact Number.
*   **Sub-header:** Receipt/Transaction #.
*   **Key Metrics:**
    *   Total Amount (Purchase Price).
    *   Balance Remaining (How much is left).
    *   Progress (e.g., "3/6 Paid").
*   **Visual Cue:**
    *   Green border/badge for "Up to Date".
    *   Red border/badge if any specific installment is "Overdue".

### B. The Detail Modal (Drill-down)
When a card is clicked, a modal appears with:
*   **Transaction Info:** Date, Items Purchased (Summary).
*   **Financials:** Total, Down Payment, Paid So Far, Balance.
*   **Schedule Table:**
    *   List of all due dates.
    *   Status of each (Paid/Unpaid/Overdue).
    *   **Action:** "Mark as Paid" button for unpaid items.

## 3. Implementation Steps

### Phase 1: Backend Data Preparation
**File:** `src/modules/reports/index.php` (Installment Tab Logic)
*   **Query Change:** instead of selecting all `installments` rows, fetch `transactions` where `payment_type = 'Installment'`.
*   **Aggregation:** For each transaction, sub-query or join to calculate:
    *   Total installments count.
    *   Paid installments count.
    *   Sum of remaining balances.
    *   Check for any overdue items.
*   **Data Structure:** Prepare an array `$installment_accounts` to loop through for the cards.

### Phase 2: Frontend - Card Grid
**File:** `src/modules/reports/index.php`
*   **Layout:** Replace the `<table>` with a `<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">`.
*   **Card HTML:** Design the card using Tailwind CSS (white bg, shadow, rounded corners).
*   **Logic:** Loop through `$installment_accounts` to render each card.

### Phase 3: Frontend - Detail Modal
**File:** `src/modules/reports/index.php`
*   **Modal HTML:** Add a hidden modal structure at the bottom of the page.
*   **Population Logic:**
    *   **Option A (Simpler):** Render the details hidden inside the card and move them to the modal on click (Good for small data sets).
    *   **Option B (Scalable):** Use AJAX to fetch details.
    *   *Decision:* We will use **Option A** (Render hidden data attributes or a hidden div) for now as it's faster to implement and feels snappier for the user, assuming < 100 active installment accounts.

### Phase 4: Interactions (JS)
**File:** `public/assets/js/reports.js` (or inline in `index.php`)
*   **Event Listener:** click on `.installment-card`.
*   **Action:**
    1.  Read data from the clicked card.
    2.  Populate the Modal fields (Name, Balances).
    3.  Populate the Schedule Table in the modal.
    4.  Show Modal.
*   **Form Handling:** Ensure the "Mark as Paid" forms inside the modal work correctly and refresh the page (or update UI) upon success.

## 4. Expected User Experience
1.  Admin clicks "Installment Reports".
2.  Sees a clean grid of customer cards.
3.  Notices a card with a Red badge ("Overdue").
4.  Clicks the card.
5.  Modal opens showing "Dec 12" payment is missed.
6.  Admin collects payment, clicks "Mark as Paid".
7.  Page refreshes, card is now Green ("Up to Date").
