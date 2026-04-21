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
                    <li><a href="#transport-dashboard">transport</a></li>
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
                    <div class="editorial-card reveal">
                        <h3>Transport Network</h3>
                        <p>Manage fleet, update routes, handle digital boarding passes, and oversee city-wide transportation operations.</p>
                        <a href="#transport-dashboard" class="btn" style="align-self: flex-start; margin-top: auto;">Manage Transport</a>
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
    },

    /* =========================================================================
       TRANSPORT MANAGEMENT 
       ========================================================================= */
    renderTransportDashboard() {
        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                    <h2 class="reveal" style="margin:0; border:none; padding:0;">Transport Dashboard</h2>
                </div>
                <div class="editorial-grid">
                    <div class="editorial-card reveal" style="cursor:pointer;" onclick="window.location.hash='#transport-types'">
                        <h3>Transport Types</h3>
                        <p>Manage transport categories (e.g., Bus, Metro, Tram) and their display images.</p>
                        <a href="#transport-types" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Manage Types</a>
                    </div>
                    <div class="editorial-card reveal" style="cursor:pointer;" onclick="window.location.hash='#fleet'">
                        <h3>Fleet Management</h3>
                        <p>Register and manage individual vehicles, tracking their capacity and operational status.</p>
                        <a href="#fleet" class="btn" style="align-self: flex-start; margin-top: auto;">Manage Fleet</a>
                    </div>
                    <div class="editorial-card reveal" style="cursor:pointer;" onclick="window.location.hash='#routes'">
                        <h3>Routes & Schedules</h3>
                        <p>Plan routes, set departure times and pricing, and monitor active trajets.</p>
                        <a href="#routes" class="btn" style="align-self: flex-start; margin-top: auto;">Manage Routes</a>
                    </div>
                    <div class="editorial-card reveal" style="cursor:pointer;" onclick="window.location.hash='#admin-tickets'">
                        <h3>Ticketing</h3>
                        <p>Monitor citizen ticket purchases, view active boarding passes, and handle cancellations.</p>
                        <a href="#admin-tickets" class="btn" style="align-self: flex-start; margin-top: auto;">View Tickets</a>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderTransportTypes(types) {
        const rows = types.map(t => `
            <tr>
                <td><strong>#${t.idTransportType}</strong></td>
                <td>
                    <div style="display:flex; align-items:center; gap: 10px;">
                        ${t.photo_url ? `<img src="../assets/images/${t.photo_url}" style="width:40px; height:40px; border-radius:4px; object-fit:cover;">` : ''}
                        <span>${t.name}</span>
                    </div>
                </td>
                <td>${t.description}</td>
                <td>
                    <button class="btn btn-small btn-primary" data-action="edit-transport-type" data-id="${t.idTransportType}" style="margin-right:4px;">EDIT</button>
                    <button class="btn btn-small btn-danger" data-action="delete-transport-type" data-id="${t.idTransportType}">DELETE</button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#transport-dashboard" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Dashboard</a>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                    <h2 class="reveal" style="margin:0; border:none; padding:0;">Transport Types</h2>
                    <button class="btn btn-primary reveal" data-action="new-transport-type">+ NEW TYPE</button>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="4" style="text-align:center; padding:2rem;">No transport types found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderTransportTypeForm(type = null) {
        const isEdit = !!type;
        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#transport-types" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Types</a>
                </div>
                <h2 class="reveal">${isEdit ? 'Edit Transport Type' : 'New Transport Type'}</h2>
                <div class="form-card reveal">
                    <form id="transport-type-form">
                        ${isEdit ? `<input type="hidden" name="idTransportType" value="${type.idTransportType}">` : ''}
                        <div class="form-group">
                            <label for="name">Type Name (e.g. Bus)</label>
                            <input type="text" id="name" name="name" value="${isEdit ? type.name : ''}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="3" required>${isEdit ? type.description : ''}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="photo">Photo / Icon</label>
                            <input type="file" id="photo" name="image" accept="image/*" ${isEdit ? '' : 'required'}>
                            ${isEdit && type.photo_url ? `
                                <div style="margin-top:1rem;">
                                    <p style="font-size:0.8rem; margin-bottom:5px;">Current Image:</p>
                                    <img src="../assets/images/${type.photo_url}" style="height:100px; border-radius:4px; border: var(--border-main);">
                                </div>
                            ` : ''}
                        </div>
                        <div style="display:flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">${isEdit ? 'UPDATE TYPE' : 'CREATE TYPE'}</button>
                            <button type="button" class="btn" style="flex:1;" onclick="window.location.hash='#transport-types'">CANCEL</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
        
        if (window.Validator && window.Validator.attachLiveValidation) {
            window.Validator.attachLiveValidation(document.getElementById('transport-type-form'), [
                { field: '#name', validate: (el) => window.Validator.required(el.value, 'Name') },
                { field: '#description', validate: (el) => window.Validator.required(el.value, 'Description') }
            ]);
        }
    },

    renderFleet(transports) {
        const rows = transports.map(t => `
            <tr>
                <td><strong>#${t.idTransport}</strong></td>
                <td>${t.name}</td>
                <td><span class="category-badge">${t.typeName || t.type}</span></td>
                <td>${t.capacity} seats</td>
                <td><span class="status-badge status-${t.status.toLowerCase()}">${t.status}</span></td>
                <td>
                    <button class="btn btn-small btn-primary" data-action="edit-fleet" data-id="${t.idTransport}" style="margin-right:4px;">EDIT</button>
                    <button class="btn btn-small btn-danger" data-action="delete-fleet" data-id="${t.idTransport}">DELETE</button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#transport-dashboard" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Dashboard</a>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                    <h2 class="reveal" style="margin:0; border:none; padding:0;">Fleet Management</h2>
                    <button class="btn btn-primary reveal" data-action="new-fleet">+ NEW VEHICLE</button>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Vehicle Name</th>
                                    <th>Type</th>
                                    <th>Capacity</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="6" style="text-align:center; padding:2rem;">No vehicles found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderFleetForm(transport = null, types = []) {
        const isEdit = !!transport;
        
        const typeOptions = types.map(t => 
            `<option value="${t.idTransportType}" ${isEdit && transport.idTransportType == t.idTransportType ? 'selected' : ''}>${t.name}</option>`
        ).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#fleet" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Fleet</a>
                </div>
                <h2 class="reveal">${isEdit ? 'Edit Vehicle' : 'New Vehicle'}</h2>
                <div class="form-card reveal">
                    <form id="fleet-form">
                        ${isEdit ? `<input type="hidden" name="idTransport" value="${transport.idTransport}">` : ''}
                        <div class="form-group">
                            <label for="name">Vehicle Name (e.g. City Bus A)</label>
                            <input type="text" id="name" name="name" value="${isEdit ? transport.name : ''}" required>
                        </div>
                        <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="idTransportType">Transport Type</label>
                                <select id="idTransportType" name="idTransportType" required>
                                    <option value="">-- Select Type --</option>
                                    ${typeOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="capacity">Passenger Capacity</label>
                                <input type="number" id="capacity" name="capacity" value="${isEdit ? transport.capacity : '50'}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Active" ${isEdit && transport.status === 'Active' ? 'selected' : ''}>Active</option>
                                <option value="Maintenance" ${isEdit && transport.status === 'Maintenance' ? 'selected' : ''}>Maintenance</option>
                                <option value="Retired" ${isEdit && transport.status === 'Retired' ? 'selected' : ''}>Retired</option>
                            </select>
                        </div>
                        <div style="display:flex; gap: 1rem; margin-top: 1rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">${isEdit ? 'UPDATE VEHICLE' : 'REGISTER VEHICLE'}</button>
                            <button type="button" class="btn" style="flex:1;" onclick="window.location.hash='#fleet'">CANCEL</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderRoutes(trajets) {
        const rows = trajets.map(t => {
            const pct = t.capacity > 0 ? Math.round((t.sold / t.capacity) * 100) : 0;
            const depTime = new Date(t.departureTime);
            
            return `
            <tr>
                <td><strong>#${t.idTrajet}</strong></td>
                <td>
                    <div style="font-weight:700;">${t.departure} &rarr; ${t.destination}</div>
                    <div style="font-size:0.8rem; opacity:0.7;">${depTime.toLocaleString()}</div>
                </td>
                <td>
                    <div>${t.transportName} <span class="category-badge">${t.transportType}</span></div>
                </td>
                <td>$${parseFloat(t.price).toFixed(2)}</td>
                <td>
                    <div class="capacity-track" style="width:100px; height:8px; margin-bottom:4px; background:var(--bg-light); border-radius:4px; overflow:hidden;">
                        <div class="capacity-fill${pct >= 100 ? ' full' : ''}" style="width: ${Math.min(pct, 100)}%; height:100%; background:var(--primary-red);"></div>
                    </div>
                    <div style="font-size:0.8rem;">${t.sold} / ${t.capacity} sold (${pct}%)</div>
                </td>
                <td>
                    <button class="btn btn-small btn-primary" data-action="edit-route" data-id="${t.idTrajet}" style="margin-right:4px;">EDIT</button>
                    <button class="btn btn-small btn-danger" data-action="delete-route" data-id="${t.idTrajet}">DELETE</button>
                </td>
            </tr>
        `}).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#transport-dashboard" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Dashboard</a>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                    <h2 class="reveal" style="margin:0; border:none; padding:0;">Routes & Schedules</h2>
                    <button class="btn btn-primary reveal" data-action="new-route">+ NEW ROUTE</button>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Route Details</th>
                                    <th>Vehicle Assigned</th>
                                    <th>Price</th>
                                    <th>Occupancy</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="6" style="text-align:center; padding:2rem;">No routes found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderRouteForm(trajet = null, fleets = []) {
        const isEdit = !!trajet;
        
        const vehicleOptions = fleets.map(f => 
            `<option value="${f.idTransport}" ${isEdit && trajet.idTransport == f.idTransport ? 'selected' : ''}>${f.name} (${f.capacity} seats)</option>`
        ).join('');

        let defaultTime = '';
        if (isEdit && trajet.departureTime) {
            defaultTime = trajet.departureTime.replace(' ', 'T').substring(0, 16);
        }

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#routes" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Routes</a>
                </div>
                <h2 class="reveal">${isEdit ? 'Edit Route' : 'New Route'}</h2>
                <div class="form-card reveal">
                    <form id="route-form">
                        ${isEdit ? `<input type="hidden" name="idTrajet" value="${trajet.idTrajet}">` : ''}
                        
                        <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="departure">Departure</label>
                                <input type="text" id="departure" name="departure" value="${isEdit ? trajet.departure : ''}" required>
                            </div>
                            <div class="form-group">
                                <label for="destination">Destination</label>
                                <input type="text" id="destination" name="destination" value="${isEdit ? trajet.destination : ''}" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="idTransport">Assign Vehicle</label>
                            <select id="idTransport" name="idTransport" required>
                                <option value="">-- Select Active Vehicle --</option>
                                ${vehicleOptions}
                            </select>
                        </div>

                        <div class="form-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div class="form-group">
                                <label for="departureTime">Departure Time</label>
                                <input type="datetime-local" id="departureTime" name="departureTime" value="${defaultTime}" required>
                            </div>
                            <div class="form-group">
                                <label for="price">Ticket Price ($)</label>
                                <input type="number" id="price" name="price" step="0.01" value="${isEdit ? trajet.price : '0.00'}" required>
                            </div>
                        </div>

                        <div style="display:flex; gap: 1rem; margin-top: 1.5rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">${isEdit ? 'UPDATE ROUTE' : 'CREATE ROUTE'}</button>
                            <button type="button" class="btn" style="flex:1;" onclick="window.location.hash='#routes'">CANCEL</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderAdminTickets(tickets) {
        const rows = tickets.map(t => `
            <tr>
                <td><strong>${t.ref}</strong></td>
                <td>${t.citizenName}</td>
                <td><span class="category-badge" style="background:#eee;color:#333;">${t.typeName}</span> ${t.departure} &rarr; ${t.destination}</td>
                <td>${new Date(t.issuedAt).toLocaleString()}</td>
                <td>
                    ${t.status === 'Valid' 
                        ? '<span class="status-badge" style="background:var(--success);color:white;">Valid</span>'
                        : '<span class="status-badge" style="background:var(--primary-red);color:white;">Cancelled</span>'}
                </td>
                <td>
                    ${t.status === 'Valid' ? `<button class="btn btn-small btn-danger" data-action="cancel-ticket" data-id="${t.idTicket}">CANCEL</button>` : '-'}
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom: 2rem;">
                    <a href="#transport-dashboard" style="font-weight:800; text-transform:uppercase; text-decoration:none; color:var(--primary-navy); font-size:0.9rem; letter-spacing:1px;">&larr; Back to Dashboard</a>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                    <h2 class="reveal" style="margin:0; border:none; padding:0;">Ticketing Management</h2>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Passenger</th>
                                    <th>Route</th>
                                    <th>Issued</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="6" style="text-align:center; padding:2rem;">No tickets sold yet.</td></tr>'}
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

