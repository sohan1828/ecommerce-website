"use client";

import React, { useState } from "react";
import { Send, CheckCircle2 } from "lucide-react";
import {
  Card,
  CardHeader,
  CardTitle,
  CardDescription,
  CardContent,
  CardFooter,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from "@/components/ui/select";

export default function ContactForm() {
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    setSubmitted(true);
    setTimeout(() => setSubmitted(false), 5000);
  };

  return (
    <Card className="shadow-xl border-border bg-card/95 backdrop-blur-md">
      <CardHeader>
        <CardTitle className="text-2xl font-bold">Get in Touch</CardTitle>
        <CardDescription>
          Fill out this form and our support team will respond within 24 hours.
        </CardDescription>
      </CardHeader>

      <form onSubmit={handleSubmit}>
        <CardContent className="space-y-4">
          {submitted && (
            <div className="flex items-center gap-2 p-3 text-sm text-emerald-800 bg-emerald-100 rounded-lg dark:bg-emerald-950/50 dark:text-emerald-300">
              <CheckCircle2 className="w-4 h-4 shrink-0" />
              <span>Message sent successfully! Our customer support team will be in touch.</span>
            </div>
          )}

          <div className="space-y-2">
            <Label htmlFor="contact-name">Full Name</Label>
            <Input id="contact-name" name="name" required placeholder="e.g. John Doe" />
          </div>

          <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label htmlFor="contact-email">Email Address</Label>
              <Input id="contact-email" name="email" type="email" required placeholder="john@example.com" />
            </div>

            <div className="space-y-2">
              <Label htmlFor="contact-phone">Phone Number</Label>
              <Input id="contact-phone" name="number" type="tel" required placeholder="9840000300" />
            </div>
          </div>

          <div className="space-y-2">
            <Label htmlFor="contact-topic">Inquiry Topic</Label>
            <Select defaultValue="order">
              <SelectTrigger id="contact-topic">
                <SelectValue placeholder="Select topic" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="order">Order Status & Tracking</SelectItem>
                <SelectItem value="product">Product Specs & Availability</SelectItem>
                <SelectItem value="warranty">Warranty & Repairs</SelectItem>
                <SelectItem value="returns">Returns & Refunds</SelectItem>
                <SelectItem value="general">General Feedback</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div className="space-y-2">
            <Label htmlFor="contact-msg">Your Message</Label>
            <Textarea
              id="contact-msg"
              name="msg"
              required
              rows={4}
              placeholder="Tell us what you need assistance with..."
            />
          </div>

          <div className="flex items-center space-x-2 pt-1">
            <Checkbox id="copy" name="send_copy" />
            <Label htmlFor="copy" className="text-xs text-muted-foreground font-normal cursor-pointer">
              Send me a copy of this confirmation email
            </Label>
          </div>
        </CardContent>

        <CardFooter className="pt-2">
          <Button type="submit" className="w-full gap-2">
            <Send className="w-4 h-4" /> Send Message
          </Button>
        </CardFooter>
      </form>
    </Card>
  );
}
