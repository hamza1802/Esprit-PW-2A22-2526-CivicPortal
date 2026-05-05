/**
 * view.js â€” FrontOffice rendering logic.
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
            <div class="nav-backdrop"></div>
            <button class="nav-hamburger" aria-label="Toggle menu">
                <span></span><span></span><span></span>
            </button>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#programs">programs</a></li>
                <li><a href="forum.php">forum</a></li>
                <li><a href="#request-service">requests</a></li>
                <li><a href="#appointments">appointments</a></li>
                <li><a href="#transport">transport</a></li>
                <li><a href="#dashboard">dashboard</a></li>
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls">
                ${roleBadge}
                <a href="#"
                   onclick="event.preventDefault();fetch('../../Verification.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='login.php')"
                   class="logout-link">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        `;

        // Hamburger toggle
        const hamburger = nav.querySelector('.nav-hamburger');
        const backdrop = nav.querySelector('.nav-backdrop');
        const toggle = () => nav.classList.toggle('nav-open');
        hamburger.addEventListener('click', toggle);
        backdrop.addEventListener('click', toggle);

        // Close on link click (mobile)
        nav.querySelectorAll('.nav-links a').forEach(a => {
            a.addEventListener('click', () => nav.classList.remove('nav-open'));
        });

        // Active-link highlighting
        const markActive = () => {
            const hash = window.location.hash || '#home';
            nav.querySelectorAll('.nav-links a').forEach(a => {
                const href = a.getAttribute('href');
                a.classList.toggle('active', href && hash.startsWith(href) && href !== '#');
            });
        };
        markActive();
        window.addEventListener('hashchange', markActive);
    },

    // -------------------------------------------------------------------------
    // Home
    // -------------------------------------------------------------------------
    renderHome(user) {
        this.app.innerHTML = `
            <div class="hero-container dashboard-mode reveal">
                <section class="hero-section">
                    <h1>CivicPortal</h1>
                    <p>Welcome back, ${user.name}. Navigate municipal services with clarity and precision.</p>
                    
                    <div class="smart-status-bar">
                        <div class="status-item" id="weather-status-widget">
                            <i class="bi bi-cloud-sun"></i>
                            <div>
                                <div class="status-label">Local Weather</div>
                                <div class="status-value">Loading...</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <i class="bi bi-shield-check"></i>
                            <div>
                                <div class="status-label">System Status</div>
                                <div class="status-value">Operational</div>
                            </div>
                        </div>
                        <div class="status-item">
                            <i class="bi bi-activity"></i>
                            <div>
                                <div class="status-label">Service Uptime</div>
                                <div class="status-value">99.8%</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            <section class="page-container w-full" style="padding: 6rem 0; overflow: hidden;">
                <h2 class="reveal text-center mb-48"><span class="kinetic-text">Directory of Services</span></h2>
                <div class="diagonal-scroll-wrapper">
                    <div class="editorial-grid">
                        <div class="editorial-card editorial-highlight reveal">
                            <div class="stats-number mb-16" style="opacity:0.2;">01</div>
                            <i class="bi bi-file-earmark-text card-icon"></i>
                            <h3>Service Requests</h3>
                            <p>Submit administrative documents, permits, and service requests. Track status in real-time.</p>
                            <a href="#request-service" class="btn btn-primary card-action">File a Request</a>
                        </div>
                        <div class="editorial-card reveal">
                            <div class="stats-number mb-16" style="opacity:0.1;">02</div>
                            <i class="bi bi-calendar2-check card-icon"></i>
                            <h3>Book Appointment</h3>
                            <p>Schedule a meeting with a municipal agent for document processing or inquiries.</p>
                            <a href="#appointments" class="btn card-action">Book Now</a>
                        </div>
                        <div class="editorial-card reveal">
                            <div class="stats-number mb-16" style="opacity:0.1;">03</div>
                            <i class="bi bi-people card-icon"></i>
                            <h3>Community Programs</h3>
                            <p>Engage with local initiatives. Browse the Parks &amp; Recreation activity catalog.</p>
                            <a href="#programs" class="btn card-action">View Catalog</a>
                        </div>
                        <div class="editorial-card reveal">
                            <div class="stats-number mb-16" style="opacity:0.1;">04</div>
                            <i class="bi bi-bus-front card-icon"></i>
                            <h3>Municipal Transport</h3>
                            <p>Book tickets for buses, trains, and other city transport. View live routes.</p>
                            <a href="#transport" class="btn card-action">Browse Routes</a>
                        </div>
                    </div>
                </div>
            </section>
        `;
                this.triggerObserver();
        this._initWeatherWidget();
    },

    _initWeatherWidget() {
        const widget = document.getElementById('weather-status-widget');
        if (!widget) return;

        const updateWeather = (lat, lon) => {
            fetch("https://api.open-meteo.com/v1/forecast?latitude=" + lat + "&longitude=" + lon + "&current_weather=true")
                .then(res => res.json())
                .then(data => {
                    if (data && data.current_weather) {
                        const temp = data.current_weather.temperature;
                        const code = data.current_weather.weathercode;
                        let desc = 'Clear';
                        if (code > 0) desc = 'Cloudy';
                        if (code > 50) desc = 'Rain';
                        if (code > 70) desc = 'Snow';
                        widget.querySelector('.status-value').textContent = temp + ' C - ' + desc;
                    }
                })
                .catch(err => {
                    console.error('Weather fetch error:', err);
                    widget.querySelector('.status-value').textContent = 'Unavailable';
                });
        };

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                position => updateWeather(position.coords.latitude, position.coords.longitude),
                () => updateWeather(36.7681, 10.2753) // Radès, Tunisia
            );
        } else {
            updateWeather(36.7681, 10.2753); // Radès, Tunisia
        }
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
                        <img loading="lazy" src="${imgSrc}" alt="${p.title}" class="program-img"
                             onerror="this.style.display='none';this.parentElement.style.background='var(--primary-navy)';">
                    </div>
                    <div class="card-content">
                        <span class="category-badge">${p.category}</span>
                        <h3>${p.title}</h3>
                        <div class="text-small text-bold mb-16" style="color:var(--primary-navy);opacity:0.8;">
                            <i class="bi bi-calendar3"></i> 
                            ${p.start_date ? new Date(p.start_date).toLocaleDateString() : 'TBA'} - 
                            ${p.end_date ? new Date(p.end_date).toLocaleDateString() : 'TBA'}
                        </div>
                        <p class="description-clamp" onclick="this.classList.toggle('expanded')"
                           title="Click to expand">${p.description}</p>
                        <div class="mt-auto flex-column gap-8" style="padding-bottom:5%;">

                            <button class="btn ${isEnrolled ? 'btn-success' : 'btn-primary'} w-full"
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
                <div class="flex-between mb-32">
                    <h2 class="reveal mb-0">Programs Catalog</h2>

                    <div class="filter-controls reveal flex gap-16 flex-wrap">
                        <input type="text" id="prog-search" placeholder="Search by title..."
                               class="no-bg"
                               style="flex-grow:1;padding:1rem;border:var(--border-main);
                                      font-family:inherit;font-size:1.1rem;font-weight:600;
                                      color:var(--primary-navy);outline:none;">
                        <select id="prog-filter-cat"
                                class="no-bg"
                                style="padding:1rem 2rem 1rem 1rem;border:var(--border-main);
                                       font-family:inherit;font-size:1.1rem;font-weight:600;
                                       color:var(--primary-navy);outline:none;cursor:pointer;">
                            <option value="">All Categories</option>
                            <option value="Arts">Arts</option>
                            <option value="Sports">Sports</option>
                            <option value="Environment">Environment</option>
                        </select>
                    </div>
                </div>
                                <div class="editorial-card reveal mb-32" style="background: var(--surface-glass); border-color: var(--color-accent-blue); padding: 2rem;">
                    <h3 class="mb-16" style="color: var(--color-accent-blue);"><i class="bi bi-magic"></i> AI Program Matcher</h3>
                    <p>Describe what you're looking for or your current situation, and our AI will find the best programs for you.</p>
                    <div class="flex gap-16 mt-16 flex-center">
                        <input type="text" id="ai-match-input" placeholder="e.g., I'm a student looking for part-time community service..." class="no-bg" style="flex:1; padding:1rem; border-radius:var(--radius-sm); border:var(--border-main); font-family:inherit; color:var(--primary-navy); outline:none;">
                        <button id="btn-ai-match" class="btn btn-primary" style="min-width:120px;">Match Me</button>
                    </div>
                    <div id="ai-match-results" style="display:none;" class="ai-matches-grid"></div>
                </div>
                <div class="editorial-grid" id="program-list">
                    ${programCards || '<div class="editorial-card text-center" style="grid-column:1/-1;"><p>No programs available.</p></div>'}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // -------------------------------------------------------------------------
    // Service Requests â€” Form + My Requests list
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
                <div class="flex-between mb-32 flex-wrap gap-16">
                    <h2 class="reveal mb-0">File a Service Request</h2>
                    <a href="#dashboard" class="btn reveal" style="text-decoration:none;">
                        <i class="bi bi-columns-gap"></i> Dashboard
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
                                   class="no-border"
                                   style="padding:0.5rem 0;">
                        </div>
                        <button type="submit" class="btn btn-primary reveal w-full">
                            <i class="bi bi-send"></i> SUBMIT REQUEST
                        </button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    _getRequestsHtml(requests = []) {
        const statusColor = { pending: '#f59e0b', in_progress: '#6366f1', approved: '#10b981', rejected: '#ef4444', validated: '#10b981', resolved: '#6366f1' };

        const rows = requests.length === 0
            ? `<tr><td colspan="5" class="text-center" style="padding:2rem;color:var(--text-dark);opacity:0.6;">
                   No requests found. <a href="#request-service" style="color:var(--accent-blue);">File one?</a>
               </td></tr>`
            : requests.map(r => `
                <tr>
                    <td><strong>#${r.id}</strong></td>
                    <td>${r.title || r.category || 'â€”'}</td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="${r.description || ''}">${r.description || 'â€”'}</td>
                    <td>${r.created_at ? new Date(r.created_at).toLocaleDateString() : 'â€”'}</td>
                    <td>
                        <span class="status-pill" style="display:inline-block;padding:3px 10px;border-radius:99px;font-size:0.75rem;font-weight:800;
                                     background:${(statusColor[r.status] || '#6b7280')}22;
                                     color:${statusColor[r.status] || '#6b7280'};">
                            ${r.status?.toUpperCase() || 'PENDING'}
                        </span>
                    </td>
                </tr>
            `).join('');

        return `
            <div class="flex-between mb-24 flex-wrap gap-16">
                <h3 class="mb-0" style="font-size:1.5rem;">My Requests</h3>
                <a href="#request-service" class="btn btn-primary" style="text-decoration:none;padding:0.6rem 1.2rem;">+ New Request</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Ref</th><th>Service</th><th>Details</th><th>Date</th><th>Status</th></tr></thead>
                    <tbody>${rows}</tbody>
                </table>
            </div>
        `;
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
                <div class="flex-between mb-32 flex-wrap gap-16">
                    <h2 class="reveal mb-0">Book an Appointment</h2>
                    <a href="#dashboard" class="btn reveal" style="text-decoration:none;">
                        <i class="bi bi-columns-gap"></i> Dashboard
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
                        <div class="gap-16" style="display:grid;grid-template-columns:1fr 1fr;">
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
                        <button type="submit" class="btn btn-primary reveal w-full">
                            <i class="bi bi-calendar2-plus"></i> REQUEST APPOINTMENT
                        </button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    _getAppointmentsHtml(appointments = []) {
        const statusStyles = {
            pending:     { bg: 'rgba(245,158,11,0.12)',  color: '#f59e0b' },
            confirmed:   { bg: 'rgba(16,185,129,0.12)',  color: '#10b981' },
            cancelled:   { bg: 'rgba(239,68,68,0.12)',   color: '#ef4444' },
            completed:   { bg: 'rgba(99,102,241,0.12)',  color: '#6366f1' },
            rescheduled: { bg: 'rgba(139,92,246,0.12)',  color: '#8b5cf6' },
        };

        const cards = appointments.length === 0
            ? `<div class="text-center" style="padding:60px 20px;border:2px dashed var(--border-main);border-radius:16px;">
                   <i class="bi bi-calendar2-x mb-16" style="font-size:3rem;opacity:0.3;display:block;"></i>
                   <h3 class="mb-8">No Appointments</h3>
                   <p class="mb-24" style="opacity:0.6;">You haven't booked any appointments yet.</p>
                   <a href="#appointments" class="btn btn-primary" style="text-decoration:none;">Book Now</a>
               </div>`
            : appointments.map(a => {
                const s = statusStyles[a.status] || statusStyles.pending;
                return `
                <div class="appt-card">
                    <div class="appt-header">
                        <div>
                            <div class="text-bold" style="font-size:1.1rem;">${a.service_type}</div>
                            <div class="text-small opacity-7" style="margin-top:2px;">
                                Ref #${a.id} Â· ${a.agent_name ? 'Agent: ' + a.agent_name : 'Pending assignment'}
                            </div>
                        </div>
                        <span class="status-pill" style="padding:4px 14px;border-radius:99px;font-size:0.78rem;font-weight:800;
                                     background:${s.bg};color:${s.color};border:1px solid ${s.color}44;">
                            ${a.status.toUpperCase()}
                        </span>
                    </div>
                    <div class="appt-body">
                        <div>
                            <div class="appt-detail-label">Date</div>
                            <div class="appt-detail-value">${a.preferred_date}</div>
                        </div>
                        <div>
                            <div class="appt-detail-label">Time</div>
                            <div class="appt-detail-value">${a.preferred_time?.substring(0,5) || 'â€”'}</div>
                        </div>
                        ${a.notes ? `<div style="grid-column:1/-1;">
                            <div class="appt-detail-label">Notes</div>
                            <div style="font-weight:500;">${a.notes}</div>
                        </div>` : ''}
                        ${a.reschedule_reason ? `<div style="grid-column:1/-1;">
                            <div class="appt-detail-label">Rescheduled Reason</div>
                            <div style="font-weight:500;">${a.reschedule_reason}</div>
                        </div>` : ''}
                    </div>
                    ${['pending','confirmed'].includes(a.status) ? `
                    <div style="padding:0 20px 20px;">
                        <button class="btn btn-danger text-small" data-action="cancel-appointment" data-id="${a.id}"
                                style="padding:8px 20px;">
                            Cancel Appointment
                        </button>
                    </div>` : ''}
                </div>`;
            }).join('');

        return `
            <div class="flex-between mb-24 flex-wrap gap-16">
                <h3 class="mb-0" style="font-size:1.5rem;">My Appointments</h3>
                <a href="#appointments" class="btn btn-primary" style="text-decoration:none;padding:0.6rem 1.2rem;">+ New Appointment</a>
            </div>
            ${cards}
        `;
    },

    // -------------------------------------------------------------------------
    // Profile â€” with profile pic + password change
    // -------------------------------------------------------------------------
    renderProfile(user) {
        const picSrc = user.has_profile_pic
            ? `../../get_image.php?type=profile&id=${user.id}`
            : null;

        const avatarHtml = picSrc
            ? `<img loading="lazy" src="${picSrc}" alt="Profile" id="profile-pic-preview"
                    style="width:80px;height:80px;border-radius:50%;object-fit:cover;border:3px solid var(--primary-navy);"
                    onerror="this.parentElement.innerHTML='<i class=\\'bi bi-person\\' style=\\'font-size:2.5rem;\\'></i>';">`
            : `<i class="bi bi-person" style="font-size:2.5rem;"></i>`;

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Account Profile</h2>
                <div class="form-card reveal" style="background:var(--glass-bg-light);border:var(--glass-border);padding:3rem;border-radius:24px;">

                    <!-- Profile Picture -->
                    <div class="flex gap-32 mb-32 flex-center">
                        <div id="profile-pic-container"
                             class="flex-center"
                             style="width:80px;height:80px;border-radius:50%;background:var(--primary-navy);
                                    color:white;overflow:hidden;flex-shrink:0;">
                            ${avatarHtml}
                        </div>
                        <div>
                            <h3 class="mb-0" style="font-family:var(--font-primary);font-size:1.6rem;">
                                ${user.name}
                            </h3>
                            <p class="mb-0" style="opacity:0.6;">${user.email}</p>
                            <span class="text-bold text-small" style="display:inline-block;margin-top:0.4rem;
                                         padding:2px 10px;background:rgba(29,42,68,0.1);border-radius:20px;">
                                ${(user.role || 'citizen').toUpperCase()}
                            </span>
                        </div>
                    </div>

                    <!-- Profile Pic Upload -->
                    <div class="form-group reveal mb-24"
                         style="border-bottom:1px solid var(--border-main);padding-bottom:1.5rem;">
                        <label><i class="bi bi-image"></i> Change Profile Picture</label>
                        <form id="profile-pic-form" enctype="multipart/form-data"
                              class="flex gap-16 flex-center flex-wrap">
                            <input type="file" id="profile-pic-input" name="profile_pic"
                                   accept="image/jpeg,image/png,image/webp"
                                   class="no-border"
                                   style="flex:1;padding:0.5rem 0;">
                            <button type="submit" class="btn btn-primary" style="padding:0.8rem 2rem;">
                                <i class="bi bi-upload"></i> Upload
                            </button>
                        </form>
                        <p class="text-small opacity-5" style="margin-top:0.5rem;">
                            JPEG, PNG or WebP Â· max 2MB
                        </p>
                    </div>

                    <!-- Profile Details -->
                    <form id="profile-form">
                        <div class="form-group reveal">
                            <label for="profile-name"><i class="bi bi-person-badge"></i> Display Name</label>
                            <input type="text" id="profile-name" name="name" value="${user.name}" required
                                   class="w-full"
                                   style="padding:1rem;border:var(--glass-border);border-radius:12px;
                                          background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                        </div>
                        <div class="form-group reveal">
                            <label for="profile-email"><i class="bi bi-envelope"></i> Email Address</label>
                            <input type="email" id="profile-email" name="email" value="${user.email}" required
                                   class="w-full"
                                   style="padding:1rem;border:var(--glass-border);border-radius:12px;
                                          background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                        </div>
                        <button type="submit" class="btn btn-primary reveal w-full" style="margin-top:1rem;">
                            UPDATE DETAILS
                        </button>
                    </form>

                    <!-- Password Change -->
                    <div style="margin-top:2rem;padding-top:2rem;border-top:1px solid var(--border-main);">
                        <h3 class="reveal mb-16" style="font-size:1.1rem;">Change Password</h3>
                        <form id="password-form">
                            <div class="form-group reveal">
                                <label for="new-password">New Password</label>
                                <input type="password" id="new-password" name="password" minlength="8"
                                       placeholder="Min. 8 characters"
                                       class="w-full"
                                       style="padding:1rem;border:var(--glass-border);border-radius:12px;
                                              background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                            </div>
                            <div class="form-group reveal">
                                <label for="confirm-password">Confirm Password</label>
                                <input type="password" id="confirm-password" name="confirm_password"
                                       placeholder="Repeat new password"
                                       class="w-full"
                                       style="padding:1rem;border:var(--glass-border);border-radius:12px;
                                              background:rgba(255,255,255,0.6);font-family:var(--font-primary);">
                            </div>
                            <button type="submit" class="btn reveal w-full" style="border:2px solid var(--primary-navy);">
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
                const imgHtml   = `<div class="w-full mb-16" style="height:140px;overflow:hidden;border-radius:8px;background:rgba(29,42,68,0.06);">
                    <img loading="lazy" src="../../get_image.php?type=transport_type&id=${tt.idTransportType}" alt="${tt.name}"
                         class="w-full"
                         style="height:100%;object-fit:cover;"
                         onerror="this.parentElement.innerHTML='<div class=\\'flex-center\\' style=\\'height:100%;\\' ><i class=\\'bi bi-bus-front\\' style=\\'font-size:3rem;opacity:0.3;\\'></i></div>';">
                </div>`;
                return `
                    <div class="editorial-card ${highlight} reveal">
                        ${imgHtml}
                        <h3>${tt.name}</h3>
                        <p>${tt.description || `Book routes for ${tt.name} transport.`}</p>
                        <a href="#transport_list?type=${encodeURIComponent(tt.name)}"
                           class="btn btn-primary mt-auto" style="align-self:flex-start;">View Routes</a>
                    </div>`;
            }).join('')
            : `<div class="editorial-card text-center" style="grid-column:1/-1;">
                   <h3>No Transport Methods Available</h3>
                   <p>The municipality has not added any transport types yet. Please check back later.</p>
               </div>`;

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Municipal Transport</h2>
                <p class="mb-32" style="max-width:800px;font-weight:500;font-size:1.2rem;">
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
            ? `<div class="editorial-card text-center" style="grid-column:1/-1;">
                   <h3>No Routes Found</h3>
                   <p>No active routes for <strong>${type}</strong>.</p>
                   <a href="#transport" class="btn mb-16">Back to Categories</a>
               </div>`
            : trajets.map(trajet => {
                const isFull    = trajet.capacity > 0 && trajet.sold >= trajet.capacity;
                const remaining = trajet.capacity - trajet.sold;
                const pct       = trajet.capacity > 0 ? Math.round((trajet.sold / trajet.capacity) * 100) : 0;
                return `
                <div class="editorial-card reveal flex-between">
                    <div>
                        <span class="category-badge">${trajet.transportName || type}</span>
                        <h3 class="mb-8" style="font-size:1.5rem;">${trajet.departure} → ${trajet.destination}</h3>
                        <p class="mb-8 text-bold" style="color:var(--accent-blue);">
                            ${parseFloat(trajet.price).toFixed(3)} TND
                        </p>
                        <div class="mb-24 text-small" style="font-weight:600;">
                            ⏱️ ${new Date(trajet.departureTime).toLocaleString()}<br><br>
                            ${isFull
                                ? '<span style="color:var(--danger)">Sold Out</span>'
                                : `${remaining} seat${remaining !== 1 ? 's' : ''} left`}
                            <div style="margin-top:8px;height:6px;background:rgba(29,42,68,0.1);border-radius:3px;">
                                <div style="height:100%;width:${pct}%;background:${pct > 80 ? 'var(--danger)' : 'var(--accent-blue)'};border-radius:3px;transition:width 0.4s;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-auto">
                        ${!isFull
                            ? `<form class="book-transport-form" data-id="${trajet.idTrajet}">
                                   <button type="submit" class="btn btn-primary w-full">
                                       <i class="bi bi-ticket-perforated"></i> Book Ticket
                                   </button>
                               </form>`
                            : `<button disabled class="btn btn-danger w-full" style="opacity:0.6;">Sold Out</button>`}
                    </div>
                </div>`;
            }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div class="flex-between mb-32 flex-wrap gap-16" style="align-items:flex-end; border-bottom:var(--border-main);padding-bottom:1rem;">
                    <h2 class="reveal mb-0" style="border-bottom:none;padding-bottom:0;">
                        <a href="#transport" style="text-decoration:none;color:var(--secondary-grey);">←</a>
                        Routes: ${type}
                    </h2>
                    <form id="sort-transport-form" data-type="${type}"
                          class="flex gap-8 flex-wrap" style="align-items:stretch;">
                        <select name="sort" class="no-bg text-bold" style="padding:0.8rem;border:var(--border-main);font-family:inherit;color:var(--primary-navy);">
                            <option value="departure"   ${sortBy==='departure'   ? 'selected':''}>Sort by Departure</option>
                            <option value="destination" ${sortBy==='destination' ? 'selected':''}>Sort by Destination</option>
                            <option value="price"       ${sortBy==='price'       ? 'selected':''}>Sort by Price</option>
                        </select>
                        <select name="order" class="no-bg text-bold" style="padding:0.8rem;border:var(--border-main);font-family:inherit;color:var(--primary-navy);">
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
    _getTicketsHtml(tickets = [], user) {
        const validTickets     = tickets.filter(t => t.status === 'Valid');
        const cancelledTickets = tickets.filter(t => t.status === 'Cancelled');

        const renderTicketCard = (ticket, isExpanded = true) => {
            const isValid  = ticket.status === 'Valid';
            const depTime = (() => {
                if (!ticket.departureTime) return 'Time not set';
                const date = new Date(ticket.departureTime);
                return isNaN(date.getTime()) ? 'Invalid time' : date.toLocaleString();
            })();
            const issuedAt = ticket.issuedAt ? new Date(ticket.issuedAt).toLocaleDateString() : '—';
            const photoHtml = isExpanded
                ? `<img loading="lazy" src="../../get_image.php?type=transport_type&id=${ticket.typeId || 0}" alt="${ticket.typeName || ''}"
                        style="width:80px;height:80px;object-fit:cover;border-radius:12px;border:2px solid rgba(29,42,68,0.08);"
                        onerror="this.outerHTML='<div class=&quot;flex-center&quot; style=&quot;width:80px;height:80px;background:rgba(255,255,255,0.6);border-radius:12px;font-size:2rem;color:rgba(26,26,26,0.5);&quot;><i class=&quot;bi bi-bus-front&quot;></i></div>';">`
                : '';

            const mapBlock = isExpanded
                ? (ticket.depLat && ticket.depLng && ticket.destLat && ticket.destLng
                    ? `<div style="margin:20px 0;border-radius:12px;overflow:hidden;border:2px solid rgba(29,42,68,0.08);background:rgba(255,255,255,0.98);position:relative;">
                           <div id="map-${ticket.idTicket}" class="ticket-map w-full"
                                data-deplat="${ticket.depLat}"  data-deplng="${ticket.depLng}"
                                data-destlat="${ticket.destLat}" data-destlng="${ticket.destLng}"
                                style="height:220px;"></div>
                       </div>`
                    : `<div class="text-center" style="margin:20px 0;padding:20px;border-radius:12px;border:2px solid rgba(0,0,0,0.08);background:rgba(255,255,255,0.92);color:rgba(26,26,26,0.7);">
                           No route coordinates available
                       </div>`)
                : '';

            return `
            <div class="ticket-card ${!isValid ? 'cancelled' : ''}">
                <div class="ticket-header ${!isValid ? 'cancelled' : ''}">
                    <div class="flex gap-16 flex-wrap flex-center">
                        ${photoHtml}
                        <div>
                            <div class="ticket-type-label">${ticket.typeName || 'Transport'} Ticket</div>
                            <div class="ticket-ref">${ticket.ref}</div>
                        </div>
                    </div>
                    <span class="ticket-status-badge ${isValid ? 'valid' : 'cancelled'}">${ticket.status}</span>
                </div>

                <div class="ticket-body">
                    <div class="ticket-route">
                        <div class="ticket-route-point">
                            <div class="ticket-route-label">Origin</div>
                            <div class="ticket-route-city">${ticket.departure || '—'}</div>
                        </div>
                        <div style="font-size:1.5rem;flex-shrink:0;">→</div>
                        <div class="ticket-route-point">
                            <div class="ticket-route-label">Destination</div>
                            <div class="ticket-route-city">${ticket.destination || '—'}</div>
                        </div>
                    </div>

                    ${mapBlock}

                    <div class="ticket-divider"></div>

                    <div class="ticket-details">
                        <div class="ticket-detail-item">
                            <div class="ticket-detail-label">Passenger</div>
                            <div class="ticket-detail-value">${ticket.citizenName || user.name}</div>
                        </div>
                        <div class="ticket-detail-item">
                            <div class="ticket-detail-label">Vehicle</div>
                            <div class="ticket-detail-value">${ticket.transportName || '—'}</div>
                        </div>
                        <div class="ticket-detail-item">
                            <div class="ticket-detail-label">Departure</div>
                            <div class="ticket-detail-value">⏱️ ${depTime}</div>
                        </div>
                        <div class="ticket-detail-item">
                            <div class="ticket-detail-label">Fare</div>
                            <div class="ticket-detail-value price">${ticket.price ? parseFloat(ticket.price).toFixed(3) + ' TND' : '—'}</div>
                        </div>
                    </div>

                    <div class="ticket-footer">
                        <div class="text-small" style="color:rgba(26,26,26,0.6);font-weight:600;">Issued: ${issuedAt}</div>
                        ${isValid
                            ? `<button class="btn btn-danger text-small text-bold" data-action="cancel-ticket" data-id="${ticket.idTicket}"
                                       style="padding:8px 20px;"><span class="btn-text">Cancel Booking</span></button>`
                            : `<span class="text-small text-bold" style="color:rgba(26,26,26,0.65);
                                           background:rgba(0,0,0,0.04);padding:6px 12px;border-radius:6px;">Cancelled</span>`}
                    </div>
                </div>
            </div>`;
        };

        const validCards = validTickets.length > 0
            ? validTickets.map(t => renderTicketCard(t, true)).join('')
            : `<div class="ticket-card text-center" style="padding:60px 20px;">
                   <div class="mb-16" style="font-size:4rem;">&#x1F3AB;</div>
                   <h3 style="color:var(--text-dark);">No Active Tickets</h3>
                   <p class="mb-24" style="color:rgba(26, 26, 26, 0.6);">Book a route to get your digital boarding pass.</p>
                   <a href="#transport" class="btn btn-primary" style="padding:12px 30px;"><span class="btn-text">Browse Routes</span></a>
               </div>`;

        const cancelledSection = cancelledTickets.length > 0 ? `
            <div style="margin-top:3rem;">
                <h3 class="flex gap-8 mb-24 flex-center" style="color:var(--secondary-grey);font-size:1rem;text-transform:uppercase;letter-spacing:2px;">
                    <span style="height:1px;background:var(--secondary-grey);flex:1;opacity:0.3;"></span>
                    Cancelled Bookings
                    <span style="height:1px;background:var(--secondary-grey);flex:1;opacity:0.3;"></span>
                </h3>
                <div class="flex-column gap-24">
                    ${cancelledTickets.map(t => renderTicketCard(t, false)).join('')}
                </div>
            </div>` : '';

        return `
            <div class="flex-between mb-24 flex-wrap gap-16">
                <div>
                    <h3 class="mb-0" style="font-size:1.5rem;">My Boarding Passes</h3>
                    <p class="mb-0" style="opacity:0.6;font-size:1rem;font-weight:500;">${validTickets.length} active ticket${validTickets.length !== 1 ? 's' : ''}</p>
                </div>
            </div>
            <div class="flex-column gap-32">${validCards}</div>
            ${cancelledSection}
        `;
    },

    _getEnrollmentsHtml(programs = [], enrollments = []) {
        if (enrollments.length === 0) {
            return `
                <div class="text-center" style="padding:60px 20px;border:2px dashed var(--border-main);border-radius:16px;">
                    <i class="bi bi-mortarboard mb-16" style="font-size:3rem;opacity:0.3;display:block;"></i>
                    <h3 class="mb-8">No Enrollments</h3>
                    <p class="mb-24" style="opacity:0.6;">You haven't enrolled in any programs yet.</p>
                    <a href="#programs" class="btn btn-primary" style="text-decoration:none;">Browse Programs</a>
                </div>
            `;
        }

        const cards = enrollments.map(e => {
            const prog = programs.find(p => p.id == e.program_id);
            if (!prog) return '';
            
            const statusColor = e.status === 'confirmed' ? '#10b981' : (e.status === 'pending' ? '#f59e0b' : '#ef4444');
            
            return `
                <div class="editorial-card reveal" style="padding:1.5rem; margin-bottom:1rem;">
                    <div class="flex-between">
                        <div>
                            <span class="category-badge">${prog.category}</span>
                            <h4 class="mb-8" style="margin-top:0.5rem;">${prog.title}</h4>
                            <div class="text-small opacity-7">Enrolled on: ${new Date(e.created_at).toLocaleDateString()}</div>
                        </div>
                        <span class="status-pill" style="color:${statusColor}; border-color:${statusColor}44; background:${statusColor}11;">
                            ${e.status.toUpperCase()}
                        </span>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="flex-between mb-24 flex-wrap gap-16">
                <h3 class="mb-0" style="font-size:1.5rem;">My Program Enrollments</h3>
                <a href="#programs" class="btn btn-primary" style="text-decoration:none;padding:0.6rem 1.2rem;">+ Join Program</a>
            </div>
            <div class="flex-column gap-16">${cards}</div>
        `;
    },

    _getPostsHtml(posts = []) {
        if (posts.length === 0) {
            return `
                <div class="text-center" style="padding:60px 20px;border:2px dashed var(--border-main);border-radius:16px;">
                    <i class="bi bi-chat-square-text mb-16" style="font-size:3rem;opacity:0.3;display:block;"></i>
                    <h3 class="mb-8">No Forum Posts</h3>
                    <p class="mb-24" style="opacity:0.6;">You haven't shared anything on the forum yet.</p>
                    <a href="forum.php" class="btn btn-primary" style="text-decoration:none;">Visit Forum</a>
                </div>
            `;
        }

        const cards = posts.map(p => {
            const statusColor = p.status === 'open' ? '#10b981' : '#6b7280';
            return `
                <div class="editorial-card reveal" style="padding:1.5rem; margin-bottom:1rem;">
                    <div class="flex-between">
                        <div>
                            <span class="category-badge" style="background:rgba(58,134,255,0.1); color:var(--accent-blue);">${p.category}</span>
                            <h4 class="mb-8" style="margin-top:0.5rem;">${p.title}</h4>
                            <div class="text-small opacity-7">Posted: ${new Date(p.created_at).toLocaleDateString()}</div>
                        </div>
                        <span class="status-pill" style="color:${statusColor}; border-color:${statusColor}44; background:${statusColor}11;">
                            ${p.status.toUpperCase()}
                        </span>
                    </div>
                </div>
            `;
        }).join('');

        return `
            <div class="flex-between mb-24 flex-wrap gap-16">
                <h3 class="mb-0" style="font-size:1.5rem;">My Forum Activity</h3>
                <a href="forum.php" class="btn btn-primary" style="text-decoration:none;padding:0.6rem 1.2rem;">New Post</a>
            </div>
            <div class="flex-column gap-16">${cards}</div>
        `;
    },

    renderDashboard(user, { requests, appointments, tickets, programs, enrollments, posts }) {
        const reqHtml = this._getRequestsHtml(requests);
        const apptHtml = this._getAppointmentsHtml(appointments);
        const ticketHtml = this._getTicketsHtml(tickets, user);
        const enrollHtml = this._getEnrollmentsHtml(programs, enrollments);
        const postHtml = this._getPostsHtml(posts);

        this.app.innerHTML = `
            <section class="portal-grid">
                <div class="grid-cell span-3 header-cell reveal no-bg no-border no-shadow flex-between flex-center" style="padding:0;">
                    <h2 class="kinetic-text mb-0" style="font-size:2.5rem;">Citizen Dashboard</h2>
                </div>
                
                <div class="grid-cell span-3 dashboard-tabs reveal no-bg no-border no-shadow flex" style="padding:0; overflow-x:auto;">
                    <button class="btn btn-primary dashboard-tab-btn active" data-target="tab-tickets">
                        <span class="btn-text"><i class="bi bi-ticket-perforated"></i> Tickets</span>
                    </button>
                    <button class="btn dashboard-tab-btn" data-target="tab-appointments">
                        <span class="btn-text"><i class="bi bi-calendar-check"></i> Appointments</span>
                    </button>
                    <button class="btn dashboard-tab-btn" data-target="tab-requests">
                        <span class="btn-text"><i class="bi bi-file-text"></i> Requests</span>
                    </button>
                    <button class="btn dashboard-tab-btn" data-target="tab-enrollments">
                        <span class="btn-text"><i class="bi bi-mortarboard"></i> Enrollments</span>
                    </button>
                    <button class="btn dashboard-tab-btn" data-target="tab-posts">
                        <span class="btn-text"><i class="bi bi-chat-square-text"></i> My Posts</span>
                    </button>
                </div>

                <div id="tab-tickets" class="grid-cell span-3 dashboard-tab-content reveal active">
                    ${ticketHtml}
                </div>
                <div id="tab-appointments" class="grid-cell span-3 dashboard-tab-content reveal" style="display:none;">
                    ${apptHtml}
                </div>
                <div id="tab-requests" class="grid-cell span-3 dashboard-tab-content reveal" style="display:none;">
                    ${reqHtml}
                </div>
                <div id="tab-enrollments" class="grid-cell span-3 dashboard-tab-content reveal" style="display:none;">
                    ${enrollHtml}
                </div>
                <div id="tab-posts" class="grid-cell span-3 dashboard-tab-content reveal" style="display:none;">
                    ${postHtml}
                </div>
            </section>
        `;

        const tabBtns = document.querySelectorAll('.dashboard-tab-btn');
        const contents = document.querySelectorAll('.dashboard-tab-content');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                tabBtns.forEach(b => b.classList.remove('btn-primary'));
                btn.classList.add('btn-primary');

                const targetId = btn.getAttribute('data-target');
                contents.forEach(content => {
                    if (content.id === targetId) {
                        content.style.display = 'flex'; // grid-cells are flex column by default in CSS
                        content.classList.add('tab-fade-in');
                        if (targetId === 'tab-tickets') {
                            setTimeout(() => {
                                window.dispatchEvent(new Event('resize'));
                            }, 50);
                        }
                    } else {
                        content.style.display = 'none';
                        content.classList.remove('tab-fade-in');
                    }
                });
            });
        });

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
                el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:rgba(26,26,26,0.6);font-size:0.9rem;">No route coordinates available.</div>';
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








