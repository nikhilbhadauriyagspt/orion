<?php
/**
 * Help & Contact Us Page - Orion Advent
 * Clean, Airy, Lightweight & Modern Travel Theme UI
 * 24x7 Concierge Assistance, Support Ticket Form & Extensive FAQ Knowledgebase
 */
require_once __DIR__ . '/config/settings.php';
$siteName = getSetting('site_name', 'Orion Advent');
$sitePhone = getSetting('site_phone', '+91 98765 43210');
$siteWhatsapp = getSetting('site_whatsapp', '919876543210');
$siteEmail = getSetting('site_email', 'support@orionadvent.com');
$siteAddress = getSetting('site_address', 'DLF Cyber City, Tower B, Gurugram, Haryana - 122002');
$siteLogo = getSetting('site_logo', 'assets/images/logo/logo.webp');
$socialFb = getSetting('social_facebook', '');
$socialInsta = getSetting('social_instagram', '');
$socialYt = getSetting('social_youtube', '');
$socialTw = getSetting('social_twitter', '');
$socialLi = getSetting('social_linkedin', '');

$pageTitle = 'Help & Contact Us - 24x7 Traveler Concierge | ' . htmlspecialchars($siteName);
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<main class="min-h-screen bg-slate-50/60 pb-20">

    <!-- ========================================================================= -->
    <!-- 1. HERO BANNER: 24x7 Concierge & Help Center Header                       -->
    <!-- ========================================================================= -->
    <section class="relative bg-slate-950 text-white overflow-hidden py-20 sm:py-24 lg:py-28 px-4 sm:px-6 lg:px-8">
        <!-- Ambient Travel Background Visual -->
        <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1920&q=85" 
             alt="Travel Concierge Assistance" 
             class="absolute inset-0 w-full h-full object-cover object-center opacity-30 select-none">
        
        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-slate-950/60"></div>

        <div class="relative z-10 max-w-4xl mx-auto text-center space-y-4">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold bg-white/15 text-white border border-white/25 backdrop-blur-md">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <i class="fa-solid fa-headset text-emerald-300"></i>
                <span>24x7 Traveler Support &amp; Helpdesk</span>
            </div>

            <h1 class="text-3xl sm:text-5xl font-black font-space tracking-tight text-white leading-tight">
                How Can We Help <br class="hidden sm:inline">
                <span class="bg-gradient-to-r from-teal-300 via-emerald-300 to-amber-300 bg-clip-text text-transparent">
                    Your Travel Plans Today?
                </span>
            </h1>

            <p class="text-xs sm:text-sm lg:text-base text-slate-300 max-w-2xl mx-auto font-normal leading-relaxed">
                Whether you need assistance with an existing booking, custom itinerary crafting, flight inquiry, or token advance queries — our destination specialists are here 24 hours a day.
            </p>
        </div>
    </section>

    <!-- ========================================================================= -->
    <!-- 2. DIRECT CONTACT CARDS (PHONE, WHATSAPP, EMAIL, OFFICE)                  -->
    <!-- ========================================================================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-10 sm:-mt-12 relative z-20">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Channel 1: 24x7 Helpline -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between hover:border-brand-500 transition-all group">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-2xl bg-teal-50 text-teal-600 border border-teal-200 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">24x7 Hotline</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-0.5"><?php echo htmlspecialchars($sitePhone); ?></h3>
                        <p class="text-xs text-slate-500 mt-1">Instant verbal assistance for ongoing trips and urgent changes.</p>
                    </div>
                </div>
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="tel:<?php echo htmlspecialchars($sitePhone); ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-teal-600 hover:text-teal-700">
                        <span>Call Helpdesk</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Channel 2: WhatsApp Chat -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between hover:border-emerald-500 transition-all group">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                        <i class="fa-brands fa-whatsapp text-xl"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">WhatsApp Concierge</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-0.5">Live Chat Support</h3>
                        <p class="text-xs text-slate-500 mt-1">Share PDFs, voucher details, or ask quick travel questions.</p>
                    </div>
                </div>
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="https://wa.me/<?php echo htmlspecialchars($siteWhatsapp); ?>?text=<?php echo urlencode('Hi ' . $siteName . ', I need help with travel bookings.'); ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-600 hover:text-emerald-700">
                        <span>Start WhatsApp Chat</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Channel 3: Email Helpdesk -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between hover:border-sky-500 transition-all group">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 border border-sky-200 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                        <i class="fa-regular fa-envelope"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Official Email</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-0.5 truncate"><?php echo htmlspecialchars($siteEmail); ?></h3>
                        <p class="text-xs text-slate-500 mt-1">Send comprehensive itinerary requests or group discount inquiries.</p>
                    </div>
                </div>
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="mailto:<?php echo htmlspecialchars($siteEmail); ?>" class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-600 hover:text-sky-700">
                        <span>Send Email</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Channel 4: Corporate Office -->
            <div class="bg-white rounded-3xl border border-slate-200 p-6 flex flex-col justify-between hover:border-amber-500 transition-all group">
                <div class="space-y-3">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 border border-amber-200 flex items-center justify-center text-lg group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Headquarters</span>
                        <h3 class="text-base font-extrabold text-slate-900 mt-0.5">Corporate Office</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed"><?php echo htmlspecialchars($siteAddress); ?></p>
                    </div>
                </div>
                <div class="pt-4 mt-2 border-t border-slate-100">
                    <a href="https://maps.google.com/?q=<?php echo urlencode($siteAddress); ?>" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-bold text-amber-600 hover:text-amber-700">
                        <span>Get Directions</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

        <!-- Dynamic Social Connect Bar -->
        <?php if (!empty($socialFb) || !empty($socialInsta) || !empty($socialYt) || !empty($socialTw) || !empty($socialLi) || !empty($siteWhatsapp)): ?>
            <div class="mt-6 p-4 rounded-2xl bg-white border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                <span class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="fa-solid fa-share-nodes text-brand-600"></i>
                    <span>Connect with <?php echo htmlspecialchars($siteName); ?> on Official Channels:</span>
                </span>
                <div class="flex items-center space-x-2">
                    <?php if (!empty($socialFb)): ?>
                        <a href="<?php echo htmlspecialchars($socialFb); ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-brand-600 text-slate-600 hover:text-white flex items-center justify-center transition" title="Facebook">
                            <i class="fa-brands fa-facebook-f text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($socialInsta)): ?>
                        <a href="<?php echo htmlspecialchars($socialInsta); ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-brand-600 text-slate-600 hover:text-white flex items-center justify-center transition" title="Instagram">
                            <i class="fa-brands fa-instagram text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($socialYt)): ?>
                        <a href="<?php echo htmlspecialchars($socialYt); ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-brand-600 text-slate-600 hover:text-white flex items-center justify-center transition" title="YouTube">
                            <i class="fa-brands fa-youtube text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($socialTw)): ?>
                        <a href="<?php echo htmlspecialchars($socialTw); ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-brand-600 text-slate-600 hover:text-white flex items-center justify-center transition" title="X Twitter">
                            <i class="fa-brands fa-x-twitter text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($socialLi)): ?>
                        <a href="<?php echo htmlspecialchars($socialLi); ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-brand-600 text-slate-600 hover:text-white flex items-center justify-center transition" title="LinkedIn">
                            <i class="fa-brands fa-linkedin-in text-xs"></i>
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($siteWhatsapp)): ?>
                        <a href="https://wa.me/<?php echo htmlspecialchars($siteWhatsapp); ?>" target="_blank" rel="noopener noreferrer" class="w-8 h-8 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white flex items-center justify-center transition" title="WhatsApp">
                            <i class="fa-brands fa-whatsapp text-sm"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </section>

    <!-- ========================================================================= -->
    <!-- 3. MAIN INTERACTIVE FORM & EMERGENCY STRIP (2 COLUMNS)                     -->
    <!-- ========================================================================= -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12">
            
            <!-- Left Form: Send Inquiry / Create Support Ticket (7 cols) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200 p-6 sm:p-10 space-y-6">
                <div>
                    <span class="text-xs font-bold uppercase tracking-widest text-brand-600">Send Direct Message</span>
                    <h2 class="text-2xl font-black font-space text-slate-900 mt-1">
                        How Can Our Destination Team Assist You?
                    </h2>
                    <p class="text-xs text-slate-500 mt-1">
                        Fill out the form below. We typically respond within 60 to 120 minutes during working hours.
                    </p>
                </div>

                <!-- Alert container -->
                <div id="contactAlert" class="hidden p-3.5 rounded-2xl text-xs font-semibold"></div>

                <form id="contactForm" class="space-y-4">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Full Name -->
                        <div>
                            <label for="cName" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Your Full Name <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center pointer-events-none text-xs">
                                    <i class="fa-regular fa-user"></i>
                                </div>
                                <input type="text" id="cName" name="fullname" required placeholder="John Doe" style="padding-left: 3.25rem !important;" class="w-full pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="cEmail" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Email Address <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center pointer-events-none text-xs">
                                    <i class="fa-regular fa-envelope"></i>
                                </div>
                                <input type="email" id="cEmail" name="email" required placeholder="name@example.com" style="padding-left: 3.25rem !important;" class="w-full pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Phone Number -->
                        <div>
                            <label for="cPhone" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Phone / Mobile Number
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center pointer-events-none text-xs">
                                    <i class="fa-solid fa-phone"></i>
                                </div>
                                <input type="tel" id="cPhone" name="phone" placeholder="+91 98765 43210" style="padding-left: 3.25rem !important;" class="w-full pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>

                        <!-- Inquiry Subject -->
                        <div>
                            <label for="cSubject" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Topic / Inquiry Type
                            </label>
                            <select id="cSubject" name="subject" class="w-full px-3.5 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                                <option value="Tour Package Booking">Tour Package Booking &amp; Customization</option>
                                <option value="Hotel Reservation">Hotel &amp; Resort Stay Query</option>
                                <option value="Flight Assistance">Flight Reservation &amp; Multi-City Query</option>
                                <option value="Token Advance & Payment">Token Advance &amp; Payment Support</option>
                                <option value="Cancellation or Reschedule">Cancellation / Date Change Request</option>
                                <option value="General Support">General Traveler Assistance</option>
                            </select>
                        </div>
                    </div>

                    <!-- Message -->
                    <div>
                        <label for="cMessage" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            How Can We Help You? (Destination, Dates, Number of Travelers, etc.) <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="cMessage" name="message" rows="4" required placeholder="Tell us about your travel plans, package preferences, or questions..." class="w-full p-3.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl resize-y"></textarea>
                    </div>

                    <button type="submit" id="contactSubmitBtn" class="w-full py-3.5 px-6 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition-all transform active:scale-95 cursor-pointer">
                        <span>Send Message to Concierge</span>
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </form>
            </div>

            <!-- Right: Emergency Traveler Helpline & Guarantee Badges (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- On-Trip Emergency Card -->
                <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 text-white border border-slate-800 space-y-4">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-[10px] font-bold bg-rose-500/20 text-rose-300 border border-rose-400/30">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Active Trip Traveler Support</span>
                    </div>

                    <h3 class="text-xl font-bold font-space text-white leading-snug">
                        Currently On a Trip &amp; Need Immediate Support?
                    </h3>

                    <p class="text-xs text-slate-300 leading-relaxed">
                        If you are currently traveling on an active <?php echo htmlspecialchars($siteName); ?> itinerary and require immediate driver coordination, hotel check-in support, or emergency itinerary adjustment:
                    </p>

                    <div class="p-4 rounded-2xl bg-white/10 border border-white/15 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-amber-300 uppercase">Emergency Helpline:</span>
                            <span class="text-xs font-mono font-bold text-white"><?php echo htmlspecialchars($sitePhone); ?></span>
                        </div>
                        <p class="text-[11px] text-slate-300">Keep your Booking Reference handy for instant verification.</p>
                    </div>

                    <a href="https://wa.me/<?php echo htmlspecialchars($siteWhatsapp); ?>?text=<?php echo urlencode('EMERGENCY: I am currently traveling and need immediate on-trip support.'); ?>" target="_blank" class="w-full py-3 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider text-center block transition">
                        <i class="fa-brands fa-whatsapp text-sm mr-1"></i> WhatsApp Emergency Support
                    </a>
                </div>

                <!-- Trust Guarantee Card -->
                <div class="p-6 rounded-3xl bg-white border border-slate-200 space-y-3">
                    <h4 class="text-sm font-bold text-slate-900 font-space flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-teal-600"></i>
                        <span>The <?php echo htmlspecialchars($siteName); ?> Service Promise</span>
                    </h4>
                    
                    <ul class="space-y-2.5 text-xs text-slate-600">
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-teal-600 text-[11px] mt-0.5"></i>
                            <span><strong>Zero-Cost Rescheduling:</strong> Free date changes up to 7 days before departure for domestic tours.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-teal-600 text-[11px] mt-0.5"></i>
                            <span><strong>Token Advance Protection:</strong> Pay only 30% to reserve, balance is verified on arrival.</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <i class="fa-solid fa-check text-teal-600 text-[11px] mt-0.5"></i>
                            <span><strong>Verified Hotel Rooms:</strong> What you see on our details page is guaranteed at check-in.</span>
                        </li>
                    </ul>
                </div>

            </div>

        </div>
    </section>

   


</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // 1. FAQ Accordion Handler
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach(item => {
        const btn = item.querySelector('.faq-btn');
        const content = item.querySelector('.faq-content');
        const icon = item.querySelector('.faq-icon');

        btn.addEventListener('click', () => {
            const isOpen = !content.classList.contains('hidden');
            
            // Close all
            document.querySelectorAll('.faq-content').forEach(c => c.classList.add('hidden'));
            document.querySelectorAll('.faq-icon').forEach(i => i.classList.remove('rotate-180'));

            if (!isOpen) {
                content.classList.remove('hidden');
                icon.classList.add('rotate-180');
            }
        });
    });

    // 2. Contact Form AJAX Submission
    const contactForm = document.getElementById('contactForm');
    const contactAlert = document.getElementById('contactAlert');
    const submitBtn = document.getElementById('contactSubmitBtn');

    function showAlert(msg, isSuccess = false) {
        contactAlert.classList.remove('hidden', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
        if (isSuccess) {
            contactAlert.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else {
            contactAlert.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
        }
        contactAlert.innerHTML = msg;
        contactAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    if (contactForm) {
        contactForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Sending Message...</span>`;

            try {
                const formData = new FormData(contactForm);
                const res = await fetch('api/contact.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showAlert(`<i class="fa-solid fa-circle-check mr-1.5"></i> ${data.message}`, true);
                    contactForm.reset();
                } else {
                    showAlert(`<i class="fa-solid fa-circle-exclamation mr-1.5"></i> ${data.message || 'Failed to submit form.'}`, false);
                }
            } catch (err) {
                showAlert('Network error. Please try again or reach out via WhatsApp.', false);
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
