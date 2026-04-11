# CivicPortal

## 1. Model (Data Layer)
* **`Model/`**: This folder contains your "Blueprints."
    * **`EntityName.php`**: Defines the object (e.g., Book, User, Product) with private attributes.
    * Includes a `__construct()` for initialization and `getters/setters` for data access.

## 2. View (Interface Layer)
* **`View/`**: This folder is what the user actually sees.
    * **`FrontOffice/`**: Public-facing pages (e.g., `index.html` for landing, catalog browsing).
    * **`BackOffice/`**: Admin/Management pages (e.g., `addForm.php`, `dashboard.php`).
    * **`assets/`**: Local storage for CSS, JavaScript (validation scripts), and media.

## 3. Controller (Logic Layer)
* **`Controller/`**: The "Brain" of the operation.
    * **`MainController.php`**: Contains methods to process data (like `showData()`) and bridges the Model and the View.

## 4. Execution Logic
* **`Verification.php`**: The entry point for forms. It captures `$_POST` data, creates an object from your Model, and passes it to the Controller.

