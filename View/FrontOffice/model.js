/**
 * model.js 
 * Client-side Model for CivicPortal FrontOffice
 * Syncs with PHP backend API (MySQL database)
 */

const model = {
    state: {
        currentUser: { id: 1, name: 'John Citizen', role: 'citizen', email: 'john@example.com' },
        programs: [
            { id: 101, title: 'Summer Pottery Workshop', category: 'Arts', description: 'Learn basic pottery techniques for all ages.', image: 'pottery.jpg' },
            { id: 102, title: 'Youth Swimming Program', category: 'Sports', description: 'Daily swimming lessons at the Municipal Pool.', image: 'swimming.jpg' },
            { id: 103, title: 'Community Gardening', category: 'Environment', description: 'Join our local group in the North Park garden.', image: 'gardening.jpg' }
        ],
        serviceRequests: [],
        enrollments: [],
        complaints: []
    },

    /** JSON API call */
    async apiCall(action, data = {}) {
        try {
            const response = await fetch('../../Verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action, data })
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error("API Error:", error);
            return null;
        }
    },

    /** File upload call (multipart/form-data) */
    async uploadFiles(formData) {
        try {
            const response = await fetch('../../Verification.php', {
                method: 'POST',
                body: formData  // No Content-Type header — browser sets it with boundary
            });
            const result = await response.json();
            if (!result.success) throw new Error(result.error);
            return result.data;
        } catch (error) {
            console.error("Upload Error:", error);
            return null;
        }
    },

    async sync() {
        const requests = await this.apiCall('get_requests');
        if (requests) this.state.serviceRequests = requests;
    },

    getPrograms() {
        return this.state.programs;
    },

    getServiceRequests() {
        return this.state.serviceRequests.filter(r => r.userId === this.state.currentUser.id);
    },

    getEnrollments(userId) {
        return this.state.enrollments.filter(e => e.userId === userId);
    },

    // ── Request CRUD ─────────────────────────────────────────────

    async addServiceRequest(requestData) {
        const response = await this.apiCall('add_request', requestData);
        if (response) {
            this.state.serviceRequests.push(response);
            return response;
        }
        return null;
    },

    async updateServiceRequest(requestId, description) {
        const response = await this.apiCall('update_request', { id: requestId, description });
        if (response) {
            const idx = this.state.serviceRequests.findIndex(r => r.id === requestId);
            if (idx !== -1) this.state.serviceRequests[idx] = response;
            return response;
        }
        return null;
    },

    async deleteServiceRequest(requestId) {
        const response = await this.apiCall('delete_request', { id: requestId });
        if (response) {
            this.state.serviceRequests = this.state.serviceRequests.filter(r => r.id !== requestId);
            return true;
        }
        return false;
    },

    // ── Document CRUD ────────────────────────────────────────────

    async getDocuments(requestId) {
        return await this.apiCall('get_documents', { requestId });
    },

    /**
     * Upload multiple files for a request.
     * @param {number} requestId
     * @param {FileList|File[]} files - array of File objects
     * @param {string[]} docTypes - parallel array of document type enums
     */
    async addDocuments(requestId, files, docTypes) {
        const formData = new FormData();
        formData.append('action', 'upload_files');
        formData.append('requestId', requestId);

        for (let i = 0; i < files.length; i++) {
            formData.append('files[]', files[i]);
            formData.append('docTypes[]', docTypes[i] || 'other');
        }

        return await this.uploadFiles(formData);
    },

    /**
     * Replace a single document file.
     */
    async replaceDocument(docId, requestId, file, docType) {
        const formData = new FormData();
        formData.append('action', 'replace_file');
        formData.append('docId', docId);
        formData.append('requestId', requestId);
        formData.append('docType', docType || 'other');
        formData.append('file', file);

        return await this.uploadFiles(formData);
    },

    async deleteDocument(docId) {
        return await this.apiCall('delete_document', { id: docId });
    },

    // ── Other ────────────────────────────────────────────────────

    addEnrollment(userId, programId) {
        const exists = this.state.enrollments.find(e => e.userId === userId && e.programId === programId);
        if (!exists) {
            this.state.enrollments.push({ userId, programId });
        }
    },

    getCurrentUser() {
        return this.state.currentUser;
    },

    async addComplaint(subject, body, userId) {
        const response = await this.apiCall('add_complaint', { subject, body, userId });
        if (response) {
            this.state.complaints.push(response);
            return response;
        }
    },

    updateUser(data) {
        this.state.currentUser.name = data.name;
        this.state.currentUser.email = data.email;
    },

    deleteUser() {
        this.state.currentUser = null;
    }
};

export default model;
