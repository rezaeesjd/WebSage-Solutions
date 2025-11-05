import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from './ui/tooltip';

interface DisabledOverlayProps {
  children: React.ReactNode;
  disabled?: boolean;
  tooltip?: string;
}

export function DisabledOverlay({ children, disabled = false, tooltip = 'Available in the full app. In this demo please choose the highlighted option.' }: DisabledOverlayProps) {
  if (!disabled) {
    return <>{children}</>;
  }

  return (
    <TooltipProvider>
      <Tooltip>
        <TooltipTrigger asChild>
          <div className="relative">
            <div className="opacity-40 pointer-events-none" aria-disabled="true">
              {children}
            </div>
            <div className="absolute inset-0 bg-gray-500 opacity-10 pointer-events-auto cursor-not-allowed" />
          </div>
        </TooltipTrigger>
        <TooltipContent>
          <p className="max-w-xs">{tooltip}</p>
        </TooltipContent>
      </Tooltip>
    </TooltipProvider>
  );
}
