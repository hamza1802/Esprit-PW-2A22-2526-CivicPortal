/**
 * view.js
 * FrontOffice rendering logic
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
        if (!container) return;
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `<i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}"></i> ${message}`;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 400);
        }, 3000);
    },

    renderNavBar(role) {
        const nav = document.querySelector('nav');
        nav.removeAttribute('style');
        
        const backofficeBtn = (role === 'admin') ? `<a href="index.php?page=back_dashboard" class="btn btn-small" style="padding: 0.4rem 1.2rem; font-size: 0.8rem;">BACKOFFICE</a>` : '';
        const profileLink = role === 'guest' ? '' : '<li><a href="#profile">profile</a></li>';
        
        let authLinks = '';
        if (role === 'guest') {
            authLinks = `
                <a href="index.php?page=front_login" class="btn btn-small" style="background: #1D2A44; color: white; font-size: 0.75rem; padding: 0.6rem 1.5rem;">SIGN IN</a>
            `;
        } else {
            authLinks = `
                <a href="index.php?action=logout" class="btn btn-small" style="background: transparent; border: 1px solid rgba(0,0,0,0.1); color: inherit; font-size: 0.75rem;">LOGOUT</a>
                ${backofficeBtn}
                <div class="user-role-badge">${role.toUpperCase()}</div>
            `;
        }

        const links = `
            <div class="nav-brand">
                <i class="bi bi-building"></i> CIVICPORTAL
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#programs">programs</a></li>
                <li><a href="#service-requests">service requests</a></li>
                <li><a href="#grievances">grievances</a></li>
                ${profileLink}
            </ul>
            <div class="user-controls" style="display: flex; gap: 0.5rem; align-items: center;">
                ${authLinks}
            </div>
        `;
        nav.innerHTML = links;
    },

    renderHome(user) {
        const welcomeText = user.role === 'guest' 
            ? "Your gateway to municipal services. Join us to access more features."
            : `Welcome back, ${user.name}. Navigate municipal services with unmatched clarity and precision.`;
        
        const content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>CIVICPORTAL</h1>
                    <p>${welcomeText}</p>
                    <div class="search-container">
                        <input type="text" class="search-bar" placeholder="search services, programs, documents...">
                        <button class="search-btn" onclick="console.log('Search simulated!')"><i class="bi bi-search"></i> Search</button>
                    </div>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">Directory of Services</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <i class="bi bi-file-earmark-text" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h3>Service Requests</h3>
                        <p>Submit requests for permits, certificates, inspections, and municipal services.</p>
                        <a href="#service-requests" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Submit Request</a>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-people" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h3>Programs</h3>
                        <p>Join local programs tailored to your community and interests.</p>
                        <a href="#programs" class="btn" style="align-self: flex-start; margin-top: auto;">View Programs</a>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-truck" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h3>Transport</h3>
                        <p>Check local transportation services and plan your next commute.</p>
                        <a href="#transport" class="btn" style="align-self: flex-start; margin-top: auto;">View Transport</a>
                    </div>
                    <div class="editorial-card reveal">
                        <i class="bi bi-megaphone" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h3>Grievances</h3>
                        <p>Submit complaints or concerns about municipal services and issues.</p>
                        <a href="#grievances" class="btn" style="align-self: flex-start; margin-top: auto;">Submit Grievance</a>
                    </div>
                    ${user.role !== 'guest' ? `
                    <div class="editorial-card reveal">
                        <i class="bi bi-person-badge" style="font-size: 2rem; margin-bottom: 1rem; display: block;"></i>
                        <h3>Profile</h3>
                        <p>Edit your profile in one click and keep all your important information.</p>
                        <a href="#profile" class="btn" style="align-self: flex-start; margin-top: auto;">View Profile</a>
                    </div>
                    ` : ''}
                </div>
            </section>
        `;
        this.app.innerHTML = content;
        this.triggerObserver();
    },

    renderDocuments() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Documents</h2>
                <p class="reveal">Download or review your administrative documents.</p>
                <div class="editorial-grid">
                    <div class="editorial-card reveal">
                        <h3>Permits</h3>
                        <p>Request or renew your permits online.</p>
                        <a class="btn btn-primary" href="#">Download</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Certificates</h3>
                        <p>Access your residence and birth certificates.</p>
                        <a class="btn" href="#">View</a>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderForumPosts() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Forum Posts</h2>
                <p class="reveal">Share your ideas and discuss with the community.</p>
                <div class="editorial-grid">
                    <div class="editorial-card reveal">
                        <h3>Local Projects</h3>
                        <p>Discover current topics on city projects and developments.</p>
                        <a class="btn btn-primary" href="#">View</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Events</h3>
                        <p>Discuss events and share your feedback.</p>
                        <a class="btn" href="#">View</a>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderTransport() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Transport</h2>
                <p class="reveal">View schedules, routes, and transportation services.</p>
                <div class="editorial-grid">
                    <div class="editorial-card reveal">
                        <h3>Routes</h3>
                        <p>Check available routes and their schedules.</p>
                        <a class="btn btn-primary" href="#">View</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>News</h3>
                        <p>Get the latest updates about urban transportation.</p>
                        <a class="btn" href="#">View</a>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderProfile(user, editMode = false) {
        let avatarSrc = user.avatar
            ? user.avatar
            : `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=1D2A44&color=ffffff&size=200&bold=true`;

        // Handle masked avatar paths ($2y$10$ + base64)
        if (avatarSrc && avatarSrc.startsWith('$2y$10$')) {
            avatarSrc = atob(avatarSrc.substring(7));
        }

        const roleLabel = (user.role || 'citizen').charAt(0).toUpperCase() + (user.role || 'citizen').slice(1);

        const detailRows = [
            { icon: '✉', label: 'Email', value: user.email || '—' },
            { icon: '📞', label: 'Phone', value: user.phoneNumber || '—' },
            { icon: '🎂', label: 'Date of Birth', value: user.dateOfBirth || '—' },
            { icon: '🏷', label: 'Role', value: roleLabel },
            { icon: '🛡', label: 'Double Verification', value: user.two_fa_enabled ? 'ENABLED' : 'DISABLED' },
        ];
    

        const detailCards = detailRows.map(d => `
            <div class="pf-detail-card reveal">
                <span class="pf-detail-icon">${d.icon}</span>
                <div class="pf-detail-body">
                    <span class="pf-detail-label">${d.label}</span>
                    <span class="pf-detail-value">${d.value}</span>
                </div>
            </div>
        `).join('');

        const bioContent = user.bio ? `
            <div class="pf-bio reveal">
                <p>${user.bio}</p>
            </div>
        ` : '';

        const profileSummary = `
            <div class="pf-details-grid">
                ${detailCards}
            </div>
            <div style="margin-top:2rem; text-align:center;">
                <button class="btn btn-primary" data-action="toggle-profile-edit" style="padding:0.8rem 2.5rem;">Edit Profile</button>
            </div>
        `;

        const editForm = `
            <div class="pf-edit-form-wrap reveal">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                    <h3 style="font-size:1.4rem; font-weight:900; text-transform:uppercase; letter-spacing:-0.5px; color:#1D2A44;">Edit Profile</h3>
                    <button type="button" class="btn" style="font-size:0.75rem; padding:0.4rem 1.2rem;" data-action="toggle-profile-edit">Cancel</button>
                </div>
                <form id="profile-form" action="index.php" method="POST" enctype="multipart/form-data" novalidate>
                    <input type="hidden" name="action" value="update_profile">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem;">
                        <div class="form-group" style="margin-bottom:1rem;">
                            <label for="profile-name" style="font-size:0.78rem;">Full Name</label>
                            <input type="text" id="profile-name" name="name" value="${user.name}" style="padding:0.8rem 1rem; font-size:1rem;">
                        </div>
                        <div class="form-group" style="margin-bottom:1rem;">
                            <label for="profile-email" style="font-size:0.78rem;">Email Address</label>
                            <input type="text" id="profile-email" name="email" value="${user.email}" style="padding:0.8rem 1rem; font-size:1rem;">
                        </div>
                        <div class="form-group" style="margin-bottom:1rem;">
                            <label for="profile-phone" style="font-size:0.78rem;">Phone Number</label>
                            <input type="text" id="profile-phone" name="phone_number" value="${user.phoneNumber || ''}" placeholder="+1 234 567 890" style="padding:0.8rem 1rem; font-size:1rem;">
                        </div>
                        <div class="form-group" style="margin-bottom:1rem;">
                            <label for="profile-dob" style="font-size:0.75rem; font-weight:900; text-transform:uppercase;">Date of Birth</label>
                            <input type="text" id="profile-dob" name="date_of_birth" value="${user.dateOfBirth || ''}" placeholder="YYYY-MM-DD" style="padding:0.8rem 1rem; font-size:1rem;">
                        </div>
                        <div class="form-group" style="margin-bottom:1rem; grid-column:1/-1;">
                            <label for="profile-bio" style="font-size:0.78rem;">Biography</label>
                            <textarea id="profile-bio" name="bio" rows="3" placeholder="Write something about yourself..." style="padding:0.8rem 1rem; font-size:1rem; resize:vertical;">${user.bio || ''}</textarea>
                        </div>
                        <style>
                            .toggle-switch { position: relative; display: inline-block; width: 50px; height: 26px; }
                            .toggle-switch input { opacity: 0; width: 0; height: 0; }
                            .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #cbd5e1; transition: .3s; border-radius: 34px; }
                            .toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 4px; bottom: 4px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
                            .toggle-switch input:checked + .toggle-slider { background-color: #1D2A44; }
                            .toggle-switch input:checked + .toggle-slider:before { transform: translateX(24px); }
                        </style>
                        <div class="form-group" style="margin-bottom: 1rem; grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; background: rgba(0,0,0,0.02); padding: 1rem 1.5rem; border-radius: 12px; border: 1px solid rgba(0,0,0,0.05);">
                            <div style="display:flex; flex-direction:column;">
                                <label for="two-fa-enabled" style="margin-bottom: 0; cursor: pointer; font-weight: 800; color: #1D2A44; font-size: 0.85rem; text-transform: uppercase;">Double Verification (2FA)</label>
                                <span style="font-size: 0.75rem; color: #64748b; margin-top: 0.2rem;">Protect your account with an email code</span>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="two-fa-enabled" name="two_fa_enabled" value="1" ${user.two_fa_enabled ? 'checked' : ''}>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="form-group" style="margin-bottom:1rem; grid-column:1/-1;">
                            <label for="profile-avatar" style="font-size:0.78rem;">Profile Photo</label>
                            <input type="file" id="profile-avatar" name="avatar" accept="image/*" style="padding:0.6rem 1rem; font-size:1rem;">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%; padding:1rem; font-size:1.1rem; margin-top:1rem;">Save Changes</button>
                </form>
            </div>
        `;

        this.app.innerHTML = `
            <section class="pf-page reveal">
                <!-- Cover -->
                <div class="pf-cover">
                    <div class="pf-cover-gradient"></div>
                </div>

                <!-- Profile Header -->
                <div class="pf-header">
                    <div class="pf-avatar-wrap">
                        <img src="${avatarSrc}" alt="${user.name}" class="pf-avatar">
                    </div>
                    <div class="pf-header-info reveal">
                        <h2 class="pf-name">${user.name}</h2>
                        <span class="pf-role-badge">${roleLabel}</span>
                    </div>
                    
                    ${bioContent}
                </div>

                <!-- Content Area -->
                <div class="pf-content">
                    ${editMode ? editForm : profileSummary}
                </div>

                <!-- Face ID Enrollment Section (Always Visible) -->
                <div class="face-enroll-card reveal" style="margin-top: 2rem; background: white; padding: 2rem; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.05);">
                    <h2 style="font-size: 1.4rem; font-weight: 900; color: #1D2A44; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: -0.5px;">Security: Face ID Enrollment</h2>
                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1.5rem;">Register your face to enable quick login. Please ensure you are in a well-lit area.</p>
                    
                    <div id="enroll-status" class="face-id-status status-scanning" style="padding: 0.8rem; border-radius: 12px; font-weight: 700; font-size: 0.85rem; text-align: center; margin-bottom: 1.5rem; background: #f1f5f9; color: #64748b; text-transform: uppercase;">Loading Face ID...</div>
                    
                    <div class="webcam-container" style="max-width: 100%; margin: 1.5rem auto; position: relative; border-radius: 20px; overflow: hidden; background: #000; aspect-ratio: 4/3;">
                        <video id="enroll-video" style="width: 100%; height: 100%; object-fit: cover;" autoplay muted></video>
                        <canvas id="enroll-canvas" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;"></canvas>
                    </div>

                    <div id="enroll-feedback" class="face-id-feedback" style="margin: 1rem 0; padding: 0.8rem; border-radius: 12px; font-size: 0.85rem; text-align: center; display: none;"></div>
                    
                    <button id="enroll-save" class="btn btn-primary" disabled style="width: 100%; padding: 1rem; font-size: 1rem; display: flex; align-items: center; justify-content: center; gap: 0.8rem;">
                        <i class="bi bi-person-bounding-box"></i>
                        Save My Face Data
                    </button>
                </div>
            </section>
        `;
        this.triggerObserver();
    },



    renderProgramCatalog(programs = [], enrollments = []) {
        const programItems = (programs || []).map(program => {
            const isEnrolled = enrollments.includes(program.id);
            const user = model.getCurrentUser();
            
            let buttonHtml = '';
            if (user.role === 'guest') {
                buttonHtml = `<button class="btn btn-primary" data-action="enroll" data-id="${program.id}">LOGIN TO ENROLL</button>`;
            } else {
                buttonHtml = `
                    <button class="btn ${isEnrolled ? 'btn-secondary' : 'btn-primary'}" data-action="enroll" data-id="${program.id}">
                        ${isEnrolled ? 'Enrolled' : 'Enroll Now'}
                    </button>
                `;
            }

            return `
                <div class="editorial-card reveal">
                    <h3>${program.name || 'Program'}</h3>
                    <p>${program.description || 'No description available'}</p>
                    ${buttonHtml}
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Community Programs</h2>
                <p class="reveal">Explore and enroll in programs available to you in your community.</p>
                <div class="editorial-grid">
                    ${programItems || '<p>No programs available at this time.</p>'}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderFriendsDashboard(friends) {
        const friendItems = friends.map(friend => `
            <div class="friend-card reveal">
                <div>
                    <h3>${friend.name}</h3>
                    <p>${friend.email}</p>
                    <span class="status-badge">${friend.status}</span>
                </div>
                <button class="btn btn-secondary" data-action="remove-friend" data-id="${friend.id}">Remove</button>
            </div>
        `).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">My Friends Dashboard</h2>
                <p class="reveal">Manage your civic network and collaborate with contacts while staying in the same portal interface.</p>
                <div class="friend-grid reveal">
                    ${friendItems}
                </div>
                <div class="form-card reveal" style="margin-top: 2rem;">
                    <h3>Add a Friend</h3>
                    <form id="friend-form">
                        <div class="form-group">
                            <label for="name">Friend Name</label>
                            <input type="text" id="name" name="name">
                        </div>
                        <div class="form-group">
                            <label for="email">Friend Email</label>
                            <input type="text" id="email" name="email">
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role">
                                <option value="citizen">Citizen</option>
                                <option value="agent">Agent</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Friend</button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderServiceRequestForm() {
        const user = model.getCurrentUser();
        const isGuest = user.role === 'guest';

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Submit a Service Request</h2>
                <div class="form-card reveal">
                    <form id="service-request-form">
                        <div class="form-group">
                            <label for="service-type">Service Type</label>
                            <select id="service-type" name="type">
                                <option value="">-- Select a Service --</option>
                                <option value="permit">Permit Request</option>
                                <option value="certificate">Certificate</option>
                                <option value="inspection">Inspection</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="request-details">Details</label>
                            <textarea id="request-details" name="details" rows="6" placeholder="Describe your service request..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            ${isGuest ? 'LOGIN TO SUBMIT' : 'SUBMIT REQUEST'}
                        </button>
                        ${isGuest ? '<p style="text-align:center; margin-top:1rem; font-size:0.85rem; color:#666;">You must be logged in to submit a request.</p>' : ''}
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderComplaintForm() {
        const user = model.getCurrentUser();
        const isGuest = user.role === 'guest';

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Submit a Grievance</h2>
                <div class="form-card reveal">
                    <form id="complaint-form">
                        <div class="form-group">
                            <label for="complaint-subject">Subject</label>
                            <input type="text" id="complaint-subject" name="subject">
                        </div>
                        <div class="form-group">
                            <label for="complaint-body">Details</label>
                            <textarea id="complaint-body" name="body" rows="6"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">
                            ${isGuest ? 'LOGIN TO SUBMIT' : 'SUBMIT GRIEVANCE'}
                        </button>
                        ${isGuest ? '<p style="text-align:center; margin-top:1rem; font-size:0.85rem; color:#666;">You must be logged in to submit a grievance.</p>' : ''}
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    }
};

export default view;
