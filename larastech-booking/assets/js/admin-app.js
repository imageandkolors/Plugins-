document.addEventListener('alpine:init', () => {
    Alpine.data('adminApp', () => ({
        view: 'dashboard',
        loading: false,
        menu: [
            { name: 'Dashboard', view: 'dashboard' },
            { name: 'Bookings', view: 'bookings' },
            { name: 'Services', view: 'services' },
            { name: 'Staff', view: 'staff' },
            { name: 'Settings', view: 'settings' },
            { name: 'License', view: 'license' }
        ],
        stats: { today: 0, customers: 0 },
        bookings: [],
        services: [],
        staff: [],
        settings: {
            business_name: 'Larastech',
            pro_whatsapp_token: '',
            pro_telegram_token: '',
            pro_google_sheet_id: ''
        },
        license: { is_active: false, data: {} },
        licenseKey: '',
        toasts: [],
        modal: {
            show: false,
            type: '',
            data: {}
        },

        init() {
            this.fetchStats();
            this.fetchBookings();
            this.fetchServices();
            this.fetchStaff();
            this.fetchLicenseStatus();
        },

        async fetchStats() {
            this.stats.today = this.bookings.filter(b => b.booking_date === new Date().toISOString().split('T')[0]).length;

            // Fetch customers count
            const response = await fetch(`${ltBookingData.rest_url}/customers`, {
                headers: { 'X-WP-Nonce': ltBookingData.nonce }
            });
            if (response.ok) {
                const customers = await response.json();
                this.stats.customers = customers.length;
            }
        },

        async fetchBookings() {
            const response = await fetch(`${ltBookingData.rest_url}/bookings`);
            this.bookings = await response.json();
            this.fetchStats();
        },

        async fetchServices() {
            const response = await fetch(`${ltBookingData.rest_url}/services`);
            this.services = await response.json();
        },

        async fetchStaff() {
            const response = await fetch(`${ltBookingData.rest_url}/staff`);
            this.staff = await response.json();
        },

        async fetchLicenseStatus() {
            const response = await fetch(`${ltBookingData.rest_url}/license/status`, {
                headers: { 'X-WP-Nonce': ltBookingData.nonce }
            });
            if (response.ok) {
                const data = await response.json();
                this.license.is_active = data.is_active;
                this.license.data = data.license;
            }
        },

        async activateLicense() {
            if (!this.licenseKey) return;
            const response = await fetch(`${ltBookingData.rest_url}/license/activate`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': ltBookingData.nonce },
                body: JSON.stringify({ key: this.licenseKey })
            });
            if (response.ok) {
                this.showToast('Pro activated!');
                this.fetchLicenseStatus();
            } else {
                this.showToast('Invalid key', 'error');
            }
        },

        async deactivateLicense() {
            const response = await fetch(`${ltBookingData.rest_url}/license/deactivate`, {
                method: 'POST',
                headers: { 'X-WP-Nonce': ltBookingData.nonce }
            });
            if (response.ok) {
                this.showToast('License deactivated');
                this.fetchLicenseStatus();
            }
        },

        async updateBookingStatus(id, status) {
            const response = await fetch(`${ltBookingData.rest_url}/bookings/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': ltBookingData.nonce },
                body: JSON.stringify({ status })
            });
            if (response.ok) {
                this.showToast('Status updated');
                this.fetchBookings();
            }
        },

        async deleteService(id) {
            if (!confirm('Delete service?')) return;
            const response = await fetch(`${ltBookingData.rest_url}/services/${id}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': ltBookingData.nonce }
            });
            if (response.ok) {
                this.showToast('Service deleted');
                this.fetchServices();
            }
        },

        async deleteStaff(id) {
            if (!confirm('Remove staff?')) return;
            const response = await fetch(`${ltBookingData.rest_url}/staff/${id}`, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': ltBookingData.nonce }
            });
            if (response.ok) {
                this.showToast('Staff removed');
                this.fetchStaff();
            }
        },

        openModal(type) {
            this.modal.type = type;
            this.modal.data = {};
            this.modal.show = true;
        },

        closeModal() {
            this.modal.show = false;
        },

        async saveModal() {
            let endpoint = this.modal.type === 'service' ? 'services' : 'staff';
            const response = await fetch(`${ltBookingData.rest_url}/${endpoint}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': ltBookingData.nonce },
                body: JSON.stringify(this.modal.data)
            });
            if (response.ok) {
                this.showToast('Saved successfully');
                this.closeModal();
                this.fetchServices();
                this.fetchStaff();
            } else {
                this.showToast('Error saving', 'error');
            }
        },

        statusClass(status) {
            return {
                'pending': 'bg-yellow-100 text-yellow-800',
                'approved': 'bg-green-100 text-green-800',
                'rejected': 'bg-red-100 text-red-800',
                'cancelled': 'bg-gray-100 text-gray-800'
            }[status] || 'bg-gray-100';
        },

        showToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3000);
        }
    }));
});
