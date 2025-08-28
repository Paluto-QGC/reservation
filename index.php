<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paluto Philippines - Reservation</title>
    <link rel="icon" type="image/png" href="https://i.ibb.co/0RPhcmb2/logo-pal.png" />
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .calendar-day { transition: all 0.2s ease; }
        .calendar-day:hover { transform: translateY(-2px); }
        .time-slot { transition: all 0.2s ease; }
        .time-slot:hover { transform: translateY(-1px); }
        .modal { backdrop-filter: blur(8px); }
        .fade-in { animation: fadeIn 0.3s ease-in; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .social-icon { transition: all 0.3s ease; }
        .social-icon:hover { transform: scale(1.1); }
        .carousel-card { cursor: pointer; }
    </style>
</head>
<body class="bg-white min-h-screen">
    <!-- Campaign Banner -->
    <div class="bg-white text-black py-16 px-4">
        <div class="max-w-4xl mx-auto text-center">
            <div class="flex flex-col items-center mb-8">
                <img src="https://i.ibb.co/0RPhcmb2/logo-pal.png" alt="Paluto Seafood Grill & Restaurant Logo" class="w-24 h-24 mb-4" onerror="this.style.display='none';">
                <h1 class="text-4xl md:text-5xl font-bold text-center">PALUTO SEAFOOD GRILL & RESTAURANT</h1>
            </div>
            <p class="text-xl mb-8 text-gray-700">Experience authentic Filipino flavors in a warm, welcoming atmosphere</p>
            <p class="text-lg mb-8 text-gray-600 max-w-2xl mx-auto">Join us for an unforgettable dining experience featuring fresh seafood, traditional grilled specialties, and the finest Filipino cuisine. Perfect for family gatherings, celebrations, and special occasions.</p>
            
            <!-- Photo Gallery (wide 3240x1080 carousel) -->
            <div class="mb-8">
            <div class="max-w-6xl mx-auto">
                <div id="heroCarousel" class="relative w-full">
                <!-- Slide container with 3:1 aspect ratio -->
                <div class="w-full aspect-[3/1] overflow-hidden rounded-lg shadow-lg">
                    <img id="heroSlide"
                        src="https://i.ibb.co/JRGDpYbd/banner.jpg"
                        alt="Paluto gallery"
                        class="w-full h-full object-cover transition-transform duration-500">
                </div>

                <!-- Prev/Next buttons -->
                <button type="button" id="heroPrev"
                        class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-3 rounded-full focus:outline-none"
                        aria-label="Previous slide">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" id="heroNext"
                        class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/40 hover:bg-black/60 text-white p-3 rounded-full focus:outline-none"
                        aria-label="Next slide">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                <!-- Dots -->
                <div class="absolute bottom-3 inset-x-0 flex items-center justify-center gap-2">
                    <span class="hero-dot w-3 h-3 rounded-full bg-white"></span>
                    <span class="hero-dot w-3 h-3 rounded-full bg-white/40"></span>
                    <span class="hero-dot w-3 h-3 rounded-full bg-white/40"></span>
                </div>
                </div>
            </div>
            </div>

            <!-- Pricing Section -->
            <div class="mb-8 bg-white rounded-xl p-6 max-w-3xl mx-auto shadow-lg border border-gray-200">
                <h2 class="text-3xl font-bold text-center mb-6 text-gray-800">UNLI PALUTO PRICE</h2>
                <div class="grid md:grid-cols-2 gap-6 mb-6">
                    <div class="neon-border text-center p-6 bg-gray-50 rounded-lg transition-all duration-300 hover:bg-red-50 hover:scale-105 cursor-pointer">
                        <div class="text-2xl font-medium text-gray-700 mb-2">Adults</div>
                        <div class="font-bold text-4xl text-gray-800">₱599</div>
                        <div class="text-sm text-gray-600 mt-2">12 years and above</div>
                    </div>
                    <div class="neon-border text-center p-6 bg-gray-50 rounded-lg transition-all duration-300 hover:bg-red-50 hover:scale-105 cursor-pointer">
                        <div class="text-2xl font-medium text-gray-700 mb-2">Kids</div>
                        <div class="font-bold text-4xl text-gray-800">₱299</div>
                        <div class="text-sm text-gray-600 mt-2">3-11 years old</div>
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 transition-all duration-300 hover:bg-red-100 hover:shadow-lg hover:scale-105 cursor-pointer">
                        <div class="font-semibold text-gray-800 mb-3 text-lg">Special Rates & Discounts</div>
                        <div class="space-y-1 text-gray-700 text-sm">
                            <p>• <strong>Children below 3 feet:</strong> FREE</p>
                            <p>• <strong>Children 4 feet above:</strong> Adult rate</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4 text-center transition-all duration-300 hover:bg-red-100 hover:shadow-lg hover:scale-105 cursor-pointer">
                        <div class="font-bold text-lg text-gray-800 mb-3">🎂 Birthday Special</div>
                        <div class="space-y-1 text-gray-700 text-sm">
                            <p><strong>On birthday:</strong> FREE with 1 paying adult</p>
                            <p><strong>Birthday month:</strong> FREE with 4 paying adults</p>
                            <p class="text-gray-600 mt-2">Valid ID required</p>
                        </div>
                    </div>
                </div>
            </div>
            <button onclick="openReservationModal()" class="bg-red-600 text-white px-8 py-4 rounded-full text-xl font-semibold hover:bg-red-700 transform hover:scale-105 transition-all duration-300 shadow-lg">Make a Reservation</button>
        </div>
    </div>
    <!-- Footer -->
    <footer class="bg-orange-400 text-white py-8">
        <div class="max-w-4xl mx-auto px-4 text-center">
            <h3 class="text-xl font-semibold text-white mb-4">Follow Us</h3>
            <div class="flex justify-center space-x-6 mb-6">
                <a href="https://www.youtube.com/@paluto.philippines" target="_blank" class="social-icon bg-white bg-opacity-20 backdrop-blur-sm text-white-600 p-3 rounded-full hover:bg-opacity-30 transition-all duration-300 shadow-lg" aria-label="YouTube">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
                <a href="https://www.tiktok.com/@palutophilippines" target="_blank" class="social-icon bg-white bg-opacity-20 backdrop-blur-sm text-white p-3 rounded-full hover:bg-opacity-30 transition-all duration-300 shadow-lg" aria-label="TikTok">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                </a>
                <a href="https://www.instagram.com/paluto_philippines" target="_blank" class="social-icon bg-white bg-opacity-20 backdrop-blur-sm text-white-500 p-3 rounded-full hover:bg-opacity-30 transition-all duration-300 shadow-lg" aria-label="Instagram">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                <a href="https://www.facebook.com/palutophilippines" target="_blank" class="social-icon bg-white bg-opacity-20 backdrop-blur-sm text-white-600 p-3 rounded-full hover:bg-opacity-30 transition-all duration-300 shadow-lg" aria-label="Facebook">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                </a>
            </div>
            <div class="border-t border-orange-500 pt-6">
                <p class="text-gray-300 text-sm">Managed by <span class="font-semibold text-white">Quirao Group of Companies, OPC</span></p>
                <p class="text-gray-300 text-xs mt-2">© 2024 Paluto Seafood Grill & Restaurant. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <!-- Reservation Modal -->
    <div id="reservationModal" class="fixed inset-0 bg-black bg-opacity-50 modal hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto fade-in">
            <div class="p-6 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-black">Make a Reservation</h2>
                    <button onclick="closeReservationModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                </div>
            </div>
            <div class="p-6">
                <form id="reservationForm" action="process.php" method="post">
                    <!-- Calendar Section -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-black mb-3">Select Date (Weekends Only)</label>
                        <div class="flex justify-between items-center mb-4">
                            <button type="button" id="prevMonth" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Previous Month">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            </button>
                            <h3 id="currentMonth" class="text-lg font-semibold text-black"></h3>
                            <button type="button" id="nextMonth" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Next Month">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                        </div>
                        <div id="calendar" class="grid grid-cols-7 gap-1 mb-4"></div>
                        <input type="hidden" id="selectedDate" name="selectedDate" required>
                    </div>
                    <!-- Time Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-black mb-3">Select Time</label>
                        <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="10:00">10:00 AM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="11:00">11:00 AM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="12:00">12:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="13:00">1:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="14:00">2:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="17:00">5:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="18:00">6:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="19:00">7:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="20:00">8:00 PM</button>
                            <button type="button" class="time-slot bg-gray-100 hover:bg-orange-100 border border-gray-300 rounded-lg py-2 px-3 text-sm font-medium" data-time="21:00">9:00 PM</button>
                        </div>
                        <input type="hidden" id="selectedTime" name="selectedTime" required>
                    </div>
                    <!-- Personal Information -->
                    <div class="grid md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-black mb-2">Full Name</label>
                            <input type="text" id="customerName" name="customerName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-black mb-2">Phone Number</label>
                            <input type="tel" id="phoneNumber" name="phoneNumber" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-black mb-2">Email Address</label>
                        <input type="email" id="email" name="email" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                    </div>
                    <!-- Number of Guests -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-black mb-3">Number of Guests</label>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-2">Adults (12+ years)</label>
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button type="button" onclick="changeCount('adults', -1)" class="p-2 hover:bg-gray-100 rounded-l-lg" aria-label="Decrease adults">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    </button>
                                    <input type="number" id="adults" name="adults" value="2" min="1" max="20" readonly class="flex-1 text-center py-2 border-0 focus:ring-0">
                                    <button type="button" onclick="changeCount('adults', 1)" class="p-2 hover:bg-gray-100 rounded-r-lg" aria-label="Increase adults">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-2">Children (3-11 years)</label>
                                <div class="flex items-center border border-gray-300 rounded-lg">
                                    <button type="button" onclick="changeCount('kids', -1)" class="p-2 hover:bg-gray-100 rounded-l-lg" aria-label="Decrease children">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                                    </button>
                                    <input type="number" id="kids" name="kids" value="0" min="0" max="10" readonly class="flex-1 text-center py-2 border-0 focus:ring-0">
                                    <button type="button" onclick="changeCount('kids', 1)" class="p-2 hover:bg-gray-100 rounded-r-lg" aria-label="Increase children">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-sm text-gray-500">
                            <span class="font-bold">Total guests:</span> <span id="totalGuests" class="font-medium">2</span> (Children under 3 eat free)
                        </div>
                    </div>
                    <!-- Special Requests -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-black mb-2">Special Requests</label>
                        <textarea id="specialRequests" name="specialRequests" rows="3" placeholder="Please let us know about any allergies, dietary restrictions, or special occasions..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"></textarea>
                    </div>
                    <!-- Terms and Conditions -->
                    <div class="mb-6">
                        <div class="bg-gray-50 rounded-lg p-4 mb-4 max-h-64 overflow-y-auto">
                            <h3 class="text-lg font-semibold text-black mb-3">Terms and Conditions</h3>
                            <div class="space-y-4 text-sm text-gray-700">
                                <div>
                                    <h4 class="font-semibold text-black mb-2">Operating Hours</h4>
                                    <p>Lunch: 10:00 AM to 3:00 PM (Last order at 2:00 PM)</p>
                                    <p>Dinner: 5:00 PM to 9:00 PM (Last order at 8:00 PM)</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-black mb-2">Pricing</h4>
                                    <p>Adults: ₱599</p>
                                    <p class="font-medium">Children:</p>
                                    <ul class="ml-4 list-disc">
                                        <li>Below 3 feet: Free</li>
                                        <li>3 to 4 feet: ₱299</li>
                                        <li>4 feet above: ₱599</li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-black mb-2">A la Carte Options</h4>
                                    <p>Native Chicken, Alimango (Mud Crabs), and Lobsters are available upon order.</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-black mb-2">Policies</h4>
                                    <p class="font-medium">Dine-In Only</p>
                                    <p>Strictly no sharing or takeouts. Leftovers cannot be taken home.</p>
                                    <p class="font-medium mt-2">No Leftovers</p>
                                    <p>Excessive leftovers (200 grams or more per dish) will incur a charge of ₱100 per 100 grams.</p>
                                    <p class="font-medium mt-2">No Outside Food & Drinks</p>
                                    <p>Bringing of outside food or beverages is strictly prohibited.</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-black mb-2">Birthday Promo</h4>
                                    <p class="font-medium">On the Day of the Birthday</p>
                                    <p>Eat for free when accompanied by one (1) full-paying adult.</p>
                                    <p class="font-medium mt-2">Within the Birthday Month</p>
                                    <p>Eat for free when accompanied by four (4) full-paying adults.</p>
                                    <p class="font-medium mt-2">Requirements</p>
                                    <p>Present a valid government-issued or company ID (with photo and birthdate).</p>
                                    <p>If no ID is available, a birth certificate and a photo ID will be accepted.</p>
                                    <p class="font-medium mt-2">Note</p>
                                    <p>The Birthday Promo cannot be combined with other discounts.</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-start space-x-3">
                            <input type="checkbox" id="agreeTerms" name="agreeTerms" required class="mt-1 w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                            <label for="agreeTerms" class="text-sm text-gray-700">I have read and agree to the terms and conditions, policies, and pricing listed above.</label>
                        </div>
                    </div>
                    <button type="submit" class="w-full bg-red-600 text-white py-3 px-6 rounded-lg font-semibold hover:bg-red-700 transform hover:scale-105 transition-all duration-300">Submit Reservation</button>
                </form>
            </div>
        </div>
    </div>
    <script>
        (function(){
        const images = [
            "https://i.ibb.co/JRGDpYbd/banner.jpg",
            "https://i.ibb.co/nsMtzSy4/0296273846-n.jpg",
            "https://i.ibb.co/gLZQFpcn/70360153841-n.jpg",
        ];

        const slide   = document.getElementById('heroSlide');
        const prevBtn = document.getElementById('heroPrev');
        const nextBtn = document.getElementById('heroNext');
        const dots    = Array.from(document.querySelectorAll('#heroCarousel .hero-dot'));
        const container = document.getElementById('heroCarousel');

        let i = 0, timer = null, isHover = false;
        const DURATION = 5000;

        function setSlide(idx) {
            i = (idx + images.length) % images.length;
            // subtle zoom effect
            slide.style.transform = 'scale(1.03)';
            slide.src = images[i];
            requestAnimationFrame(()=> {
            setTimeout(()=> slide.style.transform = 'scale(1.0)', 50);
            });
            dots.forEach((d, k)=> d.className = 'hero-dot w-2.5 h-2.5 rounded-full ' + (k===i ? 'bg-white' : 'bg-white/40'));
        }

        function next(){ setSlide(i+1); }
        function prev(){ setSlide(i-1); }

        function start(){
            if (timer) clearInterval(timer);
            timer = setInterval(()=> { if (!isHover) next(); }, DURATION);
        }

        // Events
        nextBtn.addEventListener('click', ()=> { next(); start(); });
        prevBtn.addEventListener('click', ()=> { prev(); start(); });
        dots.forEach((d, k)=> d.addEventListener('click', ()=> { setSlide(k); start(); }));

        container.addEventListener('mouseenter', ()=> { isHover = true; });
        container.addEventListener('mouseleave', ()=> { isHover = false; });

        // Touch swipe
        let x0 = null;
        container.addEventListener('touchstart', e => x0 = e.touches[0].clientX, {passive:true});
        container.addEventListener('touchend', e => {
            if (x0 === null) return;
            const dx = e.changedTouches[0].clientX - x0;
            if (Math.abs(dx) > 40) (dx > 0 ? prev() : next());
            x0 = null; start();
        }, {passive:true});

        // Init
        setSlide(0);
        start();
        })();
        let selectedDate = null;
        let selectedTime = null;
        let currentCalendarMonth = new Date().getMonth();
        let currentCalendarYear = new Date().getFullYear();
        function openReservationModal(){ document.getElementById('reservationModal').classList.remove('hidden'); document.body.style.overflow='hidden'; generateCalendar(); }
        function closeReservationModal(){ document.getElementById('reservationModal').classList.add('hidden'); document.body.style.overflow='auto'; resetForm(); }
        function closeConfirmationModal(){ document.getElementById('confirmationModal').classList.add('hidden'); document.body.style.overflow='auto'; closeReservationModal(); }
        function resetForm() {
            document.getElementById('reservationForm').reset();
            selectedDate = null; selectedTime = null;
            document.getElementById('selectedDate').value = '';
            document.getElementById('selectedTime').value = '';
            document.getElementById('adults').value = '2';
            document.getElementById('kids').value = '0';
            updateTotalGuests();
            document.querySelectorAll('.calendar-day').forEach(day => { day.classList.remove('bg-orange-600','text-white'); if (!day.disabled) day.classList.add('bg-gray-100','text-gray-800'); });
            document.querySelectorAll('.time-slot').forEach(slot => { slot.classList.remove('bg-orange-600','text-white'); slot.classList.add('bg-gray-100'); });
        }
        function changeCount(type, change) {
            const input = document.getElementById(type);
            let v = parseInt(input.value); let n = v + change;
            if (type === 'adults') n = Math.max(1, Math.min(20, n)); else n = Math.max(0, Math.min(10, n));
            input.value = n; updateTotalGuests();
        }
        function updateTotalGuests() {
            const adults = parseInt(document.getElementById('adults').value);
            const kids = parseInt(document.getElementById('kids').value);
            document.getElementById('totalGuests').textContent = adults + kids;
        }
        function generateCalendar() {
            const calendar = document.getElementById('calendar');
            const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            document.getElementById('currentMonth').textContent = `${monthNames[currentCalendarMonth]} ${currentCalendarYear}`;
            calendar.innerHTML = '';
            const dayHeaders = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
            dayHeaders.forEach(d => { const h = document.createElement('div'); h.className = 'text-center text-xs font-semibold text-gray-600 py-2'; h.textContent = d; calendar.appendChild(h); });
            const firstDay = new Date(currentCalendarYear, currentCalendarMonth, 1).getDay();
            const daysInMonth = new Date(currentCalendarYear, currentCalendarMonth + 1, 0).getDate();
            const today = new Date(); today.setHours(0,0,0,0);
            for (let i = 0; i < firstDay; i++) calendar.appendChild(document.createElement('div'));
            for (let day = 1; day <= daysInMonth; day++) {
                const date = new Date(currentCalendarYear, currentCalendarMonth, day);
                const dayElement = document.createElement('button');
                dayElement.type = 'button';
                dayElement.className = 'calendar-day p-3 text-sm font-medium rounded-lg transition-all duration-200';
                dayElement.textContent = day;
                const dayOfWeek = date.getDay();
                const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
                const isPast = date < today;
                if (isWeekend && !isPast) {
                    dayElement.className += ' bg-gray-100 hover:bg-orange-100 border border-gray-300 text-gray-800 cursor-pointer hover:shadow-md';
                    dayElement.onclick = () => selectDate(date, dayElement);
                } else {
                    dayElement.className += ' bg-gray-50 text-gray-400 cursor-not-allowed';
                    dayElement.disabled = true;
                }
                calendar.appendChild(dayElement);
            }
        }
        function selectDate(date, element) {
            document.querySelectorAll('.calendar-day').forEach(day => { if (!day.disabled) { day.classList.remove('bg-orange-600','text-white'); day.classList.add('bg-gray-100','text-gray-800'); } });
            element.classList.remove('bg-gray-100','text-gray-800'); element.classList.add('bg-orange-600','text-white');
            selectedDate = date;
            const iso = new Date(date.getTime() - date.getTimezoneOffset()*60000).toISOString().slice(0,10);
            document.getElementById('selectedDate').value = iso; // YYYY-MM-DD for server
        }
        document.getElementById('prevMonth').addEventListener('click', function() { currentCalendarMonth--; if (currentCalendarMonth < 0) { currentCalendarMonth = 11; currentCalendarYear--; } generateCalendar(); });
        document.getElementById('nextMonth').addEventListener('click', function() { currentCalendarMonth++; if (currentCalendarMonth > 11) { currentCalendarMonth = 0; currentCalendarYear++; } generateCalendar(); });
        document.querySelectorAll('.time-slot').forEach(slot => { slot.addEventListener('click', function() { document.querySelectorAll('.time-slot').forEach(s => { s.classList.remove('bg-orange-600','text-white'); s.classList.add('bg-gray-100'); }); this.classList.remove('bg-gray-100'); this.classList.add('bg-orange-600','text-white'); selectedTime = this.dataset.time; document.getElementById('selectedTime').value = selectedTime; }); });
        document.getElementById('reservationModal').addEventListener('click', function(e) { if (e.target === this) { closeReservationModal(); } });
        document.addEventListener('DOMContentLoaded', function() { updateTotalGuests(); });
    </script>
<script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9752828806a3454d',t:'MTc1NjIwMjcxNy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>
