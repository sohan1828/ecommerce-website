import React, { useState } from "react";
import { SignInPage } from "@/components/ui/sign-in";
import { SignUpPage } from "@/components/ui/sign-up";
import { ForgotPasswordPage } from "@/components/ui/forgot-password";

const AuthDemo = () => {
  const [currentView, setCurrentView] = useState<"signin" | "signup" | "forgot">("signin");

  const phoneHeroImage = "https://images.unsplash.com/photo-1592750475338-74b7b21085ab?q=80&w=1200&auto=format&fit=crop";

  return (
    <div className="min-h-screen bg-background text-foreground">
      {/* View Switcher Bar for Demo */}
      <div className="fixed top-4 right-4 z-50 flex items-center gap-2 rounded-full border border-border bg-background/80 p-1.5 shadow-lg backdrop-blur-md">
        <button
          onClick={() => setCurrentView("signin")}
          className={`rounded-full px-4 py-1.5 text-xs font-medium transition-colors ${currentView === "signin" ? "bg-primary text-primary-foreground" : "text-muted-foreground hover:text-foreground"}`}
        >
          Sign In
        </button>
        <button
          onClick={() => setCurrentView("signup")}
          className={`rounded-full px-4 py-1.5 text-xs font-medium transition-colors ${currentView === "signup" ? "bg-primary text-primary-foreground" : "text-muted-foreground hover:text-foreground"}`}
        >
          Sign Up
        </button>
        <button
          onClick={() => setCurrentView("forgot")}
          className={`rounded-full px-4 py-1.5 text-xs font-medium transition-colors ${currentView === "forgot" ? "bg-primary text-primary-foreground" : "text-muted-foreground hover:text-foreground"}`}
        >
          Reset Password
        </button>
      </div>

      {currentView === "signin" && (
        <SignInPage
          heroImageSrc={phoneHeroImage}
          testimonials={[]}
          onResetPassword={() => setCurrentView("forgot")}
          onCreateAccount={() => setCurrentView("signup")}
          onSignIn={(e) => {
            e.preventDefault();
            alert("Sign in submitted!");
          }}
        />
      )}

      {currentView === "signup" && (
        <SignUpPage
          heroImageSrc={phoneHeroImage}
          onSignInRedirect={() => setCurrentView("signin")}
          onSignUp={(e) => {
            e.preventDefault();
            alert("Sign up submitted!");
          }}
        />
      )}

      {currentView === "forgot" && (
        <ForgotPasswordPage
          heroImageSrc={phoneHeroImage}
          onBackToSignIn={() => setCurrentView("signin")}
          onResetPassword={(e) => {
            e.preventDefault();
            alert("Password reset submitted!");
          }}
        />
      )}
    </div>
  );
};

export default AuthDemo;
