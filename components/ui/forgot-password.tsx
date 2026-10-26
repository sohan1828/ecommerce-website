import React, { useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

interface ForgotPasswordPageProps {
  title?: React.ReactNode;
  description?: React.ReactNode;
  heroImageSrc?: string;
  onResetPassword?: (event: React.FormEvent<HTMLFormElement>) => void;
  onBackToSignIn?: () => void;
}

const GlassInputWrapper = ({ children }: { children: React.ReactNode }) => (
  <div className="rounded-2xl border border-border bg-foreground/5 backdrop-blur-sm transition-colors focus-within:border-violet-400/70 focus-within:bg-violet-500/10">
    {children}
  </div>
);

export const ForgotPasswordPage: React.FC<ForgotPasswordPageProps> = ({
  title = <span className="font-light text-foreground tracking-tighter">Reset Password</span>,
  description = "Enter your registered email and choose a new password",
  heroImageSrc = "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?q=80&w=1200&auto=format&fit=crop",
  onResetPassword,
  onBackToSignIn,
}) => {
  const [showPassword, setShowPassword] = useState(false);
  const [showConfirmPassword, setShowConfirmPassword] = useState(false);

  return (
    <div className="h-[100dvh] flex flex-col md:flex-row font-geist w-[100dvw]">
      {/* Left column: reset form */}
      <section className="flex-1 flex items-center justify-center p-8 md:p-12 lg:p-16">
        <div className="w-full max-w-lg">
          <div className="flex flex-col gap-7">
            <div className="flex items-center gap-3">
              <img src="/images/gizmo-logo.svg" alt="Gizmo Logo" className="w-10 h-10 rounded-xl shadow-md object-contain" />
              <span className="text-xl font-bold tracking-tight text-foreground">Gizmo Store</span>
            </div>
            <div>
              <h1 className="animate-element animate-delay-100 text-4xl md:text-5xl font-semibold leading-tight">{title}</h1>
              <p className="animate-element animate-delay-200 text-muted-foreground mt-2 text-base">{description}</p>
            </div>

            <form className="space-y-6" onSubmit={onResetPassword}>
              <div className="animate-element animate-delay-300 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">Email Address</label>
                <GlassInputWrapper>
                  <input name="email" type="email" required placeholder="Enter your registered email" className="w-full bg-transparent text-sm py-4 px-5 rounded-2xl focus:outline-none" />
                </GlassInputWrapper>
              </div>

              <div className="animate-element animate-delay-400 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">New Password (Min 8 chars)</label>
                <GlassInputWrapper>
                  <div className="relative">
                    <input name="new_pass" type={showPassword ? 'text' : 'password'} required placeholder="Enter new password" className="w-full bg-transparent text-sm py-4 pl-5 pr-12 rounded-2xl focus:outline-none" />
                    <button type="button" onClick={() => setShowPassword(!showPassword)} className="absolute inset-y-0 right-4 flex items-center">
                      {showPassword ? <EyeOff className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" /> : <Eye className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" />}
                    </button>
                  </div>
                </GlassInputWrapper>
              </div>

              <div className="animate-element animate-delay-500 space-y-2">
                <label className="text-sm font-semibold text-foreground/80">Confirm New Password</label>
                <GlassInputWrapper>
                  <div className="relative">
                    <input name="cpass" type={showConfirmPassword ? 'text' : 'password'} required placeholder="Confirm new password" className="w-full bg-transparent text-sm py-4 pl-5 pr-12 rounded-2xl focus:outline-none" />
                    <button type="button" onClick={() => setShowConfirmPassword(!showConfirmPassword)} className="absolute inset-y-0 right-4 flex items-center">
                      {showConfirmPassword ? <EyeOff className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" /> : <Eye className="w-5 h-5 text-muted-foreground hover:text-foreground transition-colors" />}
                    </button>
                  </div>
                </GlassInputWrapper>
              </div>

              <button type="submit" className="animate-element animate-delay-600 w-full rounded-2xl bg-primary py-4 font-semibold text-primary-foreground hover:bg-primary/90 transition-colors shadow-md">
                Update Password
              </button>
            </form>

            <div className="animate-element animate-delay-700 relative flex items-center justify-center">
              <span className="w-full border-t border-border"></span>
              <span className="px-4 text-sm text-muted-foreground bg-background absolute">Back to</span>
            </div>

            <p className="animate-element animate-delay-800 text-center text-sm text-muted-foreground">
              Remember your credentials? <a href="#" onClick={(e) => { e.preventDefault(); onBackToSignIn?.(); }} className="text-violet-400 hover:underline transition-colors">Sign In</a>
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
                  Account Protection
                </span>
                <h2 className="text-3xl font-bold text-white">Your Security is Our Priority</h2>
                <p className="text-sm text-slate-200">We utilize modern encryption standards to keep your credentials and purchase history safe.</p>
              </div>
            </div>
          </div>
        </section>
      )}
    </div>
  );
};

