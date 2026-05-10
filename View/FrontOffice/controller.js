/**
 * controller.js
 * FrontOffice Event Handler
 */

import model from './model.js';
import view from './view.js';

const controller = {
    /** Filters for the citizen "My Requests" dashboard panel. */
    myReqFilters: { search: '', status: '', sort: 'date_desc' },
    _myReqSearchDebounce: null,

    async init() {
        try {
            console.log('Controller: Starting initialization...');
            this.setupEventListeners();
            
            const user = model.getCurrentUser();
            view.renderNavBar(user);
            this.handleRouting();

            console.log('Controller: Syncing model...');
            await model.sync();
            
            this.handleRouting();
            view.renderNavBar(model.getCurrentUser());
            console.log('Controller: Initialization complete!');
        } catch (error) {
            console.error('FATAL ERROR during initialization:', error);
            console.error('Stack:', error.stack);
            view.renderToast('Error loading CivicPortal: ' + error.message, 'error');
        }
    },

    setupEventListeners() {
        document.addEventListener('click', (e) => {
            if (e.target.id === 'btn-ai-match') {
                this.handleAIMatch();
                return;
            }
            if (e.target.classList.contains('btn-ai-simplify')) {
                this.handleAISimplify(e.target.dataset.id, e.target);
                return;
            }
            if (e.target.closest('#btn-ai-improve-req')) {
                e.preventDefault();
                this.handleAIImproveRequest(e.target.closest('#btn-ai-improve-req'));
                return;
            }
            if (e.target.id === 'btn-ai-apply-desc') {
                e.preventDefault();
                const improved  = document.getElementById('ai-req-improved');
                const target    = document.getElementById('req-description');
                if (improved && target) {
                    target.value = improved.value;
                    view.renderToast('Improved description applied.');
                }
                return;
            }

            if (e.target.id === 'my-req-reset') {
                e.preventDefault();
                this.myReqFilters = { search: '', status: '', sort: 'date_desc' };
                view.refreshMyRequestsList(model.getServiceRequests(), this.myReqFilters);
                return;
            }

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
            if (action === 'view-request') {
                window.location.hash = `#request-details/${id}`;
            }
            if (action === 'edit-request') {
                window.location.hash = `#request-edit/${id}`;
            }
            if (action === 'delete-request') {
                if (confirm('Are you sure you want to delete this request? This cannot be undone.')) {
                    this.handleDeleteRequest(parseInt(id));
                }
            }
            if (action === 'delete-document') {
                if (confirm('Delete this document?')) {
                    this.handleDeleteDocument(parseInt(id), parseInt(target.dataset.requestId));
                }
            }
        });

        document.addEventListener('input', (e) => {
            if (e.target.id === 'prog-search') this.handleCatalogFilter();
            if (e.target.id === 'my-req-search') {
                clearTimeout(this._myReqSearchDebounce);
                const value = e.target.value;
                this._myReqSearchDebounce = setTimeout(() => {
                    this.myReqFilters.search = value;
                    view.refreshMyRequestsList(model.getServiceRequests(), this.myReqFilters);
                    const el = document.getElementById('my-req-search');
                    if (el) {
                        el.focus();
                        if (el.setSelectionRange) el.setSelectionRange(el.value.length, el.value.length);
                    }
                }, 250);
            }
        });

        document.addEventListener('change', (e) => {
            if (e.target.id === 'prog-filter-cat') this.handleCatalogFilter();

            if (e.target.id === 'my-req-status') {
                this.myReqFilters.status = e.target.value;
                view.refreshMyRequestsList(model.getServiceRequests(), this.myReqFilters);
            }
            if (e.target.id === 'my-req-sort') {
                this.myReqFilters.sort = e.target.value;
                view.refreshMyRequestsList(model.getServiceRequests(), this.myReqFilters);
            }

            // Service-request form: swap required-doc inputs when service type changes.
            if (e.target.id === 'req-type') {
                view.refreshRequiredDocs(model.getRequiredDocsFor(e.target.value));
            }
        });

        window.addEventListener('hashchange', () => this.handleRouting());

        document.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (e.target.id === 'service-request-form') {
                await this.handleServiceRequest(new FormData(e.target));

            } else if (e.target.id === 'edit-request-form') {
                await this.handleEditRequestSubmit(e.target);

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

        if (hashPath.startsWith('#request-details/')) {
            await this.showRequestDetail(parseInt(hashPath.split('/')[1], 10));
            return;
        }
        if (hashPath.startsWith('#request-edit/')) {
            await this.showRequestEditForm(parseInt(hashPath.split('/')[1], 10));
            return;
        }

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
                window.location.hash = '#dashboard';
                break;
            }

            case '#appointments':
                view.renderAppointmentForm(model.getServiceTypes());
                break;

            case '#my-appointments': {
                window.location.hash = '#dashboard';
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
                let type      = params.get('type');
                const sortBy  = params.get('sort')  || 'departure';
                const order   = params.get('order') || 'ASC';

                // If no type specified, use the first available transport type
                if (!type) {
                    const transportTypes = model.getTransportTypes();
                    type = transportTypes && transportTypes.length > 0 ? transportTypes[0].name : 'Bus';
                }

                let trajets = await model.getTrajetsByTypeAndSort(type, sortBy, order);

                // If no routes found for the requested type, try the first available transport type
                if ((!trajets || trajets.length === 0) && type !== 'Bus') {
                    const transportTypes = model.getTransportTypes();
                    if (transportTypes && transportTypes.length > 0) {
                        const firstType = transportTypes[0].name;
                        if (firstType !== type) {
                            type = firstType;
                            trajets = await model.getTrajetsByTypeAndSort(type, sortBy, order);
                        }
                    }
                }

                view.renderTransportList(type, trajets || [], sortBy, order);
                break;
            }

            case '#my-tickets': {
                window.location.hash = '#dashboard';
                break;
            }

            case '#dashboard': {
                const [requests, appointments, tickets] = await Promise.all([
                    model.getMyRequests(),
                    model.getMyAppointments(),
                    model.getMyTickets()
                ]);
                view.renderDashboard(user, { 
                    requests: requests || [], 
                    appointments: appointments || [], 
                    tickets: tickets || [],
                    programs: model.getPrograms() || [],
                    enrollments: model.getEnrollments(user.id) || [],
                    posts: model.getMyPosts() || []
                });
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
        // Collect the per-service required documents (input names start with "req_doc_").
        const docInputs = Array.from(document.querySelectorAll('input.req-required-doc'));
        const requiredEntries = docInputs.map(inp => ({
            file:    inp.files?.[0] || null,
            label:   inp.dataset.label   || '',
            docType: inp.dataset.doctype || 'other',
        }));

        const missing = requiredEntries.filter(e => !e.file);
        if (missing.length > 0) {
            view.renderToast(`Missing ${missing.length} required document(s).`, 'error');
            return;
        }

        // Strip the per-doc entries from FormData so the JSON request payload
        // stays clean — they're uploaded via the dedicated multipart endpoint.
        for (const inp of docInputs) formData.delete(inp.name);

        const result = await model.addServiceRequest(formData);
        if (!result) {
            view.renderToast('Failed to submit request. Please try again.', 'error');
            return;
        }

        // Upload each required document with its proper docType.
        if (requiredEntries.length > 0 && result.id) {
            view.renderToast(`Uploading ${requiredEntries.length} document(s)...`);
            const groups = requiredEntries.reduce((acc, e) => {
                (acc[e.docType] ||= []).push(e.file);
                return acc;
            }, {});
            let uploaded = 0;
            let total    = requiredEntries.length;
            for (const [docType, files] of Object.entries(groups)) {
                const docs = await model.uploadRequestDocuments(result.id, files, docType);
                if (Array.isArray(docs)) uploaded += docs.length;
            }
            if (uploaded < total) {
                view.renderToast(`Request created. ${uploaded}/${total} files uploaded.`, 'error');
            }
        }

        view.renderToast('Service request submitted successfully!');
        window.location.hash = '#dashboard';
    },

    async handleEditRequestSubmit(form) {
        const id   = parseInt(form.dataset.id, 10);
        const desc = (form.querySelector('[name="description"]')?.value || '').trim();
        if (!id || desc.length < 10) {
            view.renderToast('Description must be at least 10 characters.', 'error');
            return;
        }
        const result = await model.updateRequest(id, desc);
        if (result) {
            view.renderToast('Request updated.');
            window.location.hash = `#request-details/${id}`;
        } else {
            view.renderToast('Failed to update request.', 'error');
        }
    },

    async handleDeleteRequest(id) {
        const ok = await model.deleteRequest(id);
        if (ok) {
            view.renderToast('Request deleted.');
            await model.getMyRequests();
            window.location.hash = '#dashboard';
        } else {
            view.renderToast('Failed to delete request.', 'error');
        }
    },

    async handleDeleteDocument(docId, requestId) {
        const ok = await model.deleteDocument(docId);
        if (ok) {
            view.renderToast('Document removed.');
            if (requestId) await this.showRequestDetail(requestId);
        } else {
            view.renderToast('Failed to delete document.', 'error');
        }
    },

    async showRequestDetail(id) {
        const request = await model.getRequestDetail(id);
        if (!request) {
            view.renderToast('Request not found.', 'error');
            window.location.hash = '#dashboard';
            return;
        }
        view.renderRequestDetail(request);
    },

    async showRequestEditForm(id) {
        const request = await model.getRequestDetail(id);
        if (!request) {
            view.renderToast('Request not found.', 'error');
            window.location.hash = '#dashboard';
            return;
        }
        if ((request.status || 'pending') !== 'pending') {
            view.renderToast('Only pending requests can be edited.', 'error');
            window.location.hash = `#request-details/${id}`;
            return;
        }
        view.renderRequestEditForm(request);
    },

    /** Ask the AI to improve the description and review attached files. */
    async handleAIImproveRequest(btn) {
        const typeEl = document.getElementById('req-type');
        const descEl = document.getElementById('req-description');

        const serviceType = typeEl?.value || '';
        const description = (descEl?.value || '').trim();

        // Build the requiredDocuments[] payload from the per-service required
        // file inputs so the AI sees exactly the same checklist the citizen
        // has to satisfy.
        const fileEntries = [];
        const pushFile    = (label, docType, file) => {
            if (!file) {
                fileEntries.push({ label, docType, provided: false, fileName: '', type: '' });
                return;
            }
            fileEntries.push({ label, docType, provided: true, fileName: file.name, type: file.type, _file: file });
        };

        const requiredInputs = Array.from(document.querySelectorAll('input.req-required-doc'));
        requiredInputs.forEach(inp => {
            pushFile(
                inp.dataset.label   || 'Required document',
                inp.dataset.doctype || 'other',
                inp.files?.[0] || null
            );
        });

        // Encode each provided file as base64 (cap at 4 MB so the request
        // payload doesn't explode).
        const PER_FILE_MAX = 4 * 1024 * 1024;
        const encoded = await Promise.all(fileEntries.map(async (entry) => {
            if (!entry.provided || !entry._file) {
                delete entry._file;
                return entry;
            }
            const f = entry._file;
            delete entry._file;
            if (f.size > PER_FILE_MAX) {
                entry.tooLarge = true;
                return entry;
            }
            entry.base64Data = await new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload  = () => resolve(String(reader.result || '').split(',')[1] || '');
                reader.onerror = reject;
                reader.readAsDataURL(f);
            });
            entry.mimeType = f.type;
            return entry;
        }));

        const original = btn.innerHTML;
        btn.disabled  = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Asking AI...';

        try {
            const res = await model.aiImproveRequest(serviceType, description, encoded);
            if (!res) {
                view.renderToast('AI is currently unavailable.', 'error');
                view.renderAIImproveResult(null);
                return;
            }
            view.renderAIImproveResult(res);
            if (res.status !== 'ok') {
                view.renderToast(res.message || 'AI fallback used.', 'error');
            }
        } catch (e) {
            console.error('[AI improve] error:', e);
            view.renderToast('AI request failed.', 'error');
        } finally {
            btn.disabled  = false;
            btn.innerHTML = original;
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
            window.location.hash = '#dashboard';
        } else {
            view.renderToast('Failed to book appointment. The slot may be unavailable.', 'error');
        }
    },

    async handleCancelAppointment(id) {
        if (!confirm('Are you sure you want to cancel this appointment?')) return;

        const result = await model.cancelAppointment(id);
        if (result) {
            view.renderToast('Appointment cancelled.');
            this.handleRouting();
        } else {
            view.renderToast('Failed to cancel appointment.', 'error');
        }
    },

    async handleTicketBooking(idTrajet, citizenName) {
        const result = await model.bookTicket(idTrajet, citizenName);
        if (result?.success) {
            view.renderToast(`Ticket booked! Reference: ${result.ref}`);
            window.location.hash = '#dashboard';
        } else {
            view.renderToast(result?.error || 'Booking failed.', 'error');
        }
    },

    async handleCancelTicket(idTicket) {
        if (!confirm('Are you sure you want to cancel this booking?')) return;

        const result = await model.cancelTicket(idTicket);
        if (result?.success) {
            view.renderToast('Booking cancelled.');
            this.handleRouting();
        } else {
            view.renderToast('Failed to cancel ticket.', 'error');
        }
    },

    // =========================================================================
    // AI Feature Layer (Citizen)
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

    async handleAIMatch() {
        const input = document.getElementById('ai-match-input').value;
        const btn = document.getElementById('btn-ai-match');
        const resultsContainer = document.getElementById('ai-match-results');
        if (!input) return;

        btn.disabled = true;
        btn.innerHTML = '<span class="ai-loading-indicator">Thinking</span>';
        resultsContainer.style.display = 'grid';
        resultsContainer.innerHTML = '<p class="ai-loading-indicator">Analyzing program catalog...</p>';

        const programs = model.getPrograms().map(p => ({ id: p.id, title: p.title, description: p.description, category: p.category }));
        const prompt = `CITIZEN INPUT: "${input}"\n\nPROGRAM CATALOG: ${JSON.stringify(programs)}`;

        try {
            const systemPrompt = `You are an expert civic program eligibility matcher for CivicPortal.

Your job: analyze the citizen's input and match them to the most relevant programs from the provided list.

MATCHING RULES:
- Match based on: age indicators, life situation, keywords, goals, and eligibility signals
- Rank results by relevance (highest first)
- Never invent programs not in the provided list
- If no strong match exists, return the closest partial matches with honest scores

OUTPUT: Return ONLY a raw JSON array. No markdown, no explanation, no wrapping text.

FORMAT:
[
  {
    "program_id": 12,
    "program_name": "Youth Tech Workshop 2026",
    "category": "Education",
    "match_score": 92,
    "match_reason": "Citizen described being a student aged 16 seeking tech skills — this program targets teenagers with technology training."
  }
]

SCORING GUIDE:
- 90–100: Direct match on multiple eligibility signals
- 70–89: Strong match on at least one key signal
- 50–69: Partial match, worth considering
- Below 50: Omit from results`;

            let jsonStr = await this.fetchGroq(prompt, systemPrompt);
            
            // Robust JSON extraction
            const jsonMatch = jsonStr.match(/\[[\s\S]*\]/);
            if (jsonMatch) jsonStr = jsonMatch[0];
            
            const matches = JSON.parse(jsonStr.trim());
            
            if (!Array.isArray(matches) || matches.length === 0) {
                resultsContainer.innerHTML = '<p>No specific matches found. Try browsing the catalog below!</p>';
                return;
            }

            let html = '<h4><i class="bi bi-stars"></i> Top AI Eligibility Matches</h4>';
            matches.forEach(m => {
                const prog = model.getPrograms().find(p => p.id == m.program_id);
                if (prog) {
                    html += `
                        <div style="background:var(--surface-glass); border:var(--surface-border); padding:1rem; border-radius:var(--radius-sm); position:relative;">
                            <div style="position:absolute; top:1rem; right:1rem; background:var(--primary-navy); color:white; font-size:0.7rem; padding:0.2rem 0.5rem; border-radius:10px; font-weight:800;">${m.match_score}% Match</div>
                            <h5 style="margin:0 0 0.5rem 0; color:var(--primary-navy); padding-right: 4rem;">${prog.title}</h5>
                            <span class="category-badge" style="font-size:0.7rem; padding:0.2rem 0.5rem;">${prog.category}</span>
                            <div class="ai-match-reason" style="font-size:0.85rem; margin-top:0.5rem; line-height:1.4; color:var(--text-main);">${m.match_reason}</div>
                            <button class="btn btn-small btn-primary" onclick="document.querySelector('[data-action=\\'enroll\\'][data-id=\\'${prog.id}\\']').click()" style="margin-top:0.5rem; width:100%;">Enroll Now</button>
                        </div>
                    `;
                }
            });
            resultsContainer.innerHTML = html;
        } catch (e) {
            resultsContainer.innerHTML = '<p style="color:var(--color-danger);">AI is currently unavailable. Please browse the catalog manually.</p>';
        } finally {
            btn.disabled = false;
            btn.textContent = 'Match Me';
        }
    },

    async handleAISimplify(id, btn) {
        const descEl = document.getElementById('prog-desc-' + id);
        if (!descEl) return;
        const originalText = descEl.dataset.originalDesc;
        
        if (btn.dataset.simplified === 'true') {
            descEl.textContent = originalText;
            btn.innerHTML = '✨ Simplify';
            btn.dataset.simplified = 'false';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<span class="ai-loading-indicator"></span>';

        const prompt = `Rewrite this program description in very simple, accessible language (B1 English level). Maximum 3 short sentences. No jargon.\n\nDescription: ${originalText}`;
        
        try {
            const simplified = await this.fetchGroq(prompt, 'You simplify civic texts for general accessibility.');
            descEl.textContent = simplified;
            btn.innerHTML = '🔄 Original';
            btn.dataset.simplified = 'true';
        } catch(e) {
            view.renderToast('AI Simplifier unavailable.', 'error');
            btn.innerHTML = '✨ Simplify';
        } finally {
            btn.disabled = false;
        }
    },

    async handleAIAssistant(id, inputEl, btn) {
        const val = inputEl.value;
        if (!val) return;

        const historyEl = document.getElementById('ai-history-' + id);
        const prog = model.getPrograms().find(p => p.id == id);
        
        // Count exchanges
        let exchanges = parseInt(btn.dataset.exchanges || '0');
        if (exchanges >= 3) {
            view.renderToast('AI limit reached for this session.', 'error');
            return;
        }

        historyEl.innerHTML += `<div class="ai-bubble ai-bubble-user">${val}</div>`;
        inputEl.value = '';
        btn.disabled = true;
        
        const loadingId = 'loading-' + Date.now();
        historyEl.innerHTML += `<div class="ai-bubble ai-bubble-ai" id="${loadingId}"><span class="ai-loading-indicator">Typing</span></div>`;
        historyEl.scrollTop = historyEl.scrollHeight;

        const context = `Program Title: ${prog.title}\nCategory: ${prog.category}\nDates: ${prog.start_date} to ${prog.end_date}\nDescription: ${prog.description}`;
        const prompt = `User question: ${val}\n\nProgram Context:\n${context}`;

        try {
            const reply = await this.fetchGroq(prompt, 'You are an enrollment assistant. Answer questions concisely based ONLY on the provided context. If unknown, say "I don\'t have that information."');
            document.getElementById(loadingId).outerHTML = `<div class="ai-bubble ai-bubble-ai">${reply}</div>`;
            btn.dataset.exchanges = exchanges + 1;
        } catch(e) {
            document.getElementById(loadingId).outerHTML = `<div class="ai-bubble ai-bubble-ai" style="color:red;">Sorry, I encountered an error.</div>`;
        } finally {
            btn.disabled = false;
            historyEl.scrollTop = historyEl.scrollHeight;
        }
    }
};

export default controller;

