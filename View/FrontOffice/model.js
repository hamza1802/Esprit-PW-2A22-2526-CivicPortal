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
        serviceTypes: []
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
        
        try {
            const promises = [
                this.apiCall('get_my_requests'),
                this.apiCall('get_programs'),
                this.apiCall('get_enrollments', { userId: this.state.currentUser.id }),
                this.transportApi('list_transport_types'),
                this.apiCall('get_service_types'),
            ];
            
            console.log('Model: Waiting for all API calls...');
            const [requests, programs, enrollments, types, svcTypes] = await Promise.all(promises);
            
            console.log('Model: API calls complete');
            console.log('  - requests:', requests);
            console.log('  - programs:', programs);
            console.log('  - enrollments:', enrollments);
            console.log('  - types:', types);
            console.log('  - svcTypes:', svcTypes);
            
            if (requests) this.state.serviceRequests = requests;
            if (programs) this.state.programs = programs;
            if (enrollments) this.state.enrollments = enrollments;
            if (types) this.state.transportTypes = types;
            if (svcTypes) this.state.serviceTypes = svcTypes;
            
            console.log('Model: Sync complete, state updated');
        } catch (error) {
            console.error('Model: Error during sync:', error);
            throw error; // Re-throw so controller can handle it
        }
    },

    // -------------------------------------------------------------------------
    // Programs
    // -------------------------------------------------------------------------
    getPrograms() { return this.state.programs; },
    getEnrollments(userId) { return this.state.enrollments.filter(e => e.user_id == userId); },

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
