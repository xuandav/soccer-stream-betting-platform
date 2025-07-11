"use client";

import React, { useEffect, useState } from "react";
import { Button } from "@/components/ui/button";
import { Card, CardHeader, CardTitle, CardContent } from "@/components/ui/card";

interface LivePollProps {
  eventId: string;
  options: string[];
}

interface PollResult {
  [key: string]: number;
}

const LivePoll: React.FC<LivePollProps> = ({ eventId, options }) => {
  const [pollResult, setPollResult] = useState<PollResult>({});
  const [error, setError] = useState<string | null>(null);
  const [voting, setVoting] = useState(false);
  const [hasVoted, setHasVoted] = useState(false);

  const fetchPollResults = async () => {
    try {
      const res = await fetch(`/api/poll?eventId=${eventId}`);
      if (!res.ok) throw new Error("Failed to fetch poll results.");
      const data = await res.json();
      setPollResult(data);
      setError(null);
    } catch (err: any) {
      setError(err.message);
    }
  };

  const handleVote = async (option: string) => {
    if (hasVoted) return;
    
    setVoting(true);
    try {
      const res = await fetch(`/api/poll`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ eventId, option }),
      });
      if (!res.ok) throw new Error("Vote submission failed.");
      
      setHasVoted(true);
      await fetchPollResults();
      setError(null);
    } catch (err: any) {
      setError(err.message);
    } finally {
      setVoting(false);
    }
  };

  useEffect(() => {
    fetchPollResults();
    const interval = setInterval(fetchPollResults, 5000);
    return () => clearInterval(interval);
  }, [eventId]);

  const totalVotes = Object.values(pollResult).reduce((a, b) => a + b, 0);

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle className="text-center">Place Your Bet</CardTitle>
        {hasVoted && (
          <p className="text-center text-sm text-green-600 font-medium">
            Thanks for voting! Results update live.
          </p>
        )}
      </CardHeader>
      <CardContent>
        {error && (
          <div className="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
            {error}
          </div>
        )}
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {options.map((option) => {
            const votes = pollResult[option] || 0;
            const percentage = totalVotes ? ((votes / totalVotes) * 100).toFixed(1) : "0";
            
            return (
              <div key={option} className="space-y-2">
                <Button
                  onClick={() => handleVote(option)}
                  disabled={voting || hasVoted}
                  variant={hasVoted ? "outline" : "default"}
                  className="w-full h-12 text-base font-medium"
                >
                  {voting ? "Voting..." : option}
                </Button>
                
                <div className="space-y-1">
                  <div className="flex justify-between text-sm">
                    <span className="font-medium">{percentage}%</span>
                    <span className="text-muted-foreground">{votes} votes</span>
                  </div>
                  <div className="w-full bg-gray-200 rounded-full h-2">
                    <div
                      className="bg-primary h-2 rounded-full transition-all duration-500"
                      style={{ width: `${percentage}%` }}
                    ></div>
                  </div>
                </div>
              </div>
            );
          })}
        </div>
        
        {totalVotes > 0 && (
          <div className="mt-4 pt-4 border-t text-center text-sm text-muted-foreground">
            Total votes: {totalVotes} • Updates every 5 seconds
          </div>
        )}
      </CardContent>
    </Card>
  );
};

export default LivePoll;
