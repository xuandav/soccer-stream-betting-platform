"use client";

import React, { useState, useEffect, useRef } from "react";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";

interface ChatMessage {
  id: string;
  username: string;
  message: string;
  timestamp: number;
}

interface LiveChatProps {
  eventId: string;
}

const LiveChat: React.FC<LiveChatProps> = ({ eventId }) => {
  const [messages, setMessages] = useState<ChatMessage[]>([]);
  const [newMessage, setNewMessage] = useState("");
  const [username, setUsername] = useState("");
  const [isUsernameSet, setIsUsernameSet] = useState(false);
  const [sending, setSending] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
  };

  const fetchMessages = async () => {
    try {
      const res = await fetch(`/api/chat?eventId=${eventId}`);
      if (!res.ok) throw new Error("Failed to fetch messages");
      const data = await res.json();
      setMessages(data);
      setError(null);
    } catch (err: any) {
      setError("Failed to load chat");
    }
  };

  const sendMessage = async () => {
    if (!newMessage.trim() || !username.trim() || sending) return;

    setSending(true);
    try {
      const res = await fetch("/api/chat", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          eventId,
          username: username.trim(),
          message: newMessage.trim()
        })
      });

      if (!res.ok) {
        const errorData = await res.json();
        throw new Error(errorData.error || "Failed to send message");
      }

      setNewMessage("");
      await fetchMessages();
      setError(null);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setSending(false);
    }
  };

  const handleUsernameSubmit = () => {
    if (username.trim().length >= 2) {
      setIsUsernameSet(true);
      setError(null);
    } else {
      setError("Username must be at least 2 characters");
    }
  };

  const formatTime = (timestamp: number) => {
    const date = new Date(timestamp);
    return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  useEffect(() => {
    fetchMessages();
    const interval = setInterval(fetchMessages, 3000); // Update every 3 seconds
    return () => clearInterval(interval);
  }, [eventId]);

  useEffect(() => {
    scrollToBottom();
  }, [messages]);

  if (!isUsernameSet) {
    return (
      <Card className="h-full">
        <CardHeader>
          <CardTitle className="text-lg">Join Live Chat</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div>
            <label className="block text-sm font-medium mb-2">
              Choose a username to start chatting:
            </label>
            <Input
              type="text"
              placeholder="Enter username..."
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              onKeyPress={(e) => e.key === "Enter" && handleUsernameSubmit()}
              maxLength={20}
              className="mb-2"
            />
            {error && <p className="text-red-500 text-sm">{error}</p>}
          </div>
          <Button onClick={handleUsernameSubmit} className="w-full">
            Join Chat
          </Button>
        </CardContent>
      </Card>
    );
  }

  return (
    <Card className="h-full flex flex-col">
      <CardHeader className="pb-3">
        <CardTitle className="text-lg flex items-center justify-between">
          Live Chat
          <span className="text-sm font-normal text-muted-foreground">
            {messages.length} messages
          </span>
        </CardTitle>
      </CardHeader>
      
      <CardContent className="flex-1 flex flex-col space-y-4">
        {/* Messages Area */}
        <div className="flex-1 overflow-y-auto space-y-2 max-h-80 min-h-40 border rounded p-3 bg-muted/20">
          {messages.length === 0 ? (
            <div className="text-center text-muted-foreground text-sm py-8">
              No messages yet. Be the first to chat!
            </div>
          ) : (
            messages.map((msg) => (
              <div key={msg.id} className="text-sm">
                <div className="flex items-center gap-2 mb-1">
                  <span className="font-medium text-primary">
                    {msg.username}
                  </span>
                  <span className="text-xs text-muted-foreground">
                    {formatTime(msg.timestamp)}
                  </span>
                </div>
                <p className="text-foreground break-words">{msg.message}</p>
              </div>
            ))
          )}
          <div ref={messagesEndRef} />
        </div>

        {/* Message Input */}
        <div className="space-y-2">
          {error && (
            <p className="text-red-500 text-sm">{error}</p>
          )}
          <div className="flex gap-2">
            <Input
              type="text"
              placeholder="Type your message..."
              value={newMessage}
              onChange={(e) => setNewMessage(e.target.value)}
              onKeyPress={(e) => e.key === "Enter" && sendMessage()}
              maxLength={200}
              disabled={sending}
              className="flex-1"
            />
            <Button 
              onClick={sendMessage} 
              disabled={!newMessage.trim() || sending}
              size="sm"
            >
              {sending ? "..." : "Send"}
            </Button>
          </div>
          <p className="text-xs text-muted-foreground">
            Chatting as <strong>{username}</strong> • Messages are temporary
          </p>
        </div>
      </CardContent>
    </Card>
  );
};

export default LiveChat;
