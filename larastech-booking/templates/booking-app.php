<div id="lt-booking-app" x-data="bookingApp()" class="max-w-md mx-auto bg-white min-h-screen shadow-lg rounded-lg overflow-hidden relative">

    <!-- Top Navigation / Progress Header -->
    <div class="p-4 border-b">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-800" x-text="stepTitle()"></h2>
            <div class="text-sm text-gray-500" x-text="'Step ' + step + ' of 7'"></div>
        </div>
        <div class="w-full bg-gray-200 h-1.5 mt-2 rounded-full overflow-hidden">
            <div class="bg-blue-600 h-full transition-all duration-300" :style="'width: ' + (step/7*100) + '%'"></div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="p-4 pb-24">

        <!-- Step 1: Service Selection -->
        <template x-if="step === 1">
            <div x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="space-y-4">
                    <template x-if="loading">
                        <div class="space-y-4">
                            <div class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
                            <div class="animate-pulse bg-gray-200 h-24 rounded-lg"></div>
                        </div>
                    </template>
                    <template x-for="service in services" :key="service.id">
                        <div @click="selectService(service)" :class="{'ring-2 ring-blue-500': selectedService?.id === service.id}" class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-semibold" x-text="service.title"></h3>
                                    <p class="text-sm text-gray-500" x-text="service.duration + ' mins'"></p>
                                </div>
                                <div class="font-bold text-blue-600" x-text="'$' + service.price"></div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Step 2: Staff Selection -->
        <template x-if="step === 2">
            <div x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="space-y-4">
                    <template x-for="member in staff" :key="member.id">
                        <div @click="selectStaff(member)" :class="{'ring-2 ring-blue-500': selectedStaff?.id === member.id}" class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50 transition">
                            <div class="w-12 h-12 bg-gray-200 rounded-full flex-shrink-0 flex items-center justify-center text-xl font-bold text-gray-500" x-text="member.full_name.charAt(0)"></div>
                            <div class="ml-4">
                                <h3 class="font-semibold" x-text="member.full_name"></h3>
                                <p class="text-xs text-green-500">Available</p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <!-- Step 3: Date Selection -->
        <template x-if="step === 3">
            <div x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="p-4 border rounded-lg">
                    <!-- Simple Calendar Placeholder -->
                    <input type="date" class="w-full p-2 border rounded" x-model="selectedDate" @change="fetchSlots()">
                </div>
            </div>
        </template>

        <!-- Step 4: Time Selection -->
        <template x-if="step === 4">
            <div x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <template x-if="loading">
                    <div class="grid grid-cols-3 gap-2">
                        <div class="animate-pulse bg-gray-200 h-10 rounded"></div>
                        <div class="animate-pulse bg-gray-200 h-10 rounded"></div>
                        <div class="animate-pulse bg-gray-200 h-10 rounded"></div>
                    </div>
                </template>
                <div class="grid grid-cols-3 gap-2">
                    <template x-for="slot in slots" :key="slot.start">
                        <button @click="selectedSlot = slot" :class="selectedSlot?.start === slot.start ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'" class="p-2 text-sm rounded transition" x-text="slot.start"></button>
                    </template>
                </div>
                <template x-if="!loading && slots.length === 0">
                    <p class="text-center text-gray-500 py-8">No slots available for this date.</p>
                </template>
            </div>
        </template>

        <!-- Step 5: Customer Details -->
        <template x-if="step === 5">
            <div x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <form class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" x-model="customer.name" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email Address</label>
                        <input type="email" x-model="customer.email" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                        <input type="tel" x-model="customer.phone" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                        <textarea x-model="customer.notes" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm p-2 border"></textarea>
                    </div>
                </form>
            </div>
        </template>

        <!-- Step 6: Booking Review -->
        <template x-if="step === 6">
            <div x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="bg-gray-50 p-4 rounded-lg space-y-3">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Service</span>
                        <span class="font-semibold" x-text="selectedService?.title"></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Staff</span>
                        <span class="font-semibold" x-text="selectedStaff?.full_name"></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Date</span>
                        <span class="font-semibold" x-text="selectedDate"></span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Time</span>
                        <span class="font-semibold" x-text="selectedSlot?.start"></span>
                    </div>
                    <div class="flex justify-between pt-2">
                        <span class="text-gray-700 font-bold">Total Price</span>
                        <span class="text-blue-600 font-bold" x-text="'$' + selectedService?.price"></span>
                    </div>
                </div>
            </div>
        </template>

        <!-- Step 7: Confirmation -->
        <template x-if="step === 7">
            <div class="text-center py-10" x-transition:enter="transition ease-out duration-500 transform" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Booking Confirmed!</h2>
                <p class="text-gray-600 mt-2">Thank you, <span x-text="customer.name"></span>. Your booking is pending approval.</p>
                <p class="text-sm text-gray-500 mt-1">Reference: #<span x-text="bookingId"></span></p>

                <button @click="resetApp()" class="mt-8 px-6 py-2 bg-blue-600 text-white rounded-full font-semibold">Book Another</button>
            </div>
        </template>

    </div>

    <!-- Navigation Footer -->
    <div x-show="step < 7" class="absolute bottom-0 left-0 right-0 p-4 bg-white border-t flex justify-between items-center">
        <button x-show="step > 1" @click="prevStep()" class="px-6 py-2 text-gray-600 font-semibold hover:bg-gray-100 rounded-lg transition">Back</button>
        <div x-show="step === 1" class="w-1"></div>

        <button @click="nextStep()" :disabled="!canGoNext()" :class="!canGoNext() ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'" class="px-8 py-2 bg-blue-600 text-white rounded-lg font-semibold transition flex items-center">
            <span x-text="step === 6 ? 'Confirm Booking' : 'Continue'"></span>
            <template x-if="loading && step === 6">
                <svg class="animate-spin ml-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            </template>
        </button>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed bottom-20 left-1/2 transform -translate-x-1/2 z-50">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition class="mb-2 px-4 py-2 rounded-lg shadow-lg text-white text-sm font-medium" :class="toast.type === 'error' ? 'bg-red-500' : 'bg-green-500'" x-text="toast.message"></div>
        </template>
    </div>
</div>
