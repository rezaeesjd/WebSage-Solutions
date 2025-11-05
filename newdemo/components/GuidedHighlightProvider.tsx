import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

type HighlightRect = {
  top: number;
  left: number;
  width: number;
  height: number;
};

interface HighlightState {
  rect: HighlightRect;
}

interface GuidedHighlightContextValue {
  showHighlight: (element: HTMLElement | null, options?: { duration?: number }) => void;
}

const GuidedHighlightContext = createContext<GuidedHighlightContextValue | undefined>(undefined);

function GuidedHighlightLayer({ highlight }: { highlight: HighlightState | null }) {
  if (!highlight) {
    return null;
  }

  const { rect } = highlight;

  return createPortal(
    <div className="pointer-events-none fixed inset-0 z-[60]">
      <div className="absolute inset-0 bg-slate-900/70" />
      <div
        aria-hidden="true"
        className="absolute rounded-xl ring-4 ring-blue-400 shadow-[0_0_0_9999px_rgba(15,23,42,0.75)] transition-all duration-300"
        style={{
          top: rect.top,
          left: rect.left,
          width: rect.width,
          height: rect.height,
        }}
      />
    </div>,
    document.body
  );
}

export function GuidedHighlightProvider({ children }: { children: React.ReactNode }) {
  const [highlight, setHighlight] = useState<HighlightState | null>(null);
  const timeoutRef = useRef<number | null>(null);

  const showHighlight = useCallback((element: HTMLElement | null, options?: { duration?: number }) => {
    if (!element) {
      return;
    }

    const rect = element.getBoundingClientRect();
    const duration = options?.duration ?? 1200;

    setHighlight({
      rect: {
        top: rect.top + window.scrollY,
        left: rect.left + window.scrollX,
        width: rect.width,
        height: rect.height,
      },
    });

    if (timeoutRef.current) {
      window.clearTimeout(timeoutRef.current);
    }

    timeoutRef.current = window.setTimeout(() => {
      setHighlight(null);
      timeoutRef.current = null;
    }, duration);
  }, []);

  useEffect(() => () => {
    if (timeoutRef.current) {
      window.clearTimeout(timeoutRef.current);
    }
  }, []);

  const value = useMemo<GuidedHighlightContextValue>(() => ({ showHighlight }), [showHighlight]);

  return (
    <GuidedHighlightContext.Provider value={value}>
      <GuidedHighlightLayer highlight={highlight} />
      {children}
    </GuidedHighlightContext.Provider>
  );
}

export function useGuidedHighlight() {
  const context = useContext(GuidedHighlightContext);
  if (!context) {
    throw new Error('useGuidedHighlight must be used within a GuidedHighlightProvider');
  }

  return context;
}
