"use client";

import React, { useState } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";

interface EventFormData {
  teamA: string;
  teamB: string;
  league: string;
  startTime: string;
  videoSrc: string;
  status: string;
}

interface AdminEventFormProps {
  event?: any;
  onSubmit: (data: EventFormData) => Promise<void>;
  onCancel: () => void;
  isEditing?: boolean;
}

const AdminEventForm: React.FC<AdminEventFormProps> = ({
  event,
  onSubmit,
  onCancel,
  isEditing = false
}) => {
  const [formData, setFormData] = useState<EventFormData>({
    teamA: event?.teamA || "",
    teamB: event?.teamB || "",
    league: event?.league || "",
    startTime: event?.startTime ? new Date(event.startTime).toISOString().slice(0, 16) : "",
    videoSrc: event?.videoSrc || "",
    status: event?.status || "UPCOMING"
  });
  
  const [loading, setLoading] = useState(false);
  const [errors, setErrors] = useState<{[key: string]: string}>({});

  const validateForm = () => {
    const newErrors: {[key: string]: string} = {};
    
    if (!formData.teamA.trim()) newErrors.teamA = "Team A is required";
    if (!formData.teamB.trim()) newErrors.teamB = "Team B is required";
    if (!formData.league.trim()) newErrors.league = "League is required";
    if (!formData.startTime) newErrors.startTime = "Start time is required";
    if (!formData.videoSrc.trim()) newErrors.videoSrc = "Video source is required";
    
    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) return;
    
    setLoading(true);
    try {
      await onSubmit(formData);
    } catch (error) {
      console.error("Form submission error:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleInputChange = (field: keyof EventFormData, value: string) => {
    setFormData(prev => ({ ...prev, [field]: value }));
    if (errors[field]) {
      setErrors(prev => ({ ...prev, [field]: "" }));
    }
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle>
          {isEditing ? "Edit Match" : "Create New Match"}
        </CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="teamA">Team A</Label>
              <Input
                id="teamA"
                value={formData.teamA}
                onChange={(e) => handleInputChange("teamA", e.target.value)}
                placeholder="e.g., Manchester United"
                className={errors.teamA ? "border-red-500" : ""}
              />
              {errors.teamA && <p className="text-red-500 text-sm mt-1">{errors.teamA}</p>}
            </div>
            
            <div>
              <Label htmlFor="teamB">Team B</Label>
              <Input
                id="teamB"
                value={formData.teamB}
                onChange={(e) => handleInputChange("teamB", e.target.value)}
                placeholder="e.g., Liverpool"
                className={errors.teamB ? "border-red-500" : ""}
              />
              {errors.teamB && <p className="text-red-500 text-sm mt-1">{errors.teamB}</p>}
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="league">League</Label>
              <Input
                id="league"
                value={formData.league}
                onChange={(e) => handleInputChange("league", e.target.value)}
                placeholder="e.g., Premier League"
                className={errors.league ? "border-red-500" : ""}
              />
              {errors.league && <p className="text-red-500 text-sm mt-1">{errors.league}</p>}
            </div>
            
            <div>
              <Label htmlFor="status">Status</Label>
              <Select value={formData.status} onValueChange={(value) => handleInputChange("status", value)}>
                <SelectTrigger>
                  <SelectValue placeholder="Select status" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="UPCOMING">Upcoming</SelectItem>
                  <SelectItem value="LIVE">Live</SelectItem>
                  <SelectItem value="FINISHED">Finished</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div>
            <Label htmlFor="startTime">Start Time</Label>
            <Input
              id="startTime"
              type="datetime-local"
              value={formData.startTime}
              onChange={(e) => handleInputChange("startTime", e.target.value)}
              className={errors.startTime ? "border-red-500" : ""}
            />
            {errors.startTime && <p className="text-red-500 text-sm mt-1">{errors.startTime}</p>}
          </div>

          <div>
            <Label htmlFor="videoSrc">Video Stream URL</Label>
            <Input
              id="videoSrc"
              value={formData.videoSrc}
              onChange={(e) => handleInputChange("videoSrc", e.target.value)}
              placeholder="https://example.com/stream.mp4"
              className={errors.videoSrc ? "border-red-500" : ""}
            />
            {errors.videoSrc && <p className="text-red-500 text-sm mt-1">{errors.videoSrc}</p>}
            <p className="text-sm text-muted-foreground mt-1">
              Enter the direct URL to the video stream
            </p>
          </div>

          <div className="flex gap-2 pt-4">
            <Button type="submit" disabled={loading}>
              {loading ? "Saving..." : (isEditing ? "Update Match" : "Create Match")}
            </Button>
            <Button type="button" variant="outline" onClick={onCancel}>
              Cancel
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
};

export default AdminEventForm;
