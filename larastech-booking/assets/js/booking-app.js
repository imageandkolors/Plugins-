document.addEventListener('alpine:init', () => {
    Alpine.data('bookingApp', () => ({
        step: 1,
        loading: false,
        services: [],
        staff: [],
        slots: [],
        toasts: [],

        selectedService: null,
        selectedStaff: null,
        selectedDate: '',
        selectedSlot: null,
        customer: {
            name: '',
            email: '',
            phone: '',
            notes: ''
        },
        bookingId: null,

        init() {
            this.fetchServices();
        },

        stepTitle() {
            const titles = {
                1: 'Select Service',
                2: 'Select Staff',
                3: 'Select Date',
                4: 'Select Time',
                5: 'Your Details',
                6: 'Review Booking',
                7: 'Confirmed'
            };
            return titles[this.step];
        },

        async fetchServices() {
            this.loading = true;
            try {
                // For Phase 3, we might need a REST endpoint to list services.
                // Assuming we'll add it or use a placeholder for now.
                // Let's use AJAX as a fallback or mock.
                const response = await fetch(`${ltBookingData.rest_url}/services`);
                if (response.ok) {
                    this.services = await response.json();
                } else {
                    // Mock data if endpoint doesn't exist yet
                    this.services = [
                        { id: 1, title: 'Haircut', duration: 30, price: 25 },
                        { id: 2, title: 'Beard Trim', duration: 20, price: 15 }
                    ];
                }
            } catch (e) {
                this.showToast('Failed to load services', 'error');
            }
            this.loading = false;
        },

        async fetchStaff() {
            this.loading = true;
            try {
                // Placeholder/Mock for now
                this.staff = [
                    { id: 1, full_name: 'John Doe' },
                    { id: 2, full_name: 'Jane Smith' }
                ];
            } catch (e) {
                this.showToast('Failed to load staff', 'error');
            }
            this.loading = false;
        },

        async fetchSlots() {
            if (!this.selectedDate || !this.selectedStaff || !this.selectedService) return;
            this.loading = true;
            try {
                const response = await fetch(`${ltBookingData.rest_url}/bookings/slots?staff_id=${this.selectedStaff.id}&service_id=${this.selectedService.id}&date=${this.selectedDate}`);
                this.slots = await response.json();
            } catch (e) {
                this.showToast('Failed to load slots', 'error');
            }
            this.loading = false;
        },

        selectService(service) {
            this.selectedService = service;
            this.nextStep();
        },

        selectStaff(member) {
            this.selectedStaff = member;
            this.nextStep();
        },

        canGoNext() {
            if (this.step === 1) return this.selectedService !== null;
            if (this.step === 2) return this.selectedStaff !== null;
            if (this.step === 3) return this.selectedDate !== '';
            if (this.step === 4) return this.selectedSlot !== null;
            if (this.step === 5) return this.customer.name && this.customer.email && this.customer.phone;
            if (this.step === 6) return !this.loading;
            return true;
        },

        nextStep() {
            if (!this.canGoNext()) return;

            if (this.step === 1) this.fetchStaff();

            if (this.step === 6) {
                this.confirmBooking();
                return;
            }

            this.step++;
        },

        prevStep() {
            if (this.step > 1) this.step--;
        },

        async confirmBooking() {
            this.loading = true;

            const formData = new FormData();
            formData.append('action', 'lt_booking_action');
            formData.append('sub_action', 'create_booking');
            formData.append('nonce', ltBookingData.lt_nonce);
            formData.append('service_id', this.selectedService.id);
            formData.append('staff_id', this.selectedStaff.id);
            formData.append('customer_id', 1); // Mock customer ID for now
            formData.append('booking_date', this.selectedDate);
            formData.append('start_time', this.selectedSlot.start);
            formData.append('notes', this.customer.notes);
            formData.append('customer_name', this.customer.name);
            formData.append('customer_email', this.customer.email);
            formData.append('customer_phone', this.customer.phone);

            try {
                const response = await fetch(ltBookingData.ajax_url, {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (result.success) {
                    this.bookingId = result.data.id;
                    this.step = 7;
                    this.showToast('Booking successful!', 'success');
                } else {
                    this.showToast(result.data.message || 'Booking failed', 'error');
                }
            } catch (e) {
                this.showToast('Connection error', 'error');
            } finally {
                this.loading = false;
            }
        },

        showToast(message, type = 'success') {
            const id = Date.now();
            this.toasts.push({ id, message, type });
            setTimeout(() => {
                this.toasts = this.toasts.filter(t => t.id !== id);
            }, 3000);
        },

        resetApp() {
            this.step = 1;
            this.selectedService = null;
            this.selectedStaff = null;
            this.selectedDate = '';
            this.selectedSlot = null;
            this.customer = { name: '', email: '', phone: '', notes: '' };
            this.bookingId = null;
        }
    }));
});
