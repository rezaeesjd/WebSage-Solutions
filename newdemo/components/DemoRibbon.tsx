import { Info } from 'lucide-react';
import { Badge } from './ui/badge';

export function DemoRibbon() {
  return (
    <div className="bg-blue-600 text-white px-4 py-2 flex items-center justify-between">
      <div className="flex items-center gap-2">
        <Badge variant="secondary" className="bg-blue-800 text-white hover:bg-blue-800">
          DEMO MODE
        </Badge>
        <span className="text-sm">Guided purchase: Gold (1 oz) → USD → Mumbai → Card → Vault</span>
      </div>
      <div className="flex items-center gap-1 text-sm">
        <Info className="w-4 h-4" />
        <span>Full app features coming soon</span>
      </div>
    </div>
  );
}
