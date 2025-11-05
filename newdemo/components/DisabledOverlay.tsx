import { useRef, useState } from 'react';

import { useGuidedHighlight } from './GuidedHighlightProvider';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip';

interface DisabledOverlayProps {
  children: React.ReactNode;
  disabled?: boolean;
  tooltip?: string;
}

export function DisabledOverlay({
  children,
  disabled = false,
  tooltip = 'Available in the full app. In this demo please choose the highlighted option.',
}: DisabledOverlayProps) {
  const containerRef = useRef<HTMLDivElement>(null);
  const { showHighlight } = useGuidedHighlight();
  const [isActive, setIsActive] = useState(false);

  if (!disabled) {
    return <>{children}</>;
  }

  const handleClick = () => {
    setIsActive(true);
    showHighlight(containerRef.current);

    window.setTimeout(() => {
      setIsActive(false);
    }, 1200);
  };

  return (
    <TooltipProvider>
      <Tooltip>
        <TooltipTrigger asChild>
          <div ref={containerRef} className="relative">
            <div
              className={`relative rounded-xl transition-opacity duration-300 ${isActive ? 'opacity-100' : 'opacity-40'}`}
              aria-disabled="true"
            >
              {children}
            </div>
            <button
              type="button"
              aria-label="Demo only"
              onClick={handleClick}
              className="absolute inset-0 rounded-xl bg-slate-500/20 transition-opacity hover:bg-slate-500/30 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-blue-500"
            />
          </div>
        </TooltipTrigger>
        <TooltipContent>
          <p className="max-w-xs">{tooltip}</p>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  );
}
