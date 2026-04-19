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
                <li><a href="#my-tickets">my tickets</a></li>
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

    // ============================================
    // TRANSPORT - Now dynamic from transport_type DB
    // ============================================
    renderTransport(transportTypes = []) {
        let cards = '';
        if (transportTypes.length > 0) {
            cards = transportTypes.map((tt, i) => {
                const isHighlight = i === 0 ? 'editorial-highlight' : '';
                const hasPhoto = tt.photo_url;
                return `
                <div class="editorial-card ${isHighlight} reveal active">
                    ${hasPhoto ? `
                        <div style="width:100%; height:140px; overflow:hidden; border-radius:8px; margin-bottom:1rem;">
                            <img src="../assets/images/${tt.photo_url}" alt="${tt.name}" style="width:100%; height:100%; object-fit:cover;" onerror="this.parentElement.innerHTML='<div style=\\'display:flex;align-items:center;justify-content:center;height:100%;color:var(--secondary-grey);\\'>No Image</div>';">
                        </div>
                    ` : `
                        <div style="width:100%; height:140px; display:flex; align-items:center; justify-content:center; margin-bottom:1rem; background:linear-gradient(135deg, rgba(99,102,241,0.15), rgba(124,58,237,0.1)); border-radius:8px; color:var(--secondary-grey);">
                            No Image Available
                        </div>
                    `}
                    <h3>${tt.name}</h3>
                    <p>${tt.description || 'Book routes for ' + tt.name + ' transport.'}</p>
                    <a href="#transport_list?type=${tt.name}" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">View Routes</a>
                </div>`;
            }).join('');
        } else {
            cards = `
                <div class="editorial-card" style="grid-column: 1 / -1; text-align: center;">
                    <h3>No Transport Methods Available</h3>
                    <p>The municipality has not added any transport types yet. Please check back later.</p>
                </div>
            `;
        }

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal active">Municipal Transport</h2>
                <p style="margin-bottom: 2rem; max-width: 800px; font-weight: 500; font-size: 1.2rem;">
                    Select your preferred mode of transportation to book tickets securely.
                </p>
                <div class="editorial-grid">
                    ${cards}
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
    },

    // ============================================
    // DEDICATED TICKET VIEW
    // ============================================
    renderMyTickets(tickets = [], user) {
        const validTickets = tickets.filter(t => t.status === 'Valid');
        const cancelledTickets = tickets.filter(t => t.status === 'Cancelled');

        const renderTicketCard = (ticket, isExpanded = true) => {
            const isValid = ticket.status === 'Valid';
            const depTime = ticket.departureTime ? new Date(ticket.departureTime).toLocaleString() : '—';
            const issuedAt = ticket.issuedAt ? new Date(ticket.issuedAt).toLocaleDateString() : '—';
            const photoHtml = ticket.typePhoto ? `<img src="../assets/images/${ticket.typePhoto}" alt="${ticket.typeName}" style="width:100px; height:100px; object-fit:cover; border-radius:12px; border:2px solid rgba(255,255,255,0.1);">` : `<div style="width:100px; height:100px; border-radius:12px; background:rgba(255,255,255,0.05); display:flex; align-items:center; justify-content:center; font-size:0.9rem; color:var(--secondary-grey); border:2px solid rgba(255,255,255,0.1);">No Image</div>`;

            return `
            <div class="ticket-card ${!isValid ? 'ticket-cancelled' : ''}" style="
                background: var(--bg-dark, #1a1a2e);
                border: 1px solid ${isValid ? 'rgba(99,102,241,0.5)' : 'rgba(239,68,68,0.2)'};
                border-radius: 16px;
                overflow: hidden;
                transition: all 0.3s ease;
                position: relative;
                box-shadow: ${isValid ? '0 10px 30px -10px rgba(99,102,241,0.3)' : 'none'};
                ${!isValid ? 'opacity: 0.6;' : ''}
            ">
                <!-- Ticket Header Band -->
                <div style="
                    background: ${isValid ? 'linear-gradient(135deg, #6366f1, #7c3aed)' : 'linear-gradient(135deg, #4b5563, #374151)'};
                    padding: 20px 24px;
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                ">
                    <div style="display:flex; align-items:center; gap:20px;">
                        ${isExpanded ? photoHtml : `<span style="font-size:0.9rem; color:var(--secondary-grey);">No Image</span>`}
                        <div>
                            <div style="font-size:0.8rem; text-transform:uppercase; letter-spacing:2px; opacity:0.9; color:#fff; font-weight:600; margin-bottom:4px;">
                                ${ticket.typeName || 'Transport'} Ticket
                            </div>
                            <div style="font-weight:800; font-size:1.4rem; color:#fff; letter-spacing:1px;">
                                ${ticket.ref}
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="
                            background: ${isValid ? 'rgba(34,197,94,0.25)' : 'rgba(239,68,68,0.25)'};
                            color: ${isValid ? '#4ade80' : '#f87171'};
                            padding: 6px 16px;
                            border-radius: 999px;
                            font-size: 0.8rem;
                            font-weight: 800;
                            text-transform: uppercase;
                            letter-spacing: 1px;
                            border: 1px solid ${isValid ? 'rgba(34,197,94,0.3)' : 'rgba(239,68,68,0.3)'};
                        ">${ticket.status}</div>
                    </div>
                </div>

                <!-- Ticket Body -->
                <div style="padding: 24px;">
                    <!-- Route -->
                    <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                        <div style="flex:1; text-align:center;">
                            <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--secondary-grey, #8b8fa3); margin-bottom:6px;">Origin</div>
                            <div style="font-weight:800; font-size:1.2rem; color:#fff;">${ticket.departure || '—'}</div>
                        </div>
                        <div style="font-size:1.5rem; color:var(--accent-blue, #6366f1); flex-shrink:0;">✈️</div>
                        <div style="flex:1; text-align:center;">
                            <div style="font-size:0.75rem; text-transform:uppercase; letter-spacing:1.5px; color:var(--secondary-grey, #8b8fa3); margin-bottom:6px;">Destination</div>
                            <div style="font-weight:800; font-size:1.2rem; color:#fff;">${ticket.destination || '—'}</div>
                        </div>
                    </div>

                    ${isExpanded ? `
                    <!-- Map Component -->
                    <div style="margin:20px 0; border-radius:12px; overflow:hidden; border:2px solid rgba(255,255,255,0.05); position:relative; background:#0f1117;">
                        <div id="map-${ticket.idTicket}" class="ticket-map" data-deplat="${ticket.depLat}" data-deplng="${ticket.depLng}" data-destlat="${ticket.destLat}" data-destlng="${ticket.destLng}" style="width:100%; height:280px;"></div>
                        <div style="position:absolute; bottom:10px; right:10px; background:rgba(0,0,0,0.7); color:#fff; padding:4px 10px; border-radius:6px; font-size:0.7rem; font-weight:600; z-index:10;">Live Route Map</div>
                    </div>
                    ` : ''}

                    <!-- Dashed separator -->
                    <div style="border-top: 2px dashed rgba(255,255,255,0.1); margin: 24px -24px; position:relative;">
                        <div style="position:absolute; left:-12px; top:-12px; width:24px; height:24px; border-radius:50%; background:var(--bg-main, #0f1117);"></div>
                        <div style="position:absolute; right:-12px; top:-12px; width:24px; height:24px; border-radius:50%; background:var(--bg-main, #0f1117);"></div>
                    </div>

                    <!-- Details Grid -->
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:20px;">
                        <div style="background:rgba(255,255,255,0.02); padding:12px; border-radius:8px;">
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:var(--secondary-grey, #8b8fa3); margin-bottom:4px;">Passenger</div>
                            <div style="font-weight:700; font-size:1rem; color:#fff;">${ticket.citizenName}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.02); padding:12px; border-radius:8px;">
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:var(--secondary-grey, #8b8fa3); margin-bottom:4px;">Vehicle info</div>
                            <div style="font-weight:700; font-size:1rem; color:#fff;">${ticket.transportName || '—'}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.02); padding:12px; border-radius:8px;">
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:var(--secondary-grey, #8b8fa3); margin-bottom:4px;">Date & Time</div>
                            <div style="font-weight:700; font-size:1rem; color:#fff;">📅 ${depTime}</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.02); padding:12px; border-radius:8px;">
                            <div style="font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; color:var(--secondary-grey, #8b8fa3); margin-bottom:4px;">Total Fare</div>
                            <div style="font-weight:800; font-size:1.1rem; color:var(--accent-blue, #6366f1);">${ticket.price ? parseFloat(ticket.price).toFixed(3) + ' TND' : '—'}</div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:24px; padding-top:16px; border-top:1px solid rgba(255,255,255,0.05);">
                        <div style="font-size:0.8rem; color:var(--secondary-grey, #8b8fa3); font-weight:600;">
                            Issued: ${issuedAt}
                        </div>
                        ${isValid ? `
                            <button class="btn btn-danger" data-action="cancel-ticket" data-id="${ticket.idTicket}" style="font-size:0.85rem; padding:8px 20px; font-weight:700; display:flex; align-items:center; gap:6px;">
                                <span>🚫</span> Cancel Booking
                            </button>
                        ` : `
                            <span style="font-size:0.85rem; color:var(--secondary-grey, #8b8fa3); font-weight:700; background:rgba(255,255,255,0.05); padding:6px 12px; border-radius:6px;">Cancelled</span>
                        `}
                    </div>
                </div>
            </div>`;
        };

        const validCards = validTickets.length > 0
            ? validTickets.map(t => renderTicketCard(t, true)).join('') // render expanded/large tickets for valid
            : `<div style="grid-column: 1 / -1; text-align:center; padding:60px 20px; background:var(--bg-dark, #1a1a2e); border-radius:16px; border:1px dashed rgba(255,255,255,0.1);">
                    <div style="font-size:4rem; margin-bottom:16px; opacity:0.8;">🎫</div>
                    <h3 style="font-size:1.5rem; margin-bottom:8px;">No Active Tickets Found</h3>
                    <p style="color:var(--secondary-grey, #8b8fa3); margin-bottom:24px; font-size:1.1rem; max-width:400px; margin-left:auto; margin-right:auto;">Ready for your next journey? Book a route to generate your digital boarding pass.</p>
                    <a href="#transport" class="btn btn-primary" style="padding:12px 30px; font-size:1.1rem;">Browse Available Routes</a>
               </div>`;

        const cancelledSection = cancelledTickets.length > 0 ? `
            <div style="margin-top:4rem;">
                <h3 style="margin-bottom:1.5rem; color:var(--secondary-grey, #8b8fa3); font-size:1.1rem; text-transform:uppercase; letter-spacing:2px; display:flex; align-items:center; gap:10px;">
                    <span style="height:1px; background:rgba(255,255,255,0.1); flex:1;"></span>
                    Cancelled Bookings
                    <span style="height:1px; background:rgba(255,255,255,0.1); flex:1;"></span>
                </h3>
                <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap:24px;">
                    ${cancelledTickets.map(t => renderTicketCard(t, false)).join('')}
                </div>
            </div>
        ` : '';

        this.app.innerHTML = `
            <section class="page-container" style="max-width:900px; margin:0 auto;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:3rem; flex-wrap:wrap; gap:1rem;">
                    <div>
                        <h2 style="margin-bottom:8px; border-bottom:none; padding-bottom:0; font-size:2.2rem; display:flex; align-items:center; gap:12px;">
                            My Digital Boarding Passes
                        </h2>
                        <p style="color:var(--secondary-grey, #8b8fa3); font-size:1rem; margin:0; font-weight:500;">
                            ${validTickets.length} active ticket${validTickets.length !== 1 ? 's' : ''} presented below
                        </p>
                    </div>
                </div>
                
                <div style="display:flex; flex-direction:column; gap:40px;">
                    ${validCards}
                </div>
                ${cancelledSection}
            </section>
        `;
        this.triggerObserver();

        // Initialize maps for valid tickets after DOM is updated
        setTimeout(() => {
            this.initTicketMaps();
        }, 100);
    },

    initTicketMaps() {
        if (typeof L === 'undefined') {
            console.warn("Leaflet not loaded yet, retrying...");
            setTimeout(() => this.initTicketMaps(), 500);
            return;
        }

        const mapElements = document.querySelectorAll('.ticket-map');

        mapElements.forEach(el => {
            const depLat = parseFloat(el.dataset.deplat);
            const depLng = parseFloat(el.dataset.deplng);
            const destLat = parseFloat(el.dataset.destlat);
            const destLng = parseFloat(el.dataset.destlng);

            if (isNaN(depLat) || isNaN(depLng) || isNaN(destLat) || isNaN(destLng)) {
                el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#8b8fa3;">No route coordinates available.</div>';
                return;
            }

            const map = L.map(el, {
                center: [36.8065, 10.1815],
                zoom: 7,
                zoomControl: false,
                attributionControl: false
            });

            L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                maxZoom: 19
            }).addTo(map);

            const origin = [depLat, depLng];
            const destination = [destLat, destLng];

            L.marker(origin).addTo(map).bindPopup('A — Departure');
            L.marker(destination).addTo(map).bindPopup('B — Destination');

            // Try OSRM routing
            fetch('https://router.project-osrm.org/route/v1/driving/' + depLng + ',' + depLat + ';' + destLng + ',' + destLat + '?overview=full&geometries=geojson')
                .then(r => r.json())
                .then(data => {
                    if (data.code === 'Ok' && data.routes.length > 0) {
                        const coords = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                        const routeLine = L.polyline(coords, { color: '#6366f1', weight: 5, opacity: 0.85 }).addTo(map);
                        map.fitBounds(routeLine.getBounds(), { padding: [20, 20] });
                    } else {
                        map.fitBounds([origin, destination], { padding: [20, 20] });
                    }
                })
                .catch(() => {
                    map.fitBounds([origin, destination], { padding: [20, 20] });
                });
        });
    }
};

export default view;
