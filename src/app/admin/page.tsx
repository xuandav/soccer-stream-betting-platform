"use client";

import React, { useState, useEffect } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import AdminEventForm from "@/components/AdminEventForm";

interface Event {
  id: string;
  teamA: string;
  teamB: string;
  league: string;
  startTime: string;
  videoSrc: string;
  status: string;
}

export default function AdminPage() {
  const [events, setEvents] = useState<Event[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [showForm, setShowForm] = useState(false);
  const [editingEvent, setEditingEvent] = useState<Event | null>(null);

  const fetchEvents = async () => {
    try {
      const res = await fetch("/api/admin/events");
      if (!res.ok) throw new Error("Failed to fetch events");
      const data = await res.json();
      setEvents(data);
      setError(null);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setLoading(false);
    }
  };

  const handleCreateEvent = async (eventData: any) => {
    try {
      const res = await fetch("/api/admin/events", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(eventData)
      });

      if (!res.ok) throw new Error("Failed to create event");
      
      await fetchEvents();
      setShowForm(false);
      setError(null);
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleUpdateEvent = async (eventData: any) => {
    if (!editingEvent) return;

    try {
      const res = await fetch("/api/admin/events", {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ id: editingEvent.id, ...eventData })
      });

      if (!res.ok) throw new Error("Failed to update event");
      
      await fetchEvents();
      setEditingEvent(null);
      setError(null);
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleDeleteEvent = async (eventId: string) => {
    if (!confirm("Are you sure you want to delete this match?")) return;

    try {
      const res = await fetch(`/api/admin/events?id=${eventId}`, {
        method: "DELETE"
      });

      if (!res.ok) throw new Error("Failed to delete event");
      
      await fetchEvents();
      setError(null);
    } catch (err: any) {
      setError(err.message);
    }
  };

  const formatDateTime = (dateString: string) => {
    return new Date(dateString).toLocaleString();
  };

  const getStatusColor = (status: string) => {
    switch (status) {
      case "LIVE": return "default";
      case "UPCOMING": return "secondary";
      case "FINISHED": return "outline";
      default: return "secondary";
    }
  };

  useEffect(() => {
    fetchEvents();
  }, []);

  if (loading) {
    return (
      <div className="flex items-center justify-center min-h-[400px]">
        <div className="text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto mb-4"></div>
          <p className="text-muted-foreground">Loading admin panel...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Admin Panel</h1>
          <p className="text-muted-foreground">Manage soccer matches and events</p>
        </div>
        <Button onClick={() => setShowForm(true)} disabled={showForm || !!editingEvent}>
          Create New Match
        </Button>
      </div>

      {error && (
        <div className="bg-red-50 border border-red-200 rounded-lg p-4">
          <p className="text-red-700">{error}</p>
        </div>
      )}

      {/* Create/Edit Form */}
      {(showForm || editingEvent) && (
        <AdminEventForm
          event={editingEvent}
          onSubmit={editingEvent ? handleUpdateEvent : handleCreateEvent}
          onCancel={() => {
            setShowForm(false);
            setEditingEvent(null);
          }}
          isEditing={!!editingEvent}
        />
      )}

      {/* Events List */}
      <div className="space-y-4">
        <h2 className="text-xl font-semibold">All Matches ({events.length})</h2>
        
        {events.length === 0 ? (
          <Card>
            <CardContent className="text-center py-12">
              <p className="text-muted-foreground">No matches found. Create your first match!</p>
            </CardContent>
          </Card>
        ) : (
          <div className="grid gap-4">
            {events.map((event) => (
              <Card key={event.id}>
                <CardHeader>
                  <div className="flex items-center justify-between">
                    <CardTitle className="text-lg">
                      {event.teamA} vs {event.teamB}
                    </CardTitle>
                    <Badge variant={getStatusColor(event.status)}>
                      {event.status}
                    </Badge>
                  </div>
                </CardHeader>
                <CardContent>
                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">League</p>
                      <p>{event.league}</p>
                    </div>
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Start Time</p>
                      <p>{formatDateTime(event.startTime)}</p>
                    </div>
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Match ID</p>
                      <p className="font-mono text-sm">{event.id}</p>
                    </div>
                    <div>
                      <p className="text-sm font-medium text-muted-foreground">Video Source</p>
                      <p className="text-sm truncate">{event.videoSrc}</p>
                    </div>
                  </div>
                  
                  <div className="flex gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => setEditingEvent(event)}
                      disabled={showForm || !!editingEvent}
                    >
                      Edit
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => handleDeleteEvent(event.id)}
                      className="text-red-600 hover:text-red-700"
                    >
                      Delete
                    </Button>
                    <Button
                      variant="outline"
                      size="sm"
                      onClick={() => window.open(`/game/${event.id}`, '_blank')}
                    >
                      View Live
                    </Button>
                  </div>
                </CardContent>
              </Card>
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
