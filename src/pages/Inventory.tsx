
import { JewelryInventory } from '@/components/JewelryInventory';

export default function Inventory() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold text-primary">Jewelry Inventory</h1>
        <p className="text-muted-foreground">Upload and manage your jewelry collection with CSV files</p>
      </div>

      <JewelryInventory />
    </div>
  );
}
