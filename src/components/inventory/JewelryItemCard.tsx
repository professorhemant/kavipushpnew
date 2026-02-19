
import { Card, CardContent } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { JewelryItem } from '@/types/jewelry';

interface JewelryItemCardProps {
  item: JewelryItem;
  isSelected: boolean;
  onSelect?: (item: JewelryItem) => void;
}

export function JewelryItemCard({ item, isSelected, onSelect }: JewelryItemCardProps) {
  return (
    <Card 
      className={`cursor-pointer transition-all hover:shadow-lg ${
        isSelected ? 'ring-2 ring-primary' : ''
      } ${!item.available ? 'opacity-50' : ''}`}
      onClick={() => item.available && onSelect?.(item)}
    >
      <CardContent className="p-4">
        <div className="flex justify-between items-start mb-2">
          <div>
            <h3 className="font-semibold text-lg">{item.name}</h3>
            <p className="text-sm font-mono text-primary">{item.id}</p>
          </div>
          <Badge variant={item.available ? "default" : "secondary"}>
            {item.available ? "Available" : "Rented"}
          </Badge>
        </div>
        
        <p className="text-sm text-muted-foreground mb-2">{item.category}</p>
        
        <p className="text-sm text-gray-600 mb-3">{item.description}</p>
        
        <div className="space-y-1">
          <div className="flex justify-between">
            <span className="text-sm font-medium">Rent:</span>
            <span className="text-sm font-bold text-primary">₹{item.rentPrice.toLocaleString()}</span>
          </div>
        </div>
        
        <div className="mt-3 text-xs text-muted-foreground">
          S.No: {item.sNo} | ID: {item.id}
        </div>
      </CardContent>
    </Card>
  );
}
