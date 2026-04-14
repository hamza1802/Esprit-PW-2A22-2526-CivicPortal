/**
 * view.js
 * BackOffice rendering logic
 */

const view = {
    app: document.getElementById('app'),

    triggerObserver() {
        if (window.initScrollObserver) {
            setTimeout(() => window.initScrollObserver(), 50);
        }
    },

    renderToast(message, type = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerText = message;
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    },

    renderNavBar(role) {
        const nav = document.querySelector('nav');
        const links = `
            <div class="nav-brand" style="color:var(--primary-red);">
                CivicPortal Staff
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                ${role === 'worker' ? '<li><a href="#worker-dashboard">dashboard</a></li>' : ''}
                ${role === 'admin' ? '<li><a href="#admin-stats">statistics</a></li><li><a href="#admin-inbox">inbox</a></li><li><a href="forumDashboard.php">forum mgmt</a></li>' : ''}
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls">
                <div class="user-role-badge" style="background:var(--primary-red);color:white;">${role}</div>
            </div>
        `;
        nav.innerHTML = links;
    },

    renderHome(user) {
        let content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>Staff Portal</h1>
                    <p>Welcome back, ${user.name}. Access your staff module.</p>
                </section>
            </div>
        `;

        if (user.role === 'worker') {
             content += `
            <section class="page-container">
                <h2 class="reveal">Operations Console</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Service Request Queue</h3>
                        <p>Process pending administrative filings. Validate or reject documents submitted by the citizens of CivicPortal.</p>
                        <a href="#worker-dashboard" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Open Dashboard</a>
                    </div>
                </div>
            </section>
            `;
        } else if (user.role === 'admin') {
             content += `
            <section class="page-container">
                <h2 class="reveal">Administrative Overview</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Platform Statistics</h3>
                        <p>View real-time aggregated data across all civic modules to monitor system health and engagement.</p>
                        <a href="#admin-stats" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Stats</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Grievance Inbox</h3>
                        <p>Review and process citizen feedback securely routed to the administrative branch.</p>
                        <a href="#admin-inbox" class="btn" style="align-self: flex-start; margin-top: auto;">Open Inbox</a>
                    </div>

                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Forum Management</h3>
                        <p>Manage citizen discussions. Pin important posts, close threads, and moderate inappropriate comments.</p>
                        <a href="forumDashboard.php" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Manage Forum</a>
                    </div>
                </div>
            </section>
            `;
        }

        this.app.innerHTML = content;
        this.triggerObserver();
    },

    renderProfile(user) {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Staff Profile</h2>
                <div class="form-card reveal">
                    <form id="profile-form">
                        <div class="form-group">
                            <label for="profile-name">Full Name</label>
                            <input type="text" id="profile-name" name="name" value="${user.name}" required>
                        </div>
                        <div class="form-group">
                            <label for="profile-email">Email Address</label>
                            <input type="email" id="profile-email" name="email" value="${user.email}" required>
                        </div>
                        <div class="form-group" style="display: flex; gap: 1rem;">
                            <button type="submit" class="btn btn-primary">UPDATE DETAILS</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderWorkerDashboard(requests) {
        const tableRows = requests.map(r => `
            <tr>
                <td><strong>#${r.id}</strong></td>
                <td>${r.type}</td>
                <td>${r.date}</td>
                <td><span class="status-badge status-${r.status}">${r.status}</span></td>
                <td>
                    <button class="btn btn-small btn-success" data-action="validate" data-id="${r.id}" style="margin-right: 5px;">VALIDATE</button>
                    <button class="btn btn-small btn-danger" data-action="reject" data-id="${r.id}">REJECT</button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Worker Dashboard</h2>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Service Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.length > 0 ? tableRows : '<tr><td colspan="5" style="text-align:center;">no data available</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderAdminStats(stats) {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">System Statistics</h2>
                <div class="editorial-grid">
                    <div class="editorial-card reveal">
                        <h3>Total Users</h3>
                        <p class="stats-number">${stats.usersCount}</p>
                    </div>
                    <div class="editorial-card reveal editorial-highlight">
                        <h3>Service Requests</h3>
                        <p class="stats-number">${stats.requestsCount}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Active Programs</h3>
                        <p class="stats-number">${stats.programsCount}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Enrollments</h3>
                        <p class="stats-number">${stats.enrollmentsCount}</p>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderAdminInbox(complaints) {
        const tableRows = complaints.map(c => `
            <tr>
                <td><strong>#${c.id}</strong></td>
                <td>${c.date}</td>
                <td>${c.subject}</td>
                <td>${c.body}</td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Grievance Inbox</h2>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref ID</th>
                                    <th>Date</th>
                                    <th>Subject</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.length > 0 ? tableRows : '<tr><td colspan="4" style="text-align:center;">inbox is empty</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    }
};

export default view;
