/**
 * controller.js
 * BackOffice Event Handler — CivicPortal Staff Portal
 */

import model from './model.js';
import view  from './view.js';
import { initRouteMap } from './map.js';

const controller = {

    async init() {
        await model.sync();
        this.setupEventListeners();
        const user = model.getCurrentUser();
        await this.handleRoleChange(user.role, false);
    },

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.id === 'btn-save-program') {
                const form = document.getElementById('program-form');
                if (form) this.handleProgramSave(new FormData(form));
                return;
            }

            if (e.target.id === 'btn-ai-generate-desc') {
                e.preventDefault();
                this.handleAIGenerateDesc();
                return;
            }
            if (e.target.id === 'btn-ai-audit') {
                e.preventDefault();
                this.handleAIAuditDescriptions(e.target);
                return;
            }
            const aiPriceBtn = e.target.closest('#btn-ai-price');
            if (aiPriceBtn) {
                e.preventDefault();
                this.handleAIPredictRoutePrice();
                return;
            }
            if (e.target.closest('.btn-ai-analyze')) {
                const targetBtn = e.target.closest('.btn-ai-analyze');
                e.preventDefault();
                this.handleAIAnalyzeEnrollment(targetBtn.dataset.id, targetBtn);
                return;
            }
            
            const actionEl = e.target.closest('[data-action]');
            if (!actionEl) {
                if (e.target.id === 'btn-generate-image') {
                    this.handleImageGeneration(e.target);
                } else if (e.target.id === 'btn-cancel-image-gen') {
                    this._imgGenCancelled = true;
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

                case 'save-program': {
                    const form = document.getElementById('program-form');
                    if (form) this.handleProgramSave(new FormData(form));
                    break;
                }

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
                case 'edit-transport-type':
                    this.handleTransportTypeEdit(parseInt(id));
                    break;
                case 'delete-transport-type':
                    if (confirm('Delete this transport type? Vehicles using it will lose their type association.')) {
                        this.handleTransportTypeDelete(parseInt(id));
                    }
                    break;

                case 'toggle-add-vehicle':
                    document.getElementById('add-vehicle-panel')?.style.setProperty('display',
                        document.getElementById('add-vehicle-panel').style.display === 'none' ? 'block' : 'none');
                    // Reset form when toggling
                    if (document.getElementById('add-vehicle-panel').style.display === 'block') {
                        this._resetVehicleForm();
                    }
                    break;
                case 'toggle-add-trajet': {
                    const trajetPanel = document.getElementById('add-trajet-panel');
                    if (trajetPanel) {
                        const wasHidden = trajetPanel.style.display === 'none';
                        trajetPanel.style.display = wasHidden ? 'block' : 'none';
                        if (wasHidden) {
                            // Reset form when opening
                            this._resetTrajetForm();
                            setTimeout(() => initRouteMap(), 50);
                        }
                    }
                    break;
                }
                case 'delete-vehicle':
                    if (confirm('Delete this vehicle?')) this.handleVehicleDelete(parseInt(id));
                    break;
                case 'edit-vehicle':
                    this.handleVehicleEdit(parseInt(id));
                    break;
                case 'edit-trajet':
                    this.handleTrajetEdit(parseInt(id));
                    break;
                case 'ai-price-row':
                    this.handleRouteRowAIPrice(actionEl.dataset);
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

        const provider     = (document.getElementById('img-gen-provider')?.value) || 'auto';
        this._imgGenCancelled = false;

        const originalText = btnElement.innerHTML;
        const cancelBtn    = document.getElementById('btn-cancel-image-gen');
        const providerSel  = document.getElementById('img-gen-provider');
        const previewEl    = document.getElementById('prog-image-preview');

        btnElement.innerHTML = '<i class="bi bi-stars"></i> GENERATING...';
        btnElement.disabled  = true;
        if (cancelBtn)   cancelBtn.style.display = 'inline-block';
        if (providerSel) providerSel.disabled     = true;

        const stages = [
            { pct: 15, label: 'Composing prompt...' },
            { pct: 40, label: 'Sending to AI...' },
            { pct: 65, label: 'Rendering image...' },
            { pct: 85, label: 'Finalizing details...' },
            { pct: 95, label: 'Almost done...' },
        ];

        const providerLabel = provider === 'puter' ? 'Puter.ai' : provider === 'pollinations' ? 'Pollinations.ai' : 'Auto';

        previewEl.innerHTML = `
            <div id="img-gen-progress" style="border:var(--border-main);border-radius:var(--radius-md);padding:1.2rem 1.4rem;background:var(--white);max-width:400px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.25rem;">
                    <span id="img-gen-label" style="font-size:0.82rem;font-weight:600;color:var(--primary-navy);letter-spacing:0.5px;">Initializing...</span>
                    <span id="img-gen-pct" style="font-size:0.82rem;font-weight:700;color:var(--accent-blue);">0%</span>
                </div>
                <div style="font-size:0.75rem;color:#888;margin-bottom:0.6rem;">Provider: ${providerLabel}</div>
                <div style="width:100%;height:6px;background:var(--secondary-grey,#eee);border-radius:99px;overflow:hidden;">
                    <div id="img-gen-bar" style="height:100%;width:0%;background:var(--accent-blue);border-radius:99px;transition:width 0.5s ease;"></div>
                </div>
            </div>
        `;

        const bar     = document.getElementById('img-gen-bar');
        const labelEl = document.getElementById('img-gen-label');
        const pctEl   = document.getElementById('img-gen-pct');

        let stageIdx = 0;
        const advanceStage = () => {
            if (stageIdx >= stages.length) return;
            const { pct, label } = stages[stageIdx++];
            bar.style.width     = pct + '%';
            labelEl.textContent = label;
            pctEl.textContent   = pct + '%';
        };

        advanceStage();
        const stageTimer = setInterval(advanceStage, 3500);

        const cleanup = () => {
            clearInterval(stageTimer);
            btnElement.innerHTML = originalText;
            btnElement.disabled  = false;
            if (cancelBtn)   cancelBtn.style.display = 'none';
            if (providerSel) providerSel.disabled     = false;
        };

        try {
            const prompt = `A professional, high-quality photograph for a community program. Theme: ${category}. Title: ${title}. ${desc}. Bright, inviting lighting. No text in the image.`;
            let imgSrc = null;

            if (provider === 'pollinations') {
                labelEl.textContent = 'Sending to Pollinations.ai...';
                const url = `https://image.pollinations.ai/prompt/${encodeURIComponent(prompt)}?width=800&height=400&nologo=true`;
                await new Promise((resolve, reject) => { const i = new Image(); i.onload = resolve; i.onerror = reject; i.src = url; });
                imgSrc = url;
            } else if (provider === 'puter') {
                const imgElement = await puter.ai.txt2img(prompt);
                imgSrc = imgElement.src;
            } else {
                try {
                    const imgElement = await puter.ai.txt2img(prompt);
                    imgSrc = imgElement.src;
                } catch (puterErr) {
                    if (this._imgGenCancelled) throw new Error('cancelled');
                    console.warn('Puter.ai unavailable, switching to Pollinations.ai:', puterErr);
                    labelEl.textContent = 'Switching to Pollinations.ai...';
                    const url = `https://image.pollinations.ai/prompt/${encodeURIComponent(prompt)}?width=800&height=400&nologo=true`;
                    await new Promise((resolve, reject) => { const i = new Image(); i.onload = resolve; i.onerror = reject; i.src = url; });
                    imgSrc = url;
                }
            }

            if (this._imgGenCancelled) throw new Error('cancelled');

            clearInterval(stageTimer);
            bar.style.width      = '100%';
            labelEl.textContent  = 'Done!';
            pctEl.textContent    = '100%';
            bar.style.background = 'var(--success)';

            await new Promise(r => setTimeout(r, 400));
            if (this._imgGenCancelled) throw new Error('cancelled');

            const response = await fetch(imgSrc);
            const blob     = await response.blob();
            const file     = new File([blob], 'generated_cover.jpg', { type: 'image/jpeg' });

            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            document.getElementById('prog-image').files = dataTransfer.files;

            previewEl.innerHTML = `
                <img src="${imgSrc}" style="width:100%;max-width:400px;height:200px;object-fit:cover;border:var(--border-main);border-radius:var(--radius-md);">
                <p style="font-size:0.8rem;color:var(--success);margin-top:0.5rem;font-weight:bold;">AI image generated and attached!</p>
            `;
            view.renderToast('AI Image generated successfully!');
        } catch (error) {
            clearInterval(stageTimer);
            previewEl.innerHTML = '';
            if (error.message === 'cancelled') {
                view.renderToast('Image generation cancelled.', 'error');
            } else {
                console.error('AI Generation Error:', error);
                view.renderToast('Failed to generate image. Try again.', 'error');
            }
        } finally {
            cleanup();
        }
    },

    async handleProgramSave(formData) {
        const title       = formData.get('title')?.trim();
        const description = formData.get('description')?.trim();
        const capacity    = parseInt(formData.get('capacity'));
        const location    = formData.get('location')?.trim();
        const category    = formData.get('category');
        const startDate   = formData.get('start_date');
        const endDate     = formData.get('end_date');

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
        if (!startDate) {
            view.renderToast('Please select a start date.', 'error'); return;
        }
        if (!endDate) {
            view.renderToast('Please select an end date.', 'error'); return;
        }
        if (new Date(startDate) > new Date(endDate)) {
            view.renderToast('Start date must be before end date.', 'error'); return;
        }

        const success = await model.saveProgram(formData);
        if (success) {
            view.renderToast(formData.get('id') ? 'Program updated!' : 'New program created!');
            window.location.hash = '#manage-programs';
        } else {
            view.renderToast('Failed to save program. Please check the console and server logs.', 'error');
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

    async handleTransportTypeEdit(id) {
        // For now, just show a message that editing transport types is not implemented
        // In a full implementation, this would fetch the type data and populate the form
        view.renderToast('Transport type editing not yet implemented.', 'error');
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

        // Check if this is an edit operation
        const submitBtn = document.querySelector('#add-vehicle-form button[type="submit"]');
        const editId = submitBtn?.dataset.editId;

        let result;
        if (editId) {
            // Update existing vehicle
            result = await model.updateVehicle(editId, { name, type, capacity, status, idTransportType: typeId });
        } else {
            // Add new vehicle
            result = await model.addVehicle({ name, type, capacity, status, idTransportType: typeId });
        }

        if (result !== null) {
            view.renderToast(editId ? 'Vehicle updated.' : 'Vehicle added.');
            await this._refreshTransport();
            // Reset form after successful operation
            this._resetVehicleForm();
        } else {
            view.renderToast(editId ? 'Failed to update vehicle.' : 'Failed to add vehicle.', 'error');
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

    async handleVehicleEdit(id) {
        // Get the vehicle data
        const vehicle = await model.getVehicle(id);
        if (!vehicle) {
            view.renderToast('Vehicle not found.', 'error');
            return;
        }

        // Show the add-vehicle panel
        const vehiclePanel = document.getElementById('add-vehicle-panel');
        if (vehiclePanel) {
            vehiclePanel.style.display = 'block';

            // Pre-populate the form
            const form = document.getElementById('add-vehicle-form');
            if (form) {
                form.querySelector('[name="name"]').value = vehicle.name || '';
                form.querySelector('[name="type"]').value = vehicle.type || '';
                form.querySelector('[name="capacity"]').value = vehicle.capacity || '';
                form.querySelector('[name="status"]').value = vehicle.status || 'Active';
                form.querySelector('[name="idTransportType"]').value = vehicle.idTransportType || '';

                // Change button text and add data attribute for update
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.textContent = 'UPDATE VEHICLE';
                    submitBtn.dataset.editId = id;
                }
            }
        }
    },

    async handleAIPredictRoutePrice() {
        const form = document.getElementById('add-trajet-form');
        if (!form) {
            return;
        }

        const departure = form.querySelector('[name="departure"]').value.trim();
        const destination = form.querySelector('[name="destination"]').value.trim();
        const depLat = parseFloat(form.querySelector('#depLat').value);
        const depLng = parseFloat(form.querySelector('#depLng').value);
        const destLat = parseFloat(form.querySelector('#destLat').value);
        const destLng = parseFloat(form.querySelector('#destLng').value);
        const transportSelect = form.querySelector('select[name="idTransport"]');
        const transportType = transportSelect?.selectedOptions?.[0]?.dataset.transportType?.trim() || '';
        const distanceInput = form.querySelector('#routeDistance');
        const transportTypeInput = form.querySelector('#routeTransportType');
        const destAddress = form.querySelector('#destAddress').value.trim();
        const suggestion = document.getElementById('ai-price-suggestion');
        const priceInput = form.querySelector('[name="price"]');

        if (!departure || !destination) {
            view.renderToast('Please enter both departure and destination to get an AI price.', 'error');
            return;
        }

        if (!transportType) {
            view.renderToast('Please select a vehicle so AI can determine the transport type.', 'error');
            return;
        }

        const routeInfo = this._getRoutePricingInfo({
            transportType,
            distance: parseFloat(distanceInput?.value),
            depLat,
            depLng,
            destLat,
            destLng
        });

        if (routeInfo.error) {
            view.renderToast(routeInfo.error, 'error');
            return;
        }

        if (transportTypeInput) {
            transportTypeInput.value = transportType;
        }

        const destinationCity = this._extractCityFromAddress(destAddress || destination);
        const livePrice = await this._fetchLivePricingSuggestion({
            transportType,
            routeInfo,
            departure,
            destination,
            destinationCity
        });

        const suggestedPrice = typeof livePrice === 'number'
            ? livePrice
            : this._calculateFallbackPrice({
                distance: routeInfo.distance,
                transportType,
                destinationCity,
                effectiveTransportType: routeInfo.effectiveTransportType
            });

        if (priceInput) {
            priceInput.value = suggestedPrice.toFixed(2);
        }
        
        // Build detailed suggestion message including internet search note if available
        let suggestionText = `AI best price suggestion: ${suggestedPrice.toFixed(2)} TND`;
        if (routeInfo.note) {
            suggestionText += ` — ${routeInfo.note}`;
        }
        
        // Show whether we found internet data or using fallback
        if (this._lastInternetSearch && this._lastInternetSearch.price !== null) {
            suggestionText += ` | ${this._lastInternetSearch.note}`;
        } else if (routeInfo.distance > 2000 && (routeInfo.effectiveTransportType.includes('flight') || routeInfo.effectiveTransportType.includes('plane'))) {
            suggestionText += ` (⚠ Estimated: No internet flight data found)`;
        } else if (livePrice === null || livePrice === undefined) {
            suggestionText += ` (Estimated based on distance and type)`;
        }
        
        if (suggestion) {
            suggestion.textContent = suggestionText;
        }
        view.renderToast(livePrice != null ? 'AI price suggestion retrieved from internet search.' : 'AI price suggestion generated locally (no internet data found).', 'success');
    },

    _computeStraightLineKm(lat1, lng1, lat2, lng2) {
        const toRad = (value) => (value * Math.PI) / 180;
        const R = 6371;
        const dLat = toRad(lat2 - lat1);
        const dLng = toRad(lng2 - lng1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2)
            + Math.cos(toRad(lat1)) * Math.cos(toRad(lat2))
            * Math.sin(dLng / 2) * Math.sin(dLng / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    },

    _extractCityFromAddress(address) {
        if (!address) return '';
        const parts = address.split(',').map(p => p.trim()).filter(Boolean);
        return parts.length > 0 ? parts[0] : address;
    },

    _calculateFallbackPrice({ distance, transportType, destinationCity, effectiveTransportType }) {
        const normalizedType = (effectiveTransportType || transportType || '').trim().toLowerCase();
        const baseTypeRate = this._getTypeBaseRate(normalizedType);
        const demandFactor = this._cityDemandFactor(destinationCity);
        const typePremium = this._getTypePremium(normalizedType);
        let price = distance * baseTypeRate * typePremium * (1 + demandFactor);
        const roundedPrice = this._roundPrice(price);

        // Apply minimum prices based on distance and transport type
        let minimumPrice = 0.5;
        if (normalizedType.includes('flight') || normalizedType.includes('plane')) {
            // Domestic flights (short distances)
            if (distance < 200) {
                minimumPrice = 50;  // Minimum for domestic flights
            }
            // Regional flights (North Africa/Mediterranean)
            else if (distance < 2000) {
                minimumPrice = 100;
            }
            // International flights (long distances)
            else {
                minimumPrice = 300;  // Minimum for intercontinental flights
            }
        } else if (normalizedType.includes('train') || normalizedType.includes('tgm')) {
            minimumPrice = distance > 500 ? 30 : 1.0;
        } else if (normalizedType.includes('bus')) {
            minimumPrice = distance > 500 ? 15 : 0.5;
        } else if (normalizedType.includes('taxi') || normalizedType.includes('car')) {
            minimumPrice = 2.0;
        }

        const finalPrice = Math.max(minimumPrice, roundedPrice);
        return Number(finalPrice.toFixed(2));
    },

    _getRoutePricingInfo({ transportType, distance, depLat, depLng, destLat, destLng }) {
        const normalizedType = (transportType || '').trim().toLowerCase();
        const routeInfo = { distance: NaN, effectiveTransportType: normalizedType, note: '' };

        if (normalizedType.includes('flight') || normalizedType.includes('plane') || normalizedType.includes('air')) {
            if ([depLat, depLng, destLat, destLng].some((value) => isNaN(value))) {
                return { error: 'Unable to determine airports for flight pricing. Please select both locations on the map.' };
            }

            const depAirport = this._getNearestAirport(depLat, depLng);
            const destAirport = this._getNearestAirport(destLat, destLng);
            const airportDistance = this._computeStraightLineKm(depAirport.lat, depAirport.lng, destAirport.lat, destAirport.lng);

            if (depAirport.code === destAirport.code || airportDistance < 10) {
                const fallbackDistance = !isNaN(distance) && distance > 0
                    ? distance
                    : this._computeStraightLineKm(depLat, depLng, destLat, destLng);
                return {
                    distance: fallbackDistance,
                    effectiveTransportType: 'car',
                    note: `Same airport detected (${depAirport.code}). Flight not recommended; use car/bus instead.`
                };
            }

            return {
                distance: airportDistance,
                effectiveTransportType: 'flight',
                note: `Flight distance via ${depAirport.code} → ${destAirport.code}.`
            };
        }

        if (!isNaN(distance) && distance > 0) {
            routeInfo.distance = distance;
            return routeInfo;
        }

        if ([depLat, depLng, destLat, destLng].every((value) => !isNaN(value))) {
            routeInfo.distance = this._computeStraightLineKm(depLat, depLng, destLat, destLng);
            return routeInfo;
        }

        return { error: 'Unable to determine route distance. Please select both departure and destination locations on the map.' };
    },

    async _fetchLivePricingSuggestion({ transportType, routeInfo, departure, destination, destinationCity }) {
        const transportKey = (transportType || '').trim().toLowerCase();

        const searchResponse = await model.searchInternetRoutePrice({
            transportType,
            departure,
            destination,
            destinationCity,
            routeDistance: routeInfo.distance
        });

        if (searchResponse && typeof searchResponse.price === 'number' && !Number.isNaN(searchResponse.price) && searchResponse.price > 0) {
            // Validate price is realistic for transport type
            if (transportKey.includes('flight') || transportKey.includes('plane')) {
                // Flights must be at least 30 TND; reject garbage matches
                if (searchResponse.price < 30) {
                    return null;
                }
            } else {
                // Other transport must be at least 1 TND
                if (searchResponse.price < 1) {
                    return null;
                }
            }
            
            this._lastInternetSearch = searchResponse;
            return searchResponse.price;
        }

        return null;
    },


    _getNearestAirport(lat, lng) {
        const airports = this._getAirports();
        let best = airports[0];
        let bestDistance = Infinity;
        airports.forEach((airport) => {
            const dist = this._computeStraightLineKm(lat, lng, airport.lat, airport.lng);
            if (dist < bestDistance) {
                bestDistance = dist;
                best = airport;
            }
        });
        return best;
    },

    _getAirports() {
        return [
            { code: 'TUN', name: 'Tunis Carthage Airport', lat: 36.8519, lng: 10.2270 },
            { code: 'NBE', name: 'Enfidha–Hammamet Intl', lat: 36.0733, lng: 10.3300 },
            { code: 'MIR', name: 'Monastir Habib Bourguiba Intl', lat: 35.7580, lng: 10.7547 },
            { code: 'DJE', name: 'Djerba–Zarzis Intl', lat: 33.8753, lng: 10.7758 },
            { code: 'SFA', name: 'Sfax–Thyna Intl', lat: 34.7478, lng: 10.6903 },
            { code: 'GAF', name: 'Gabes–Matmata Intl', lat: 33.8867, lng: 10.1018 },
            { code: 'CDG', name: 'Paris Charles de Gaulle', lat: 49.0097, lng: 2.5479 },
            { code: 'LHR', name: 'London Heathrow', lat: 51.4700, lng: -0.4543 },
            { code: 'FRA', name: 'Frankfurt Main', lat: 50.0379, lng: 8.5622 },
            { code: 'IST', name: 'Istanbul Airport', lat: 41.2753, lng: 28.7519 },
            { code: 'DXB', name: 'Dubai Intl', lat: 25.2532, lng: 55.3657 }
        ];
    },

    _roundPrice(price) {
        if (price <= 5) {
            return Math.round(price * 2) / 2; // 0.5 TND increments for small fares
        }
        return Math.round(price); // 1 TND increments for larger fares
    },

    _getTypeBaseRate(type) {
        if (!type) return 0.5;
        // Realistic Tunisian transport rates (TND per km)
        if (type.includes('taxi') || type.includes('car') || type.includes('van') || type.includes('shuttle')) return 0.8;
        if (type.includes('bus') || type.includes('autocar')) return 0.15;  // City bus ~0.15-0.20 TND/km
        if (type.includes('train') || type.includes('tgm')) return 0.12;   // Train ~0.10-0.15 TND/km
        if (type.includes('tram') || type.includes('metro') || type.includes('light rail')) return 0.10;  // Metro ~0.08-0.12 TND/km
        if (type.includes('ferry')) return 0.40;
        if (type.includes('flight') || type.includes('plane') || type.includes('air')) return 0.80;
        return 0.35;
    },

    _getTypePremium(type) {
        if (!type) return 1.0;
        if (type.includes('flight') || type.includes('plane') || type.includes('air')) return 1.30;
        if (type.includes('ferry')) return 1.10;
        if (type.includes('luxury') || type.includes('vip') || type.includes('express')) return 1.20;
        if (type.includes('night') || type.includes('premium')) return 1.10;
        return 1.00;
    },

    _cityDemandFactor(city) {
        if (!city) return 0.05;
        const popularCities = ['tunis', 'sousse', 'sfax', 'nabeul', 'monastir', 'gabes', 'bizerte', 'kairouan', 'sidi bou said', 'douz'];
        const cityKey = city.toLowerCase();
        const localBonus = popularCities.some(name => cityKey.includes(name)) ? 0.10 : 0;
        const touristBonus = cityKey.includes('beach') || cityKey.includes('hotel') || cityKey.includes('marina') ? 0.08 : 0;
        let hash = 0;
        for (let i = 0; i < cityKey.length; i++) {
            hash += cityKey.charCodeAt(i);
        }
        return Math.min(0.25, 0.04 + ((hash % 7) * 0.01) + localBonus + touristBonus);
    },

    async handleRouteRowAIPrice(dataset) {
        const transportType = (dataset.transportType || '').trim();
        const destination = dataset.destination || '';
        const depLat = parseFloat(dataset.depLat);
        const depLng = parseFloat(dataset.depLng);
        const destLat = parseFloat(dataset.destLat);
        const destLng = parseFloat(dataset.destLng);
        const suggestion = document.getElementById(`ai-suggestion-${dataset.id}`);

        if (!destination || !transportType) {
            view.renderToast('Cannot compute AI price for this route. Missing destination or transport type.', 'error');
            return;
        }

        const routeInfo = this._getRoutePricingInfo({
            transportType,
            distance: NaN,
            depLat,
            depLng,
            destLat,
            destLng
        });

        if (routeInfo.error) {
            view.renderToast(routeInfo.error, 'error');
            return;
        }

        const destinationCity = this._extractCityFromAddress(destination);
        const proposed = this._calculateFallbackPrice({
            distance: routeInfo.distance,
            transportType,
            destinationCity,
            effectiveTransportType: routeInfo.effectiveTransportType
        });

        if (suggestion) {
            suggestion.textContent = `AI best price: ${proposed.toFixed(2)} TND${routeInfo.note ? ' — ' + routeInfo.note : ''}`;
        }
        view.renderToast('AI price suggestion generated locally for this route.', 'success');
    },

    async handleTrajetAdd(formData) {
        const departure     = formData.get('departure')?.trim();
        const destination   = formData.get('destination')?.trim();
        const depDate       = formData.get('departureDate');
        const depTime       = formData.get('departureTime');
        const price         = parseFloat(formData.get('price'));
        const idTransport   = formData.get('idTransport');
        const depLat        = formData.get('depLat') ? parseFloat(formData.get('depLat')) : null;
        const depLng        = formData.get('depLng') ? parseFloat(formData.get('depLng')) : null;
        const destLat       = formData.get('destLat') ? parseFloat(formData.get('destLat')) : null;
        const destLng       = formData.get('destLng') ? parseFloat(formData.get('destLng')) : null;
        const depAddress    = formData.get('depAddress');
        const destAddress   = formData.get('destAddress');

        if (!departure || !destination || !depDate || !depTime || isNaN(price) || !idTransport) {
            view.renderToast('Please fill in all route fields.', 'error');
            return;
        }

        // Combine date and time to create a datetime
        const departureTime = `${depDate} ${depTime}:00`;

        // Check if this is an edit operation
        const submitBtn = document.querySelector('#add-trajet-form button[type="submit"]');
        const editId = submitBtn?.dataset.editId;

        let result;
        if (editId) {
            // Update existing trajet
            result = await model.updateTrajet(editId, {
                departure, destination, departureTime, price, idTransport,
                depLat, depLng, destLat, destLng, depAddress, destAddress
            });
        } else {
            // Add new trajet
            result = await model.addTrajet({
                departure, destination, departureTime, price, idTransport,
                depLat, depLng, destLat, destLng, depAddress, destAddress
            });
        }

        if (result !== null) {
            view.renderToast(editId ? 'Route updated.' : 'Route added.');
            await this._refreshTransport();
            // Reset form after successful operation
            this._resetTrajetForm();
        } else {
            view.renderToast(editId ? 'Failed to update route.' : 'Failed to add route.', 'error');
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

    async handleTrajetEdit(id) {
        // Get the trajet data
        const trajet = await model.getTrajet(id);
        if (!trajet) {
            view.renderToast('Route not found.', 'error');
            return;
        }

        // Show the add-trajet panel
        const trajetPanel = document.getElementById('add-trajet-panel');
        if (trajetPanel) {
            trajetPanel.style.display = 'block';
            setTimeout(() => initRouteMap(), 50);

            // Pre-populate the form
            const form = document.getElementById('add-trajet-form');
            if (form) {
                form.querySelector('[name="departure"]').value = trajet.departure || '';
                form.querySelector('[name="destination"]').value = trajet.destination || '';
                form.querySelector('[name="departureDate"]').value = trajet.departureTime ? trajet.departureTime.split(' ')[0] : '';
                form.querySelector('[name="departureTime"]').value = trajet.departureTime ? trajet.departureTime.split(' ')[1].substring(0, 5) : '';
                form.querySelector('[name="price"]').value = trajet.price || '';
                form.querySelector('[name="idTransport"]').value = trajet.idTransport || '';

                // Set coordinates if available
                document.getElementById('depLat').value = trajet.depLat || '';
                document.getElementById('depLng').value = trajet.depLng || '';
                document.getElementById('destLat').value = trajet.destLat || '';
                document.getElementById('destLng').value = trajet.destLng || '';
                document.getElementById('depAddress').value = trajet.depAddress || '';
                document.getElementById('destAddress').value = trajet.destAddress || '';

                // Change button text and add data attribute for update
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.textContent = 'UPDATE ROUTE';
                    submitBtn.dataset.editId = id;
                }
            }
        }
    },

    _resetVehicleForm() {
        const form = document.getElementById('add-vehicle-form');
        if (form) {
            form.reset();
            // Reset button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = 'ADD VEHICLE';
                delete submitBtn.dataset.editId;
            }
        }
    },

    _resetTrajetForm() {
        const form = document.getElementById('add-trajet-form');
        if (form) {
            form.reset();
            // Reset hidden fields
            document.getElementById('depLat').value = '';
            document.getElementById('depLng').value = '';
            document.getElementById('destLat').value = '';
            document.getElementById('destLng').value = '';
            document.getElementById('depAddress').value = '';
            document.getElementById('destAddress').value = '';
            document.getElementById('routeDistance').value = '';
            document.getElementById('routeTransportType').value = '';
            const suggestion = document.getElementById('ai-price-suggestion');
            if (suggestion) {
                suggestion.textContent = 'AI will suggest a best price once route and vehicle are selected.';
            }

            // Reset button
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.textContent = 'ADD ROUTE';
                delete submitBtn.dataset.editId;
            }
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
    },

    // =========================================================================
    // AI Feature Layer (Admin)
    // =========================================================================

    async fetchGroq(prompt, system = 'You are a helpful assistant.') {
        try {
            const res = await fetch('../../groq-proxy.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ prompt, system, max_tokens: 500 })
            });
            if (!res.ok) throw new Error('AI API Error: ' + res.status);
            const data = await res.json();
            return data.choices[0].message.content;
        } catch (e) {
            console.error('Groq Fetch Error:', e);
            throw e;
        }
    },

    async handleAIGenerateDesc() {
        const title = document.getElementById('prog-title')?.value;
        const category = document.getElementById('prog-category')?.value;
        const btn = document.getElementById('btn-ai-generate-desc');
        const descField = document.getElementById('prog-desc');
        
        if (!title || !category) {
            view.renderToast('Please fill in Title and Category first.', 'error');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="ai-loading-indicator">Generating</span>';
        
        const prompt = `Write a professional, 3-4 sentence public service description for a program titled "${title}" in the "${category}" category.`;
        
        try {
            const desc = await this.fetchGroq(prompt, 'You are an expert civic program copywriter. Write clear, engaging descriptions.');
            descField.value = desc;
        } catch(e) {
            view.renderToast('AI Generator unavailable.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = '✨ Generate with AI';
        }
    },

    async checkDuplicate(title, description) {
        const programs = model.getPrograms().filter(p => p.title !== title).map(p => p.title);
        const prompt = `New program: "${title}". Description: "${description}".\nExisting programs: ${JSON.stringify(programs)}\n\nIs there a significant overlap or near exact duplicate? Return ONLY raw JSON: {"isDuplicate": boolean, "matchedTitle": "name or null", "reason": "brief reason or null"}`;
        
        try {
            const jsonStr = await this.fetchGroq(prompt, 'You are an AI duplication detector. Return ONLY raw JSON.');
            return JSON.parse(jsonStr.replace(/```json/g, '').replace(/```/g, '').trim());
        } catch(e) {
            return { isDuplicate: false };
        }
    },

    async handleAIAnalyzeEnrollment(id, btn) {
        const prog = model.getPrograms().find(p => p.id == id);
        const enrollments = model.getEnrollments(null).filter(e => e.program_id == id);
        const count = enrollments.length;
        const target = document.getElementById('ai-explanation-' + id);
        
        btn.disabled = true;
        target.style.display = 'block';
        target.innerHTML = '<span class="ai-loading-indicator">Analyzing...</span>';

        const prompt = `Program: ${prog.title}\nCapacity: ${prog.capacity}\nEnrolled: ${count}\nCategory: ${prog.category}\n\nExplain this enrollment performance in 2 sentences. Is it high, low, or expected?`;

        try {
            const analysis = await this.fetchGroq(prompt, 'You are a data analyst for a civic portal.');
            target.innerHTML = analysis;
            btn.style.display = 'none'; // hide button after analyzing
        } catch(e) {
            target.innerHTML = 'Analysis failed.';
            btn.disabled = false;
        }
    },

    async handleAIAuditDescriptions(btn) {
        const programs = model.getPrograms();
        const target = document.getElementById('ai-audit-results');
        
        btn.disabled = true;
        target.style.display = 'block';
        target.innerHTML = '<span class="ai-loading-indicator">Auditing all descriptions. This may take a moment...</span>';

        // We batch them to avoid massive token usage or prompt limits, or send all at once if small.
        const input = programs.map(p => ({ id: p.id, title: p.title, desc: p.description }));
        const prompt = `Audit these civic program descriptions. Flag ones that are too vague, too short, or poor quality.\n${JSON.stringify(input)}\n\nReturn ONLY a raw JSON array: [{"id": 1, "status": "OK|Flagged", "suggestion": "Why it was flagged"}].`;

        try {
            const jsonStr = await this.fetchGroq(prompt, 'You are an accessibility and content auditor. Return ONLY raw JSON array.');
            const results = JSON.parse(jsonStr.replace(/```json/g, '').replace(/```/g, '').trim());
            
            let html = '<table class="ai-audit-table"><tr><th>Program</th><th>Status</th><th>Suggestion</th></tr>';
            results.forEach(r => {
                const p = programs.find(x => x.id == r.id);
                const title = p ? p.title : 'Unknown';
                const cls = r.status === 'Flagged' ? 'flagged' : '';
                html += `<tr class="${cls}"><td>${title}</td><td>${r.status}</td><td>${r.suggestion}</td></tr>`;
            });
            html += '</table>';
            target.innerHTML = html;
        } catch(e) {
            target.innerHTML = 'Audit failed or rate limit exceeded.';
        } finally {
            btn.disabled = false;
        }
    }
};

export default controller;




