# Installment Plan Feature Enhancement

This document outlines the detailed plan to enhance the installment payment functionality within the Tialo POS system. The goal is to provide a more robust, secure, and transparent installment process, including down payments, administrative authorization, and comprehensive receipt breakdowns.

## 1. Core Principles

*   **Accuracy:** All financial calculations and records must be precise.
*   **Security:** Administrative approval is required for all installment sales.
*   **Transparency:** Customers and cashiers will have clear breakdowns of payments and schedules.
*   **Maintainability:** Changes will adhere to existing code conventions and be well-documented.

## 2. Updated Installment Workflow

1.  **POS Screen - Installment Selection:**
    *   When "Installment" is selected as the payment method, new input fields and a dynamic summary will appear.
2.  **Down Payment Input:**
    *   A numeric input field for the "Down Payment" amount.
3.  **Customer Information:**
    *   `Customer Name` and `Customer Contact` fields will be made mandatory for installment sales.
4.  **Dynamic Breakdown Display:**
    *   A real-time summary will show:
        *   `Total Amount` (of the transaction)
        *   `Less Down Payment`
        *   `Balance for Installment`
        *   `Monthly Amortization` (calculated based on `Balance for Installment` and `installmentMonths`).
5.  **Admin Authorization:**
    *   Upon attempting to complete an installment sale, a modal/prompt will appear, requiring an Admin user to input their password for approval.
6.  **Sale Completion:**
    *   Upon successful admin authorization, the sale will be completed. The `down_payment` will be recorded, and the installment schedule will be generated based on the `Balance for Installment`.
7.  **Receipt Generation:**
    *   The printed receipt will include a detailed breakdown of the transaction, including the down payment and the installment schedule.

## 3. Detailed Plan of Action

### Phase 1: Database Schema Modification

**Objective:** Add a `down_payment` column to the `transactions` table to record initial payments.

*   **File:** `scripts/tialo_posdb.sql`
*   **Change:** Add a new column `down_payment` of type `DECIMAL(10,2)` with a default of `0.00` to the `transactions` table.
*   **Rationale:** This column is essential for accurately tracking the initial payment made by the customer and for calculating the correct balance for the installment plan.
*   **`CHANGELOG.md` Entry:** Will be created with a rationale for this database change.

### Phase 2: Backend Logic Updates

**Objective:** Implement admin password verification and adjust installment calculation logic.

1.  **Admin Password Verification:**
    *   **File:** `src/includes/auth_check.php` (or similar, investigate existing auth structure)
    *   **New File:** `src/modules/auth/verify_admin_password.php` (for AJAX requests)
    *   **Change:** Create an endpoint that receives a username (email) and password, checks if the user exists, has 'Admin' role, and if the password is correct. This will be a lightweight verification without full login.
    *   **Rationale:** To enforce security and control over installment plan approvals.

2.  **`complete_sale.php` Logic Adjustment:**
    *   **File:** `src/modules/pos/complete_sale.php`
    *   **Change:**
        *   Retrieve `down_payment` from the POST request.
        *   Update the `transactions` INSERT query to include the `down_payment`.
        *   Calculate `balance_for_installment = total_amount - down_payment`.
        *   Generate installment records (`installments` table) based on `balance_for_installment`.
    *   **Rationale:** To correctly account for the down payment and ensure installments are calculated on the remaining balance.

3.  **`process_checkout.php` Logic Adjustment:**
    *   **File:** `src/modules/pos/process_checkout.php`
    *   **Change:** Similar to `complete_sale.php`, ensure `down_payment` is handled and `balance_for_installment` is used for installment generation.
    *   **Rationale:** Consistency in handling installment logic across all checkout processes.

### Phase 3: Frontend (POS UI) Enhancements

**Objective:** Implement dynamic down payment input, customer info, installment breakdown, and admin password modal.

1.  **POS View Update:**
    *   **File:** `src/modules/pos/index.php` and `public/assets/js/pos.js`
    *   **Change:**
        *   Add `down_payment` input field within the "Installment" configuration block.
        *   Modify the visibility logic for the installment details section.
        *   Add display elements for the dynamic breakdown (Total, Less Down Payment, Balance, Monthly Due).
        *   Ensure `Customer Name` and `Customer Contact` fields are always visible and mandatory when installment is chosen.
    *   **Rationale:** To provide the cashier with necessary inputs and real-time financial information.

2.  **Dynamic Calculations:**
    *   **File:** `public/assets/js/pos.js`
    *   **Change:**
        *   Add JavaScript to listen for changes in the `down_payment` and `installmentMonths` fields.
        *   Implement client-side calculation to update the dynamic breakdown display.
        *   Ensure `customer_name` and `customer_contact` values are passed during checkout.
    *   **Rationale:** Immediate feedback for the cashier and accurate calculation presentation.

3.  **Admin Authorization Modal:**
    *   **File:** `src/modules/pos/index.php` (for modal HTML) and `public/assets/js/pos.js` (for logic)
    *   **Change:**
        *   Implement a modal dialog with username (email) and password fields for admin verification.
        *   When "Complete Sale" is clicked for an installment transaction, trigger this modal.
        *   Send an AJAX request to `verify_admin_password.php`.
        *   Proceed with sale completion only if verification is successful.
    *   **Rationale:** Enforce the security requirement.

### Phase 4: Receipt Enhancement

**Objective:** Display a comprehensive breakdown of installment details on the receipt.

*   **File:** `src/modules/pos/receipt.php`
*   **Change:**
    *   Retrieve `down_payment` from the `transactions` table.
    *   Retrieve all associated installment records from the `installments` table.
    *   Format and display the `total_amount`, `down_payment`, `balance_for_installment`, and a list of future installment due dates and amounts.
    *   The down payment will be listed as an initial payment, separate from the installment schedule itself, clearly indicating "Paid - Today".
*   **Rationale:** To provide the customer with a clear and transparent record of their purchase and payment plan.

---

I have created the `installmentplan.md` file. Please review it carefully.

Are you ready to proceed with implementing this plan?