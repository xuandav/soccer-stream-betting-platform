"use client";

import React, { useState } from "react";

interface VideoPlayerProps {
  videoSrc: string;
  poster?: string;
}

const VideoPlayer: React.FC<VideoPlayerProps> = ({ videoSrc, poster }) => {
  const [error, setError] = useState(false);
  const [loading, setLoading] = useState(true);

  const handleError = () => {
    setError(true);
    setLoading(false);
  };

  const handleLoadStart = () => {
    setLoading(true);
    setError(false);
  };

  const handleCanPlay = () => {
    setLoading(false);
  };

  return (
    <div className="relative w-full bg-black rounded-lg overflow-hidden">
      {loading && !error && (
        <div className="absolute inset-0 flex items-center justify-center bg-black/80 z-10">
          <div className="text-white text-center">
            <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-white mx-auto mb-2"></div>
            <p>Loading stream...</p>
          </div>
        </div>
      )}
      
      {error ? (
        <div className="p-8 text-center text-white bg-red-600/90">
          <h3 className="text-lg font-semibold mb-2">Stream Unavailable</h3>
          <p className="text-sm mb-4">The video stream failed to load. This might be due to network issues or the stream being temporarily unavailable.</p>
          <button 
            onClick={() => window.location.reload()} 
            className="px-4 py-2 bg-white text-red-600 rounded hover:bg-gray-100 transition-colors"
          >
            Retry
          </button>
        </div>
      ) : (
        <video
          src={videoSrc}
          poster={poster}
          controls
          autoPlay
          muted
          onError={handleError}
          onLoadStart={handleLoadStart}
          onCanPlay={handleCanPlay}
          className="w-full h-auto max-h-[60vh] object-cover"
          style={{ aspectRatio: "16/9" }}
        >
          Your browser does not support the video tag.
        </video>
      )}
    </div>
  );
};

export default VideoPlayer;
