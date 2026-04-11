🏛️ CivicPortal
Modernizing Public Governance through Digital Accessibility

Bridging the gap between citizens and municipal offices by digitizing administrative workflows.

## 🏗️ System Architecture (Based on ARCHITECTURE.md)

This project follows a strict Model-View-Controller (MVC) architecture designed for academic and professional clarity.

### 1. Model (Data Layer) - `/Model/`
Contains the "Blueprints" and data management logic.
* **`User.php`**: Blueprint for User objects with private attributes and getters/setters.
* **`ServiceRequest.php`**: Blueprint for Service Request objects.
* **`AppModel.php`**: Handlers for session-based data persistence.

### 2. View (Interface Layer) - `/View/`
Contains everything the user interacts with.
* **`FrontOffice/`**: Public-facing pages for Citizens (Submit requests, browse programs).
* **`BackOffice/`**: Admin/Management pages for Workers and Admins (Dashboard, Stats).
* **`assets/`**: Shared CSS, JavaScript validation, and media assets.

### 3. Controller (Logic Layer) - `/Controller/`
The "Brain" of the operation.
* **`MainController.php`**: Processes data and bridges the Model and the View. Includes methods like `showData()`.

### 4. Execution Logic - `Verification.php`
The centralized entry point for all form submissions and API calls. 
* It captures `$_POST` data, initializes Model objects, and passes them to the Controller for processing.

---

## 🛠️ Tech Stack
- **Frontend**: Vanilla JavaScript (ES6 Modules), HTML5, CSS3.
- **Backend**: PHP (Session-based MVC).
- **Architecture**: Separated Front/Back Office for enhanced security and scalability.

## 🚀 Getting Started
1. Deploy to a PHP-enabled server (e.g., XAMPP).
2. Navigate to `View/FrontOffice/` for the Citizen experience.
3. Navigate to `View/BackOffice/` for the Staff experience.
