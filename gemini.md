# Gemini Project Guide: Documentation & Change Management

This document outlines the official process for making and documenting changes to the `tialo_pos` project. Its purpose is to ensure all documentation is kept up-to-date and that every significant change is recorded with a clear justification.

## Core Principle: Document with Rationale

Every change to the codebase, database, or project structure must be documented in the `CHANGELOG.md`. It's not enough to say *what* changed; we must also document *why* the change was made.

---

## 1. `CHANGELOG.md`: The Source of Truth

The `CHANGELOG.md` is the most important historical document in the project. All notable changes must be recorded here before they are considered "done".

### How to Add an Entry

When making a change, add an entry under the `[Unreleased]` section. Follow this template, which is based on the entry for version `3.0.0`:

```markdown
### Rationale: [Explain the "Why"]

*   **Chosen Approach:** [Briefly describe the solution or change you implemented.]
*   **Alternative Considered:** [Optional: Mention any other solutions you thought about.]
*   **Reason for Decision:** [Explain why you chose this approach. Justify the benefits, such as improved security, better performance, or easier maintenance.]

### [Added | Changed | Fixed | Removed]
- [A clear, concise description of the change.]
- [Another description if needed.]
```

### Example:
```markdown
### Rationale: Why we added a discount field

*   **Chosen Approach:** Added a `discount_amount` column to the `transactions` table.
*   **Reason for Decision:** The business needs to track discounts given at the point of sale for reporting purposes. Storing this directly in the transaction record is the most direct way to associate a discount with a specific sale.

### Changed
- **Database Schema:** Added `discount_amount` column (`DECIMAL(10,2)`) to the `transactions` table.
```

---

## 2. Database Schema: `scripts/tialo_posdb.sql`

The `tialo_posdb.sql` file must always represent the **complete and latest version of the database schema**. It should be a clean file that can be used to set up a new database from scratch.

### When Changing the Database:

1.  **Log the Change:** Before anything else, create an entry in `CHANGELOG.md` explaining the schema change and the rationale behind it (see example above).
2.  **Update the Schema File:** After applying a change (e.g., using an `ALTER TABLE` command) to your local database, you must **export the entire updated schema** and overwrite the existing `scripts/tialo_posdb.sql`. Do not leave `ALTER TABLE` statements in the file. The goal is for the `CREATE TABLE` statements themselves to be the definitive schema.

This process ensures that any developer can get the latest database structure just by importing this single SQL file.

---

## 3. `README.md`: The Entry Point

The `README.md` is the project's "front door." It must always contain accurate, up-to-date instructions for setting up, configuring, and running the project.

### When to Update the `README.md`:

-   If a change to the code or structure affects the **installation steps** (e.g., a new required PHP extension).
-   If you change the **configuration process** (e.g., adding a new key to `config.php`).
-   If the **deployment process** changes.

Always review the `README.md` after a significant change to ensure it is still accurate.
