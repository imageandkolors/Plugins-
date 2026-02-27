<div id="lt-admin-app" x-data="adminApp()" class="flex h-screen bg-gray-100 overflow-hidden" x-cloak>

    <!-- Sidebar (Desktop) -->
    <aside class="hidden md:flex md:flex-shrink-0">
        <div class="flex flex-col w-64">
            <div class="flex flex-col h-0 flex-1 bg-gray-800">
                <div class="flex-1 flex flex-col pt-5 pb-4 overflow-y-auto">
                    <div class="flex items-center flex-shrink-0 px-4">
                        <span class="text-white text-xl font-bold italic">Larastech Booking</span>
                    </div>
                    <nav class="mt-5 flex-1 px-2 space-y-1">
                        <template x-for="item in menu" :key="item.view">
                            <a href="#" @click.prevent="view = item.view" :class="view === item.view ? 'bg-gray-900 text-white' : 'text-gray-300 hover:bg-gray-700 hover:text-white'" class="group flex items-center px-2 py-2 text-sm font-medium rounded-md transition">
                                <span x-text="item.name"></span>
                            </a>
                        </template>
                    </nav>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex flex-col w-0 flex-1 overflow-hidden">
        <main class="flex-1 relative z-0 overflow-y-auto focus:outline-none">

            <!-- Mobile Header -->
            <div class="md:hidden bg-gray-800 text-white p-4 flex justify-between items-center">
                <span class="font-bold">Larastech</span>
                <span class="text-sm" x-text="menu.find(m => m.view === view).name"></span>
            </div>

            <div class="py-6 px-4 sm:px-6 md:px-8">

                <!-- View: Dashboard -->
                <template x-if="view === 'dashboard'">
                    <div x-transition>
                        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Dashboard</h1>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                            <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                                <dt class="text-sm font-medium text-gray-500 truncate">Today's Bookings</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="stats.today">0</dd>
                            </div>
                            <div class="bg-white overflow-hidden shadow rounded-lg p-5">
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Customers</dt>
                                <dd class="mt-1 text-3xl font-semibold text-gray-900" x-text="stats.customers">0</dd>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- View: Bookings -->
                <template x-if="view === 'bookings'">
                    <div x-transition>
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900">Bookings</h1>
                        </div>
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                <template x-for="booking in bookings" :key="booking.id">
                                    <li class="p-4 hover:bg-gray-50">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="text-sm font-medium text-blue-600 truncate" x-text="'#' + booking.id"></p>
                                                <p class="text-gray-900 font-semibold" x-text="booking.booking_date + ' @ ' + booking.start_time"></p>
                                            </div>
                                            <div class="flex space-x-2">
                                                <span :class="statusClass(booking.status)" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" x-text="booking.status"></span>
                                                <button @click="updateBookingStatus(booking.id, 'approved')" x-show="booking.status === 'pending'" class="text-xs text-green-600 font-bold hover:underline">Approve</button>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>

                <!-- View: Services -->
                <template x-if="view === 'services'">
                    <div x-transition>
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900">Services</h1>
                            <button @click="openModal('service')" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition">Add Service</button>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <template x-for="service in services" :key="service.id">
                                <div class="bg-white p-4 shadow rounded-lg border-l-4 border-blue-600">
                                    <h3 class="font-bold text-lg" x-text="service.title"></h3>
                                    <p class="text-gray-500 text-sm" x-text="service.duration + ' mins • $' + service.price"></p>
                                    <div class="mt-4 flex justify-end space-x-2">
                                        <button @click="deleteService(service.id)" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- View: Staff -->
                <template x-if="view === 'staff'">
                    <div x-transition>
                        <div class="flex justify-between items-center mb-6">
                            <h1 class="text-2xl font-semibold text-gray-900">Staff</h1>
                            <button @click="openModal('staff')" class="bg-blue-600 text-white px-4 py-2 rounded-md shadow hover:bg-blue-700 transition">Add Staff</button>
                        </div>
                        <div class="bg-white shadow overflow-hidden sm:rounded-md">
                            <ul class="divide-y divide-gray-200">
                                <template x-for="member in staff" :key="member.id">
                                    <li class="px-6 py-4 flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center font-bold text-gray-500" x-text="member.full_name.charAt(0)"></div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900" x-text="member.full_name"></p>
                                                <p class="text-sm text-gray-500" x-text="member.email"></p>
                                            </div>
                                        </div>
                                        <button @click="deleteStaff(member.id)" class="text-red-600 hover:text-red-900 text-sm">Remove</button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                </template>

                <!-- View: Settings -->
                <template x-if="view === 'settings'">
                    <div x-transition>
                        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Settings</h1>
                        <div class="bg-white shadow p-6 rounded-lg max-w-2xl">
                            <div class="space-y-6">
                                <div>
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">General</h3>
                                    <p class="mt-1 text-sm text-gray-500">Business identity and core rules.</p>
                                </div>
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700">Business Name</label>
                                        <input type="text" x-model="settings.business_name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                    </div>
                                </div>

                                <div class="border-t pt-6">
                                    <h3 class="text-lg font-medium leading-6 text-gray-900">Pro Integrations</h3>
                                    <p class="mt-1 text-sm text-gray-500">Enable advanced notifications and sync.</p>
                                    <div class="mt-4 space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">WhatsApp API Token</label>
                                            <input type="password" x-model="settings.pro_whatsapp_token" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" placeholder="••••••••">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Telegram Bot Token</label>
                                            <input type="password" x-model="settings.pro_telegram_token" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" placeholder="••••••••">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Google Sheet ID</label>
                                            <input type="text" x-model="settings.pro_google_sheet_id" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" placeholder="1abc...XYZ">
                                        </div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-6 gap-6">
                                    <div class="col-span-6 sm:col-span-3">
                                        <label class="block text-sm font-medium text-gray-700">Business Name</label>
                                        <input type="text" value="Larastech" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                                    </div>
                                </div>
                                <div class="pt-5">
                                    <div class="flex justify-end">
                                        <button type="button" class="bg-blue-600 border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">Save</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </main>

        <!-- Mobile Bottom Nav -->
        <nav class="md:hidden bg-white border-t flex justify-around p-2">
            <template x-for="item in menu" :key="item.view">
                <button @click="view = item.view" :class="view === item.view ? 'text-blue-600' : 'text-gray-500'" class="flex flex-col items-center">
                    <span class="text-[10px]" x-text="item.name"></span>
                </button>
            </template>
        </nav>
    </div>

    <!-- Modals -->
    <template x-if="modal.show">
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 transition-opacity" aria-hidden="true" @click="closeModal()">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4" x-text="'Add ' + (modal.type === 'service' ? 'Service' : 'Staff Member')"></h3>
                    <div class="space-y-4">
                        <template x-if="modal.type === 'service'">
                            <div class="space-y-4">
                                <input type="text" x-model="modal.data.title" placeholder="Service Title" class="block w-full border border-gray-300 rounded-md p-2">
                                <input type="number" x-model="modal.data.duration" placeholder="Duration (mins)" class="block w-full border border-gray-300 rounded-md p-2">
                                <input type="number" x-model="modal.data.price" placeholder="Price ($)" class="block w-full border border-gray-300 rounded-md p-2">
                            </div>
                        </template>
                        <template x-if="modal.type === 'staff'">
                            <div class="space-y-4">
                                <input type="text" x-model="modal.data.full_name" placeholder="Full Name" class="block w-full border border-gray-300 rounded-md p-2">
                                <input type="email" x-model="modal.data.email" placeholder="Email" class="block w-full border border-gray-300 rounded-md p-2">
                            </div>
                        </template>
                    </div>
                    <div class="mt-5 sm:mt-6 flex space-x-3">
                        <button @click="closeModal()" class="flex-1 bg-gray-100 text-gray-700 py-2 rounded-md hover:bg-gray-200">Cancel</button>
                        <button @click="saveModal()" class="flex-1 bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Toasts -->
    <div class="fixed top-5 right-5 z-[100]">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition class="mb-2 p-4 rounded-lg shadow-lg text-white" :class="toast.type === 'error' ? 'bg-red-500' : 'bg-green-500'" x-text="toast.message"></div>
        </template>
    </div>
</div>
