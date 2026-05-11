/**
 * model.js
 * Client-side Model for CivicPortal FrontOffice.
 * Modules: Programs, Service Requests, Transport, Appointments, Notifications.
 * Complaints/Grievances REMOVED.
 */

const model = {
    state: {
        currentUser: window.SERVER_USER ?? {
            id: 0, name: 'Citizen', role: 'citizen', email: '',
            bio: '', phoneNumber: '', dateOfBirth: ''
        },
        programs: [],
        serviceRequests: [],
        enrollments: [],
        transportTypes: [],
        myTickets: [],
        myAppointments: [],
        notifications: [],
        serviceTypes: [],
        myPosts: []
    },

    // -------------------------------------------------------------------------
    // Core API helper — routes through Verification.php
    // -------------------------------------------------------------------------
    async apiCall(action, data = {}) {
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000); // 8 second timeout
            
            const response = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, data }),
                signal: controller.signal
            });
            clearTimeout(timeout);
            
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.error('API Timeout:', action);
            } else {
                console.error('API Error:', error);
            }
            return null;
        }
    },

    /**
     * Multipart API call for file uploads.
     */
    async apiUpload(action, formData) {
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 15000); // 15 second timeout for uploads
            
            formData.append('action', action);
            const response = await fetch('../../Verification.php', {
                method: 'POST',
                body: formData,
                signal: controller.signal
            });
            clearTimeout(timeout);
            
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.error('Upload Timeout:', action);
            } else {
                console.error('Upload Error:', error);
            }
            return null;
        }
    },

    // -------------------------------------------------------------------------
    // Transport API helper
    // -------------------------------------------------------------------------
    async transportApi(action, data = {}) {
        try {
            const controller = new AbortController();
            const timeout = setTimeout(() => controller.abort(), 8000); // 8 second timeout
            
            const response = await fetch('../../api_transport.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, ...data }),
                signal: controller.signal
            });
            clearTimeout(timeout);
            
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            if (error.name === 'AbortError') {
                console.error('Transport API Timeout:', action);
            } else {
                console.error('Transport API Error:', error);
            }
            return null;
        }
    },

    // -------------------------------------------------------------------------
    // Sync: fetch all data the front-office needs on load
    // -------------------------------------------------------------------------
    async sync() {
        console.log('Model: Starting sync...');
        const user = this.getCurrentUser();
        
        try {
            const publicPromises = [
                this.apiCall('get_programs'),
                this.transportApi('list_transport_types'),
                this.apiCall('get_service_types')
            ];

            const [programs, transportTypes, serviceTypes] = await Promise.all(publicPromises);
            
            if (programs) this.state.programs = programs;
            if (transportTypes) this.state.transportTypes = transportTypes;
            if (serviceTypes) this.state.serviceTypes = serviceTypes;

            if (!user.isGuest) {
                console.log('Model: Fetching personal data for authenticated user...');
                const privatePromises = [
                    this.apiCall('get_my_requests'),
                    this.apiCall('get_enrollments', { userId: user.id }),
                    this.apiCall('get_my_posts')
                ];
                
                const [requests, enrollments, posts] = await Promise.all(privatePromises);
                
                if (requests) this.state.serviceRequests = requests;
                if (enrollments) this.state.enrollments = enrollments;
                if (posts) this.state.myPosts = posts;
            } else {
                console.log('Model: Skipping personal data for guest.');
                this.state.serviceRequests = [];
                this.state.enrollments = [];
                this.state.myPosts = [];
            }
            
            console.log('Model: Sync complete, state updated');
        } catch (error) {
            console.error('Model: Error during sync:', error);
            throw error; 
        }
    },

    // -------------------------------------------------------------------------
    // Programs & Posts
    // -------------------------------------------------------------------------
    getPrograms() { return this.state.programs; },
    getEnrollments(userId) { return this.state.enrollments.filter(e => e.user_id == userId); },
    getMyPosts() { return this.state.myPosts; },

    async addEnrollment(userId, programId) {
        const result = await this.apiCall('enroll_user', { userId, programId });
        if (result) { this.state.enrollments.push({ user_id: userId, program_id: programId }); return true; }
        return false;
    },

    // -------------------------------------------------------------------------
    // Service Requests
    // -------------------------------------------------------------------------
    getServiceRequests() { return this.state.serviceRequests; },

    async addServiceRequest(formData) {
        const result = await this.apiUpload('add_request', formData);
        if (result) { this.state.serviceRequests.unshift(result); return result; }
        return null;
    },

    async getMyRequests() {
        const data = await this.apiCall('get_my_requests');
        if (data) this.state.serviceRequests = data;
        return data;
    },

    async getRequestDetail(id) {
        return await this.apiCall('get_request', { id });
    },

    async updateRequest(id, description) {
        return await this.apiCall('update_request', { id, description });
    },

    async deleteRequest(id) {
        return await this.apiCall('delete_request', { id });
    },

    async deleteDocument(id) {
        return await this.apiCall('delete_document', { id });
    },

    /** Look up the requiredDocs array for a service type from the loaded list. */
    getRequiredDocsFor(serviceType) {
        const t = (this.state.serviceTypes || []).find(s => s.value === serviceType);
        return t?.requiredDocs || [];
    },

    /** Ask the AI to improve a description and review (optional) document files. */
    async aiImproveRequest(serviceType, description, requiredDocuments = []) {
        return await this.apiCall('ai_improve_description', {
            serviceType,
            description,
            requiredDocuments
        });
    },

    /**
     * Upload one or more supporting documents (PDF / image) for an existing request.
     * Uses the multipart "upload_files" handler in Verification.php.
     */
    async uploadRequestDocuments(requestId, files, docType = 'other') {
        if (!files || files.length === 0) return [];
        const fd = new FormData();
        fd.append('action', 'upload_files');
        fd.append('requestId', String(requestId));
        fd.append('docType', docType);
        for (const f of files) fd.append('files[]', f);

        try {
            const response = await fetch('../../Verification.php', { method: 'POST', body: fd });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data || [];
        } catch (e) {
            console.error('Document upload error:', e);
            return null;
        }
    },

    // -------------------------------------------------------------------------
    // Profile
    // -------------------------------------------------------------------------
    getCurrentUser() { return this.state.currentUser; },
    updateUser(data) { Object.assign(this.state.currentUser, data); },

    async uploadProfilePic(file) {
        const fd = new FormData();
        fd.append('profile_pic', file);
        return await this.apiUpload('upload_profile_pic', fd);
    },

    async updatePassword(data) {
        return await this.apiCall('update_profile', data);
    },

    // -------------------------------------------------------------------------
    // Transport
    // -------------------------------------------------------------------------
    getTransportTypes() { return this.state.transportTypes; },

    async getTrajetsByTypeAndSort(type, sortBy, order) {
        return await this.transportApi('list_trajets', { type, sortBy, order });
    },

    async getMyTickets() {
        return await this.transportApi('list_tickets_enriched');
    },

    async bookTicket(idTrajet, citizenName) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'book_ticket', idTrajet, citizenName })
            });
            return await response.json();
        } catch (error) { return { success: false, error: 'Network error.' }; }
    },

    async cancelTicket(idTicket) {
        try {
            const response = await fetch('../../api_transport.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'cancel_ticket', idTicket })
            });
            return await response.json();
        } catch (error) { return { success: false, error: 'Network error.' }; }
    },

    // -------------------------------------------------------------------------
    // Appointments
    // -------------------------------------------------------------------------
    async bookAppointment(data) {
        return await this.apiCall('book_appointment', data);
    },

    async getMyAppointments() {
        const data = await this.apiCall('get_my_appointments');
        if (data) this.state.myAppointments = data;
        return data;
    },

    async cancelAppointment(id) {
        return await this.apiCall('cancel_appointment', { id });
    },

    async getAvailableSlots(serviceType, date) {
        return await this.apiCall('get_available_slots', { service_type: serviceType, date });
    },

    getServiceTypes() { return this.state.serviceTypes; },

    // -------------------------------------------------------------------------
    // Notifications
    // -------------------------------------------------------------------------
    async getNotifications() {
        const data = await this.apiCall('get_notifications');
        if (data) this.state.notifications = data;
        return data;
    },

    async markNotificationRead(id) {
        return await this.apiCall('mark_notification_read', { id });
    }
};

export default model;
