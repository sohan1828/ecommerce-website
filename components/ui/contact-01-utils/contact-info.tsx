import React from "react";
import { Mail, Phone, MapPin, Clock, Headphones, ShieldCheck } from "lucide-react";

export default function ContactInfo() {
  return (
    <div className="flex flex-col justify-between h-full space-y-8">
      <div className="space-y-4">
        <div className="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold tracking-wide text-violet-700 bg-violet-100 rounded-full dark:bg-violet-900/40 dark:text-violet-300">
          <Headphones className="w-3.5 h-3.5" /> Customer Care & Support
        </div>
        <h1 className="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
          We're here to help your gadget journey
        </h1>
        <p className="text-base text-muted-foreground leading-relaxed">
          Have questions about an order, flagship smartphone specifications, warranty coverage, or delivery in Nepal? Reach out to our dedicated Gizmo team.
        </p>
      </div>

      <div className="relative rounded-2xl overflow-hidden shadow-lg border border-border group">
        <img
          src="https://images.unsplash.com/photo-1534536281715-e28d76689b4d?q=80&w=1000&auto=format&fit=crop"
          alt="Gizmo Customer Service Team"
          className="object-cover w-full h-48 transition-transform duration-500 group-hover:scale-105"
        />
        <div className="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent p-5 flex items-end">
          <div className="flex items-center gap-2 text-white text-xs font-medium">
            <ShieldCheck className="w-4 h-4 text-emerald-400" />
            100% Genuine Products & Dedicated Tech Support
          </div>
        </div>
      </div>

      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div className="p-4 rounded-xl border border-border bg-card/60 backdrop-blur-sm space-y-1.5">
          <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
            <Phone className="w-4 h-4 text-violet-600 dark:text-violet-400" />
            Phone Support
          </div>
          <p className="text-xs text-muted-foreground">+977 9840000300</p>
          <p className="text-xs text-muted-foreground">+977 9800000300</p>
        </div>

        <div className="p-4 rounded-xl border border-border bg-card/60 backdrop-blur-sm space-y-1.5">
          <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
            <Mail className="w-4 h-4 text-violet-600 dark:text-violet-400" />
            Email Inquiries
          </div>
          <p className="text-xs text-muted-foreground">gizmostore@gmail.com</p>
          <p className="text-xs text-muted-foreground">support@gizmo.com</p>
        </div>

        <div className="p-4 rounded-xl border border-border bg-card/60 backdrop-blur-sm space-y-1.5">
          <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
            <MapPin className="w-4 h-4 text-violet-600 dark:text-violet-400" />
            Main Store
          </div>
          <p className="text-xs text-muted-foreground">Kathmandu 44600, Nepal</p>
          <p className="text-xs text-muted-foreground">New Road Tech Plaza</p>
        </div>

        <div className="p-4 rounded-xl border border-border bg-card/60 backdrop-blur-sm space-y-1.5">
          <div className="flex items-center gap-2 text-sm font-semibold text-foreground">
            <Clock className="w-4 h-4 text-violet-600 dark:text-violet-400" />
            Opening Hours
          </div>
          <p className="text-xs text-muted-foreground">Mon - Sat: 9:00 AM - 7:00 PM</p>
          <p className="text-xs text-muted-foreground">Sunday: 10:00 AM - 4:00 PM</p>
        </div>
      </div>
    </div>
  );
}
