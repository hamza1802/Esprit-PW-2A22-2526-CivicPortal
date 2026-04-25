/**
 * view.js — FrontOffice rendering logic.
 * Modules: Home, Programs, Service Requests, Appointments,
 *          Transport, My Tickets, Profile, Notifications.
 * Complaints/Grievances module REMOVED.
 */

const view = {
    app: document.getElementById('app'),

    triggerObserver() {
        if (window.initScrollObserver) setTimeout(() => window.initScrollObserver(), 50);
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
        }, 3500);
    },

    // -------------------------------------------------------------------------
    // Navigation
    // -------------------------------------------------------------------------
    renderNavBar(user) {
        const nav = document.querySelector('nav');

        const roleBadge = user?.role === 'admin'
            ? `<a href="../BackOffice/index.php" class="user-role-badge" style="text-decoration:none;">Admin Panel</a>`
            : `<div class="user-role-badge">Citizen</div>`;

        nav.innerHTML = `
            <div class="nav-brand"><i class="bi bi-building"></i> CivicPortal</div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#programs">programs</a></li>
                <li><a href="forum.php">forum</a></li>
                <li><a href="#request-service">requests</a></li>
                <li><a href="#appointments">appointments</a></li>
                <li><a href="#transport">transport</a></li>
                <li><a href="#my-tickets">my tickets</a></li>
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls" style="display:flex;align-items:center;gap:1rem;">
                ${roleBadge}
                <a href="#"
                   onclick="event.preventDefault();fetch('../../Verification.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='login.php')"
                   class="logout-link"
                   style="color:var(--danger);font-weight:600;text-decoration:none;font-family:var(--font-primary);font-size:0.9rem;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        `;
    },

    // -------------------------------------------------------------------------
    // Home
    // -------------------------------------------------------------------------
    renderHome(user) {
        this.app.innerHTML = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>CivicPortal</h1>
                    <p>Welcome back, ${user.name}. Navigate municipal services with clarity and precision.</p>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">Directory of Services</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <i class="bi bi-file-earmark-text" style="font-size:2rem;margin-bottom:1rem;display:block;"></i>
                        <h3>Service Requests</h3>
                        <p>Submit administrative documents, permits, and service requests. Track status in real-time.</p>
                        <a href="#request-service" class="btn btn-primary" style="align-self:flex-start;margin-top:auto;">File a Request</a>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-calendar2-check" style="font-size:2rem;margin-bottom:1rem;display:block;"></i>
                        <h3>Book Appointment</h3>
                        <p>Schedule a meeting with a municipal agent for document processing or inquiries.</p>
                        <a href="#appointments" class="btn" style="align-self:flex-start;margin-top:auto;">Book Now</a>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-people" style="font-size:2rem;margin-bottom:1rem;display:block;"></i>
                        <h3>Community Programs</h3>
                        <p>Engage with local initiatives. Browse the Parks &amp; Recreation activity catalog.</p>
                        <a href="#programs" class="btn" style="align-self:flex-start;margin-top:auto;">View Catalog</a>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-bus-front" style="font-size:2rem;margin-bottom:1rem;display:block;"></i>
                        <h3>Municipal Transport</h3>
                        <p>Book tickets for buses, trains, and other city transport. View live routes.</p>
                        <a href="#transport" class="btn" style="align-self:flex-start;margin-top:auto;">Browse Routes</a>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Programs
    // -------------------------------------------------------------------------
    renderProgramCatalog(programs, userEnrollments) {
        const programCards = programs.map((p) => {
            const isEnrolled = userEnrollments.some(e => e.program_id == p.id);
            // Use BLOB endpoint; fall back to grey placeholder on error
            const imgSrc = `../../get_image.php?type=program&id=${p.id}&t=${p.id}`;
            return `
                <div class="program-card reveal">
                    <div class="program-img-wrapper">
                        <img src="${imgSrc}" alt="${p.title}" class="program-img"
                             onerror="this.style.display='none';this.parentElement.style.background='var(--primary-navy)';">
                    </div>
                    <div class="card-content">
                        <span class="category-badge">${p.category}</span>
                        <h3>${p.title}</h3>
                        <p class="description-clamp" onclick="this.classList.toggle('expanded')"
                           title="Click to expand">${p.description}</p>
                        <div style="margin-top:auto;padding-bottom:5%;">
                            <button class="btn ${isEnrolled ? 'btn-success' : 'btn-primary'}"
                                    style="width:100%"
                                    data-id="${p.id}"
                                    data-action="enroll"
                                    ${isEnrolled ? 'disabled' : ''}>
                                ${isEnrolled ? 'ENROLLED' : 'ENROLL'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;">
                    <h2 class="reveal" style="margin:0;">Programs Catalog</h2>
                    <div class="filter-controls reveal" style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <input type="text" id="prog-search" placeholder="Search by title..."
                               style="flex-grow:1;padding:1rem;border:var(--border-main);background:transparent;
                                      font-family:inherit;font-size:1.1rem;font-weight:600;
                                      color:var(--primary-navy);outline:none;">
                        <select id="prog-filter-cat"
                                style="padding:1rem 2rem 1rem 1rem;border:var(--border-main);background:transparent;
                                       font-family:inherit;font-size:1.1rem;font-weight:600;
                                       color:var(--primary-navy);outline:none;cursor:pointer;">
                            <option value="">All Categories</option>
                            <option value="Arts">Arts</option>
                            <option value="Sports">Sports</option>
                            <option value="Environment">Environment</option>
                        </select>
                    </div>
                </div>
                <div class="editorial-grid" id="program-list">
                    ${programCards || '<div class="editorial-card" style="grid-column:1/-1;text-align:center;"><p>No programs available.</p></div>'}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Service Requests — Form + My Requests list
    // -------------------------------------------------------------------------
    renderServiceRequestForm(serviceTypes = []) {
        const typeOptions = serviceTypes.length > 0
            ? serviceTypes.map(t => `<option value="${t.value}">${t.label}</option>`).join('')
            : `<option value="Birth Certificate">Birth Certificate</option>
               <option value="ID Card Renewal">ID Card Renewal</option>
               <option value="Residence Certificate">Residence Certificate</option>
               <option value="Building Permit">Building Permit</option>`;

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;">File a Service Request</h2>
                    <a href="#my-requests" class="btn reveal" style="text-decoration:none;">
                        <i class="bi bi-clock-history"></i> My Requests
                    </a>
                </div>
                <div class="form-card reveal">
                    <form id="service-request-form" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="req-type">Service Type</label>
                            <select id="req-type" name="type" required>${typeOptions}</select>
                        </div>
                        <div class="form-group">
                            <label for="req-description">Description / Details</label>
                            <textarea id="req-description" name="description" rows="4"
                                      placeholder="Describe your request in detail..."></textarea>
                        </div>
                        <div class="form-group">
                            <label for="req-attachment">
                                <i class="bi bi-paperclip"></i> Supporting Document (PDF / Image, max 2MB)
                            </label>
                            <input type="file" id="req-attachment" name="attachment"
                                   accept=".pdf,image/*"
                                   style="border:none;padding:0.5rem 0;">
                        </div>
                        <button type="submit" class="btn btn-primary reveal" style="width:100%;">
                            <i class="bi bi-send"></i> SUBMIT REQUEST
                        </button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderMyRequests(requests = []) {
        const statusColor = { pending: '#f59e0b', in_progress: '#6366f1', approved: '#10b981', rejected: '#ef4444', validated: '#10b981', resolved: '#6366f1' };

        const rows = requests.length === 0
            ? `<tr><td colspan="5" style="text-align:center;padding:2rem;color:var(--text-dark);opacity:0.6;">
                   No requests found. <a href="#request-service" style="color:var(--accent-blue);">File one?</a>
               </td></tr>`
            : requests.map(r => `
                <tr>
                    <td><strong>#${r.id}</strong></td>
                    <td>${r.title || r.category || '—'}</td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${r.description || ''}">${r.description || '—'}</td>
                    <td>${r.created_at ? new Date(r.created_at).toLocaleDateString() : '—'}</td>
                    <td>
                        <span style="display:inline-block;padding:3px 10px;border-radius:99px;font-size:0.75rem;font-weight:800;
                                     background:${(statusColor[r.status] || '#6b7280')}22;
                                     color:${statusColor[r.status] || '#6b7280'};">
                            ${r.status?.toUpperCase() || 'PENDING'}
                        </span>
                    </td>
                </tr>
            `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;">My Requests</h2>
                    <a href="#request-service" class="btn btn-primary reveal" style="text-decoration:none;">
                        + New Request
                    </a>
                </div>
                <div class="reveal">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Ref</th><th>Service</th><th>Details</th><th>Date</th><th>Status</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Appointments
    // -------------------------------------------------------------------------
    renderAppointmentForm(serviceTypes = []) {
        const typeOptions = serviceTypes.length > 0
            ? serviceTypes.map(t => `<option value="${t.value}">${t.label}</option>`).join('')
            : `<option value="Birth Certificate">Birth Certificate</option>
               <option value="ID Card Renewal">ID Card Renewal</option>
               <option value="Residence Certificate">Residence Certificate</option>
               <option value="Building Permit">Building Permit</option>
               <option value="General Inquiry">General Inquiry</option>
               <option value="Document Verification">Document Verification</option>`;

        const today = new Date().toISOString().split('T')[0];

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;">Book an Appointment</h2>
                    <a href="#my-appointments" class="btn reveal" style="text-decoration:none;">
                        <i class="bi bi-calendar2-week"></i> My Appointments
                    </a>
                </div>
                <div class="form-card reveal">
                    <form id="appointment-form">
                        <div class="form-group">
                            <label for="appt-type">Service Type</label>
                            <select id="appt-type" name="service_type" required>
                                ${typeOptions}
                            </select>
                        </div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                            <div class="form-group">
                                <label for="appt-date">Preferred Date</label>
                                <input type="date" id="appt-date" name="preferred_date"
                                       min="${today}" required>
                            </div>
                            <div class="form-group">
                                <label for="appt-time">Preferred Time</label>
                                <input type="time" id="appt-time" name="preferred_time"
                                       min="08:00" max="17:00" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="appt-notes">Notes (optional)</label>
                            <textarea id="appt-notes" name="notes" rows="3"
                                      placeholder="Any specific details about your visit..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary reveal" style="width:100%;">
                            <i class="bi bi-calendar2-plus"></i> REQUEST APPOINTMENT
                        </button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderMyAppointments(appointments = []) {
        const statusStyles = {
            pending:     { bg: 'rgba(245,158,11,0.12)',  color: '#f59e0b' },
            confirmed:   { bg: 'rgba(16,185,129,0.12)',  color: '#10b981' },
            cancelled:   { bg: 'rgba(239,68,68,0.12)',   color: '#ef4444' },
            completed:   { bg: 'rgba(99,102,241,0.12)',  color: '#6366f1' },
            rescheduled: { bg: 'rgba(139,92,246,0.12)',  color: '#8b5cf6' },
        };

        const cards = appointments.length === 0
            ? `<div style="text-align:center;padding:60px 20px;border:2px dashed var(--border-main);border-radius:16px;">
                   <i class="bi bi-calendar2-x" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:1rem;"></i>
                   <h3 style="margin-bottom:0.5rem;">No Appointments</h3>
                   <p style="opacity:0.6;margin-bottom:1.5rem;">You haven't booked any appointments yet.</p>
                   <a href="#appointments" class="btn btn-primary" style="text-decoration:none;">Book Now</a>
               </div>`
            : appointments.map(a => {
                const s = statusStyles[a.status] || statusStyles.pending;
                return `
                <div style="border:var(--border-main);border-radius:16px;overflow:hidden;margin-bottom:1rem;">
                    <div style="background:var(--primary-navy);color:#fff;padding:16px 20px;
                                display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
                        <div>
                            <div style="font-weight:800;font-size:1.1rem;">${a.service_type}</div>
                            <div style="font-size:0.85rem;opacity:0.7;margin-top:2px;">
                                Ref #${a.id} · ${a.agent_name ? 'Agent: ' + a.agent_name : 'Pending assignment'}
                            </div>
                        </div>
                        <span style="padding:4px 14px;border-radius:99px;font-size:0.78rem;font-weight:800;
                                     background:${s.bg};color:${s.color};border:1px solid ${s.color}44;">
                            ${a.status.toUpperCase()}
                        </span>
                    </div>
                    <div style="padding:20px;display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:16px;">
                        <div>
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;opacity:0.5;margin-bottom:4px;">Date</div>
                            <div style="font-weight:700;">${a.preferred_date}</div>
                        </div>
                        <div>
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;opacity:0.5;margin-bottom:4px;">Time</div>
                            <div style="font-weight:700;">${a.preferred_time?.substring(0,5) || '—'}</div>
                        </div>
                        ${a.notes ? `<div style="grid-column:1/-1;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;opacity:0.5;margin-bottom:4px;">Notes</div>
                            <div style="font-weight:500;">${a.notes}</div>
                        </div>` : ''}
                        ${a.reschedule_reason ? `<div style="grid-column:1/-1;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;opacity:0.5;margin-bottom:4px;">Rescheduled Reason</div>
                            <div style="font-weight:500;">${a.reschedule_reason}</div>
                        </div>` : ''}
                    </div>
                    ${['pending','confirmed'].includes(a.status) ? `
                    <div style="padding:0 20px 20px;">
                        <button class="btn btn-danger" data-action="cancel-appointment" data-id="${a.id}"
                                style="font-size:0.85rem;padding:8px 20px;">
                            Cancel Appointment
                        </button>
                    </div>` : ''}
                </div>`;
            }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:2rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin:0;">My Appointments</h2>
                    <a href="#appointments" class="btn btn-primary reveal" style="text-decoration:none;">
                        + New Appointment
                    </a>
                </div>
                ${cards}
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Profile — with profile pic + password change
    // -------------------------------------------------------------------------
    renderProfile(user) {
        const picSrc = user.has_profile_pic
            ? `../../get_image.php?type=profile&id=${user.id}`
            : null;

        const avatarHtml = picSrc
            ? `<img src="${picSrc}" alt="Profile" id="profile-pic-preview"
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-navy);"
                    onerror="this.parentElement.innerHTML='<i class=\\'bi bi-person\\' style=\\'font-size:2.5rem;\\'></i>';">`
            : `<i class="bi bi-person" style="font-size:2.5rem;"></i>`;

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Account Profile</h2>
                <div class="form-card reveal" style="background:var(--glass-bg-light);border:var(--glass-border);padding:3rem;border-radius:24px;">

                    <!-- Profile Picture -->
                    <div style="display:flex;align-items:center;gap:2rem;margin-bottom:2.5rem;">
                        <div id="profile-pic-container"
                             style="width:80px;height:80px;border-radius:50%;background:var(--primary-navy);
                                    display:flex;align-items:center;justify-content:center;
                                    color:white;overflow:hidden;flex-shrink:0;">
                            ${avatarHtml}
                        </div>
                        <div>
                            <h3 style="margin:0 0 0.2rem;font-family:var(--font-primary);font-size:1.6rem;">
                                ${user.name}
                            </h3>
                            <p style="margin:0;opacity:0.6;">${user.email}</p>
                            <span style="display:inline-block;margin-top:0.4rem;font-size:0.78rem;font-weight:bold;
                                         padding:2px 10px;background:rgba(29,42,68,0.1);border-radius:20px;">
                                ${(user.role || 'citizen').toUpperCase()}
                            </span>
                        </div>
                    </div>

                    <!-- Profile Pic Upload -->
                    <div class="form-group reveal"
                         style="border-bottom:1px solid var(--border-main);padding-bottom:1.5rem;margin-bottom:1.5rem;">
                        <label><i class="bi bi-image"></i> Change Profile Picture</label>
                        <form id="profile-pic-form" enctype="multipart/form-data"
                              style="display:flex;gap:1rem;align-items:center;flex-wrap:wrap;">
                            <input type="file" id="profile-pic-input" name="profile_pic"
                                   accept="image/jpeg,image/png,image/webp"
                                   style="flex:1;border:none;padding:0.5rem 0;">
                            <button type="submit" class="btn btn-primary" style="padding:0.8rem 2rem;">
                                <i class="bi bi-upload"></i> Upload
                            </button>
                        </form>
                        <p style="font-size:0.8rem;opacity:0.5;margin-top:0.5rem;">
                            JPEG, PNG or WebP · max 2MB
                        </p>
                    </div>

                    <!-- Profile Details -->
                    <form id="profile-form">
                        <div class="form-group reveal">
                            <label for="profile-name"><i class="bi bi-person-badge"></i> Display Name</label>
                            <input type="text" id="profile-name" name="name" value="${user.name}" required
                                   style="width:100%;padding:1rem;border:var(--glass-border);border-radius:12px;
                                          background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                        </div>
                        <div class="form-group reveal">
                            <label for="profile-email"><i class="bi bi-envelope"></i> Email Address</label>
                            <input type="email" id="profile-email" name="email" value="${user.email}" required
                                   style="width:100%;padding:1rem;border:var(--glass-border);border-radius:12px;
                                          background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                        </div>
                        <button type="submit" class="btn btn-primary reveal" style="width:100%;margin-top:1rem;">
                            UPDATE DETAILS
                        </button>
                    </form>

                    <!-- Password Change -->
                    <div style="margin-top:2rem;padding-top:2rem;border-top:1px solid var(--border-main);">
                        <h3 class="reveal" style="font-size:1.1rem;margin-bottom:1rem;">Change Password</h3>
                        <form id="password-form">
                            <div class="form-group reveal">
                                <label for="new-password">New Password</label>
                                <input type="password" id="new-password" name="password" minlength="8"
                                       placeholder="Min. 8 characters"
                                       style="width:100%;padding:1rem;border:var(--glass-border);border-radius:12px;
                                              background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                            </div>
                            <div class="form-group reveal">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm_password"
                                       placeholder="Repeat new password"
                                       style="width:100%;padding:1rem;border:var(--glass-border);border-radius:12px;
                                              background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                            </div>
                            <button type="submit" class="btn reveal" style="width:100%;border:2px solid var(--primary-navy);">
                                UPDATE PASSWORD
                            </button>
                        </form>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Transport Hub
    // -------------------------------------------------------------------------
    renderTransport(transportTypes = []) {
        const cards = transportTypes.length > 0
            ? transportTypes.map((tt, i) => {
                const highlight = i === 0 ? 'editorial-highlight' : '';
                const imgHtml   = `<div style="width:100%;height:140px;overflow:hidden;border-radius:8px;margin-bottom:1rem;background:rgba(29,42,68,0.06);">
                    <img src="../../get_image.php?type=transport_type&id=${tt.idTransportType}" alt="${tt.name}"
                         style="width:100%;height:100%;object-fit:cover;"
                         onerror="this.parentElement.innerHTML='<div style=\\'display:flex;align-items:center;justify-content:center;height:100%;\\' ><i class=\\'bi bi-bus-front\\' style=\\'font-size:3rem;opacity:0.3;\\'></i></div>';">
                </div>`;
                return `
                    <div class="editorial-card ${highlight} reveal">
                        ${imgHtml}
                        <h3>${tt.name}</h3>
                        <p>${tt.description || `Book routes for ${tt.name} transport.`}</p>
                        <a href="#transport_list?type=${encodeURIComponent(tt.name)}"
                           class="btn btn-primary" style="align-self:flex-start;margin-top:auto;">View Routes</a>
                    </div>`;
            }).join('')
            : `<div class="editorial-card" style="grid-column:1/-1;text-align:center;">
                   <h3>No Transport Methods Available</h3>
                   <p>The municipality has not added any transport types yet. Please check back later.</p>
               </div>`;

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Municipal Transport</h2>
                <p style="margin-bottom:2rem;max-width:800px;font-weight:500;font-size:1.2rem;">
                    Select your preferred mode of transportation to book tickets securely.
                </p>
                <div class="editorial-grid">${cards}</div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Transport Route List
    // -------------------------------------------------------------------------
    renderTransportList(type, trajets, sortBy = 'departure', order = 'ASC') {
        const routeCards = trajets.length === 0
            ? `<div class="editorial-card" style="grid-column:1/-1;text-align:center;">
                   <h3>No Routes Found</h3>
                   <p>No active routes for <strong>${type}</strong>.</p>
                   <a href="#transport" class="btn" style="margin-top:1rem;">Back to Categories</a>
               </div>`
            : trajets.map(trajet => {
                const isFull    = trajet.capacity > 0 && trajet.sold >= trajet.capacity;
                const remaining = trajet.capacity - trajet.sold;
                const pct       = trajet.capacity > 0 ? Math.round((trajet.sold / trajet.capacity) * 100) : 0;
                return `
                <div class="editorial-card reveal" style="justify-content:space-between;">
                    <div>
                        <span class="category-badge">${trajet.transportName || type}</span>
                        <h3 style="font-size:1.5rem;margin-bottom:0.5rem;">${trajet.departure} → ${trajet.destination}</h3>
                        <p style="margin-bottom:0.5rem;font-weight:700;color:var(--accent-blue);">
                            ${parseFloat(trajet.price).toFixed(3)} TND
                        </p>
                        <div style="font-size:0.9rem;margin-bottom:1.5rem;font-weight:600;">
                            📅 ${new Date(trajet.departureTime).toLocaleString()}<br><br>
                            🎟️ ${isFull
                                ? '<span style="color:var(--danger)">Sold Out</span>'
                                : `${remaining} seat${remaining !== 1 ? 's' : ''} left`}
                            <div style="margin-top:8px;height:6px;background:rgba(29,42,68,0.1);border-radius:3px;">
                                <div style="height:100%;width:${pct}%;background:${pct > 80 ? 'var(--danger)' : 'var(--accent-blue)'};border-radius:3px;transition:width 0.4s;"></div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top:auto;">
                        ${!isFull
                            ? `<form class="book-transport-form" data-id="${trajet.idTrajet}">
                                   <button type="submit" class="btn btn-primary" style="width:100%;">
                                       <i class="bi bi-ticket-perforated"></i> Book Ticket
                                   </button>
                               </form>`
                            : `<button disabled class="btn btn-danger" style="width:100%;opacity:0.6;">Sold Out</button>`}
                    </div>
                </div>`;
            }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:2rem;
                            border-bottom:var(--border-main);padding-bottom:1rem;flex-wrap:wrap;gap:1rem;">
                    <h2 class="reveal" style="margin-bottom:0;border-bottom:none;padding-bottom:0;">
                        <a href="#transport" style="text-decoration:none;color:var(--secondary-grey);">←</a>
                        Routes: ${type}
                    </h2>
                    <form id="sort-transport-form" data-type="${type}"
                          style="display:flex;gap:10px;align-items:stretch;flex-wrap:wrap;">
                        <select name="sort" style="padding:0.8rem;border:var(--border-main);background:transparent;font-weight:bold;font-family:inherit;color:var(--primary-navy);">
                            <option value="departure"   ${sortBy==='departure'   ? 'selected':''}>Sort by Departure</option>
                            <option value="destination" ${sortBy==='destination' ? 'selected':''}>Sort by Destination</option>
                            <option value="price"       ${sortBy==='price'       ? 'selected':''}>Sort by Price</option>
                        </select>
                        <select name="order" style="padding:0.8rem;border:var(--border-main);background:transparent;font-weight:bold;font-family:inherit;color:var(--primary-navy);">
                            <option value="ASC"  ${order==='ASC'  ? 'selected':''}>A → Z</option>
                            <option value="DESC" ${order==='DESC' ? 'selected':''}>Z → A</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding:0.8rem 1.5rem;">Sort</button>
                    </form>
                </div>
                <div class="editorial-grid">${routeCards}</div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // My Tickets
    // -------------------------------------------------------------------------
    renderMyTickets(tickets = [], user) {
        const validTickets     = tickets.filter(t => t.status === 'Valid');
        const cancelledTickets = tickets.filter(t => t.status === 'Cancelled');

        const renderTicketCard = (ticket, isExpanded = true) => {
            const isValid  = ticket.status === 'Valid';
            const depTime  = ticket.departureTime ? new Date(ticket.departureTime).toLocaleString() : '—';
            const issuedAt = ticket.issuedAt ? new Date(ticket.issuedAt).toLocaleDateString() : '—';
            const photoHtml = isExpanded
                ? `<img src="../../get_image.php?type=transport&id=${ticket.typeId || 0}" alt="${ticket.typeName || ''}"
                        style="width:100px;height:100px;object-fit:cover;border-radius:12px;border:2px solid rgba(255,255,255,0.1);"
                        onerror="this.style.display='none';">`
                : '';

            const mapBlock = isExpanded
                ? `<div style="margin:20px 0;border-radius:12px;overflow:hidden;border:2px solid rgba(255,255,255,0.05);background:#0f1117;">
                       <div id="map-${ticket.idTicket}" class="ticket-map"
                            data-deplat="${ticket.depLat}"  data-deplng="${ticket.depLng}"
                            data-destlat="${ticket.destLat}" data-destlng="${ticket.destLng}"
                            style="width:100%;height:260px;"></div>
                       <div style="position:absolute;bottom:10px;right:10px;background:rgba(0,0,0,0.7);color:#fff;
                                   padding:4px 10px;border-radius:6px;font-size:0.7rem;font-weight:600;z-index:10;">
                           Live Route Map
                       </div>
                   </div>`
                : '';

            return `
            <div class="ticket-card" style="
                background:var(--bg-dark,#1a1a2e);
                border:1px solid ${isValid ? 'rgba(99,102,241,0.5)' : 'rgba(239,68,68,0.2)'};
                border-radius:16px;overflow:hidden;
                box-shadow:${isValid ? '0 10px 30px -10px rgba(99,102,241,0.3)' : 'none'};
                ${!isValid ? 'opacity:0.6;' : ''}">

                <div style="background:${isValid ? 'linear-gradient(135deg,#6366f1,#7c3aed)' : 'linear-gradient(135deg,#4b5563,#374151)'};
                            padding:20px 24px;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:20px;">
                        ${photoHtml}
                        <div>
                            <div style="font-size:0.8rem;text-transform:uppercase;letter-spacing:2px;opacity:0.9;color:#fff;font-weight:600;margin-bottom:4px;">
                                ${ticket.typeName || 'Transport'} Ticket
                            </div>
                            <div style="font-weight:800;font-size:1.4rem;color:#fff;letter-spacing:1px;">${ticket.ref}</div>
                        </div>
                    </div>
                    <div style="background:${isValid ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)'};
                                color:${isValid ? '#4ade80' : '#f87171'};
                                padding:6px 16px;border-radius:999px;font-size:0.8rem;font-weight:800;
                                text-transform:uppercase;border:1px solid ${isValid ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'};">
                        ${ticket.status}
                    </div>
                </div>

                <div style="padding:24px;">
                    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
                        <div style="flex:1;text-align:center;">
                            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,0.5);margin-bottom:6px;">Origin</div>
                            <div style="font-weight:800;font-size:1.2rem;color:#fff;">${ticket.departure || '—'}</div>
                        </div>
                        <div style="font-size:1.5rem;flex-shrink:0;">✈️</div>
                        <div style="flex:1;text-align:center;">
                            <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:1.5px;color:rgba(255,255,255,0.5);margin-bottom:6px;">Destination</div>
                            <div style="font-weight:800;font-size:1.2rem;color:#fff;">${ticket.destination || '—'}</div>
                        </div>
                    </div>

                    ${mapBlock}

                    <div style="border-top:2px dashed rgba(255,255,255,0.1);margin:24px -24px;position:relative;">
                        <div style="position:absolute;left:-12px;top:-12px;width:24px;height:24px;border-radius:50%;background:#1a1a2e;"></div>
                        <div style="position:absolute;right:-12px;top:-12px;width:24px;height:24px;border-radius:50%;background:#1a1a2e;"></div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px;">
                        <div style="background:rgba(255,255,255,0.02);padding:12px;border-radius:8px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.4);margin-bottom:4px;">Passenger</div>
                            <div style="font-weight:700;color:#fff;">${ticket.citizenName || user.name}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.02);padding:12px;border-radius:8px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.4);margin-bottom:4px;">Vehicle</div>
                            <div style="font-weight:700;color:#fff;">${ticket.transportName || '—'}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.02);padding:12px;border-radius:8px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.4);margin-bottom:4px;">Departure</div>
                            <div style="font-weight:700;color:#fff;">📅 ${depTime}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.02);padding:12px;border-radius:8px;">
                            <div style="font-size:0.7rem;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,0.4);margin-bottom:4px;">Fare</div>
                            <div style="font-weight:800;color:#6366f1;">${ticket.price ? parseFloat(ticket.price).toFixed(3) + ' TND' : '—'}</div>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:24px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.05);">
                        <div style="font-size:0.8rem;color:rgba(255,255,255,0.4);font-weight:600;">Issued: ${issuedAt}</div>
                        ${isValid
                            ? `<button class="btn btn-danger" data-action="cancel-ticket" data-id="${ticket.idTicket}"
                                       style="font-size:0.85rem;padding:8px 20px;font-weight:700;">🚫 Cancel Booking</button>`
                            : `<span style="font-size:0.85rem;color:rgba(255,255,255,0.4);font-weight:700;
                                           background:rgba(255,255,255,0.05);padding:6px 12px;border-radius:6px;">Cancelled</span>`}
                    </div>
                </div>
            </div>`;
        };

        const validCards = validTickets.length > 0
            ? validTickets.map(t => renderTicketCard(t, true)).join('')
            : `<div style="text-align:center;padding:60px 20px;background:var(--bg-dark,#1a1a2e);border-radius:16px;border:1px dashed rgba(255,255,255,0.1);">
                   <div style="font-size:4rem;margin-bottom:16px;">🎫</div>
                   <h3 style="color:#fff;">No Active Tickets</h3>
                   <p style="color:rgba(255,255,255,0.5);margin-bottom:24px;">Book a route to get your digital boarding pass.</p>
                   <a href="#transport" class="btn btn-primary" style="padding:12px 30px;">Browse Routes</a>
               </div>`;

        const cancelledSection = cancelledTickets.length > 0 ? `
            <div style="margin-top:4rem;">
                <h3 style="margin-bottom:1.5rem;color:rgba(255,255,255,0.4);font-size:1rem;text-transform:uppercase;letter-spacing:2px;display:flex;align-items:center;gap:10px;">
                    <span style="height:1px;background:rgba(255,255,255,0.1);flex:1;"></span>
                    Cancelled Bookings
                    <span style="height:1px;background:rgba(255,255,255,0.1);flex:1;"></span>
                </h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(380px,1fr));gap:24px;">
                    ${cancelledTickets.map(t => renderTicketCard(t, false)).join('')}
                </div>
            </div>` : '';

        this.app.innerHTML = `
            <section class="page-container" style="max-width:900px;margin:0 auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:3rem;flex-wrap:wrap;gap:1rem;">
                    <div>
                        <h2 style="margin-bottom:8px;border-bottom:none;padding-bottom:0;font-size:2.2rem;">My Digital Boarding Passes</h2>
                        <p style="color:rgba(255,255,255,0.5);font-size:1rem;margin:0;font-weight:500;">
                            ${validTickets.length} active ticket${validTickets.length !== 1 ? 's' : ''}
                        </p>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;gap:40px;">${validCards}</div>
                ${cancelledSection}
            </section>
        `;
        this.triggerObserver();
        setTimeout(() => this.initTicketMaps(), 150);
    },

    // -------------------------------------------------------------------------
    // Leaflet map initialisation for ticket cards
    // -------------------------------------------------------------------------
    initTicketMaps() {
        if (typeof L === 'undefined') { setTimeout(() => this.initTicketMaps(), 500); return; }

        document.querySelectorAll('.ticket-map').forEach(el => {
            const depLat  = parseFloat(el.dataset.deplat);
            const depLng  = parseFloat(el.dataset.deplng);
            const destLat = parseFloat(el.dataset.destlat);
            const destLng = parseFloat(el.dataset.destlng);

            if (isNaN(depLat) || isNaN(depLng) || isNaN(destLat) || isNaN(destLng)) {
                el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:rgba(255,255,255,0.3);font-size:0.9rem;">No route coordinates available.</div>';
                return;
            }

            const map = L.map(el, {
                center: [(depLat + destLat) / 2, (depLng + destLng) / 2],
                zoom: 7, zoomControl: false, attributionControl: false
            });
            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);

            const origin = [depLat, depLng], destination = [destLat, destLng];
            L.marker(origin).addTo(map).bindPopup('Departure');
            L.marker(destination).addTo(map).bindPopup('Destination');

            fetch(`https://router.project-osrm.org/route/v1/driving/${depLng},${depLat};${destLng},${destLat}?overview=full&geometries=geojson`)
                .then(r => r.json())
                .then(data => {
                    if (data.code === 'Ok' && data.routes.length > 0) {
                        const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                        const line   = L.polyline(coords, { color: '#6366f1', weight: 5, opacity: 0.85 }).addTo(map);
                        map.fitBounds(line.getBounds(), { padding: [20, 20] });
                    } else {
                        map.fitBounds([origin, destination], { padding: [20, 20] });
                    }
                })
                .catch(() => map.fitBounds([origin, destination], { padding: [20, 20] }));
        });
    }
};

export default view;
