<?php
/**
 * User Login Page
 * Clean, Airy, Lightweight & Modern Travel Theme UI with Live Authentication
 */
require_once __DIR__ . '/config/settings.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$siteName = getSetting('site_name', 'Orion Advent');
$redirect = isset($_GET['redirect']) ? trim($_GET['redirect']) : '';

// If already logged in, send them straight back
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    header("Location: " . (!empty($redirect) ? $redirect : 'index.php'));
    exit();
}

$pageTitle = 'Log In to Your Account - ' . htmlspecialchars($siteName);
require_once 'components/header.php';
require_once 'components/navbar.php';
?>

<main class="flex-grow flex items-center justify-center py-12 sm:py-16 lg:py-20 px-4 sm:px-6 lg:px-8 relative bg-slate-50/70">

    <!-- Subtle Ambient Background Accents -->
    <div class="absolute inset-0 pointer-events-none select-none overflow-hidden">
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-brand-100/40 blur-3xl"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-teal-100/30 blur-3xl"></div>
    </div>

    <div class="w-full max-w-4xl mx-auto relative z-10">
        <!-- Main Auth Container: 2 Column Split (Travel Card + Clean Form) -->
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[580px]">
            
            <!-- Left Column: Inspiring Real Traveler Visual & Perks (5 cols) -->
            <div class="lg:col-span-5 relative bg-slate-900 text-white flex flex-col justify-between overflow-hidden min-h-[420px] lg:min-h-full">
                <!-- High-Quality Realistic Traveler Image Background with Natural Atmosphere -->
                <img src="https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=85" 
                     alt="Happy Solo Traveler Exploring Nature" 
                     class="absolute inset-0 w-full h-full object-cover object-center transform hover:scale-105 transition-transform duration-700 select-none">
                
                <!-- Modern Gradient Overlay for Readability -->
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-900/40 to-slate-900/30"></div>

                <!-- Top Floating Destination Badge -->
                <div class="relative z-10 p-6 sm:p-8 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-[11px] font-bold bg-white/20 text-white border border-white/30 backdrop-blur-md shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-teal-400 animate-pulse"></span>
                        <i class="fa-solid fa-compass text-teal-300"></i>
                        <span>Explore 150+ Destinations</span>
                    </div>

                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight drop-shadow-sm">
                        Your Passport to <br>
                        <span class="bg-gradient-to-r from-teal-300 via-emerald-300 to-amber-300 bg-clip-text text-transparent">Adventure</span>
                    </h2>
                </div>

                <!-- Bottom Traveler Card & Perks -->
                <div class="relative z-10 p-6 sm:p-8 space-y-3.5">
                    
                    <!-- Verified Guest Rating Card -->
                    <div class="p-3.5 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 text-white space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=120&q=80" 
                                     alt="Sneha S." 
                                     class="w-7 h-7 rounded-full object-cover border border-white/40">
                                <div class="truncate">
                                    <p class="text-xs font-bold leading-none">Sneha Sharma</p>
                                    <span class="text-[10px] text-teal-200">Verified Traveler &bull; Maldives Tour</span>
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
                            "Booking our honeymoon through <?php echo htmlspecialchars($siteName); ?> was completely hassle-free. Got 20% off member rate!"
                        </p>
                    </div>

                    <!-- Bottom Trust Indicator -->
                    <div class="flex items-center justify-between text-[11px] text-slate-200 pt-1">
                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-teal-300"></i>
                            <span>256-bit Bank Grade Secure</span>
                        </span>
                        <span class="font-bold text-amber-300">100% Rate Guarantee</span>
                    </div>

                </div>
            </div>

            <!-- Right Column: Lightweight & Modern Clean Form (7 cols) -->
            <div class="lg:col-span-7 p-6 sm:p-10 lg:p-12 flex flex-col justify-center bg-white">
                
                <div class="mb-6">
                    <h3 class="text-2xl font-black text-slate-900 tracking-tight">Sign In</h3>
                    <p class="text-xs text-slate-500 mt-1">Enter your details to access your travel bookings & perks</p>
                </div>

                <!-- Alert message container -->
                <?php if (isset($_GET['error']) && !empty($_GET['error'])): ?>
                    <div class="mb-4 p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                        <i class="fa-solid fa-circle-exclamation mr-1"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>
                <div id="loginAlert" class="hidden mb-4 p-3 rounded-xl text-xs font-semibold"></div>

                <!-- Quick Social Login (Flat, Modern) -->
                <div class="grid grid-cols-2 gap-3 mb-5">
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
                <div class="relative flex items-center justify-center mb-5">
                    <div class="border-t border-slate-200 w-full"></div>
                    <span class="bg-white px-3 text-[11px] font-semibold text-slate-400 uppercase tracking-wider shrink-0">or continue with email</span>
                </div>

                <!-- Login Form -->
                <form id="loginForm" class="space-y-4">
                    <input type="hidden" name="redirect" id="redirectInput" value="<?php echo htmlspecialchars($redirect); ?>">
                    
                    <!-- Email Input -->
                    <div>
                        <label for="loginEmail" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider mb-1.5">
                            Email Address or Mobile
                        </label>
                        <div class="relative flex items-center">
                            <div class="absolute left-3 w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center pointer-events-none z-10">
                                <i class="fa-regular fa-envelope text-xs"></i>
                            </div>
                            <input type="email" 
                                   id="loginEmail" 
                                   name="email" 
                                   required 
                                   placeholder="name@example.com"
                                   style="padding-left: 3.25rem !important;"
                                   class="w-full pr-4 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="loginPassword" class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                Password
                            </label>
                            <a href="forgot-password.php" class="text-[11px] font-bold text-brand-600 hover:text-brand-700 transition">Forgot Password?</a>
                        </div>
                        <div class="relative flex items-center">
                            <div class="absolute left-3 w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center pointer-events-none z-10">
                                <i class="fa-solid fa-lock text-xs"></i>
                            </div>
                            <input type="password" 
                                   id="loginPassword" 
                                   name="password" 
                                   required 
                                   placeholder="••••••••"
                                   style="padding-left: 3.25rem !important;"
                                   class="w-full pr-11 py-2.5 text-xs sm:text-sm bg-slate-50/80 border border-slate-200 text-slate-900 placeholder:text-slate-400 font-semibold focus:outline-none focus:border-brand-600 focus:bg-white transition rounded-xl">
                            <button type="button" id="togglePasswordBtn" class="absolute right-3.5 text-slate-400 hover:text-slate-600 text-xs focus:outline-none z-10" onclick="
                                const pass = document.getElementById('loginPassword');
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

                    <!-- Remember Me Checkbox -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer select-none">
                            <input type="checkbox" name="remember" checked class="w-4 h-4 text-brand-600 border-slate-300 rounded focus:ring-brand-500 accent-brand-600">
                            <span class="text-xs text-slate-600 font-medium">Keep me signed in</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" id="loginSubmitBtn" class="w-full py-3 px-6 rounded-full bg-brand-600 hover:bg-brand-700 text-white font-bold text-xs sm:text-sm uppercase tracking-wider flex items-center justify-center gap-2 transition-all active:scale-95 cursor-pointer">
                            <span>Sign In to Account</span>
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                        </button>
                    </div>

                </form>

                <!-- Footer Link to Sign Up -->
                <div class="mt-7 text-center pt-5 border-t border-slate-100">
                    <p class="text-xs text-slate-500 font-medium">
                        Don't have a <?php echo htmlspecialchars($siteName); ?> account yet? 
                        <a href="signup.php<?php echo !empty($redirect) ? '?redirect=' . urlencode($redirect) : ''; ?>" class="text-brand-600 hover:text-brand-700 font-bold ml-1 hover:underline">
                            Create Account Free
                        </a>
                    </p>
                </div>

            </div>

        </div>
    </div>

</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const loginAlert = document.getElementById('loginAlert');
    const loginBtn = document.getElementById('loginSubmitBtn');

    function showAlert(el, msg, type = 'error') {
        el.classList.remove('hidden', 'bg-rose-50', 'text-rose-700', 'border-rose-200', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
        if (type === 'success') {
            el.classList.add('bg-emerald-50', 'text-emerald-700', 'border', 'border-emerald-200');
        } else {
            el.classList.add('bg-rose-50', 'text-rose-700', 'border', 'border-rose-200');
        }
        el.innerHTML = msg;
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const originalBtn = loginBtn.innerHTML;
            loginBtn.disabled = true;
            loginBtn.innerHTML = `<i class="fa-solid fa-spinner fa-spin text-xs"></i> <span>Signing in...</span>`;

            try {
                const formData = new FormData(loginForm);
                formData.append('action', 'login');

                const res = await fetch('api/auth.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    showAlert(loginAlert, data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect || 'index.php';
                    }, 800);
                } else {
                    if (data.require_verification) {
                        showAlert(loginAlert, `${data.message} <a href="signup.php" class="font-bold underline ml-1">Verify with OTP &rarr;</a>`, 'error');
                    } else {
                        showAlert(loginAlert, data.message || 'Invalid credentials.', 'error');
                    }
                }
            } catch (err) {
                showAlert(loginAlert, 'Server communication error. Please try again.', 'error');
            } finally {
                loginBtn.disabled = false;
                loginBtn.innerHTML = originalBtn;
            }
        });
    }
});
</script>

<?php require_once 'components/footer.php'; ?>
