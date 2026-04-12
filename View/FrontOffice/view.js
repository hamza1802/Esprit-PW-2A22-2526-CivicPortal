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
        const backofficeBtn = (role === 'admin' || role === 'agent') ? `<a href="index.php?page=back_users_list" class="nav-btn nav-btn-outlined">BACKOFFICE</a>` : '';
        const links = `
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#programs">programs</a></li>
                <li><a href="#service-requests">service requests</a></li>
                <li><a href="#grievances">grievances</a></li>
                <li><a href="#transport">transport</a></li>
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="nav-actions">
                ${backofficeBtn}
                <button class="nav-btn nav-btn-filled" data-action="logout-btn">CITIZEN</button>
            </div>
        `;
        nav.innerHTML = links;
    },

    renderHome(user) {
        const content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>CivicPortal</h1>
                    <p>Welcome, ${user.name}. Manage your services, programs, transport, grievances, and profile all in one secure dashboard.</p>
                    <div class="search-container">
                        <input type="text" class="search-bar" placeholder="Search services, programs, transport...">
                        <button class="search-btn" onclick="alert('Search simulated!')">Search</button>
                    </div>
                </section>
            </div>
            <section class="page-container">
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Service Requests</h3>
                        <p>Submit requests for permits, certificates, inspections, and municipal services.</p>
                        <a href="#service-requests" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Submit Request</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Programs</h3>
                        <p>Join local programs tailored to your community and interests.</p>
                        <a href="#programs" class="btn" style="align-self: flex-start; margin-top: auto;">View Programs</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Transport</h3>
                        <p>Check local transportation services and plan your next commute.</p>
                        <a href="#transport" class="btn" style="align-self: flex-start; margin-top: auto;">View Transport</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Grievances</h3>
                        <p>Submit complaints or concerns about municipal services and issues.</p>
                        <a href="#grievances" class="btn" style="align-self: flex-start; margin-top: auto;">Submit Grievance</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>Profile</h3>
                        <p>Edit your profile in one click and keep all your important information.</p>
                        <a href="#profile" class="btn" style="align-self: flex-start; margin-top: auto;">View Profile</a>
                    </div>
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
        const avatarSrc = user.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=3A86FF&color=ffffff&size=256`;
        const profileFields = `
            ${user.bio ? `<p><strong>Bio:</strong> ${user.bio}</p>` : ''}
            ${user.phoneNumber ? `<p><strong>Phone:</strong> ${user.phoneNumber}</p>` : ''}
            ${user.dateOfBirth ? `<p><strong>Date of Birth:</strong> ${user.dateOfBirth}</p>` : ''}
        `;

        const profileSummary = editMode ? '' : `
            <div class="profile-summary reveal">
                <h3>Information</h3>
                <p><strong>Email:</strong> ${user.email}</p>
                ${profileFields}
            </div>
        `;

        const editForm = `
            <div class="form-card reveal">
                <h3>Edit Profile</h3>
                <form id="profile-form">
                    <div class="form-group">
                        <label for="profile-name">Full Name</label>
                        <input type="text" id="profile-name" name="name" value="${user.name}" required pattern="[A-Za-zÀ-ÿ '\-]+" title="Name cannot contain numbers.">
                    </div>
                    <div class="form-group">
                        <label for="profile-email">Email</label>
                        <input type="email" id="profile-email" name="email" value="${user.email}" required>
                    </div>
                    <div class="form-group">
                        <label for="profile-bio">Biography</label>
                        <textarea id="profile-bio" name="bio" rows="4" placeholder="Add a biography...">${user.bio || ''}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="profile-phone">Phone</label>
                        <input type="text" id="profile-phone" name="phoneNumber" value="${user.phoneNumber || ''}">
                    </div>
                    <div class="form-group">
                        <label for="profile-dob">Date of Birth</label>
                        <input type="date" id="profile-dob" name="dateOfBirth" value="${user.dateOfBirth || ''}">
                    </div>
                    <div class="form-group">
                        <label for="profile-avatar">Profile Photo</label>
                        <input type="file" id="profile-avatar" name="avatar" accept="image/*">
                    </div>
                    <div class="form-group" style="display:flex; gap:1rem; flex-wrap:wrap;">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <button type="button" class="btn btn-secondary" data-action="toggle-profile-edit">Cancel</button>
                    </div>
                </form>
            </div>
        `;

        this.app.innerHTML = `
            <section class="page-container profile-page">
                <div class="profile-banner reveal"></div>
                <div class="profile-card reveal">
                    <div class="profile-sidebar">
                        <img class="profile-avatar" src="${avatarSrc}" alt="${user.name}">
                        <h2>${user.name}</h2>
                        <p class="profile-role">${user.role.charAt(0).toUpperCase() + user.role.slice(1)}</p>
                        <div class="profile-stats">
                            <div>
                                <strong>Programs</strong>
                                <span>${user.programs || 4}</span>
                            </div>
                            <div>
                                <strong>Requests</strong>
                                <span>${user.requests || 2}</span>
                            </div>
                        </div>
                        <button class="btn btn-primary" data-action="toggle-profile-edit">${editMode ? 'Close' : 'Edit Profile'}</button>
                    </div>
                    <div class="profile-main">
                        ${editMode ? editForm : profileSummary}
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderProgramCatalog(programs = [], enrollments = []) {
        const programItems = (programs || []).map(program => {
            const isEnrolled = enrollments.includes(program.id);
            return `
                <div class="editorial-card reveal">
                    <h3>${program.name || 'Program'}</h3>
                    <p>${program.description || 'No description available'}</p>
                    <button class="btn ${isEnrolled ? 'btn-secondary' : 'btn-primary'}" data-action="enroll" data-id="${program.id}">
                        ${isEnrolled ? 'Enrolled' : 'Enroll Now'}
                    </button>
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
                            <input type="text" id="name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Friend Email</label>
                            <input type="text" id="email" name="email" required>
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
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Submit a Service Request</h2>
                <div class="form-card reveal">
                    <form id="service-request-form">
                        <div class="form-group">
                            <label for="service-type">Service Type</label>
                            <select id="service-type" name="type" required>
                                <option value="">-- Select a Service --</option>
                                <option value="permit">Permit Request</option>
                                <option value="certificate">Certificate</option>
                                <option value="inspection">Inspection</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="request-details">Details</label>
                            <textarea id="request-details" name="details" rows="6" placeholder="Describe your service request..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">SUBMIT REQUEST</button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderComplaintForm() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Submit a Grievance</h2>
                <div class="form-card reveal">
                    <form id="complaint-form">
                        <div class="form-group">
                            <label for="complaint-subject">Subject</label>
                            <input type="text" id="complaint-subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="complaint-body">Details</label>
                            <textarea id="complaint-body" name="body" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">SUBMIT GRIEVANCE</button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    }
};

export default view;
