/**
 * view.js
 * BackOffice rendering logic
 */

const view = {
    get app() {
        return document.getElementById('app');
    },

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
            <div class="nav-brand">
                CIVICPORTAL<br/>STAFF
            </div>
            <ul class="nav-links">
                <li><a href="#home"><i class="bi bi-grid-1x2"></i> HOME</a></li>
                ${role === 'worker' ? `
                    <li><a href="#worker-dashboard"><i class="bi bi-clipboard-check"></i> QUEUE</a></li>
                ` : ''}
                ${role === 'admin' ? `
                    <li><a href="#admin-stats"><i class="bi bi-bar-chart"></i> STATS</a></li>
                    <li><a href="#admin-inbox"><i class="bi bi-inbox"></i> INBOX</a></li>
                ` : ''}
                <li><a href="#profile"><i class="bi bi-person"></i> PROFILE</a></li>
            </ul>
            <div class="user-controls" style="display:flex; flex-direction:column; gap:10px; align-items:center;">
                <div style="display:flex; gap:5px; width:100%;">
                    <a href="../FrontOffice/index.php" class="btn btn-small" style="flex:1; background:white; color:var(--primary-navy); font-weight:900;">FRONT</a>
                    <div class="user-role-badge" style="flex:1; margin:0;">${role.toUpperCase()}</div>
                </div>
            </div>
        `;
        nav.innerHTML = links;
        
        // Highlight active link
        setTimeout(() => {
            const hash = window.location.hash || '#home';
            document.querySelectorAll('.nav-links a').forEach(link => {
                const href = link.getAttribute('href');
                if (href && hash.startsWith(href)) {
                    link.classList.add('active');
                } else {
                    link.classList.remove('active');
                }
            });
        }, 50);
    },

    renderHome(user) {
        if (!user) {
            if (this.app) this.app.innerHTML = '<section class="page-container"><p>Loading system data...</p></section>';
            return;
        }

        let content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1 style="font-size: 2.5rem; color: var(--primary-navy); font-weight: 900;">Staff Portal Dashboard</h1>
                    <p style="font-size: 1.1rem; opacity: 0.8; margin-top: 5px;">Operational overview for <strong>${user.name}</strong></p>
                </section>
            </div>
        `;

        const role = (user.role || 'staff').toLowerCase();

        if (role === 'worker') {
             content += `
            <section class="page-container">
                <h2 class="reveal"><i class="bi bi-gear-fill" style="margin-right: 10px; color: var(--accent-blue);"></i> Operations Console</h2>
                <div class="editorial-grid">
                    <div class="editorial-card reveal" style="border-left: 5px solid var(--accent-blue);">
                        <h3>Service Request Queue</h3>
                        <p>Process and validate citizen filings. Current pending items: <strong>${this.getServiceRequestsCount() || 0}</strong></p>
                        <a href="#worker-dashboard" class="btn btn-primary" style="margin-top: auto; border-radius: 50px;">OPEN QUEUE</a>
                    </div>
                </div>
            </section>
            `;
        } else if (role === 'admin') {
             content += `
            <section class="page-container">
                <h2 class="reveal"><i class="bi bi-shield-check" style="margin-right: 10px; color: var(--success);"></i> Administrative Overview</h2>
                <div class="editorial-grid">
                    <div class="editorial-card reveal" style="border-left: 5px solid var(--accent-blue);">
                        <h3>Platform Statistics</h3>
                        <p>Aggregated metrics across all civic modules. Monitor system health and engagement.</p>
                        <a href="#admin-stats" class="btn btn-primary" style="margin-top: auto; border-radius: 50px;">VIEW STATS</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Grievance Inbox</h3>
                        <p>Review and process citizen feedback securely routed to the administration.</p>
                        <a href="#admin-inbox" class="btn" style="margin-top: auto; border-radius: 50px;">OPEN INBOX</a>
                    </div>
                </div>
            </section>
            `;
        } else {
            content += `
            <section class="page-container">
                <div class="editorial-card reveal" style="text-align: center; border: 2px dashed var(--secondary-grey);">
                    <p style="padding: 50px; font-weight: 600;">You are logged in as ${role.toUpperCase()}. Administrative or Worker privileges are required for console access.</p>
                </div>
            </section>
            `;
        }

        if (this.app) this.app.innerHTML = content;
        this.triggerObserver();
    },

    getServiceRequestsCount() {
        // Safe access helper
        try {
            return document.querySelector('.nav-links a[href="#worker-dashboard"] .nav-dot')?.textContent || 0;
        } catch (e) { return 0; }
    },

    renderProfile(user, editMode = false) {
        if (!user) {
            if (this.app) this.app.innerHTML = '<section class="page-container"><p>Failed to load profile details.</p></section>';
            return;
        }

        const avatarSrc = user.avatar
            ? user.avatar
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=1D2A44&color=ffffff&size=200&bold=true`;

        const roleLabel = (user.role || 'staff').toUpperCase();

        const detailRows = [
            { icon: 'bi-envelope', label: 'Staff Email', value: user.email || '—' },
            { icon: 'bi-shield-lock', label: 'Access Role', value: roleLabel + ' PORTAL' },
        ];

        const detailCards = detailRows.map(d => `
            <div class="pf-detail-card reveal">
                <span class="pf-detail-icon"><i class="bi ${d.icon}"></i></span>
                <div class="pf-detail-body">
                    <span class="pf-detail-label">${d.label}</span>
                    <span class="pf-detail-value">${d.value}</span>
                </div>
            </div>
        `).join('');

        const bioContent = user.bio ? `
            <div class="pf-bio reveal" style="margin-top: 1rem; text-align: center; max-width: 600px; font-weight: 500; font-size: 1.1rem; line-height: 1.6; color: var(--primary-navy);">
                <i class="bi bi-quote" style="opacity: 0.3; font-size: 1.5rem;"></i>
                ${user.bio}
                <i class="bi bi-quote" style="opacity: 0.3; font-size: 1.5rem; transform: scaleX(-1); display: inline-block;"></i>
            </div>
        ` : '<p class="reveal" style="opacity: 0.5; margin-top: 1rem;">Staff biography not provided.</p>';

        const profileSummary = `
            <div class="pf-details-grid">
                ${detailCards}
            </div>
        `;

        const editForm = `
            <div class="pf-edit-form-wrap reveal" style="margin-top:3rem; width: 100%; max-width: 900px;">
                <h3 style="font-size:1.4rem; font-weight:900; text-transform:uppercase; letter-spacing:-0.5px; color:#1D2A44; margin-bottom:2rem;">Staff Details</h3>
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
                        <button type="submit" class="btn btn-primary" style="border-radius: 50px;">UPDATE DETAILS</button>
                        <a href="#home" class="btn" style="text-decoration:none; text-align:center; border-radius: 50px;">CANCEL</a>
                    </div>
                </form>
            </div>
        `;

        if (this.app) {
            this.app.innerHTML = `
                <section class="pf-page reveal">
                    <!-- Cover -->
                    <div class="pf-cover">
                        <div class="pf-cover-gradient"></div>
                    </div>

                    <!-- Profile Header (Pic -> Name -> Bio) -->
                    <div class="pf-header" style="padding-bottom: 4rem;">
                        <div class="pf-avatar-wrap">
                            <img src="${avatarSrc}" alt="${user.name}" class="pf-avatar">
                        </div>
                        <div class="pf-header-info reveal" style="text-align: center;">
                            <h2 class="pf-name" style="margin: 0;">${user.name}</h2>
                            <span class="pf-role-badge" style="margin-top: 0.5rem;">${roleLabel}</span>
                        </div>
                        ${bioContent}
                    </div>

                    <!-- Info Area (Below Bio) -->
                    <div class="pf-content" style="background: var(--bg-neutral); padding-top: 4rem;">
                        ${editMode ? editForm : profileSummary}
                    </div>
                </section>
            `;
        }
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

        if (this.app) {
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
        }
        this.triggerObserver();
    },

    renderAdminStats(stats) {
        if (this.app) {
            this.app.innerHTML = `
                <section class="page-container">
                    <h2 class="reveal">System Statistics</h2>
                    <div class="editorial-grid">
                        <div class="editorial-card reveal">
                            <i class="bi bi-people-fill" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; color: inherit;"></i><h3>Total Users</h3>
                            <p class="stats-number">${stats.usersCount}</p>
                        </div>
                        <div class="editorial-card reveal editorial-highlight">
                            <i class="bi bi-file-earmark-arrow-up" style="font-size: 2rem; margin-bottom: 0.5rem; display: block; color: inherit;"></i><h3>Service Requests</h3>
                            <p class="stats-number">${stats.requestsCount}</p>
                        </div>
                    </div>
                </section>
            `;
        }
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

        if (this.app) {
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
        }
        this.triggerObserver();
    }
};

export default view;