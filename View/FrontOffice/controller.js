/**
 * controller.js
 * FrontOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    async init() {
        try {
            console.log('Controller: Starting initialization...');
            
            console.log('Controller: Syncing model...');
            await model.sync();
            console.log('Controller: Sync complete');
            
            console.log('Controller: Setting up event listeners...');
            this.setupEventListeners();
            console.log('Controller: Event listeners set up');
            
            console.log('Controller: Handling routing...');
            this.handleRouting();
            console.log('Controller: Routing handled');
            
            console.log('Controller: Rendering navigation...');
            view.renderNavBar(model.getCurrentUser());
            console.log('Controller: Navigation rendered');
            
            console.log('Controller: Initialization complete!');
        } catch (error) {
            console.error('FATAL ERROR during initialization:', error);
            console.error('Stack:', error.stack);
            view.renderToast('Error loading CivicPortal: ' + error.message, 'error');
        }
    },

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            const target = e.target.closest('[data-action]') || e.target;
            const action = target.dataset.action;
            const id     = target.dataset.id;

            if (action === 'enroll') {
                const user = model.getCurrentUser();
                this.handleEnrollment(user.id, parseInt(id));
            }
            if (action === 'cancel-ticket') {
                this.handleCancelTicket(parseInt(id));
            }
            if (action === 'cancel-appointment') {
                this.handleCancelAppointment(parseInt(id));
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.id === 'prog-search') this.handleCatalogFilter();
        });

        document.addEventListener('change', (e) => {
            if (e.target.id === 'prog-filter-cat') this.handleCatalogFilter();
        });

        window.addEventListener('hashchange', () => this.handleRouting());

        document.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (e.target.id === 'service-request-form') {
                await this.handleServiceRequest(new FormData(e.target));

            } else if (e.target.id === 'profile-form') {
                await this.handleProfileUpdate(new FormData(e.target));

            } else if (e.target.id === 'profile-pic-form') {
                await this.handleProfilePicUpload(e.target);

            } else if (e.target.id === 'password-form') {
                await this.handlePasswordChange(new FormData(e.target));

            } else if (e.target.id === 'appointment-form') {
                await this.handleAppointmentBooking(new FormData(e.target));

            } else if (e.target.id === 'sort-transport-form') {
                const type     = e.target.dataset.type;
                const formData = new FormData(e.target);
                const sort     = formData.get('sort');
                const order    = formData.get('order');
                window.location.hash = `#transport_list?type=${encodeURIComponent(type)}&sort=${sort}&order=${order}`;

            } else if (e.target.classList.contains('book-transport-form')) {
                const idTrajet = parseInt(e.target.dataset.id);
                const user     = model.getCurrentUser();
                await this.handleTicketBooking(idTrajet, user.name);
            }
        });
    },

    async handleRouting() {
        const rawHash = window.location.hash || '#home';
        const [hashPath, queryStr] = rawHash.split('?');
        const user = model.getCurrentUser();

        switch (hashPath) {
            case '#home':
                view.renderHome(user);
                break;

            case '#programs':
                view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(user.id));
                break;

            case '#request-service':
                view.renderServiceRequestForm(model.getServiceTypes());
                break;

            case '#my-requests': {
                const requests = await model.getMyRequests();
                view.renderMyRequests(requests || []);
                break;
            }

            case '#appointments':
                view.renderAppointmentForm(model.getServiceTypes());
                break;

            case '#my-appointments': {
                const appointments = await model.getMyAppointments();
                view.renderMyAppointments(appointments || []);
                break;
            }

            case '#profile':
                view.renderProfile(user);
                break;

            case '#transport':
                view.renderTransport(model.getTransportTypes());
                break;

            case '#transport_list': {
                const params  = new URLSearchParams(queryStr || '');
                const type    = params.get('type')  || 'Bus';
                const sortBy  = params.get('sort')  || 'departure';
                const order   = params.get('order') || 'ASC';
                const trajets = await model.getTrajetsByTypeAndSort(type, sortBy, order);
                view.renderTransportList(type, trajets || [], sortBy, order);
                break;
            }

            case '#my-tickets': {
                const tickets = await model.getMyTickets();
                view.renderMyTickets(tickets || [], user);
                break;
            }

            default:
                view.renderHome(user);
                break;
        }
    },

    async handleEnrollment(userId, programId) {
        const success = await model.addEnrollment(userId, programId);
        if (success) {
            view.renderToast('Enrollment requested — pending validation.');
            await model.sync();
            view.renderProgramCatalog(model.getPrograms(), model.getEnrollments(userId));
        } else {
            view.renderToast('Enrollment failed or already enrolled.', 'error');
        }
    },

    handleCatalogFilter() {
        const search   = document.getElementById('prog-search')?.value.toLowerCase() || '';
        const category = document.getElementById('prog-filter-cat')?.value || '';

        const filtered = model.getPrograms().filter(p => {
            const matchesSearch = p.title.toLowerCase().includes(search);
            const matchesCat    = category === '' || p.category === category;
            return matchesSearch && matchesCat;
        });

        const activeId = document.activeElement?.id || null;
        const user     = model.getCurrentUser();
        view.renderProgramCatalog(filtered, model.getEnrollments(user.id));

        if (document.getElementById('prog-search'))     document.getElementById('prog-search').value = search;
        if (document.getElementById('prog-filter-cat')) document.getElementById('prog-filter-cat').value = category;
        if (activeId && document.getElementById(activeId)) {
            const el = document.getElementById(activeId);
            el.focus();
            if (el.setSelectionRange) el.setSelectionRange(el.value.length, el.value.length);
        }
    },

    async handleServiceRequest(formData) {
        const result = await model.addServiceRequest(formData);
        if (result) {
            view.renderToast('Service request submitted successfully!');
            window.location.hash = '#my-requests';
        } else {
            view.renderToast('Failed to submit request. Please try again.', 'error');
        }
    },

    async handleProfileUpdate(formData) {
        const data = {
            name:        formData.get('name'),
            email:       formData.get('email'),
            bio:         formData.get('bio'),
            phoneNumber: formData.get('phoneNumber'),
            dateOfBirth: formData.get('dateOfBirth'),
            role:        model.getCurrentUser().role
        };

        try {
            const res    = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_profile', data })
            });
            const result = await res.json();

            if (result.success && !result.data?.errors) {
                model.updateUser(data);
                view.renderToast('Profile updated successfully!');
                view.renderNavBar(model.getCurrentUser());
                window.location.hash = '#home';
            } else {
                const msg = result.data?.errors
                    ? Object.values(result.data.errors).join(', ')
                    : 'Failed to update profile.';
                view.renderToast('Error: ' + msg, 'error');
            }
        } catch {
            view.renderToast('Network error while updating profile.', 'error');
        }
    },

    async handleProfilePicUpload(form) {
        const fileInput = form.querySelector('input[type="file"]');
        const file      = fileInput?.files?.[0];
        if (!file) {
            view.renderToast('Please select an image to upload.', 'error');
            return;
        }

        const result = await model.uploadProfilePic(file);
        if (result) {
            model.updateUser({ has_profile_pic: true });
            view.renderToast('Profile picture updated!');
            view.renderNavBar(model.getCurrentUser());
            // Re-render profile and bust the image cache
            view.renderProfile(model.getCurrentUser());
            document.querySelectorAll('img[src*="type=profile"]').forEach(img => {
                const base = img.src.split('&_t=')[0];
                img.src = base + '&_t=' + Date.now();
            });
        } else {
            view.renderToast('Failed to upload profile picture.', 'error');
        }
    },

    async handlePasswordChange(formData) {
        const currentPassword = formData.get('current_password')?.trim();
        const newPassword     = formData.get('new_password')?.trim();
        const confirmPassword = formData.get('confirm_password')?.trim();

        if (!currentPassword || !newPassword) {
            view.renderToast('Please fill in all password fields.', 'error');
            return;
        }
        if (newPassword !== confirmPassword) {
            view.renderToast('New passwords do not match.', 'error');
            return;
        }
        if (newPassword.length < 8) {
            view.renderToast('Password must be at least 8 characters.', 'error');
            return;
        }

        try {
            const res    = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update_profile', data: {
                    current_password: currentPassword,
                    new_password:     newPassword
                }})
            });
            const result = await res.json();
            if (result.success) {
                view.renderToast('Password changed successfully!');
                document.getElementById('password-form')?.reset();
            } else {
                view.renderToast(result.error || result.data?.error || 'Failed to change password.', 'error');
            }
        } catch {
            view.renderToast('Network error while changing password.', 'error');
        }
    },

    async handleAppointmentBooking(formData) {
        const serviceType = formData.get('service_type')?.trim();
        const date        = formData.get('preferred_date')?.trim();
        const time        = formData.get('preferred_time')?.trim();
        const notes       = formData.get('notes')?.trim() || '';

        if (!serviceType || !date || !time) {
            view.renderToast('Please fill in all required fields.', 'error');
            return;
        }

        const result = await model.bookAppointment({
            service_type:   serviceType,
            preferred_date: date,
            preferred_time: time,
            notes
        });
        if (result) {
            view.renderToast('Appointment booked successfully!');
            window.location.hash = '#my-appointments';
        } else {
            view.renderToast('Failed to book appointment. The slot may be unavailable.', 'error');
        }
    },

    async handleCancelAppointment(id) {
        if (!confirm('Are you sure you want to cancel this appointment?')) return;

        const result = await model.cancelAppointment(id);
        if (result) {
            view.renderToast('Appointment cancelled.');
            const appointments = await model.getMyAppointments();
            view.renderMyAppointments(appointments || []);
        } else {
            view.renderToast('Failed to cancel appointment.', 'error');
        }
    },

    async handleTicketBooking(idTrajet, citizenName) {
        const result = await model.bookTicket(idTrajet, citizenName);
        if (result?.success) {
            view.renderToast(`Ticket booked! Reference: ${result.ref}`);
            window.location.hash = '#my-tickets';
        } else {
            view.renderToast(result?.error || 'Booking failed.', 'error');
        }
    },

    async handleCancelTicket(idTicket) {
        if (!confirm('Are you sure you want to cancel this booking?')) return;

        const result = await model.cancelTicket(idTicket);
        if (result?.success) {
            view.renderToast('Booking cancelled.');
            const tickets = await model.getMyTickets();
            view.renderMyTickets(tickets || [], model.getCurrentUser());
        } else {
            view.renderToast('Failed to cancel ticket.', 'error');
        }
    }
};

export default controller;
