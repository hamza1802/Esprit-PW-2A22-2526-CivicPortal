/**
 * model.js
 * Client-side Model for CivicPortal BackOffice
 */

const model = {
    state: {
        currentUser:     window.SERVER_USER || null,
        serviceRequests: [],
        programs:        [],
        categories:      [],
        users:           [],
        stats:           null
    },

    async apiCall(action, data = {}) {
        try {
            let options = { method: 'POST', body: null };

            if (data instanceof FormData) {
                data.append('action', action);
                options.body = data;
            } else {
                options.headers = { 'Content-Type': 'application/json' };
                options.body = JSON.stringify({ action, data });
            }

            const response = await fetch('../../Verification.php', options);
            const result   = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error('API Error:', error);
            return null;
        }
    },

    // -------------------------------------------------------------------------
    // Sync: parallelized initial data load
    // -------------------------------------------------------------------------
    async sync() {
        const [requests, programs, categories] = await Promise.all([
            this.apiCall('get_requests'),
            this.apiCall('get_programs'),
            this.apiCall('get_categories'),
        ]);
        if (requests)   this.state.serviceRequests = requests;
        if (programs)   this.state.programs        = programs;
        if (categories) this.state.categories      = categories;
    },

    // -------------------------------------------------------------------------
    // Categories
    // -------------------------------------------------------------------------
    getCategories() { return this.state.categories; },

    async addCategory(name) {
        const result = await this.apiCall('add_category', { name });
        if (result) { await this.syncCategories(); return true; }
        return false;
    },

    async updateCategory(id, name) {
        const result = await this.apiCall('update_category', { id, name });
        if (result) { await this.syncCategories(); return true; }
        return false;
    },

    async deleteCategory(id) {
        const result = await this.apiCall('delete_category', { id });
        if (result) { await this.syncCategories(); return true; }
        return false;
    },

    async syncCategories() {
        const categories = await this.apiCall('get_categories');
        if (categories) this.state.categories = categories;
    },

    // -------------------------------------------------------------------------
    // Programs
    // -------------------------------------------------------------------------
    getPrograms() { return this.state.programs; },
    getProgram(id) { return this.state.programs.find(p => p.id == id); },

    async saveProgram(data) {
        const id     = (data instanceof FormData) ? data.get('id') : data.id;
        const action = id ? 'update_program' : 'add_program';
        const result = await this.apiCall(action, data);
        if (result) { await this.sync(); return true; }
        return false;
    },

    async deleteProgram(id) {
        const result = await this.apiCall('delete_program', { id });
        if (result) { await this.sync(); return true; }
        return false;
    },

    async getPendingEnrollments() {
        return await this.apiCall('get_pending_enrollments');
    },

    async getEnrollmentsByProgram(programId) {
        return await this.apiCall('get_enrollments_by_program', { programId });
    },

    async getProgramDetail(id) {
        return await this.apiCall('get_program_detail', { id });
    },

    async getEnrollmentCounts() {
        return await this.apiCall('get_enrollment_counts');
    },

    async updateEnrollmentStatus(id, status) {
        const result = await this.apiCall('update_enrollment_status', { id, status });
        return !!result;
    },

    // -------------------------------------------------------------------------
    // Service Requests
    // -------------------------------------------------------------------------
    getServiceRequests() { return this.state.serviceRequests; },

    async updateRequestStatus(requestId, status) {
        const response = await this.apiCall('update_status', { id: requestId, status });
        if (response !== null) {
            const req = this.state.serviceRequests.find(r => r.id === requestId);
            if (req) req.status = status;
        }
    },

    async assignRequest(requestId, agentId) {
        return await this.apiCall('assign_request', { id: requestId, agent_id: agentId });
    },

    async getAgents() {
        return await this.apiCall('get_agents');
    },

    // -------------------------------------------------------------------------
    // User Management (admin only)
    // -------------------------------------------------------------------------
    async getUsers() {
        const data = await this.apiCall('get_users');
        if (data) this.state.users = data;
        return data;
    },

    getStoredUsers() { return this.state.users; },

    async toggleUserActive(userId, active) {
        return await this.apiCall('toggle_user_active', { id: userId, active });
    },

    // -------------------------------------------------------------------------
    // Appointments
    // -------------------------------------------------------------------------
    async getAgentAppointments() {
        return await this.apiCall('get_agent_appointments');
    },

    async getAllAppointments() {
        return await this.apiCall('get_all_appointments');
    },

    async updateAppointmentStatus(id, status, extra = {}) {
        return await this.apiCall('update_appointment_status', { id, status, ...extra });
    },

    // -------------------------------------------------------------------------
    // Appointment Slots (admin only)
    // -------------------------------------------------------------------------
    async getSlots() {
        return await this.apiCall('get_all_slots');
    },

    async createSlot(data) {
        return await this.apiCall('create_slot', data);
    },

    async deleteSlot(id) {
        return await this.apiCall('delete_slot', { id });
    },

    getServiceTypes() {
        return [
            'Birth Certificate', 'ID Card Renewal', 'Residence Certificate',
            'Building Permit', 'General Inquiry', 'Document Verification'
        ];
    },

    // -------------------------------------------------------------------------
    // Transport management (admin) — routes to api_transport.php
    // -------------------------------------------------------------------------
    async transportApi(action, data = {}) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, ...data })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error('Transport API Error:', error);
            return null;
        }
    },

    async getTransportOverview() {
        const [types, vehicles, trajets] = await Promise.all([
            this.transportApi('list_transport_types'),
            this.transportApi('list_transports'),
            this.transportApi('list_all_trajets'),
        ]);
        return { types: types || [], vehicles: vehicles || [], trajets: trajets || [] };
    },

    async addVehicle(data)          { return await this.transportApi('add_transport',   data); },
    async getVehicle(id)           { return await this.transportApi('get_transport',   { idTransport: id }); },
    async updateVehicle(id, data)  { return await this.transportApi('update_transport', { idTransport: id, ...data }); },
    async deleteVehicle(id)         { return await this.transportApi('delete_transport', { idTransport: id }); },
    async addTrajet(data)           { return await this.transportApi('add_trajet',       data); },
    async getTrajet(id)             { return await this.transportApi('get_trajet',       { idTrajet: id }); },
    async updateTrajet(id, data)    { return await this.transportApi('update_trajet',    { idTrajet: id, ...data }); },
    async deleteTrajet(id)          { return await this.transportApi('delete_trajet',    { idTrajet: id }); },
    async searchInternetRoutePrice(data) { return await this.transportApi('search_route_price', data); },

    async addTransportType(formData) { return await this.apiCall('add_transport_type',    formData); },
    async deleteTransportType(id)    { return await this.apiCall('delete_transport_type', { id }); },

    // -------------------------------------------------------------------------
    // Extended user admin actions
    // -------------------------------------------------------------------------
    async createUser(data)                   { return await this.apiCall('create_user', data); },
    async deleteUser(id)                     { return await this.apiCall('delete_user', { id }); },
    async updateUserRole(id, role, name, email) {
        return await this.apiCall('update_user', { id, name, email, role });
    },

    // -------------------------------------------------------------------------
    // Profile / auth helpers
    // -------------------------------------------------------------------------
    setCurrentUser(role) {
        if (this.state.currentUser) this.state.currentUser.role = role;
    },

    getCurrentUser() { return this.state.currentUser; },

    updateUser(data) {
        if (this.state.currentUser) {
            this.state.currentUser.name  = data.name;
            this.state.currentUser.email = data.email;
        }
    },

    // -------------------------------------------------------------------------
    // Stats
    // -------------------------------------------------------------------------
    async getStats() {
        const stats = await this.apiCall('get_stats');
        return stats || {
            usersCount:        0,
            programsCount:     this.state.programs.length,
            requestsCount:     this.state.serviceRequests.length,
            enrollmentsCount:  0,
            appointmentsCount: 0
        };
    },


    // --- Transport API ---
    async getTransportTypes() { return await this.apiCall('list_transport_types'); },
    async saveTransportType(formData) {
        // Must send as multipart to support file upload
        const id = formData.get('idTransportType');
        formData.set('action', id ? 'update_transport_type' : 'add_transport_type');
        try {
            const response = await fetch('../../Verification.php', {
                method: 'POST',
                body: formData  // No Content-Type header — browser sets multipart boundary
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error('Transport Type Save Error:', error);
            return null;
        }
    },
    async deleteTransportType(idTransportType) { return await this.apiCall('delete_transport_type', { idTransportType }); },

    async getTransports() { return await this.apiCall('list_transports'); },
    async saveTransport(formData) {
        const data = Object.fromEntries(formData.entries());
        const id = data.idTransport;
        return await this.apiCall(id ? 'update_transport' : 'add_transport', data);
    },
    async deleteTransport(idTransport) { return await this.apiCall('delete_transport', { idTransport }); },

    async getTrajets() { return await this.apiCall('list_all_trajets'); },
    async saveTrajet(formData) {
        const data = Object.fromEntries(formData.entries());
        const id = data.idTrajet;
        return await this.apiCall(id ? 'update_trajet' : 'add_trajet', data);
    },
    async deleteTrajet(idTrajet) { return await this.apiCall('delete_trajet', { idTrajet }); },

    async getTickets() { return await this.apiCall('list_tickets_enriched'); },
    async cancelTicket(idTicket) { return await this.apiCall('cancel_ticket', { idTicket }); },

    // -------------------------------------------------------------------------
    // Forum Moderation (admin)
    // -------------------------------------------------------------------------
    async getForumPosts(category = null, status = null) {
        const data = {};
        if (category) data.category = category;
        if (status) data.status = status;
        return await this.apiCall('get_forum_posts', data);
    },

    async getForumComments(postId = null) {
        return await this.apiCall('get_forum_comments', postId ? { post_id: postId } : {});
    },

    async forumUpdateStatus(postId, status) {
        return await this.apiCall('forum_update_status', { post_id: postId, status });
    },

    async forumDeletePost(postId) {
        return await this.apiCall('forum_delete_post', { post_id: postId });
    },

    async forumDeleteComment(commentId) {
        return await this.apiCall('forum_delete_comment', { comment_id: commentId });
    },

    async getForumStats() {
        return await this.apiCall('get_forum_stats');
    }
};

export default model;

