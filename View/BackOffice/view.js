/**
 * view.js
 * BackOffice rendering logic
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
        nav.classList.add('nav--staff');
        const links = `
            <div class="nav-brand nav-brand--staff">
                CivicPortal
            </div>
            <ul class="nav-links">
                <li><a href="#home">home</a></li>
                <li><a href="#transports">transports</a></li>
                <li><a href="#trajets">trajets</a></li>
                <li><a href="#tickets">tickets</a></li>
                ${role === 'worker' ? '<li><a href="#worker-dashboard">dashboard</a></li>' : ''}
                ${role === 'admin' ? '<li><a href="#admin-stats">statistics</a></li><li><a href="#admin-inbox">inbox</a></li>' : ''}
            </ul>
            <div class="user-controls">
                <a href="../FrontOffice/index.php" class="btn btn-small" style="text-decoration:none; border-color: var(--primary-navy);">FrontOffice</a>
                <div class="user-role-badge user-role-badge--staff">${role}</div>
            </div>
        `;
        nav.innerHTML = links;
    },

    renderHome(user) {
        let content = `
            <div class="hero-container reveal">
                <section class="hero-section">
                    <h1>Staff Portal</h1>
                    <p>Welcome back, ${user.name}. Manage transport fleet, routes, and tickets.</p>
                </section>
            </div>
            <section class="page-container">
                <h2 class="reveal">Transport Management</h2>
                <div class="editorial-grid">
                    <div class="editorial-card editorial-highlight reveal">
                        <h3>🚐 Fleet</h3>
                        <p>View and manage the municipality's physical vehicles — planes, buses, trains, and metros.</p>
                        <a href="#transports" class="btn btn-primary" style="align-self: flex-start; margin-top: auto;">Manage Fleet</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>🗺️ Routes</h3>
                        <p>Schedule trips by assigning vehicles to routes with departure times and pricing.</p>
                        <a href="#trajets" class="btn" style="align-self: flex-start; margin-top: auto;">Manage Routes</a>
                    </div>
                    <div class="editorial-card reveal">
                        <h3>🎟️ Tickets</h3>
                        <p>Review all transport tickets booked by citizens. Cancel invalid bookings.</p>
                        <a href="#tickets" class="btn" style="align-self: flex-start; margin-top: auto;">View Tickets</a>
                    </div>
                </div>
            </section>
        `;
        this.app.innerHTML = content;
        this.triggerObserver();
    },

    // ============================================
    // TRANSPORT VIEWS
    // ============================================

    renderTransports(transports) {
        const rows = transports.map(t => {
            const statusClass = t.status === 'Active' ? 'status-validated' : (t.status === 'Maintenance' ? 'status-pending' : 'status-rejected');
            return `
            <tr>
                <td style="color:var(--secondary-grey); font-size:0.78rem;">${t.idTransport}</td>
                <td><strong>${t.name}</strong></td>
                <td>${t.type}</td>
                <td><strong>${t.capacity}</strong> seats</td>
                <td><span class="status-badge ${statusClass}">${t.status}</span></td>
                <td>
                    <div style="display:flex; gap:8px;">
                        <button class="btn btn-small" data-action="edit-transport" data-id="${t.idTransport}" title="Edit">✎ Edit</button>
                        <button class="btn btn-small btn-danger" data-action="delete-transport" data-id="${t.idTransport}" title="Delete">🗑</button>
                    </div>
                </td>
            </tr>`;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem; flex-wrap:wrap; gap:1rem;">
                    <h2 class="reveal active" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">Transport Fleet</h2>
                    <a href="#add-transport" class="btn btn-primary">＋ Add Vehicle</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="transportTable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name / Label</th>
                                <th>Type</th>
                                <th>Capacity</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.length > 0 ? rows : '<tr><td colspan="6" style="text-align:center; padding:30px;">🚐 No vehicles found.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderAddTransport() {
        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
                    <h2 class="reveal active" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                        <a href="#transports" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                        Add New Vehicle
                    </h2>
                </div>
                <div class="form-card reveal active">
                    <form id="add-transport-form">
                        <div class="form-group">
                            <label for="name">Vehicle Name / Label</label>
                            <input type="text" id="name" name="name" placeholder="e.g. Bus #001" required>
                        </div>
                        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="type">Type</label>
                                <select id="type" name="type" required>
                                    <option value="">Select type</option>
                                    <option value="Plane">Plane</option>
                                    <option value="Bus">Bus</option>
                                    <option value="Train">Train</option>
                                    <option value="Metro">Metro</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="capacity">Capacity (seats)</label>
                                <input type="number" id="capacity" name="capacity" min="1" max="500" placeholder="e.g. 50" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Active">Active</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Retired">Retired</option>
                            </select>
                        </div>
                        <div style="margin-top:25px; display:flex; gap:15px;">
                            <a href="#transports" class="btn">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Vehicle</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderEditTransport(t) {
        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
                    <h2 class="reveal active" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                        <a href="#transports" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                        Edit Vehicle
                    </h2>
                </div>
                <div class="form-card reveal active">
                    <form id="edit-transport-form" data-id="${t.idTransport}">
                        <div class="form-group">
                            <label for="name">Vehicle Name / Label</label>
                            <input type="text" id="name" name="name" value="${t.name}" required>
                        </div>
                        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="type">Type</label>
                                <select id="type" name="type" required>
                                    <option value="Plane" ${t.type==='Plane'?'selected':''}>Plane</option>
                                    <option value="Bus" ${t.type==='Bus'?'selected':''}>Bus</option>
                                    <option value="Train" ${t.type==='Train'?'selected':''}>Train</option>
                                    <option value="Metro" ${t.type==='Metro'?'selected':''}>Metro</option>
                                </select>
                            </div>
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="capacity">Capacity (seats)</label>
                                <input type="number" id="capacity" name="capacity" min="1" max="500" value="${t.capacity}" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="Active" ${t.status==='Active'?'selected':''}>Active</option>
                                <option value="Maintenance" ${t.status==='Maintenance'?'selected':''}>Maintenance</option>
                                <option value="Retired" ${t.status==='Retired'?'selected':''}>Retired</option>
                            </select>
                        </div>
                        <div style="margin-top:25px; display:flex; gap:15px;">
                            <a href="#transports" class="btn">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Vehicle</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ============================================
    // TRAJET VIEWS
    // ============================================

    renderTrajets(trajets) {
        const rows = trajets.map(t => {
            const pct = t.capacity > 0 ? Math.round((t.sold / t.capacity) * 100) : 0;
            const isFull = t.capacity > 0 && t.sold >= t.capacity;
            return `
            <tr>
                <td style="color:var(--secondary-grey); font-size:0.78rem;">${t.idTrajet}</td>
                <td><strong>${t.departure} → ${t.destination}</strong></td>
                <td>${t.transportName || '⚠ Unassigned'}</td>
                <td>${new Date(t.departureTime).toLocaleString()}</td>
                <td><strong>${parseFloat(t.price).toFixed(3)}</strong></td>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="progress-track" style="width:80px; margin:0;"><div class="progress-fill" style="width:${pct}%; ${pct > 80 ? 'background:var(--danger);' : ''}"></div></div>
                        <span style="font-size:0.85rem; font-weight:700;">${t.sold}/${t.capacity}</span>
                        ${isFull ? '<span class="status-badge status-rejected" style="font-size:0.7rem;">FULL</span>' : ''}
                    </div>
                </td>
                <td>
                    <button class="btn btn-small btn-danger" data-action="delete-trajet" data-id="${t.idTrajet}">🗑</button>
                </td>
            </tr>`;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem; flex-wrap:wrap; gap:1rem;">
                    <h2 class="reveal active" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">Trajets Management</h2>
                    <a href="#add-trajet" class="btn btn-primary">＋ Add Trajet</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Route</th>
                                <th>Vehicle</th>
                                <th>Departure</th>
                                <th>Price (TND)</th>
                                <th>Occupancy</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.length > 0 ? rows : '<tr><td colspan="7" style="text-align:center; padding:30px;">🗺️ No trajets found.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    renderAddTrajet(transports) {
        const options = transports.filter(t => t.status === 'Active').map(t =>
            `<option value="${t.idTransport}">${t.name} (${t.type}, ${t.capacity} seats)</option>`
        ).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <div style="display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:2rem; border-bottom: var(--border-main); padding-bottom:1rem;">
                    <h2 class="reveal active" style="margin-bottom:0; border-bottom:none; padding-bottom:0;">
                        <a href="#trajets" style="text-decoration:none; color:var(--secondary-grey);" title="Back">←</a>
                        Add New Trajet
                    </h2>
                </div>
                <div class="form-card reveal active">
                    <form id="add-trajet-form">
                        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="departure">From (Departure)</label>
                                <input type="text" id="departure" name="departure" placeholder="e.g. Tunis" required>
                            </div>
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="destination">To (Destination)</label>
                                <input type="text" id="destination" name="destination" placeholder="e.g. Marsa" required>
                            </div>
                        </div>
                        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="idTransport">Assign Vehicle</label>
                                <select id="idTransport" name="idTransport" required>
                                    <option value="">Select vehicle</option>
                                    ${options}
                                </select>
                            </div>
                            <div class="form-group" style="flex:1; min-width:200px;">
                                <label for="departureTime">Departure Time</label>
                                <input type="datetime-local" id="departureTime" name="departureTime" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="price">Price (TND)</label>
                            <input type="number" id="price" name="price" min="0" step="0.1" placeholder="e.g. 2.500" required>
                        </div>
                        <div style="margin-top:25px; display:flex; gap:15px;">
                            <a href="#trajets" class="btn">Cancel</a>
                            <button type="submit" class="btn btn-primary">Save Trajet</button>
                        </div>
                    </form>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ============================================
    // TICKET VIEWS
    // ============================================

    renderTickets(tickets) {
        const rows = tickets.map(t => {
            const statusClass = t.status === 'Valid' ? 'status-validated' : 'status-rejected';
            return `
            <tr>
                <td style="color:var(--secondary-grey); font-size:0.78rem;">${t.idTicket}</td>
                <td><span class="status-badge">${t.ref}</span></td>
                <td><strong>${t.citizenName}</strong></td>
                <td>${t.idUser || '<span style="color:var(--secondary-grey)">Guest</span>'}</td>
                <td>${t.departure} → ${t.destination}</td>
                <td>${new Date(t.issuedAt).toLocaleString()}</td>
                <td><span class="status-badge ${statusClass}">${t.status}</span></td>
                <td>
                    ${t.status === 'Valid' ? `<button class="btn btn-small btn-danger" data-action="cancel-ticket" data-id="${t.idTicket}">🚫 Cancel</button>` : '<span style="color:var(--secondary-grey); font-size:0.8rem;">—</span>'}
                </td>
            </tr>`;
        }).join('');

        this.app.innerHTML = `
            <section class="page-container">
                <h2 class="reveal active">Tickets Management</h2>
                <div style="margin-bottom:2rem;">
                    <div class="editorial-grid" style="border:none;">
                        <div class="editorial-card" style="border: var(--border-main); padding:2rem;">
                            <h3>🎟️ ${tickets.length}</h3>
                            <p style="margin-bottom:0;">Total Tickets</p>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="data-table" id="ticketTable">
                        <thead>
                            <tr>
                                <th>Ticket ID</th>
                                <th>Reference</th>
                                <th>Citizen Name</th>
                                <th>User ID</th>
                                <th>Route</th>
                                <th>Issued At</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${rows.length > 0 ? rows : '<tr><td colspan="8" style="text-align:center; padding:30px;">🎟️ No tickets booked yet.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </section>
        `;
        this.triggerObserver();
    },

    // ============================================
    // EXISTING VIEWS (preserved)
    // ============================================

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
    }
};

export default view;
