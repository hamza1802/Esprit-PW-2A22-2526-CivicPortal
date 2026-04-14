/**
 * view.js
 * BackOffice rendering logic — Next‑Level Parks & Recreation
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

    renderNavBar(role, enrollmentCounts = null) {
        const nav = document.querySelector('nav');
        const totalBadge = enrollmentCounts && enrollmentCounts.pending > 0
            ? `<span class="nav-dot">${enrollmentCounts.pending}</span>` : '';

        const links = `
            <div class="nav-brand" style="color:var(--primary-red);">
                CivicPortal Staff
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                ${role === 'worker' ? `
                    <li><a href="#worker-dashboard">dashboard</a></li>
                    <li><a href="#manage-programs" style="position:relative;">programs ${totalBadge}</a></li>
                ` : ''}
                ${role === 'admin' ? `
                    <li><a href="#admin-stats">statistics</a></li>
                    <li><a href="#manage-programs" style="position:relative;">programs ${totalBadge}</a></li>
                    <li><a href="#admin-inbox">inbox</a></li>
                ` : ''}
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
                    <div class="editorial-card reveal">
                        <h3>Parks & Recreation</h3>
                        <p>View community programs and manage citizen enrollment requests. Approve or reject registrations.</p>
                        <a href="#manage-programs" class="btn" style="align-self: flex-start; margin-top: auto;">View Programs</a>
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
                    <div class="editorial-card reveal">
                        <h3>Parks & Recreation</h3>
                        <p>Manage community programs, workshops, and facilities. Ensure civic engagement is vibrant and accessible.</p>
                        <a href="#manage-programs" class="btn" style="align-self: flex-start; margin-top: auto;">Manage Programs</a>
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
    },

    /* =========================================================================
       PROGRAMS MANAGER — Premium Card Grid with Notification Dots
       ========================================================================= */
    renderProgramsManager(programs, role) {
        const totalEnrollments = programs.reduce((sum, p) => sum + parseInt(p.enrollment_count || 0), 0);

        const programCards = programs.map(p => {
            const enrolled = parseInt(p.enrollment_count || 0);
            const pending = parseInt(p.pending_count || 0);
            const confirmed = parseInt(p.confirmed_count || 0);
            const cap = parseInt(p.capacity || 1);
            const fillPct = Math.min(Math.round((enrolled / cap) * 100), 100);
            const isFull = enrolled >= cap;

            return `
                <div class="program-mgmt-card reveal" data-action="view-program" data-id="${p.id}" style="cursor:pointer;">
                    <div class="program-mgmt-header">
                        <div class="program-mgmt-img" style="background-image: url('../assets/images/${p.image || 'default.jpg'}');"></div>
                        ${enrolled > 0 ? `<span class="program-dot${pending > 0 ? ' has-pending' : ''}">${enrolled}</span>` : ''}
                    </div>
                    <div class="program-mgmt-body">
                        <span class="category-badge">${p.category}</span>
                        <h3 style="margin: 0.5rem 0;">${p.title}</h3>
                        <p style="font-size: 0.95rem; flex-grow: 1; margin-bottom: 1rem;">${p.description.substring(0, 80)}${p.description.length > 80 ? '...' : ''}</p>
                        <div style="display: flex; gap: 0.5rem; align-items: center; font-size: 0.85rem; margin-bottom: 0.5rem;">
                            <span style="font-weight:800;">📍 ${p.location}</span>
                            <span style="margin-left:auto; font-weight:800;">${enrolled}/${cap} enrolled</span>
                        </div>
                        <div class="capacity-track">
                            <div class="capacity-fill${isFull ? ' full' : ''}" style="width: ${fillPct}%;"></div>
                        </div>
                        <div style="display:flex; gap:0.5rem; margin-top: 1rem; font-size: 0.8rem;">
                            ${pending > 0 ? `<span class="mini-stat pending">${pending} pending</span>` : ''}
                            ${confirmed > 0 ? `<span class="mini-stat confirmed">${confirmed} confirmed</span>` : ''}
                            ${isFull ? '<span class="mini-stat full">FULL</span>' : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="reveal" style="margin:0; border:none; padding:0;">Parks & Recreation</h2>
                    <div style="display:flex; gap: 1rem; align-items: center;">
                        <span class="reveal" style="font-weight:800; font-size:0.9rem; text-transform:uppercase; letter-spacing:1px;">${totalEnrollments} total enrollments</span>
                        ${role === 'admin' ? '<button class="btn btn-primary reveal" data-action="new-program">+ NEW PROGRAM</button>' : ''}
                    </div>
                </div>
                <div class="programs-mgmt-grid">
                    ${programCards.length > 0 ? programCards : '<p class="reveal" style="text-align:center; padding: 3rem;">No programs found.</p>'}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       PROGRAM DETAIL — Full View with Enrollment Roster
       ========================================================================= */
    renderProgramDetail(program, enrollments, role) {
        const enrolled = parseInt(program.enrollment_count || 0);
        const pending = parseInt(program.pending_count || 0);
        const confirmed = parseInt(program.confirmed_count || 0);
        const cap = parseInt(program.capacity || 1);
        const fillPct = Math.min(Math.round((enrolled / cap) * 100), 100);

        const enrollmentRows = enrollments.map(e => {
            const isPending = e.status === 'pending';
            return `
                <tr>
                    <td><strong>#${e.id}</strong></td>
                    <td>${e.username}</td>
                    <td>${e.email}</td>
                    <td>${new Date(e.enrolled_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</td>
                    <td><span class="status-badge status-${e.status}">${e.status}</span></td>
                    <td>
                        ${isPending ? `
                            <button class="btn btn-small btn-success" data-action="confirm-enroll" data-id="${e.id}" data-program-id="${program.id}" style="margin-right:4px;">CONFIRM</button>
                            <button class="btn btn-small btn-danger" data-action="cancel-enroll" data-id="${e.id}" data-program-id="${program.id}">REJECT</button>
                        ` : `<span style="font-size:0.85rem; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; opacity:0.6;">—</span>`}
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#manage-programs" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Programs</a>
                </div>

                <div class="program-detail-hero reveal">
                    <div class="program-detail-img" style="background-image: url('../assets/images/${program.image || 'default.jpg'}');"></div>
                    <div class="program-detail-info">
                        <span class="category-badge" style="margin-bottom: 1rem;">${program.category}</span>
                        <h2 style="border:none; padding:0; margin: 0 0 0.5rem 0;">${program.title}</h2>
                        <p style="margin-bottom: 1.5rem;">${program.description}</p>
                        <div style="display:flex; gap:2rem; flex-wrap:wrap; margin-bottom:1.5rem;">
                            <div><span style="font-weight:900; text-transform:uppercase; font-size:0.8rem; letter-spacing:1px; display:block; margin-bottom:4px;">Location</span>${program.location}</div>
                            <div><span style="font-weight:900; text-transform:uppercase; font-size:0.8rem; letter-spacing:1px; display:block; margin-bottom:4px;">Capacity</span>${cap}</div>
                            <div><span style="font-weight:900; text-transform:uppercase; font-size:0.8rem; letter-spacing:1px; display:block; margin-bottom:4px;">Status</span><span class="status-badge status-${program.status}">${program.status}</span></div>
                        </div>
                        <div style="display:flex; gap:2rem; flex-wrap:wrap; margin-bottom:1rem;">
                            <div class="detail-stat">
                                <span class="detail-stat-number">${enrolled}</span>
                                <span class="detail-stat-label">ENROLLED</span>
                            </div>
                            <div class="detail-stat">
                                <span class="detail-stat-number" style="color: var(--accent-blue);">${pending}</span>
                                <span class="detail-stat-label">PENDING</span>
                            </div>
                            <div class="detail-stat">
                                <span class="detail-stat-number" style="color: var(--success);">${confirmed}</span>
                                <span class="detail-stat-label">CONFIRMED</span>
                            </div>
                        </div>
                        <div class="capacity-track" style="height: 16px;">
                            <div class="capacity-fill${fillPct >= 100 ? ' full' : ''}" style="width: ${fillPct}%;"></div>
                        </div>
                        <p style="font-size:0.8rem; font-weight:700; margin-top:0.5rem;">${fillPct}% capacity filled</p>
                        ${role === 'admin' ? `
                            <div style="display:flex; gap:0.5rem; margin-top:1.5rem;">
                                <button class="btn btn-small btn-primary" data-action="edit-program" data-id="${program.id}">EDIT PROGRAM</button>
                                <button class="btn btn-small btn-danger" data-action="delete-program" data-id="${program.id}">DELETE</button>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <div style="margin-top: 3rem;">
                    <h2 class="reveal" style="font-size: clamp(1.5rem, 3vw, 2.5rem);">Enrollment Roster <span style="font-weight:400; font-size:0.7em;">(${enrollments.length})</span></h2>
                    <div class="reveal">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Citizen</th>
                                        <th>Email</th>
                                        <th>Enrolled</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${enrollmentRows.length > 0 ? enrollmentRows : '<tr><td colspan="6" style="text-align:center; padding:2rem;">No enrollments yet for this program.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       PROGRAM FORM — Create / Edit with AI Image Generation
       ========================================================================= */
    renderProgramForm(program = null) {
        const isEdit = !!program;
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">${isEdit ? 'Edit Program' : 'New Community Program'}</h2>
                <div class="form-card reveal">
                    <form id="program-form" novalidate>
                        ${isEdit ? `<input type="hidden" name="id" value="${program.id}">` : ''}
                        <div class="form-group">
                            <label for="prog-title">Program Title</label>
                            <input type="text" id="prog-title" name="title" value="${isEdit ? program.title : ''}">
                        </div>
                        <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="prog-category">Category</label>
                                <select id="prog-category" name="category">
                                    <option value="Arts" ${isEdit && program.category === 'Arts' ? 'selected' : ''}>Arts</option>
                                    <option value="Sports" ${isEdit && program.category === 'Sports' ? 'selected' : ''}>Sports</option>
                                    <option value="Environment" ${isEdit && program.category === 'Environment' ? 'selected' : ''}>Environment</option>
                                    <option value="Education" ${isEdit && program.category === 'Education' ? 'selected' : ''}>Education</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="prog-capacity">Capacity</label>
                                <input type="number" id="prog-capacity" name="capacity" value="${isEdit ? program.capacity : '20'}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="prog-location">Location</label>
                            <input type="text" id="prog-location" name="location" value="${isEdit ? program.location : ''}">
                        </div>
                        <div class="form-group">
                            <label for="prog-desc">Description</label>
                            <textarea id="prog-desc" name="description" rows="4">${isEdit ? program.description : ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="prog-image">Program Image (Visual Identity)</label>
                            <div style="display: flex; gap: 1rem; align-items: center;">
                                <input type="file" id="prog-image" name="image" accept="image/*" style="flex: 1;">
                                <button type="button" class="btn" id="btn-generate-image" style="padding: 0.8rem 1.5rem; font-size: 0.9rem;">✨ GENERATE WITH AI</button>
                            </div>
                            <div id="prog-image-preview" style="margin-top: 1rem;">
                                ${isEdit && program.image && program.image !== 'default.jpg' ? `<img src="../assets/images/${program.image}" style="max-width: 200px; border: var(--border-main);" />` : ''}
                            </div>
                        </div>
                        ${isEdit ? `
                        <div class="form-group">
                            <label for="prog-status">Status</label>
                            <select id="prog-status" name="status">
                                <option value="active" ${program.status === 'active' ? 'selected' : ''}>Active</option>
                                <option value="cancelled" ${program.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                <option value="full" ${program.status === 'full' ? 'selected' : ''}>Full</option>
                            </select>
                        </div>
                        ` : ''}
                        <div style="display:flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">${isEdit ? 'UPDATE PROGRAM' : 'CREATE PROGRAM'}</button>
                            <button type="button" class="btn" style="flex:1;" onclick="window.location.hash='#manage-programs'">CANCEL</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    }
};

export default view;
