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
        const links = `
            <div class="nav-brand">
                CivicPortal
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#programs">programs</a></li>
                <li><a href="#request-service">service requests</a></li>
                <li><a href="#complaints">grievances</a></li>
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls">
                <div class="user-role-badge">Citizen</div>
            </div>
        `;
        nav.innerHTML = links;
    },

    renderHome(user) {
        const content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>CivicPortal</h1>
                    <p>Welcome back, ${user.name}. Navigate municipal services with unmatched clarity and precision.</p>
                    <div class="search-container">
                        <input type="text" class="search-bar" placeholder="search services, programs, documents...">
                        <button class="search-btn" onclick="alert('Search simulated!')">Search</button>
                    </div>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">Directory of Services</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>Document Submission</h3>
                        <p>Submit critical civil documents securely online. Ensure civic records are updated without the need for physical visitation.</p>
                        <a href="#request-service" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Start Filing</a>
                    </div>
                    
                    <div class="editorial-card reveal">
                        <h3>Community Programs</h3>
                        <p>Engage with local initiatives. Our Parks & Recreation catalog lists the latest activities sponsored by the city.</p>
                        <a href="#programs" class="btn" style="align-self: flex-start; margin-top: auto;">View Catalog</a>
                    </div>

                    <div class="editorial-card reveal">
                        <h3>Grievances & Feedback</h3>
                        <p>Your voice matters. Submit complaints or feedback directly to the administration for review.</p>
                        <a href="#complaints" class="btn" style="align-self: flex-start; margin-top: auto;">Submit Grievance</a>
                    </div>
                </div>
            </section>
        `;
        this.app.innerHTML = content;
        this.triggerObserver();
    },

    renderProgramCatalog(programs, userEnrollments) {
        const programCards = programs.map((p) => {
            const isEnrolled = userEnrollments.some(e => e.program_id == p.id);
            return `
                <div class="program-card reveal">
                    <div class="program-img-wrapper">
                        <img src="../assets/images/${p.image || 'default.jpg'}" alt="${p.title}" class="program-img" onerror="this.src=''; this.style.backgroundColor='var(--primary-navy)';">
                    </div>
                    <div class="card-content">
                        <span class="category-badge">${p.category}</span>
                        <h3>${p.title}</h3>
                        <p>${p.description}</p>
                        <button class="btn ${isEnrolled ? 'btn-success' : 'btn-primary'}" 
                                style="width: 100%"
                                data-id="${p.id}" 
                                data-action="enroll"
                                ${isEnrolled ? 'disabled' : ''}>
                            ${isEnrolled ? 'ENROLLED' : 'ENROLL'}
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 2rem;">
                    <h2 class="reveal" style="margin:0;">Programs Catalog</h2>
                    <div class="filter-controls reveal" style="display:flex; gap:1rem; flex-wrap: wrap;">
                        <input type="text" id="prog-search" placeholder="Search by title..." style="flex-grow: 1; padding: 1rem; border: var(--border-main); background: transparent; font-family: inherit; font-size: 1.1rem; font-weight: 600; color: var(--primary-navy); outline: none;">
                        <select id="prog-filter-cat" style="padding: 1rem 2rem 1rem 1rem; border: var(--border-main); background: transparent; font-family: inherit; font-size: 1.1rem; font-weight: 600; color: var(--primary-navy); outline: none; cursor: pointer;">
                            <option value="">All Categories</option>
                            <option value="Arts">Arts</option>
                            <option value="Sports">Sports</option>
                            <option value="Environment">Environment</option>
                        </select>
                    </div>
                </div>
                <div class="editorial-grid" id="program-list">
                    ${programCards}
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderServiceRequestForm() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">File a Request</h2>
                <div class="form-card reveal">
                    <form id="service-request-form">
                        <div class="form-group reveal">
                            <label for="request-type">Service Type</label>
                            <select id="request-type" name="type" required>
                                <option value="Birth Certificate">Birth Certificate</option>
                                <option value="ID Card Renewal">ID Card Renewal</option>
                                <option value="Residence Certificate">Residence Certificate</option>
                                <option value="Building Permit">Building Permit</option>
                            </select>
                        </div>
                        <div class="form-group reveal">
                            <label for="document-upload">Upload Documents (PDF/JPG)</label>
                            <input type="file" id="document-upload" name="file" required style="border:none; padding:1.5rem 0;">
                        </div>
                        <button type="submit" class="btn btn-primary reveal" style="width: 100%;">SUBMIT REQUEST</button>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderProfile(user) {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal">Account Profile</h2>
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
