
import { Button } from '@/components/ui/button';
import { Package, Upload } from 'lucide-react';

interface EmptyStateProps {
  onUploadClick: () => void;
}

export function EmptyState({ onUploadClick }: EmptyStateProps) {
  return (
    <div className="text-center py-12">
      <Package className="w-16 h-16 text-muted-foreground mx-auto mb-4 opacity-50" />
      <h3 className="text-lg font-semibold mb-2">No Inventory Data</h3>
      <p className="text-muted-foreground mb-4">
        Upload a CSV file with headers: ITEMID, RENTALPRICE, CATEGORY, DESCRIPTION
      </p>
      <Button onClick={onUploadClick}>
        <Upload className="w-4 h-4 mr-2" />
        Upload CSV
      </Button>
    </div>
  );
}
