/**
 * controller.js
 * BackOffice Event Handler — CivicPortal Staff Portal
 */

import model from './model.js';
import view  from './view.js';

const controller = {
    async init() {
        await model.sync();
        this.setupEventListeners();
        const user = model.getCurrentUser();
        this.handleRoleChange(user.role, false);
    },

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            const actionEl = e.target.closest('[data-action]');
            if (!actionEl) {
                if (e.target.id === 'btn-generate-image') {
                    this.handleImageGeneration(e.target);
                }
                return;
            }

            const action = actionEl.dataset.action;
            const id     = actionEl.dataset.id;

            switch (action) {
                // Service request actions
                case 'validate':
                    this.handleStatusUpdate(id, 'validated');
                    break;
                case 'reject':
                    this.handleStatusUpdate(id, 'rejected');
                    break;

                // Program actions
                case 'new-program':
                    window.location.hash = '#add-program';
                    break;
                case 'edit-program':
                    window.location.hash = `#edit-program/${id}`;
                    break;
                case 'delete-program':
                    if (confirm('Are you sure you want to delete this program?')) {
                        this.handleProgramDelete(id);
                    }
                    break;
                case 'view-program':
                    window.location.hash = `#program/${id}`;
                    break;

                // Enrollment actions
                case 'confirm-enroll':
                    this.handleEnrollmentUpdate(id, 'confirmed', actionEl.dataset.programId);
                    break;
                case 'cancel-enroll':
                    this.handleEnrollmentUpdate(id, 'cancelled', actionEl.dataset.programId);
                    break;

                // Category actions
                case 'manage-categories':
                    window.location.hash = '#manage-categories';
                    break;
                case 'edit-category':
                    this.toggleCategoryEdit(id, true);
                    break;
                case 'cancel-edit-category':
                    this.toggleCategoryEdit(id, false);
                    break;
                case 'save-category':
                    this.handleCategorySave(id);
                    break;
                case 'delete-category':
                    if (confirm('Are you sure you want to delete this category?')) {
                        this.handleCategoryDelete(id);
                    }
                    break;

                // Transport actions (admin)
                case 'toggle-add-type':
                    document.getElementById('add-type-panel')?.style.setProperty('display',
                        document.getElementById('add-type-panel').style.display === 'none' ? 'block' : 'none');
                    break;
                case 'delete-transport-type':
                    if (confirm('Delete this transport type? Vehicles using it will lose their type association.')) {
                        this.handleTransportTypeDelete(parseInt(id));
                    }
                    break;

                case 'toggle-add-vehicle':
                    document.getElementById('add-vehicle-panel')?.style.setProperty('display',
                        document.getElementById('add-vehicle-panel').style.display === 'none' ? 'block' : 'none');
                    break;
                case 'toggle-add-trajet':
                    document.getElementById('add-trajet-panel')?.style.setProperty('display',
                        document.getElementById('add-trajet-panel').style.display === 'none' ? 'block' : 'none');
                    break;
                case 'delete-vehicle':
                    if (confirm('Delete this vehicle?')) this.handleVehicleDelete(parseInt(id));
                    break;
                case 'delete-trajet':
                    if (confirm('Delete this route? All booked tickets will be affected.')) this.handleTrajetDelete(parseInt(id));
                    break;

                // User management actions
                case 'toggle-create-user': {
                    const panel = document.getElementById('create-user-panel');
                    if (panel) panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
                    break;
                }
                case 'save-user-role': {
                    const row    = actionEl.closest('tr[data-uid]');
                    const select = row?.querySelector('.role-select');
                    if (select) {
                        this.handleUserRoleChange(
                            parseInt(id),
                            select.value,
                            actionEl.dataset.name,
                            actionEl.dataset.email
                        );
                    }
                    break;
                }
                case 'delete-user':
                    if (confirm(`Delete user "${actionEl.dataset.name}"? This cannot be undone.`)) {
                        this.handleUserDelete(parseInt(id));
                    }
                    break;

                // Slot management actions (admin)
                case 'delete-slot':
                    if (confirm('Delete this availability slot?')) {
                        this.handleSlotDelete(parseInt(id));
                    }
                    break;

                // User management actions (admin)
                case 'toggle-user-active': {
                    const active = actionEl.dataset.active === '1';
                    this.handleToggleUserActive(parseInt(id), active);
                    break;
                }

                // Appointment actions
                case 'confirm-appointment':
                    this.handleAppointmentStatus(parseInt(id), 'confirmed');
                    break;
                case 'cancel-appointment':
                    if (confirm('Cancel this appointment?')) {
                        this.handleAppointmentStatus(parseInt(id), 'cancelled');
                    }
                    break;
                case 'complete-appointment':
                    this.handleAppointmentStatus(parseInt(id), 'completed');
                    break;
            }
        });

        window.addEventListener('hashchange', () => this.handleRouting());

        document.addEventListener('submit', (e) => {
            e.preventDefault();
            if (e.target.id === 'profile-form') {
                this.handleProfileUpdate(new FormData(e.target));
            } else if (e.target.id === 'program-form') {
                this.handleProgramSave(new FormData(e.target));
            } else if (e.target.id === 'category-form') {
                this.handleCategoryAdd(new FormData(e.target));
            } else if (e.target.id === 'slot-form') {
                this.handleSlotCreate(new FormData(e.target));
            } else if (e.target.id === 'create-user-form') {
                this.handleUserCreate(new FormData(e.target));
            } else if (e.target.id === 'add-type-form') {
                this.handleTransportTypeAdd(new FormData(e.target));
            } else if (e.target.id === 'add-vehicle-form') {
                this.handleVehicleAdd(new FormData(e.target));
            } else if (e.target.id === 'add-trajet-form') {
                this.handleTrajetAdd(new FormData(e.target));
            }
        });
    },

    async handleRouting() {
        const hash = window.location.hash || '#home';
        const user = model.getCurrentUser();

        if (hash.startsWith('#program/')) {
            await this.showProgramDetail(hash.split('/')[1]);
            return;
        }

        if (hash.startsWith('#edit-program/')) {
            const program = model.getProgram(hash.split('/')[1]);
            view.renderProgramForm(program, model.getCategories());
            return;
        }

        switch (hash) {
            case '#home':
                view.renderHome(user);
                break;

            case '#worker-dashboard':
                if (user.role === 'agent' || user.role === 'admin') {
                    view.renderWorkerDashboard(model.getServiceRequests());
                } else {
                    window.location.hash = '#home';
                }
                break;

            case '#appointments': {
                const appointments = user.role === 'admin'
                    ? await model.getAllAppointments()
                    : await model.getAgentAppointments();
                view.renderAppointmentQueue(appointments || [], user.role);
                break;
            }

            case '#user-management':
                if (user.role === 'admin') {
                    const users = await model.getUsers();
                    view.renderUserManagement(users || []);
                } else {
                    window.location.hash = '#home';
                }
                break;

            case '#profile':
                view.renderProfile(user);
                break;

            case '#admin-stats':
                if (user.role === 'admin') {
                    const stats = await model.getStats();
                    view.renderAdminStats(stats);
                } else {
                    window.location.hash = '#home';
                }
                break;

            case '#manage-programs':
                if (user.role === 'admin' || user.role === 'agent') {
                    await model.sync();
                    view.renderProgramsManager(model.getPrograms(), user.role);
                } else {
                    window.location.hash = '#home';
                }
                break;

            case '#add-program':
                view.renderProgramForm(null, model.getCategories());
                break;

            case '#manage-categories':
                if (user.role === 'admin') {
                    await model.syncCategories();
                    view.renderCategoryManager(model.getCategories());
                } else {
                    window.location.hash = '#home';
                }
                break;

            case '#transport-management':
                if (user.role === 'admin') {
                    const overview = await model.getTransportOverview();
                    view.renderTransportManagement(overview);
                } else {
                    window.location.hash = '#home';
                }
                break;

            case '#slot-management':
                if (user.role === 'admin') {
                    const [slots, agents] = await Promise.all([
                        model.getSlots(),
                        model.getAgents()
                    ]);
                    view.renderSlotManagement(slots || [], agents || [], model.getServiceTypes());
                } else {
                    window.location.hash = '#home';
                }
                break;

            default:
                view.renderHome(user);
                break;
        }
    },

    async showProgramDetail(programId) {
        const user        = model.getCurrentUser();
        const program     = await model.getProgramDetail(programId);
        const enrollments = await model.getEnrollmentsByProgram(programId);
        if (program) {
            view.renderProgramDetail(program, enrollments || [], user.role);
        } else {
            view.renderToast('Program not found.', 'error');
            window.location.hash = '#manage-programs';
        }
    },

    async handleRoleChange(role, triggerRouting = true) {
        model.setCurrentUser(role);
        const counts = await model.getEnrollmentCounts();
        view.renderNavBar(role, counts);
        if (triggerRouting) {
            if (window.location.hash && window.location.hash !== '#home') {
                window.location.hash = '#home';
            } else {
                this.handleRouting();
            }
        } else {
            this.handleRouting();
        }
    },

    async handleStatusUpdate(requestId, status) {
        await model.updateRequestStatus(parseInt(requestId), status);
        view.renderToast(`Request ${status} successfully.`);
        view.renderWorkerDashboard(model.getServiceRequests());
    },

    handleProfileUpdate(formData) {
        const data = {
            name:  formData.get('name'),
            email: formData.get('email')
        };
        model.updateUser(data);
        view.renderToast('Staff profile updated!');
        window.location.hash = '#home';
    },

    async handleToggleUserActive(userId, active) {
        const result = await model.toggleUserActive(userId, active);
        if (result !== null) {
            view.renderToast(active ? 'User activated.' : 'User deactivated.');
            const users = await model.getUsers();
            view.renderUserManagement(users || []);
        } else {
            view.renderToast('Failed to update user status.', 'error');
        }
    },

    async handleAppointmentStatus(id, status) {
        const result = await model.updateAppointmentStatus(id, status);
        if (result !== null) {
            view.renderToast(`Appointment ${status}.`);
            const user         = model.getCurrentUser();
            const appointments = user.role === 'admin'
                ? await model.getAllAppointments()
                : await model.getAgentAppointments();
            view.renderAppointmentQueue(appointments || [], user.role);
        } else {
            view.renderToast('Failed to update appointment.', 'error');
        }
    },

    async handleImageGeneration(btnElement) {
        const title    = document.getElementById('prog-title').value.trim();
        const desc     = document.getElementById('prog-desc').value.trim();
        const category = document.getElementById('prog-category').value;

        if (!title || !desc) {
            view.renderToast('Please enter both Title and Description before generating.', 'error');
            return;
        }

        const originalText = btnElement.innerHTML;
        btnElement.innerHTML = '⏳ GENERATING...';
        btnElement.disabled  = true;

        try {
            const prompt     = `A professional, high-quality photograph for a community program. Theme: ${category}. Title: ${title}. ${desc}. Bright, inviting lighting. No text in the image.`;
            const imgElement = await puter.ai.txt2img(prompt);

            const response = await fetch(imgElement.src);
            const blob     = await response.blob();
            const file     = new File([blob], 'generated_cover.jpg', { type: 'image/jpeg' });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('prog-image').files = dataTransfer.files;

            document.getElementById('prog-image-preview').innerHTML = `
                <img src="${imgElement.src}" style="width:100%;max-width:400px;height:200px;object-fit:cover;border:var(--border-main);">
                <p style="font-size:0.8rem;color:var(--success);margin-top:0.5rem;font-weight:bold;">AI image generated and attached!</p>
            `;
            view.renderToast('AI Image generated successfully!');
        } catch (error) {
            console.error('AI Generation Error:', error);
            view.renderToast('Failed to generate image. Try again.', 'error');
        } finally {
            btnElement.innerHTML = originalText;
            btnElement.disabled  = false;
        }
    },

    async handleProgramSave(formData) {
        const title       = formData.get('title')?.trim();
        const description = formData.get('description')?.trim();
        const capacity    = parseInt(formData.get('capacity'));
        const location    = formData.get('location')?.trim();
        const category    = formData.get('category');

        if (!title || title.length < 5) {
            view.renderToast('Title must be at least 5 characters.', 'error'); return;
        }
        if (!description || description.length < 20) {
            view.renderToast('Description must be at least 20 characters.', 'error'); return;
        }
        if (isNaN(capacity) || capacity <= 0) {
            view.renderToast('Capacity must be a positive number.', 'error'); return;
        }
        if (!location || location.length < 3) {
            view.renderToast('Location must be at least 3 characters.', 'error'); return;
        }
        if (!category) {
            view.renderToast('Please select a category.', 'error'); return;
        }

        const success = await model.saveProgram(formData);
        if (success) {
            view.renderToast(formData.get('id') ? 'Program updated!' : 'New program created!');
            window.location.hash = '#manage-programs';
        } else {
            view.renderToast('Failed to save program.', 'error');
        }
    },

    async handleProgramDelete(id) {
        const success = await model.deleteProgram(id);
        if (success) {
            view.renderToast('Program deleted.');
            window.location.hash = '#manage-programs';
        } else {
            view.renderToast('Failed to delete program.', 'error');
        }
    },

    async handleEnrollmentUpdate(enrollmentId, status, programId) {
        const success = await model.updateEnrollmentStatus(enrollmentId, status);
        if (success) {
            view.renderToast(`Enrollment ${status}.`);
            await this.showProgramDetail(programId);
            const counts = await model.getEnrollmentCounts();
            view.renderNavBar(model.getCurrentUser().role, counts);
        } else {
            view.renderToast('Failed to update enrollment.', 'error');
        }
    },

    /* =========================================================================
       TRANSPORT MANAGEMENT
       ========================================================================= */
    async _refreshTransport() {
        const overview = await model.getTransportOverview();
        view.renderTransportManagement(overview);
    },

    async handleTransportTypeAdd(formData) {
        const name = formData.get('name')?.trim();
        if (!name) {
            view.renderToast('Type name is required.', 'error');
            return;
        }
        const result = await model.addTransportType(formData);
        if (result) {
            view.renderToast(`Transport type "${name}" added.`);
            await this._refreshTransport();
        } else {
            view.renderToast('Failed to add transport type.', 'error');
        }
    },

    async handleTransportTypeDelete(id) {
        const result = await model.deleteTransportType(id);
        if (result !== null) {
            view.renderToast('Transport type deleted.');
            await this._refreshTransport();
        } else {
            view.renderToast('Failed to delete type. It may be in use by vehicles.', 'error');
        }
    },

    async handleVehicleAdd(formData) {
        const name     = formData.get('name')?.trim();
        const type     = formData.get('type')?.trim();
        const capacity = parseInt(formData.get('capacity'));
        const status   = formData.get('status');
        const typeId   = formData.get('idTransportType') || null;

        if (!name || !type || isNaN(capacity) || capacity < 1) {
            view.renderToast('Please fill in all vehicle fields.', 'error');
            return;
        }

        const result = await model.addVehicle({ name, type, capacity, status, idTransportType: typeId });
        if (result !== null) {
            view.renderToast('Vehicle added.');
            await this._refreshTransport();
        } else {
            view.renderToast('Failed to add vehicle.', 'error');
        }
    },

    async handleVehicleDelete(id) {
        const result = await model.deleteVehicle(id);
        if (result !== null) {
            view.renderToast('Vehicle deleted.');
            await this._refreshTransport();
        } else {
            view.renderToast('Failed to delete vehicle. It may have active routes.', 'error');
        }
    },

    async handleTrajetAdd(formData) {
        const departure    = formData.get('departure')?.trim();
        const destination  = formData.get('destination')?.trim();
        const depTime      = formData.get('departureTime');
        const price        = parseFloat(formData.get('price'));
        const idTransport  = formData.get('idTransport');

        if (!departure || !destination || !depTime || isNaN(price) || !idTransport) {
            view.renderToast('Please fill in all route fields.', 'error');
            return;
        }

        const result = await model.addTrajet({ departure, destination, departureTime: depTime, price, idTransport });
        if (result !== null) {
            view.renderToast('Route added.');
            await this._refreshTransport();
        } else {
            view.renderToast('Failed to add route.', 'error');
        }
    },

    async handleTrajetDelete(id) {
        const result = await model.deleteTrajet(id);
        if (result !== null) {
            view.renderToast('Route deleted.');
            await this._refreshTransport();
        } else {
            view.renderToast('Failed to delete route.', 'error');
        }
    },

    /* =========================================================================
       EXTENDED USER MANAGEMENT
       ========================================================================= */
    async _refreshUsers() {
        const users = await model.getUsers();
        view.renderUserManagement(users || []);
    },

    async handleUserCreate(formData) {
        const name     = formData.get('name')?.trim();
        const email    = formData.get('email')?.trim();
        const password = formData.get('password');
        const role     = formData.get('role');

        if (!name || !email || !password || !role) {
            view.renderToast('Please fill in all fields.', 'error');
            return;
        }
        if (password.length < 8) {
            view.renderToast('Password must be at least 8 characters.', 'error');
            return;
        }

        const result = await model.createUser({ name, email, password, role });
        if (result) {
            view.renderToast(`User "${name}" created.`);
            await this._refreshUsers();
        } else {
            view.renderToast('Failed to create user. Email may already be taken.', 'error');
        }
    },

    async handleUserDelete(id) {
        const result = await model.deleteUser(id);
        if (result !== null) {
            view.renderToast('User deleted.');
            await this._refreshUsers();
        } else {
            view.renderToast('Failed to delete user.', 'error');
        }
    },

    async handleUserRoleChange(id, role, name, email) {
        const result = await model.updateUserRole(id, role, name, email);
        if (result !== null) {
            view.renderToast(`Role updated to "${role}".`);
            await this._refreshUsers();
        } else {
            view.renderToast('Failed to update role.', 'error');
        }
    },

    /* =========================================================================
       SLOT MANAGEMENT
       ========================================================================= */
    async handleSlotCreate(formData) {
        const agentId    = formData.get('agent_id');
        const svcType    = formData.get('service_type');
        const dayOfWeek  = formData.get('day_of_week');
        const startTime  = formData.get('start_time');
        const endTime    = formData.get('end_time');

        if (!agentId || !svcType || dayOfWeek === '' || !startTime || !endTime) {
            view.renderToast('Please fill in all slot fields.', 'error');
            return;
        }
        if (startTime >= endTime) {
            view.renderToast('End time must be after start time.', 'error');
            return;
        }

        const result = await model.createSlot({ agent_id: agentId, service_type: svcType, day_of_week: dayOfWeek, start_time: startTime, end_time: endTime });
        if (result) {
            view.renderToast('Slot created.');
            const [slots, agents] = await Promise.all([model.getSlots(), model.getAgents()]);
            view.renderSlotManagement(slots || [], agents || [], model.getServiceTypes());
        } else {
            view.renderToast('Failed to create slot.', 'error');
        }
    },

    async handleSlotDelete(id) {
        const result = await model.deleteSlot(id);
        if (result) {
            view.renderToast('Slot deleted.');
            const [slots, agents] = await Promise.all([model.getSlots(), model.getAgents()]);
            view.renderSlotManagement(slots || [], agents || [], model.getServiceTypes());
        } else {
            view.renderToast('Failed to delete slot.', 'error');
        }
    },

    /* =========================================================================
       CATEGORY MANAGEMENT
       ========================================================================= */
    toggleCategoryEdit(id, editing) {
        const nameSpan  = document.querySelector(`.category-display-name[data-id="${id}"]`);
        const nameInput = document.querySelector(`.category-edit-input[data-id="${id}"]`);
        const editBtn   = document.querySelector(`[data-action="edit-category"][data-id="${id}"]`);
        const saveBtn   = document.querySelector(`[data-action="save-category"][data-id="${id}"]`);
        const cancelBtn = document.querySelector(`[data-action="cancel-edit-category"][data-id="${id}"]`);

        if (editing) {
            nameSpan.style.display  = 'none';
            nameInput.style.display = 'inline-block';
            nameInput.focus();
            editBtn.style.display   = 'none';
            saveBtn.style.display   = 'inline-flex';
            cancelBtn.style.display = 'inline-flex';
        } else {
            nameSpan.style.display  = 'inline';
            nameInput.style.display = 'none';
            editBtn.style.display   = 'inline-flex';
            saveBtn.style.display   = 'none';
            cancelBtn.style.display = 'none';
        }
    },

    async handleCategoryAdd(formData) {
        const name = formData.get('name')?.trim();
        if (!name || name.length < 2) {
            view.renderToast('Category name must be at least 2 characters.', 'error');
            return;
        }
        const success = await model.addCategory(name);
        if (success) {
            view.renderToast('Category added!');
            view.renderCategoryManager(model.getCategories());
        } else {
            view.renderToast('Failed to add category. It may already exist.', 'error');
        }
    },

    async handleCategorySave(id) {
        const input = document.querySelector(`.category-edit-input[data-id="${id}"]`);
        const name  = input.value.trim();
        if (!name || name.length < 2) {
            view.renderToast('Category name must be at least 2 characters.', 'error');
            return;
        }
        const success = await model.updateCategory(id, name);
        if (success) {
            view.renderToast('Category updated!');
            view.renderCategoryManager(model.getCategories());
        } else {
            view.renderToast('Failed to update category.', 'error');
        }
    },

    async handleCategoryDelete(id) {
        const success = await model.deleteCategory(id);
        if (success) {
            view.renderToast('Category deleted.');
            view.renderCategoryManager(model.getCategories());
        } else {
            view.renderToast('Failed to delete category. It may be in use.', 'error');
        }
    }
};

export default controller;
