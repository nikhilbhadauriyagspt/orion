<?php
/**
 * User Forgot Password Page - Orion Advent
 * Clean, Airy, Lightweight & Modern Travel Theme UI with OTP Reset
 */
require_once __DIR__ . '/config/settings.php';
$pageTitle = 'Reset Your Password - ' . htmlspecialchars(getSetting('site_name', 'Orion Advent'));
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<main class="flex-grow flex items-center justify-center py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8 relative bg-slate-50/70">

    <!-- Subtle Ambient Background Accents -->
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-100/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-amber-100/30 blur-3xl"></div>
    </div>

    <div class="w-full max-w-4xl mx-auto relative z-10">
        <!-- Main Auth Container: 2 Column Split (Travel Card + Clean Form) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[540px]">
            
            <!-- Left Column: Travel Ambient Visual & Help (5 cols) -->
            <div class="lg:col-span-5 relative bg-slate-900 text-white flex flex-col justify-between overflow-hidden min-h-[380px] lg:min-h-full">
                <!-- High-Quality Realistic Traveler Image Background -->
                <img src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=900&q=85" 
                     alt="Traveler Scenic Valley" 
                     class="absolute inset-0 w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-700 select-none">
                
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/45 to-slate-900/30"></div>

                <!-- Top Floating Destination Badge -->
                <div class="relative z-10 p-6 sm:p-8 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30 backdrop-blur-md shadow-sm">
                        <i class="fa-solid fa-key text-amber-300"></i>
                        <span>Secure Account Recovery</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight drop-shadow-sm">
                        Don't Worry, We've <br>
                        <span class="bg-gradient-to-r from-teal-300 via-emerald-300 to-amber-300 bg-clip-text text-transparent">Got You Covered</span>
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-200 leading-relaxed font-normal">
                        Enter your registered email address and we'll send a one-time verification code (OTP) to securely reset your password.
                    </p>
                </div>

                <!-- Bottom Support Contact -->
                <div class="relative z-10 p-6 sm:p-8">
                    <div class="p-3.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 text-white flex items-center gap-3">
                        <div class="w-8 h-8 rounded-xl bg-teal-400/20 text-teal-300 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-headset text-xs"></i>
                        </div>
                        <div class="text-left truncate">
                            <p class="text-xs font-bold leading-none">Need urgent help?</p>
                            <span class="text-[11px] text-teal-200">Call 24x7 Support: <?php echo htmlspecialchars(getSetting('site_phone', '+91 98765 43210')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Step 1 (Send OTP) & Step 2 (Enter OTP + New Password) (7 cols) -->
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                
                <!-- STEP 1: Enter Email Form -->
                <div id="forgotStep1">
                    <div class="mb-6">
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Forgot Password</h3>
                        <p class="text-xs text-slate-500 mt-1">Enter your registered email to receive an OTP verification code</p>
                    </div>

                    <!-- Alert message container -->
                    <div id="step1Alert" class="hidden mb-4 p-3 rounded-xl text-xs font-semibold"></div>

                    <form id="forgotSendOtpForm" class="space-y-4">
                        <div>
                            <label for="forgotEmail" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Registered Email Address
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center pointer-events-none z-10">
                                    <i class="fa-regular fa-envelope text-xs"></i>
                                </div>
                                <input type="email" 
                                       id="forgotEmail" 
                                       name="email" 
                                       required 
                                       placeholder="name@example.com"
                                       style="padding-left: 3.25rem !important;"
                                       class="w-full pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>

                        <div class="pt-2">
                            <button type="submit" id="sendOtpBtn" class="w-full py-3 px-6 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer">
                                <span>Send Verification Code</span>
                                <i class="fa-solid fa-paper-plane text-xs"></i>
                            </button>
                        </div>
                    </form>

                    <div class="mt-7 text-center pt-5 border-t border-slate-100">
                        <p class="text-xs text-slate-500 font-medium">
                            Remember your password? 
                            <a href="login.php" class="text-brand-600 hover:text-brand-700 font-bold ml-1 hover:underline">
                                Back to Log In
                            </a>
                        </p>
                    </div>
                </div>

                <!-- STEP 2: Enter OTP & Set New Password -->
                <div id="forgotStep2" class="hidden">
                    <div class="mb-5">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-teal-50 text-teal-700 border border-teal-200 mb-2">
                            <i class="fa-solid fa-shield-check"></i> Code Sent
                        </span>
                        <h3 class="text-2xl font-black text-slate-900 tracking-tight">Set New Password</h3>
                        <p class="text-xs text-slate-500 mt-1">
                            Enter the 6-digit OTP code sent to <strong id="sentEmailDisplay" class="text-slate-800"></strong>
                        </p>
                    </div>

                    <!-- Alert message container -->
                    <div id="step2Alert" class="hidden mb-4 p-3 rounded-xl text-xs font-semibold"></div>

                    <form id="forgotResetPassForm" class="space-y-4">
                        <input type="hidden" id="resetEmailHidden" name="email">

                        <!-- OTP Input -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="resetOtp" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                    6-Digit OTP Code
                                </label>
                                <button type="button" id="resendOtpBtn" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 transition">
                                    Resend Code
                                </button>
                            </div>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center pointer-events-none z-10">
                                    <i class="fa-solid fa-shield-halved text-xs"></i>
                                </div>
                                <input type="text" 
                                       id="resetOtp" 
                                       name="otp" 
                                       required 
                                       maxlength="6"
                                       placeholder="123456"
                                       style="padding-left: 3.25rem !important;"
                                       class="w-full pr-4 py-2.5 text-base tracking-widest font-mono font-bold bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            </div>
                        </div>

                        <!-- New Password -->
                        <div>
                            <label for="resetNewPassword" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                                Create New Password
                            </label>
                            <div class="relative flex items-center">
                                <div class="absolute left-3 w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center pointer-events-none z-10">
                                    <i class="fa-solid fa-lock text-xs"></i>
                                </div>
                                <input type="password" 
                                       id="resetNewPassword" 
                                       name="new_password" 
                                       required 
                                       placeholder="At least 6 characters"
                                       style="padding-left: 3.25rem !important;"
                                       class="w-full pr-11 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                                <button type="button" class="absolute right-3.5 text-slate-400 hover:text-slate-600 text-xs focus:outline-none z-10" onclick="
                                    const pass = document.getElementById('resetNewPassword');
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

                        <div class="pt-2">
                            <button type="submit" id="resetPassBtn" class="w-full py-3 px-6 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer">
                                <span>Reset &amp; Save Password</span>
                                <i class="fa-solid fa-check text-xs"></i>
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 text-center pt-4 border-t border-slate-100">
                        <button type="button" onclick="
                            document.getElementById('forgotStep2').classList.add('hidden');
                            document.getElementById('forgotStep1').classList.remove('hidden');
                        " class="text-xs text-slate-500 hover:text-slate-800 font-semibold inline-flex items-center gap-1.5">
                            <i class="fa-solid fa-arrow-left text-[10px]"></i>
                            <span>Change Email Address</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const step1Form = document.getElementById('forgotSendOtpForm');
    const step2Form = document.getElementById('forgotResetPassForm');
    const step1Alert = document.getElementById('step1Alert');
    const step2Alert = document.getElementById('step2Alert');
    const sendOtpBtn = document.getElementById('sendOtpBtn');
    const resetPassBtn = document.getElementById('resetPassBtn');

    function showAlert(el, msg, type = 'error') {
        el.classList.remove('hidden', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
        if (type === 'success') {
            el.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else {
            el.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
        }
        el.innerHTML = msg;
    }

    // Step 1: Send Reset OTP
    if (step1Form) {
        step1Form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('forgotEmail').value.trim();
            if (!email) return;

            const originalBtn = sendOtpBtn.innerHTML;
            sendOtpBtn.disabled = true;
            sendOtpBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Sending OTP...</span>`;

            try {
                const formData = new FormData();
                formData.append('action', 'forgot_password_send_otp');
                formData.append('email', email);

                const res = await fetch('api/auth.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    document.getElementById('sentEmailDisplay').textContent = email;
                    document.getElementById('resetEmailHidden').value = email;
                    
                    if (data.mail_mode === 'testing' && data.testing_otp) {
                        document.getElementById('resetOtp').value = data.testing_otp;
                        showAlert(step2Alert, `<strong>Testing Mode:</strong> Auto-filled OTP code: <code>${data.testing_otp}</code>`, 'success');
                    } else {
                        showAlert(step2Alert, data.message, 'success');
                    }

                    document.getElementById('forgotStep1').classList.add('hidden');
                    document.getElementById('forgotStep2').classList.remove('hidden');
                } else {
                    showAlert(step1Alert, data.message || 'Failed to send OTP code.', 'error');
                }
            } catch (err) {
                showAlert(step1Alert, 'Server communication error. Please try again.', 'error');
            } finally {
                sendOtpBtn.disabled = false;
                sendOtpBtn.innerHTML = originalBtn;
            }
        });
    }

    // Resend OTP
    const resendOtpBtn = document.getElementById('resendOtpBtn');
    if (resendOtpBtn) {
        resendOtpBtn.addEventListener('click', () => {
            step1Form.dispatchEvent(new Event('submit'));
        });
    }

    // Step 2: Reset Password
    if (step2Form) {
        step2Form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('resetEmailHidden').value.trim();
            const otp = document.getElementById('resetOtp').value.trim();
            const newPassword = document.getElementById('resetNewPassword').value;

            if (!otp || !newPassword) return;

            const originalBtn = resetPassBtn.innerHTML;
            resetPassBtn.disabled = true;
            resetPassBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Updating Password...</span>`;

            try {
                const formData = new FormData();
                formData.append('action', 'reset_password');
                formData.append('email', email);
                formData.append('otp', otp);
                formData.append('new_password', newPassword);

                const res = await fetch('api/auth.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showAlert(step2Alert, data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'login.php';
                    }, 1200);
                } else {
                    showAlert(step2Alert, data.message || 'Failed to reset password.', 'error');
                }
            } catch (err) {
                showAlert(step2Alert, 'Server communication error. Please try again.', 'error');
            } finally {
                resetPassBtn.disabled = false;
                resetPassBtn.innerHTML = originalBtn;
            }
        });
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
