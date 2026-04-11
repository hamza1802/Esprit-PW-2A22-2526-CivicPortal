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
                <li><a href="#transport">transport</a></li>
                <li><a href="#profile">profile</a></li>
            </ul>
            <div class="user-controls">
                <a href="../BackOffice/index.php" class="btn btn-small" style="text-decoration:none; border-color: var(--primary-navy);">BackOffice</a>
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
            const isEnrolled = userEnrollments.some(e => e.programId === p.id);
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
                <h2 class="reveal">Programs Catalog</h2>
                <div class="editorial-grid">
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
    },

    renderTransport() {
        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal active">Municipal Transport</h2>
                <p style="margin-bottom: 2rem; max-width: 800px; font-weight: 500; font-size: 1.2rem;">
                    Select your preferred mode of transportation to book tickets securely.
                </p>

                <div class="editorial-grid">
                    <div class="editorial-card reveal active">
                        <h3 style="display:flex; align-items:center; gap:10px;"><span style="font-size: 2.5rem;">✈️</span> Plane</h3>
                        <p>Book domestic or international flights from our central hubs.</p>
                        <a href="#transport_list?type=Plane" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Flights</a>
                    </div>
                    <div class="editorial-card editorial-highlight reveal active">
                        <h3 style="display:flex; align-items:center; gap:10px;"><span style="font-size: 2.5rem;">🚌</span> Bus</h3>
                        <p>Affordable inner-city and inter-city bus routes.</p>
                        <a href="#transport_list?type=Bus" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Bus Routes</a>
                    </div>
                    <div class="editorial-card reveal active">
                        <h3 style="display:flex; align-items:center; gap:10px;"><span style="font-size: 2.5rem;">🚆</span> Train</h3>
                        <p>Fast, reliable train networks.</p>
                        <a href="#transport_list?type=Train" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Train Routes</a>
                    </div>
                    <div class="editorial-card reveal active">
                        <h3 style="display:flex; align-items:center; gap:10px;"><span style="font-size: 2.5rem;">🚇</span> Metro</h3>
                        <p>Rapid underground metro transit.</p>
                        <a href="#transport_list?type=Metro" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Metro Routes</a>
                    </div>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderTransportList(type, trajets, sortBy = 'departure', order = 'ASC') {
        const routeCards = trajets.length === 0 ? 
            `<div class="editorial-card" style="grid-column: 1 / -1; text-align: center; border-right:none;">
                <h3>No Routes Found</h3>
                <p>We couldn't find any active routes for ${type}.</p>
                <a href="#transport" class="btn">Back to Categories</a>
            </div>` : 
            trajets.map(trajet => {
                const isFull = trajet.capacity > 0 && trajet.sold >= trajet.capacity;
                const remaining = trajet.capacity - trajet.sold;
                const pct = trajet.capacity > 0 ? Math.round((trajet.sold / trajet.capacity) * 100) : 0;
                
                return `
                <div class="editorial-card reveal active" style="justify-content: space-between;">
                    <div>
                        <span class="category-badge">${trajet.transportName}</span>
                        <h3 style="font-size: 1.5rem; margin-bottom: 0.5rem; overflow-wrap: break-word;">
                            ${trajet.departure} → ${trajet.destination}
                        </h3>
                        <p style="margin-bottom: 0.5rem; font-weight: 700; color: var(--accent-blue);">
                            ${parseFloat(trajet.price).toFixed(3)} TND
                        </p>
                        <div style="font-size: 0.9rem; margin-bottom: 1.5rem; font-weight: 600;">
                            📅 ${new Date(trajet.departureTime).toLocaleString()}<br><br>
                            🎟️ ${isFull ? '<span style="color:var(--danger)">Sold Out</span>' : `${remaining} seats left (${trajet.sold}/${trajet.capacity})`}
                            <div class="progress-track" style="margin-top: 8px;">
                                <div class="progress-fill" style="width: ${pct}%; ${pct > 80 ? 'background:var(--danger);' : ''}"></div>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: auto;">
                        ${!isFull ? `
                            <form class="book-transport-form" data-id="${trajet.idTrajet}">
                                <div style="display:flex; flex-direction:column; gap:10px;">
                                    <button type="submit" class="btn btn-primary" style="width: 100%;">Book Ticket Automatically</button>
                                </div>
                            </form>
                        ` : `
                            <button disabled class="btn btn-danger" style="width: 100%; opacity: 0.7; cursor: not-allowed; border: none;">Sold Out</button>
                        `}
                    </div>
                </div>`;
            }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem; border-bottom: var(--border-main); padding-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
                    <h2 class="reveal active" style="margin-bottom: 0; border-bottom: none; padding-bottom: 0; display: flex; align-items: center; gap: 10px;">
                        <a href="#transport" style="text-decoration: none; color: var(--secondary-grey);" title="Back">←</a>
                        Routes: ${type}
                    </h2>
                    
                    <form id="sort-transport-form" style="display: flex; gap: 10px; align-items: stretch; flex-wrap: wrap;" data-type="${type}">
                        <select name="sort" class="form-control" style="padding: 0.8rem; border: var(--border-main); background: transparent; font-weight: bold; font-family: inherit; color: var(--primary-navy);">
                            <option value="departure" ${sortBy === 'departure' ? 'selected' : ''}>Sort by Departure</option>
                            <option value="destination" ${sortBy === 'destination' ? 'selected' : ''}>Sort by Destination</option>
                            <option value="price" ${sortBy === 'price' ? 'selected' : ''}>Sort by Price</option>
                        </select>
            
                        <select name="order" class="form-control" style="padding: 0.8rem; border: var(--border-main); background: transparent; font-weight: bold; font-family: inherit; color: var(--primary-navy);">
                            <option value="ASC" ${order === 'ASC' ? 'selected' : ''}>A-Z (Asc)</option>
                            <option value="DESC" ${order === 'DESC' ? 'selected' : ''}>Z-A (Desc)</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.5rem;">Sort</button>
                    </form>
                </div>

                <div class="editorial-grid">
                    ${routeCards}
                </div>
            </section>
        `;
        this.triggerObserver();
    }
};

export default view;
