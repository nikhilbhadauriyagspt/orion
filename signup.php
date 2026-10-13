<?php
/**
 * User Sign Up Page - Orion Advent
 * Clean, Airy, Lightweight & Modern Travel Theme UI with OTP Email Verification
 */
require_once __DIR__ . '/config/settings.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$redirect = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';

// If already logged in, send them straight back
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: " . (!empty($redirect) ? $redirect : 'index.php'));
    exit();
}

$pageTitle = 'Create Your Travel Account - ' . htmlspecialchars(getSetting('site_name', 'Orion Advent'));
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<main class="flex-grow flex items-center justify-center py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8 relative bg-slate-50/70">

    <!-- Subtle Ambient Background Accents -->
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
        <div class="absolute -top-32 -right-32 w-96 h-96 rounded-full bg-brand-100/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -left-32 w-96 h-96 rounded-full bg-teal-100/30 blur-3xl"></div>
    </div>

    <div class="w-full max-w-4xl mx-auto relative z-10">
        <!-- Main Auth Container: 2 Column Split (Travel Card + Clean Form) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[620px]">
            
            <!-- Left Column: Inspiring Real Traveler Visual & Perks (5 cols) -->
            <div class="lg:col-span-5 relative bg-slate-900 text-white flex flex-col justify-between overflow-hidden min-h-[420px] lg:min-h-full">
                <!-- High-Quality Realistic Traveler Image Background with Natural Atmosphere -->
                <img src="https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=900&q=85" 
                     alt="Happy Traveler Exploring Tropical Coast" 
                     class="absolute inset-0 w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-700 select-none">
                
                <!-- Modern Gradient Overlay for Readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/40 to-slate-900/30"></div>

                <!-- Top Floating Welcome Badge -->
                <div class="relative z-10 p-6 sm:p-8 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30 backdrop-blur-md shadow-sm">
                        <i class="fa-solid fa-gift text-amber-300"></i>
                        <span>₹1,500 Welcome Discount</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight drop-shadow-sm">
                        Start Exploring the <br>
                        <span class="bg-gradient-to-r from-teal-300 via-emerald-300 to-amber-300 bg-clip-text text-transparent">World With Us</span>
                    </h2>
                </div>

                <!-- Bottom Traveler Card & Perks -->
                <div class="relative z-10 p-6 sm:p-8 space-y-3.5">
                    
                    <!-- Verified Guest Rating Card -->
                    <div class="p-3.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 text-white space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=120&q=80" 
                                     alt="Aditya R." 
                                     class="w-7 h-7 rounded-full object-cover border border-white/40">
                                <div class="truncate">
                                    <p class="text-xs font-bold leading-none">Aditya Rao</p>
                                    <span class="text-[10px] text-teal-200">Verified Traveler &bull; Ladakh Tour</span>
                                </div>
                            </div>
                            <div class="flex items-center text-amber-300 text-[10px] gap-0.5">
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                                <i class="fa-solid fa-star"></i>
                            </div>
                        </div>
                        <p class="text-[11px] text-slate-100 font-medium italic line-clamp-2">
                            "The personalized itinerary planner saved us hours. 100% genuine rates and instant support!"
                        </p>
                    </div>

                    <!-- Bottom Trust Indicator -->
                    <div class="flex items-center justify-between text-[11px] text-slate-200 pt-1">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-check text-teal-300"></i>
                            <span>Free &bull; Instant Confirmation</span>
                        </span>
                        <span class="font-bold text-amber-300">24x7 Support</span>
                    </div>

                </div>
            </div>

            <!-- Right Column: Step 1 (Sign Up Form) & Step 2 (OTP Verification Modal) (7 cols) -->
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                
                <!-- STEP 1: SIGN UP REGISTRATION -->
                <div id="signupStep1">
                    <div class="mb-5">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Create Free Account</h3>
                        <p class="text-xs text-slate-500 mt-1">Start your journey with <?php echo htmlspecialchars(getSetting('site_name', 'Orion Advent')); ?> today</p>
                    </div>

                    <!-- Alert message container -->
                    <div id="signupAlert" class="hidden mb-4 p-3 rounded-xl text-xs font-semibold"></div>

                    <!-- Quick Social Sign Up -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <a href="api/google-callback.php" class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-bold text-slate-700 transition active:scale-95">
                            <i class="fa-brands fa-google text-rose-500 text-sm"></i>
                            <span>Google</span>
                        </a>
                        <a href="api/facebook-callback.php" class="flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-50 text-xs font-bold text-slate-700 transition active:scale-95">
                            <i class="fa-brands fa-facebook text-blue-600 text-sm"></i>
                            <span>Facebook</span>
                        </a>
                    </div>

                    <!-- Divider -->
                    <div class="relative flex items-center justify-center mb-4">
                        <div class="border-t border-slate-200 w-full"></div>
                        <span class="bg-white px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider shrink-0">or sign up with details</span>
                    </div>

                    <!-- Sign Up Form -->
                    <form id="signupForm" class="space-y-3.5">
                        
                        <!-- Full Name -->
                        <div>
                            <label for="signupName" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                Full Name
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center pointer-events-none z-10">
                                    <i class="fa-regular fa-user text-xs"></i>
                                </div>
                                <input type="text" 
                                       id="signupName" 
                                       name="fullname" 
                                       required 
                                       placeholder="John Doe"
                                       style="padding-left: 3.25rem !important;"
                                       class="w-full pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>

                        <!-- Email & Mobile Row -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label for="signupEmail" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                    Email Address
                                </label>
                                <div class="relative flex items-center">
                                    <div class="absolute left-3 w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center pointer-events-none z-10">
                                        <i class="fa-regular fa-envelope text-xs"></i>
                                    </div>
                                    <input type="email" 
                                           id="signupEmail" 
                                           name="email" 
                                           required 
                                           placeholder="name@example.com"
                                           style="padding-left: 3.25rem !important;"
                                           class="w-full pr-3 py-2.5 text-xs bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                                </div>
                            </div>

                            <div>
                                <label for="signupPhone" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                    Phone Number
                                </label>
                                <div class="relative flex items-center">
                                    <div class="absolute left-3 w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center pointer-events-none z-10">
                                        <i class="fa-solid fa-phone text-xs"></i>
                                    </div>
                                    <input type="tel" 
                                           id="signupPhone" 
                                           name="phone" 
                                           required 
                                           placeholder="+91 98765 43210"
                                           style="padding-left: 3.25rem !important;"
                                           class="w-full pr-3 py-2.5 text-xs bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                                </div>
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div>
                            <label for="signupPassword" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1">
                                Create Password
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center pointer-events-none z-10">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </div>
                                <input type="password" 
                                       id="signupPassword" 
                                       name="password" 
                                       required 
                                       placeholder="At least 6 characters"
                                       style="padding-left: 3.25rem !important;"
                                       class="w-full pr-11 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                                <button type="button" class="absolute right-3.5 text-slate-400 hover:text-slate-600 text-xs focus:outline-none z-10" onclick="
                                    const pass = document.getElementById('signupPassword');
                                    const icon = this.querySelector('i');
                                    if (pass.type === 'password') {
                                        pass.type = 'text';
                                        icon.className = 'fa-regular fa-eye-slash';
                                    } else {
                                        pass.type = 'password';
                                        icon.className = 'fa-regular fa-eye';
                                    }
                                ">
                                    <i class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Terms agreement -->
                        <div class="flex items-start gap-2 pt-1">
                            <input type="checkbox" required name="terms" id="termsAgree" checked class="mt-0.5 w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500 accent-brand-600">
                            <label for="termsAgree" class="text-[11px] text-slate-500 leading-tight select-none cursor-pointer">
                                I agree to the <a href="#" class="text-brand-600 font-semibold hover:underline">Terms of Service</a> &amp; <a href="#" class="text-brand-600 font-semibold hover:underline">Privacy Policy</a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit" id="signupSubmitBtn" class="w-full py-3 px-6 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer">
                                <span>Get Verification Code</span>
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                            </button>
                        </div>

                    </form>

                    <!-- Footer Link to Log In -->
                    <div class="mt-6 text-center pt-4 border-t border-slate-100">
                        <p class="text-xs text-slate-500 font-medium">
                            Already have an account? 
                            <a href="login.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-brand-600 hover:text-brand-700 font-bold ml-1 hover:underline">
                                Log In Here
                            </a>
                        </p>
                    </div>
                </div>

                <!-- STEP 2: OTP VERIFICATION MODAL -->
                <div id="signupStep2" class="hidden">
                    <div class="mb-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 mb-2">
                            <i class="fa-solid fa-envelope-circle-check"></i> Verification Code Sent
                        </span>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Verify Your Email</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            We've sent a 6-digit OTP code to <strong id="verifyEmailDisplay" class="text-slate-800"></strong>
                        </p>
                    </div>

                    <!-- Alert message container -->
                    <div id="verifyAlert" class="hidden mb-4 p-3 rounded-xl text-xs font-semibold"></div>

                    <form id="verifyOtpForm" class="space-y-4">
                        <input type="hidden" id="verifyEmailHidden" name="email">
                        <input type="hidden" id="verifyRedirectInput" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="verifyOtpInput" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    Enter 6-Digit OTP
                                </label>
                                <button type="button" id="signupResendOtpBtn" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 transition">
                                    Resend Code
                                </button>
                            </div>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center pointer-events-none z-10">
                                    <i class="fa-solid fa-shield-halved text-xs"></i>
                                </div>
                                <input type="text" 
                                       id="verifyOtpInput" 
                                       name="otp" 
                                       required 
                                       maxlength="6"
                                       placeholder="123456"
                                       style="padding-left: 3.25rem !important;"
                                       class="w-full pr-4 py-2.5 text-base tracking-widest font-mono font-bold bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" id="verifySubmitBtn" class="w-full py-3 px-6 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer">
                                <span>Confirm &amp; Create Account</span>
                                <i class="fa-solid fa-check text-xs"></i>
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center pt-4 border-t border-slate-100">
                        <button type="button" onclick="
                            document.getElementById('signupStep2').classList.add('hidden');
                            document.getElementById('signupStep1').classList.remove('hidden');
                        " class="text-xs text-slate-500 hover:text-slate-800 font-semibold inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Edit Signup Details</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');
    const verifyForm = document.getElementById('verifyOtpForm');
    const signupAlert = document.getElementById('signupAlert');
    const verifyAlert = document.getElementById('verifyAlert');
    const signupBtn = document.getElementById('signupSubmitBtn');
    const verifyBtn = document.getElementById('verifySubmitBtn');

    function showAlert(el, msg, type = 'error') {
        el.classList.remove('hidden', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
        if (type === 'success') {
            el.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else {
            el.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
        }
        el.innerHTML = msg;
    }

    // Submit Signup Form -> Triggers OTP Email
    if (signupForm) {
        signupForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const originalBtn = signupBtn.innerHTML;
            signupBtn.disabled = true;
            signupBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Sending OTP...</span>`;

            try {
                const formData = new FormData(signupForm);
                formData.append('action', 'signup');

                const res = await fetch('api/auth.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    const email = document.getElementById('signupEmail').value.trim();
                    document.getElementById('verifyEmailDisplay').textContent = email;
                    document.getElementById('verifyEmailHidden').value = email;

                    if (data.mail_mode === 'testing' && data.testing_otp) {
                        document.getElementById('verifyOtpInput').value = data.testing_otp;
                        showAlert(verifyAlert, `<strong>Testing Mode Active:</strong> OTP Auto-filled: <code>${data.testing_otp}</code> (or enter 1234)`, 'success');
                    } else {
                        showAlert(verifyAlert, data.message, 'success');
                    }

                    document.getElementById('signupStep1').classList.add('hidden');
                    document.getElementById('signupStep2').classList.remove('hidden');
                } else {
                    showAlert(signupAlert, data.message || 'Failed to sign up.', 'error');
                }
            } catch (err) {
                showAlert(signupAlert, 'Server communication error. Please try again.', 'error');
            } finally {
                signupBtn.disabled = false;
                signupBtn.innerHTML = originalBtn;
            }
        });
    }

    // Resend OTP in Signup
    const resendBtn = document.getElementById('signupResendOtpBtn');
    if (resendBtn) {
        resendBtn.addEventListener('click', () => {
            signupForm.dispatchEvent(new Event('submit'));
        });
    }

    // Verify OTP & Complete Signup
    if (verifyForm) {
        verifyForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const originalBtn = verifyBtn.innerHTML;
            verifyBtn.disabled = true;
            verifyBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Verifying OTP...</span>`;

            try {
                const formData = new FormData(verifyForm);
                formData.append('action', 'verify_signup_otp');

                const res = await fetch('api/auth.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showAlert(verifyAlert, data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 1000);
                } else {
                    showAlert(verifyAlert, data.message || 'Verification failed.', 'error');
                }
            } catch (err) {
                showAlert(verifyAlert, 'Server communication error. Please try again.', 'error');
            } finally {
                verifyBtn.disabled = false;
                verifyBtn.innerHTML = originalBtn;
            }
        });
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
