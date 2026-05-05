# CivicPortal Test Credentials

Use the following accounts to test the different modules and role-based permissions in the CivicPortal platform.

## 🛠 Default Test Accounts
All accounts use the same default password: **`password123`**

| Role | Username | Email | Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | `fatma_admin` | `admin@municipalite.tn` | Full access to Back-Office, User Management, and Program creation. |
| **Agent** | `ali_agent` | `ali@municipalite.tn` | Access to Service Request validation and Program management. |
| **Citizen** | `ahmed_citizen` | `ahmed@gmail.com` | Standard access to Front-Office, Program Enrollment, and Service Requests. |

---

## 🚀 How to Reset Data
If the database state becomes inconsistent, you can reset the tables and re-seed the default data by navigating to:
`http://localhost/CivicPortal/Model/DbSetup.php`

> [!IMPORTANT]
> The reset script will **TRUNCATE** all tables before seeding. Any custom data created during testing will be lost.

## 📝 Registration
You can also create new Citizen accounts using the registration form at `/View/FrontOffice/register.php`. 

To create an **Admin** account via registration for testing purposes, prefix the Full Name field with `admin-` (e.g., `admin-Hamza`). The system will automatically promote the account to the Admin role.
