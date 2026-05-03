/**
 * view.js
 * BackOffice rendering logic — CivicPortal Staff Portal
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
        const toast     = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    },

    renderNavBar(role, enrollmentCounts = null) {
        const nav        = document.querySelector('nav');
        const totalBadge = enrollmentCounts && enrollmentCounts.pending > 0
            ? `<span class="nav-dot">${enrollmentCounts.pending}</span>` : '';

        const agentLinks = `
            <li><a href="#worker-dashboard"><i class="bi bi-clipboard2-check"></i> Requests</a></li>
            <li><a href="#appointments"><i class="bi bi-calendar-check"></i> Appointments</a></li>
            <li><a href="#manage-programs"><i class="bi bi-tree"></i> Programs ${totalBadge}</a></li>
        `;

        const adminLinks = `
            <li><a href="#admin-stats"><i class="bi bi-bar-chart-line"></i> Statistics</a></li>
            <li><a href="#worker-dashboard"><i class="bi bi-inbox-fill"></i> Requests</a></li>
            <li><a href="#manage-programs"><i class="bi bi-tree"></i> Programs ${totalBadge}</a></li>
            <li><a href="#appointments"><i class="bi bi-calendar-check"></i> Appointments</a></li>
            <li><a href="#transport-management"><i class="bi bi-bus-front"></i> Transport</a></li>
            <li><a href="#slot-management"><i class="bi bi-clock-history"></i> Slots</a></li>
            <li><a href="#user-management"><i class="bi bi-people"></i> Users</a></li>
        `;

        nav.innerHTML = `
            <div class="nav-brand">
                <i class="bi bi-building"></i>
                <div>
                    CivicPortal
                    <span class="nav-brand-sub">STAFF PORTAL</span>
                </div>
            </div>
            <ul class="nav-links">
                <li><a href="#home"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                ${role === 'agent' ? agentLinks : ''}
                ${role === 'admin' ? adminLinks : ''}
                <li><a href="#profile"><i class="bi bi-person-circle"></i> Profile</a></li>
            </ul>
            <div class="user-controls">
                <div class="user-role-badge">${role}</div>
                <button class="bo-logout-btn"
                    onclick="fetch('../../Verification.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../FrontOffice/login.php')">
                    <i class="bi bi-box-arrow-left"></i> Logout
                </button>
            </div>
        `;

        setTimeout(() => {
            const hash = window.location.hash || '#home';
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.classList.toggle('active', hash.startsWith(link.getAttribute('href')));
            });
        }, 50);
    },

    renderHome(user) {
        const agentCards = `
            <div class="editorial-card editorial-highlight reveal">
                <h3>Service Request Queue</h3>
                <p>Process pending administrative filings. Validate or reject documents submitted by citizens.</p>
                <a href="#worker-dashboard" class="btn btn-primary" style="align-self:flex-start;margin-top:auto;">Open Dashboard</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Appointments</h3>
                <p>Manage citizen appointment requests. Confirm, reschedule, or complete scheduled service visits.</p>
                <a href="#appointments" class="btn" style="align-self:flex-start;margin-top:auto;">View Appointments</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Parks &amp; Recreation</h3>
                <p>View community programs and manage citizen enrollment requests.</p>
                <a href="#manage-programs" class="btn" style="align-self:flex-start;margin-top:auto;">View Programs</a>
            </div>
        `;

        const adminCards = `
            <div class="editorial-card editorial-highlight reveal">
                <h3>Platform Statistics</h3>
                <p>View real-time aggregated data across all civic modules to monitor system health.</p>
                <a href="#admin-stats" class="btn btn-primary" style="align-self:flex-start;margin-top:auto;">View Stats</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Service Requests</h3>
                <p>Review and process all citizen service filings. Validate or reject submitted requests.</p>
                <a href="#worker-dashboard" class="btn" style="align-self:flex-start;margin-top:auto;">View Requests</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Parks &amp; Recreation</h3>
                <p>Manage community programs, workshops, and facilities.</p>
                <a href="#manage-programs" class="btn" style="align-self:flex-start;margin-top:auto;">Manage Programs</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Appointments</h3>
                <p>Oversee all citizen appointment bookings across all service types and agents.</p>
                <a href="#appointments" class="btn" style="align-self:flex-start;margin-top:auto;">View Queue</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Transport</h3>
                <p>Manage vehicles, routes, and timetables. Monitor seat occupancy and ticket sales.</p>
                <a href="#transport-management" class="btn" style="align-self:flex-start;margin-top:auto;">Manage Transport</a>
            </div>
            <div class="editorial-card reveal">
                <h3>Slot Management</h3>
                <p>Configure agent availability windows. Define which agents handle which services and when.</p>
                <a href="#slot-management" class="btn" style="align-self:flex-start;margin-top:auto;">Manage Slots</a>
            </div>
            <div class="editorial-card reveal">
                <h3>User Management</h3>
                <p>Manage citizen and staff accounts. Change roles, activate, deactivate, or create new accounts.</p>
                <a href="#user-management" class="btn" style="align-self:flex-start;margin-top:auto;">Manage Users</a>
            </div>
        `;

        this.app.innerHTML = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>Staff Portal</h1>
                    <p>Welcome back, ${user.name}. Access your staff module.</p>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">${user.role === 'admin' ? 'Administrative Overview' : 'Operations Console'}</h2>
                <div class="editorial-grid">
                    ${user.role === 'admin' ? adminCards : agentCards}
                </div>
            </section>
        `;
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
                        <div class="form-group" style="display:flex;gap:1rem;">
                            <button type="submit" class="btn btn-primary">UPDATE DETAILS</button>
                            <a href="#home" class="btn" style="text-decoration:none;text-align:center;">CANCEL</a>
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
                <td>${r.title}</td>
                <td>${r.category || '—'}</td>
                <td>${r.created_at ? new Date(r.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                <td><span class="status-badge status-${r.status}">${r.status}</span></td>
                <td>
                    <button class="btn btn-small btn-success" data-action="validate" data-id="${r.id}" style="margin-right:4px;"><i class="bi bi-check-lg"></i> VALIDATE</button>
                    <button class="btn btn-small btn-danger" data-action="reject" data-id="${r.id}"><i class="bi bi-x-lg"></i> REJECT</button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Service Request Queue</h2>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Service Type</th>
                                    <th>Category</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${tableRows.length > 0 ? tableRows : '<tr><td colspan="6" style="text-align:center;padding:2rem;">No requests found.</td></tr>'}
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
                        <i class="bi bi-people-fill" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        <h3>Total Users</h3>
                        <p class="stats-number">${stats.usersCount ?? 0}</p>
                    </div>
                    <div class="editorial-card editorial-highlight reveal">
                        <i class="bi bi-file-earmark-arrow-up" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        <h3>Service Requests</h3>
                        <p class="stats-number">${stats.requestsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-calendar2-event" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        <h3>Active Programs</h3>
                        <p class="stats-number">${stats.programsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-person-check" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        <h3>Enrollments</h3>
                        <p class="stats-number">${stats.enrollmentsCount ?? 0}</p>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-calendar-check" style="font-size:2rem;margin-bottom:0.5rem;display:block;"></i>
                        <h3>Appointments</h3>
                        <p class="stats-number">${stats.appointmentsCount ?? 0}</p>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       USER MANAGEMENT (admin only)
       ========================================================================= */
    renderUserManagement(users) {
        const rows = users.map(u => {
            const isActive = u.is_active != 0;
            const joined   = u.created_at
                ? new Date(u.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';
            const avatar   = u.has_pic
                ? `<img src="../../get_image.php?type=profile&id=${u.id}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid var(--border-main);">`
                : `<div style="width:36px;height:36px;border-radius:50%;background:var(--primary-navy);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:0.85rem;">${u.username.charAt(0).toUpperCase()}</div>`;

            return `
                <tr data-uid="${u.id}">
                    <td>${avatar}</td>
                    <td>
                        <strong>${u.username}</strong><br>
                        <span style="font-size:0.82rem;opacity:0.65;">${u.email}</span>
                    </td>
                    <td>
                        <select class="role-select" style="padding:0.3rem 0.5rem;border:2px solid var(--border-main);font-weight:700;font-size:0.8rem;"
                                data-id="${u.id}" data-name="${u.username}" data-email="${u.email}">
                            <option value="citizen" ${u.role === 'citizen' ? 'selected' : ''}>Citizen</option>
                            <option value="agent"   ${u.role === 'agent'   ? 'selected' : ''}>Agent</option>
                            <option value="admin"   ${u.role === 'admin'   ? 'selected' : ''}>Admin</option>
                        </select>
                        <button class="btn btn-small btn-success" data-action="save-user-role"
                                data-id="${u.id}" data-name="${u.username}" data-email="${u.email}"
                                style="margin-left:4px;padding:0.3rem 0.6rem;">✓</button>
                    </td>
                    <td><span class="status-badge status-${isActive ? 'validated' : 'rejected'}">${isActive ? 'Active' : 'Inactive'}</span></td>
                    <td style="font-size:0.82rem;">${joined}</td>
                    <td style="white-space:nowrap;">
                        <button class="btn btn-small ${isActive ? 'btn-danger' : 'btn-success'}"
                                data-action="toggle-user-active" data-id="${u.id}" data-active="${isActive ? '0' : '1'}"
                                style="margin-right:4px;">${isActive ? 'DEACTIVATE' : 'ACTIVATE'}</button>
                        <button class="btn btn-small btn-danger"
                                data-action="delete-user" data-id="${u.id}" data-name="${u.username}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;border:none;padding:0;">User Management</h2>
                    <button class="btn btn-primary reveal" data-action="toggle-create-user">+ NEW USER</button>
                </div>

                <div id="create-user-panel" style="display:none;margin-bottom:2rem;">
                    <div class="form-card reveal">
                        <h3 style="margin:0 0 1.5rem;font-size:1rem;text-transform:uppercase;letter-spacing:1px;">Create New User</h3>
                        <form id="create-user-form">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Full Name</label>
                                    <input type="text" name="name" placeholder="e.g. John Doe" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" placeholder="user@example.com" required>
                                </div>
                                <div class="form-group">
                                    <label>Password</label>
                                    <input type="password" name="password" placeholder="Min. 8 characters" required>
                                </div>
                                <div class="form-group">
                                    <label>Role</label>
                                    <select name="role" required>
                                        <option value="citizen">Citizen</option>
                                        <option value="agent">Agent (Worker)</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                            </div>
                            <div style="display:flex;gap:1rem;margin-top:0.5rem;">
                                <button type="submit" class="btn btn-primary">CREATE USER</button>
                                <button type="button" class="btn" data-action="toggle-create-user">CANCEL</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name / Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="6" style="text-align:center;padding:2rem;">No users found.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       APPOINTMENT QUEUE (agents + admin)
       ========================================================================= */
    renderAppointmentQueue(appointments, role) {
        const rows = appointments.map(a => {
            const dateStr = a.preferred_date
                ? new Date(a.preferred_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                : '—';
            const time      = a.preferred_time ? a.preferred_time.substring(0, 5) : '—';
            const isPending = a.status === 'pending';
            const isConf    = a.status === 'confirmed';

            return `
                <tr>
                    <td><strong>#${a.id}</strong></td>
                    <td>${a.username || '—'}</td>
                    <td>${a.service_type}</td>
                    <td>${dateStr} ${time}</td>
                    <td><span class="status-badge status-${a.status}">${a.status}</span></td>
                    <td style="max-width:180px;white-space:normal;">${a.notes || '—'}</td>
                    <td>
                        ${isPending ? `
                            <button class="btn btn-small btn-success" data-action="confirm-appointment" data-id="${a.id}" style="margin-right:4px;">CONFIRM</button>
                            <button class="btn btn-small btn-danger"  data-action="cancel-appointment"  data-id="${a.id}">CANCEL</button>
                        ` : ''}
                        ${isConf ? `
                            <button class="btn btn-small btn-success" data-action="complete-appointment" data-id="${a.id}">COMPLETE</button>
                        ` : ''}
                        ${!isPending && !isConf ? '<span style="opacity:0.5;font-size:0.85rem;">—</span>' : ''}
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Appointment Queue</h2>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th>
                                    <th>Citizen</th>
                                    <th>Service</th>
                                    <th>Date &amp; Time</th>
                                    <th>Status</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="7" style="text-align:center;padding:2rem;">No appointments found.</td></tr>'}
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
            const pending  = parseInt(p.pending_count   || 0);
            const confirmed= parseInt(p.confirmed_count || 0);
            const cap      = parseInt(p.capacity || 1);
            const fillPct  = Math.min(Math.round((enrolled / cap) * 100), 100);
            const isFull   = enrolled >= cap;

            return `
                <div class="program-mgmt-card reveal" data-action="view-program" data-id="${p.id}" style="cursor:pointer;">
                    <div class="program-mgmt-header">
                        <div class="program-mgmt-img" style="background-image:url('../../get_image.php?type=program&id=${p.id}');"></div>
                        ${enrolled > 0 ? `<span class="program-dot${pending > 0 ? ' has-pending' : ''}">${enrolled}</span>` : ''}
                    </div>
                    <div class="program-mgmt-body">
                        <span class="category-badge">${p.category}</span>
                        <h3 style="margin:0.5rem 0;">${p.title}</h3>
                        <p style="font-size:0.95rem;flex-grow:1;margin-bottom:1rem;">${p.description.substring(0, 80)}${p.description.length > 80 ? '...' : ''}</p>
                        <div style="display:flex;gap:0.5rem;align-items:center;font-size:0.85rem;margin-bottom:0.5rem;">
                            <span style="font-weight:800;"><i class="bi bi-geo-alt-fill"></i> ${p.location}</span>
                            <span style="margin-left:auto;font-weight:800;">${enrolled}/${cap} enrolled</span>
                        </div>
                        <div class="capacity-track">
                            <div class="capacity-fill${isFull ? ' full' : ''}" style="width:${fillPct}%;"></div>
                        </div>
                        <div style="display:flex;gap:0.5rem;margin-top:1rem;font-size:0.8rem;">
                            ${pending   > 0 ? `<span class="mini-stat pending">${pending} pending</span>` : ''}
                            ${confirmed > 0 ? `<span class="mini-stat confirmed">${confirmed} confirmed</span>` : ''}
                            ${isFull ? '<span class="mini-stat full">FULL</span>' : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;border:none;padding:0;">Parks &amp; Recreation</h2>
                    <div style="display:flex;gap:1rem;align-items:center;">
                        <span class="reveal" style="font-weight:800;font-size:0.9rem;text-transform:uppercase;letter-spacing:1px;">${totalEnrollments} total enrollments</span>
                        ${role === 'admin' ? '<button class="btn reveal" data-action="manage-categories" style="border:2px solid var(--primary-navy);"><i class="bi bi-tags"></i> CATEGORIES</button>' : ''}
                        ${role === 'admin' ? '<button class="btn btn-primary reveal" data-action="new-program">+ NEW PROGRAM</button>' : ''}
                    </div>
                </div>
                <div class="programs-mgmt-grid">
                    ${programCards.length > 0 ? programCards : '<p class="reveal" style="text-align:center;padding:3rem;">No programs found.</p>'}
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
        const pending  = parseInt(program.pending_count   || 0);
        const confirmed= parseInt(program.confirmed_count || 0);
        const cap      = parseInt(program.capacity || 1);
        const fillPct  = Math.min(Math.round((enrolled / cap) * 100), 100);

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
                            <button class="btn btn-small btn-danger"  data-action="cancel-enroll"  data-id="${e.id}" data-program-id="${program.id}">REJECT</button>
                        ` : `<span style="font-size:0.85rem;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;opacity:0.6;">—</span>`}
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom:2rem;">
                    <a href="#manage-programs" style="font-weight:800;text-transform:uppercase;text-decoration:none;color:var(--primary-navy);font-size:0.9rem;letter-spacing:1px;"><i class="bi bi-arrow-left"></i> Back to Programs</a>
                </div>

                <div class="program-detail-hero reveal">
                    <div class="program-detail-img" style="background-image:url('../../get_image.php?type=program&id=${program.id}');"></div>
                    <div class="program-detail-info">
                        <span class="category-badge" style="margin-bottom:1rem;">${program.category}</span>
                        <h2 style="border:none;padding:0;margin:0 0 0.5rem 0;">${program.title}</h2>
                        <p style="margin-bottom:1.5rem;">${program.description}</p>
                        <div style="display:flex;gap:2rem;flex-wrap:wrap;margin-bottom:1.5rem;">
                            <div><span style="font-weight:900;text-transform:uppercase;font-size:0.8rem;letter-spacing:1px;display:block;margin-bottom:4px;">Location</span>${program.location}</div>
                            <div><span style="font-weight:900;text-transform:uppercase;font-size:0.8rem;letter-spacing:1px;display:block;margin-bottom:4px;">Capacity</span>${cap}</div>
                            <div><span style="font-weight:900;text-transform:uppercase;font-size:0.8rem;letter-spacing:1px;display:block;margin-bottom:4px;">Status</span><span class="status-badge status-${program.status}">${program.status}</span></div>
                        </div>
                        <div style="display:flex;gap:2rem;flex-wrap:wrap;margin-bottom:1rem;">
                            <div class="detail-stat"><span class="detail-stat-number">${enrolled}</span><span class="detail-stat-label">ENROLLED</span></div>
                            <div class="detail-stat"><span class="detail-stat-number" style="color:var(--accent-blue);">${pending}</span><span class="detail-stat-label">PENDING</span></div>
                            <div class="detail-stat"><span class="detail-stat-number" style="color:var(--success);">${confirmed}</span><span class="detail-stat-label">CONFIRMED</span></div>
                        </div>
                        <div class="capacity-track" style="height:16px;">
                            <div class="capacity-fill${fillPct >= 100 ? ' full' : ''}" style="width:${fillPct}%;"></div>
                        </div>
                        <p style="font-size:0.8rem;font-weight:700;margin-top:0.5rem;">${fillPct}% capacity filled</p>
                        ${role === 'admin' ? `
                            <div style="display:flex;gap:0.5rem;margin-top:1.5rem;">
                                <button class="btn btn-small btn-primary" data-action="edit-program" data-id="${program.id}"><i class="bi bi-pencil-square"></i> EDIT PROGRAM</button>
                                <button class="btn btn-small btn-danger"  data-action="delete-program" data-id="${program.id}"><i class="bi bi-trash3"></i> DELETE</button>
                            </div>
                        ` : ''}
                    </div>
                </div>

                <div style="margin-top:3rem;">
                    <h2 class="reveal" style="font-size:clamp(1.5rem,3vw,2.5rem);">Enrollment Roster <span style="font-weight:400;font-size:0.7em;">(${enrollments.length})</span></h2>
                    <div class="reveal">
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th><th>Citizen</th><th>Email</th><th>Enrolled</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${enrollmentRows.length > 0 ? enrollmentRows : '<tr><td colspan="6" style="text-align:center;padding:2rem;">No enrollments yet.</td></tr>'}
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
    renderProgramForm(program = null, categories = []) {
        const isEdit = !!program;
        const categoryOptions = categories.map(c =>
            `<option value="${c.name}" ${isEdit && program.category === c.name ? 'selected' : ''}>${c.name}</option>`
        ).join('');

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
                        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label for="prog-category">Category</label>
                                <select id="prog-category" name="category">
                                    <option value="" disabled ${!isEdit ? 'selected' : ''}>Select a category</option>
                                    ${categoryOptions}
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
                            <label for="prog-image">Program Image</label>
                            <div style="display:flex;gap:1rem;align-items:center;">
                                <input type="file" id="prog-image" name="image" accept="image/*" style="flex:1;">
                                <button type="button" class="btn" id="btn-generate-image" style="padding:0.8rem 1.5rem;font-size:0.9rem;"><i class="bi bi-stars"></i> GENERATE WITH AI</button>
                            </div>
                            <div id="prog-image-preview" style="margin-top:1rem;">
                                ${isEdit ? `<img src="../../get_image.php?type=program&id=${program.id}" style="max-width:200px;border:var(--border-main);" onerror="this.style.display='none'">` : ''}
                            </div>
                        </div>
                        ${isEdit ? `
                        <div class="form-group">
                            <label for="prog-status">Status</label>
                            <select id="prog-status" name="status">
                                <option value="active"     ${program.status === 'active'     ? 'selected' : ''}>Active</option>
                                <option value="cancelled"  ${program.status === 'cancelled'  ? 'selected' : ''}>Cancelled</option>
                                <option value="full"       ${program.status === 'full'       ? 'selected' : ''}>Full</option>
                            </select>
                        </div>
                        ` : ''}
                        <div style="display:flex;gap:1rem;margin-top:1rem;">
                            <button type="submit" class="btn btn-primary" style="flex:1;">${isEdit ? 'UPDATE PROGRAM' : 'CREATE PROGRAM'}</button>
                            <a href="#manage-programs" class="btn" style="flex:1;text-decoration:none;text-align:center;display:flex;align-items:center;justify-content:center;">CANCEL</a>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    /* =========================================================================
       CATEGORY MANAGER — Admin only
       ========================================================================= */
    /* =========================================================================
       TRANSPORT MANAGEMENT (admin only)
       ========================================================================= */
    renderTransportManagement({ types, vehicles, trajets }) {
        // ── Transport Type cards ──────────────────────────────────────────────
        const typeCards = types.map(t => `
            <div style="display:flex;align-items:center;gap:1rem;padding:1rem;border:var(--border-main);background:var(--white);">
                <img src="../../get_image.php?type=transport_type&id=${t.idTransportType}"
                     style="width:64px;height:48px;object-fit:cover;border:var(--border-main);flex-shrink:0;"
                     onerror="this.style.display='none'">
                <div style="flex:1;min-width:0;">
                    <strong style="display:block;font-size:0.95rem;">${t.name}</strong>
                    ${t.description ? `<span style="font-size:0.82rem;opacity:0.65;">${t.description}</span>` : ''}
                </div>
                <div style="display:flex;gap:8px;">
                    <button class="btn btn-small" data-action="edit-transport-type" data-id="${t.idTransportType}">
                        <i class="bi bi-pencil-square"></i> EDIT
                    </button>
                    <button class="btn btn-small btn-danger" data-action="delete-transport-type" data-id="${t.idTransportType}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
        `).join('');

        const typeOptions = types.map(t =>
            `<option value="${t.idTransportType}">${t.name}</option>`
        ).join('');

        const vehicleOptions = vehicles.map(v =>
            `<option value="${v.idTransport}">${v.name} (${v.type})</option>`
        ).join('');

        const vehicleRows = vehicles.map(v => `
            <tr>
                <td>
                    <img src="../../get_image.php?type=transport&id=${v.idTransportType || 0}"
                         style="width:48px;height:32px;object-fit:cover;border:var(--border-main);"
                         onerror="this.style.display='none'">
                </td>
                <td><strong>${v.name}</strong></td>
                <td>${v.typeName || v.type}</td>
                <td>${v.capacity}</td>
                <td><span class="status-badge status-${v.status === 'Active' ? 'validated' : 'rejected'}">${v.status}</span></td>
                <td>
                    <button class="btn btn-small" data-action="edit-vehicle" data-id="${v.idTransport}" style="margin-right:4px;">
                        <i class="bi bi-pencil-square"></i> EDIT
                    </button>
                    <button class="btn btn-small btn-danger" data-action="delete-vehicle" data-id="${v.idTransport}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </td>
            </tr>
        `).join('');

        const trajetRows = trajets.map(t => {
            const pct  = t.capacity > 0 ? Math.round((t.sold / t.capacity) * 100) : 0;
            const full = t.sold >= t.capacity && t.capacity > 0;
            return `
                <tr>
                    <td><strong>#${t.idTrajet}</strong></td>
                    <td>${t.departure}</td>
                    <td>${t.destination}</td>
                    <td>${t.departureTime ? t.departureTime.substring(0,5) : '—'}</td>
                    <td>${Number(t.price).toFixed(2)}</td>
                    <td>${t.transportName || '—'}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:0.5rem;">
                            <div style="flex:1;height:6px;background:var(--border-main);border-radius:3px;min-width:60px;">
                                <div style="width:${pct}%;height:100%;background:${full ? 'var(--danger)' : 'var(--success)'};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:0.8rem;font-weight:700;">${t.sold}/${t.capacity}</span>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-small" data-action="edit-trajet" data-id="${t.idTrajet}" style="margin-right:4px;">
                            <i class="bi bi-pencil-square"></i> EDIT
                        </button>
                        <button class="btn btn-small btn-danger" data-action="delete-trajet" data-id="${t.idTrajet}">
                            <i class="bi bi-trash3"></i>
                        </button>
                    </td>
                </tr>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Transport Management</h2>

                <!-- TRANSPORT TYPES -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin:0 0 1rem;flex-wrap:wrap;gap:0.5rem;">
                    <h3 class="reveal" style="margin:0;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="bi bi-tag"></i> Transport Types — ${types.length}
                    </h3>
                    <button class="btn reveal" data-action="toggle-add-type" style="border:2px solid var(--primary-navy);">+ ADD TYPE</button>
                </div>

                <div id="add-type-panel" style="display:none;margin-bottom:1.5rem;">
                    <div class="form-card">
                        <form id="add-type-form" enctype="multipart/form-data">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Type Name</label>
                                    <input type="text" name="name" placeholder="e.g. Bus, Train, Metro">
                                </div>
                                <div class="form-group">
                                    <label>Description</label>
                                    <input type="text" name="description" placeholder="Optional description">
                                </div>
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label>Icon / Photo (optional, max 2MB)</label>
                                    <input type="file" name="type_image" accept="image/*">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">ADD TYPE</button>
                        </form>
                    </div>
                </div>

                <div class="reveal" style="display:flex;flex-direction:column;gap:0.5rem;margin-bottom:3rem;">
                    ${typeCards || '<p style="opacity:0.6;padding:1rem 0;">No transport types yet.</p>'}
                </div>

                <!-- VEHICLES -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin:2rem 0 1rem;flex-wrap:wrap;gap:0.5rem;">
                    <h3 class="reveal" style="margin:0;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="bi bi-truck"></i> Fleet — ${vehicles.length} vehicle${vehicles.length !== 1 ? 's' : ''}
                    </h3>
                    <button class="btn reveal" data-action="toggle-add-vehicle" style="border:2px solid var(--primary-navy);">+ ADD VEHICLE</button>
                </div>

                <div id="add-vehicle-panel" style="display:none;margin-bottom:1.5rem;">
                    <div class="form-card">
                        <form id="add-vehicle-form">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Vehicle Name</label>
                                    <input type="text" name="name" placeholder="e.g. City Express 01">
                                </div>
                                <div class="form-group">
                                    <label>Type Label</label>
                                    <input type="text" name="type" placeholder="Bus / Train / Metro">
                                </div>
                                <div class="form-group">
                                    <label>Capacity</label>
                                    <input type="number" name="capacity" min="1" placeholder="50">
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status">
                                        <option value="Active">Active</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Transport Type</label>
                                    <select name="idTransportType">
                                        <option value="">— None —</option>
                                        ${typeOptions}
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">ADD VEHICLE</button>
                        </form>
                    </div>
                </div>

                <div class="reveal" style="margin-bottom:3rem;">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Photo</th><th>Name</th><th>Type</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
                            <tbody>${vehicleRows || '<tr><td colspan="6" style="text-align:center;padding:2rem;">No vehicles.</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>

                <!-- ROUTES -->
                <div style="display:flex;justify-content:space-between;align-items:center;margin:0 0 1rem;flex-wrap:wrap;gap:0.5rem;">
                    <h3 class="reveal" style="margin:0;font-size:1.2rem;text-transform:uppercase;letter-spacing:1px;">
                        <i class="bi bi-signpost-split"></i> Routes — ${trajets.length} route${trajets.length !== 1 ? 's' : ''}
                    </h3>
                    <button class="btn reveal" data-action="toggle-add-trajet" style="border:2px solid var(--primary-navy);">+ ADD ROUTE</button>
                </div>

                <div id="add-trajet-panel" style="display:none;margin-bottom:1.5rem;">
                    <div class="form-card">
                        <form id="add-trajet-form">
                            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                                <div class="form-group">
                                    <label>Departure</label>
                                    <input type="text" name="departure" placeholder="Search a location..." autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label>Destination</label>
                                    <input type="text" name="destination" placeholder="Search a location..." autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label>Departure Date</label>
                                    <input type="date" name="departureDate" value="${new Date().toISOString().split('T')[0]}">
                                </div>
                                <div class="form-group">
                                    <label>Departure Time</label>
                                    <input type="time" name="departureTime">
                                </div>
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" name="price" min="0" step="0.01" placeholder="9.99">
                                </div>
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label>Vehicle</label>
                                    <select name="idTransport">
                                        <option value="" disabled selected>Select vehicle</option>
                                        ${vehicleOptions}
                                    </select>
                                </div>
                                <div class="form-group" style="grid-column:1/-1;">
                                    <label>Route Map (Click or drag markers)</label>
                                    <div id="route-map" style="height:300px; border:2px solid var(--border-main); border-radius:8px; z-index: 1;"></div>
                                    <input type="hidden" name="depLat" id="depLat">
                                    <input type="hidden" name="depLng" id="depLng">
                                    <input type="hidden" name="destLat" id="destLat">
                                    <input type="hidden" name="destLng" id="destLng">
                                    <input type="hidden" name="depAddress" id="depAddress">
                                    <input type="hidden" name="destAddress" id="destAddress">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">ADD ROUTE</button>
                        </form>
                    </div>
                </div>

                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead><tr><th>Ref</th><th>From</th><th>To</th><th>Time</th><th>Price</th><th>Vehicle</th><th>Occupancy</th><th>Actions</th></tr></thead>
                            <tbody>${trajetRows || '<tr><td colspan="8" style="text-align:center;padding:2rem;">No routes.</td></tr>'}</tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        // Setup form validations for dynamically added forms
        if (window.setupTransportValidations) {
            setTimeout(() => window.setupTransportValidations(), 100);
        }
        this.triggerObserver();
    },

    /* =========================================================================
       SLOT MANAGEMENT — Admin only (agent availability configuration)
       ========================================================================= */
    renderSlotManagement(slots, agents, serviceTypes) {
        const DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

        const agentOptions = agents.map(a =>
            `<option value="${a.id}">${a.username}</option>`
        ).join('');

        const serviceOptions = serviceTypes.map(s =>
            `<option value="${s}">${s}</option>`
        ).join('');

        const dayOptions = DAYS.map((d, i) =>
            `<option value="${i}">${d}</option>`
        ).join('');

        const rows = slots.map(s => `
            <tr>
                <td><strong>#${s.id}</strong></td>
                <td>${s.agent_name || '—'}</td>
                <td>${s.service_type}</td>
                <td>${DAYS[s.day_of_week] ?? s.day_of_week}</td>
                <td>${s.start_time.substring(0,5)} – ${s.end_time.substring(0,5)}</td>
                <td><span class="status-badge status-${s.is_active ? 'validated' : 'rejected'}">${s.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>
                    <button class="btn btn-small btn-danger" data-action="delete-slot" data-id="${s.id}"><i class="bi bi-trash3"></i></button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Slot Management</h2>
                <p class="reveal" style="margin-bottom:2rem;opacity:0.7;">Define when each agent is available to handle specific service types. Citizens can only book appointments during configured slots.</p>

                <div class="form-card reveal" style="margin-bottom:2rem;">
                    <h3 style="margin:0 0 1.5rem;font-size:1.1rem;text-transform:uppercase;letter-spacing:1px;">Add New Slot</h3>
                    <form id="slot-form">
                        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label for="slot-agent">Agent</label>
                                <select id="slot-agent" name="agent_id" required>
                                    <option value="" disabled selected>Select agent</option>
                                    ${agentOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="slot-service">Service Type</label>
                                <select id="slot-service" name="service_type" required>
                                    <option value="" disabled selected>Select service</option>
                                    ${serviceOptions}
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="slot-day">Day of Week</label>
                                <select id="slot-day" name="day_of_week" required>
                                    <option value="" disabled selected>Select day</option>
                                    ${dayOptions}
                                </select>
                            </div>
                            <div class="form-group" style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;align-items:end;">
                                <div>
                                    <label for="slot-start">Start Time</label>
                                    <input type="time" id="slot-start" name="start_time" required>
                                </div>
                                <div>
                                    <label for="slot-end">End Time</label>
                                    <input type="time" id="slot-end" name="end_time" required>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;">+ ADD SLOT</button>
                    </form>
                </div>

                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th><th>Agent</th><th>Service</th><th>Day</th><th>Hours</th><th>Status</th><th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="7" style="text-align:center;padding:2rem;">No slots configured yet.</td></tr>'}
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderCategoryManager(categories) {
        const rows = categories.map(c => `
            <tr>
                <td><strong>#${c.id}</strong></td>
                <td>
                    <span class="category-display-name" data-id="${c.id}">${c.name}</span>
                    <input type="text" class="category-edit-input" data-id="${c.id}" value="${c.name}" style="display:none;width:100%;padding:0.5rem;border:2px solid var(--primary-navy);font-weight:700;">
                </td>
                <td>${c.created_at ? new Date(c.created_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '—'}</td>
                <td>
                    <button class="btn btn-small" data-action="edit-category" data-id="${c.id}" style="margin-right:4px;"><i class="bi bi-pencil-square"></i> EDIT</button>
                    <button class="btn btn-small btn-success" data-action="save-category" data-id="${c.id}" style="display:none;margin-right:4px;"><i class="bi bi-check-lg"></i> SAVE</button>
                    <button class="btn btn-small btn-secondary" data-action="cancel-edit-category" data-id="${c.id}" style="display:none;margin-right:4px;">CANCEL</button>
                    <button class="btn btn-small btn-danger" data-action="delete-category" data-id="${c.id}"><i class="bi bi-trash3"></i></button>
                </td>
            </tr>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="margin-bottom:2rem;">
                    <a href="#manage-programs" style="font-weight:800;text-transform:uppercase;text-decoration:none;color:var(--primary-navy);font-size:0.9rem;letter-spacing:1px;"><i class="bi bi-arrow-left"></i> Back to Programs</a>
                </div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;border:none;padding:0;">Manage Categories</h2>
                </div>
                <div class="form-card reveal" style="margin-bottom:2rem;">
                    <form id="category-form" style="display:flex;gap:1rem;align-items:flex-end;">
                        <div class="form-group" style="flex:1;margin:0;">
                            <label for="new-category-name">New Category Name</label>
                            <input type="text" id="new-category-name" name="name" placeholder="e.g. Health &amp; Wellness" required>
                        </div>
                        <button type="submit" class="btn btn-primary" style="height:fit-content;padding:0.85rem 2rem;">+ ADD</button>
                    </form>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr><th>ID</th><th>Name</th><th>Created</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                ${rows.length > 0 ? rows : '<tr><td colspan="4" style="text-align:center;padding:2rem;">No categories yet.</td></tr>'}
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
