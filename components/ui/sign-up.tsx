import React, { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

const GoogleIcon = () => (
  <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 48 48">
    <path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s12-5.373 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-2.641-.21-5.236-.611-7.743z" />
    <path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />
    <path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />
    <path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571l6.19 5.238C42.022 35.026 44 30.038 44 24c0-2.641-.21-5.236-.611-7.743z" />
  </svg>
);

interface SignUpPageProps {
  title?: React.ReactNode;
  description?: React.ReactNode;
  heroImageSrc?: string;
  onSignUp?: (event: React.FormEvent<HTMLFormElement>) => void;
  onGoogleSignUp?: () => void;
  onSignInRedirect?: () => void;
}

const GlassInputWrapper = ({ children }: { children: React.ReactNode }) => (
  <div className="rounded-2xl border border-border bg-foreground/5 backdrop-blur-sm transition-colors focus-within:border-violet-400/70 focus-within:bg-violet-500/10">
    {children}
  </div>
);

export const SignUpPage: React.FC<SignUpPageProps> = ({
  title = <span className="font-light text-foreground tracking-tighter">Create Account</span>,
  description = "Join Gizmo to explore premier smartphones and gadgets",
  heroImageSrc = "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?q=80&w=1200&auto=format&fit=crop",
  onSignUp,
  onGoogleSignUp,
  onSignInRedirect,
}) => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  return (
    <div className="h-[100dvh] flex flex-col md:flex-row font-geist w-[100dvw]">
      {/* Left column: sign-up form */}
      <section className="flex-1 flex items-center justify-center p-8 md:p-12 lg:p-16">
        <div className="w-full max-w-lg">
          <div className="flex flex-col gap-6">
            <div className="flex items-center gap-3">
              <img src="/images/gizmo-logo.svg" alt="Gizmo Logo" className="w-10 h-10 rounded-xl shadow-md object-contain" />
              <span className="text-xl font-bold tracking-tight text-foreground">Gizmo Store</span>
            </div>
            <div>
              <h1 className="animate-element animate-delay-100 text-4xl md:text-5xl font-semibold leading-tight">{title}</h1>
              <p className="animate-element animate-delay-200 text-muted-foreground mt-2 text-base">{description}</p>
            </div>

            <form className="space-y-5" onSubmit={onSignUp}>
              <div className="animate-element animate-delay-300 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">Full Name</label>
                <GlassInputWrapper>
                  <input name="name" type="text" required placeholder="Enter your full name" className="w-full bg-transparent text-sm py-4 px-5 rounded-2xl focus:outline-none" />
                </GlassInputWrapper>
              </div>

              <div className="animate-element animate-delay-400 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">Email Address</label>
                <GlassInputWrapper>
                  <input name="email" type="email" required placeholder="Enter your email address" className="w-full bg-transparent text-sm py-4 px-5 rounded-2xl focus:outline-none" />
                </GlassInputWrapper>
              </div>

              <div className="animate-element animate-delay-500 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">Password (Min 8 chars)</label>
                <GlassInputWrapper>
                  <div className="relative">
                    <input name="password" type={showPassword ? 'text' : 'password'} required placeholder="Create a password" className="w-full bg-transparent text-sm py-4 pl-5 pr-12 rounded-2xl focus:outline-none" />
                    <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute inset-y-0 right-4 flex items-center">
                      {showPassword ? <EyeOff className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" /> : <Eye className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" />}
                    </button>
                  </div>
                </GlassInputWrapper>
              </div>

              <div className="animate-element animate-delay-600 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">Confirm Password</label>
                <GlassInputWrapper>
                  <div className="relative">
                    <input name="cpass" type={showConfirmPassword ? 'text' : 'password'} required placeholder="Confirm your password" className="w-full bg-transparent text-sm py-4 pl-5 pr-12 rounded-2xl focus:outline-none" />
                    <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="absolute inset-y-0 right-4 flex items-center">
                      {showConfirmPassword ? <EyeOff className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" /> : <Eye className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" />}
                    </button>
                  </div>
                </GlassInputWrapper>
              </div>

              <div className="animate-element animate-delay-700 flex items-center justify-between text-sm py-1">
                <label className="flex items-center gap-3 cursor-pointer select-none">
                  <input type="checkbox" name="terms" required defaultChecked className="custom-checkbox" />
                  <span className="text-foreground/90 font-medium">I agree to Terms &amp; Privacy Policy</span>
                </label>
              </div>

              <button type="submit" className="animate-element animate-delay-800 w-full rounded-2xl bg-primary py-4 font-semibold text-primary-foreground hover:bg-primary/90 transition-colors shadow-md">
                Create Account
              </button>
            </form>

            <div className="animate-element animate-delay-900 relative flex items-center justify-center">
              <span className="w-full border-t border-border"></span>
              <span className="px-4 text-sm text-muted-foreground bg-background absolute">Or continue with</span>
            </div>

            <button onClick={onGoogleSignUp} className="animate-element animate-delay-1000 w-full flex items-center justify-center gap-3 border border-border rounded-2xl py-4 hover:bg-secondary transition-colors">
              <GoogleIcon />
              Sign up with Google
            </button>

            <p className="animate-element animate-delay-1000 text-center text-sm text-muted-foreground">
              Already have an account? <a href="#" onClick={(e) => { e.preventDefault(); onSignInRedirect?.(); }} className="text-violet-400 hover:underline transition-colors">Sign In</a>
            </p>
          </div>
        </div>
      </section>

      {/* Right column: clean phone showcase theme */}
      {heroImageSrc && (
        <section className="hidden md:block flex-1 relative p-4">
          <div className="animate-slide-right animate-delay-300 absolute inset-4 rounded-3xl bg-cover bg-center overflow-hidden" style={{ backgroundImage: `url(${heroImageSrc})` }}>
            <div className="absolute inset-0 bg-gradient-to-t from-slate-950/85 via-slate-950/30 to-transparent p-10 flex flex-col justify-end">
              <div className="max-w-md space-y-3">
                <span className="inline-flex items-center gap-2 rounded-full border border-white/20 bg-black/40 backdrop-blur-md px-4 py-1.5 text-xs text-white">
                  Exclusive Member Perks
                </span>
                <h2 className="text-3xl font-bold text-white">Join Nepal's Premier Tech Hub</h2>
                <p className="text-sm text-slate-200">Get early access to flagship smartphone launches and exclusive member discounts.</p>
              </div>
            </div>
          </div>
        </section>
      )}
    </div>
  );
};

