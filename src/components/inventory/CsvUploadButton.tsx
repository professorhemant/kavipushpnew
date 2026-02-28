
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { FileSpreadsheet } from 'lucide-react';
import { toast } from '@/hooks/use-toast';
import { CsvInventoryService } from '@/services/csvInventoryService';
import { StorageService } from '@/services/storageService';
import { JewelryItem } from '@/types/jewelry';

interface CsvUploadButtonProps {
  onInventoryLoad: (inventory: JewelryItem[]) => void;
}

export function CsvUploadButton({ onInventoryLoad }: CsvUploadButtonProps) {
  const [csvUploading, setCsvUploading] = useState(false);

  const saveToMultipleStorageKeys = (csvText: string, inventory: JewelryItem[]) => {
    try {
      console.log('=== SAVING DATA WITH ENHANCED STORAGE ===');
      console.log('CSV text length:', csvText.length);
      console.log('Inventory items count:', inventory.length);
      
      // Clear any existing data first
      const keysToSave = ['csvInventoryData', 'csvInventoryData_backup', 'parsedInventoryData', 'parsedInventoryData_backup'];
      keysToSave.forEach(key => {
        StorageService.removeItem(key);
      });

      // Save raw CSV data with multiple keys for redundancy
      const csvSaved1 = StorageService.setItem('csvInventoryData', csvText);
      const csvSaved2 = StorageService.setItem('csvInventoryData_backup', csvText);
      
      // Also save parsed inventory as JSON backup
      const jsonData = JSON.stringify(inventory);
      const jsonSaved1 = StorageService.setItem('parsedInventoryData', jsonData);
      const jsonSaved2 = StorageService.setItem('parsedInventoryData_backup', jsonData);
      
      // Save metadata
      const timestamp = Date.now().toString();
      StorageService.setItem('inventoryDataTimestamp', timestamp);
      StorageService.setItem('inventoryDataVersion', '3.0');
      
      const storageInfo = StorageService.getStorageInfo();
      console.log('Storage info:', storageInfo);
      
      const allSaved = csvSaved1 && csvSaved2 && jsonSaved1 && jsonSaved2;
      
      if (!allSaved) {
        console.warn('Some data failed to save, but continuing with available storage');
      }
      
      return allSaved || (csvSaved1 || csvSaved2) || (jsonSaved1 || jsonSaved2);
    } catch (error) {
      console.error('Failed to save inventory data:', error);
      return false;
    }
  };

  const handleCsvUpload = async (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    if (!file.name.toLowerCase().endsWith('.csv')) {
      toast({
        title: "Invalid File Type",
        description: "Please upload a CSV file.",
        variant: "destructive",
      });
      return;
    }

    setCsvUploading(true);
    try {
      console.log('=== CSV UPLOAD STARTED ===');
      console.log('File name:', file.name);
      console.log('File size:', file.size, 'bytes');
      
      const csvText = await file.text();
      console.log('CSV file content preview:', csvText.substring(0, 200) + '...');
      console.log('CSV file size:', csvText.length, 'characters');
      
      const csvInventory = CsvInventoryService.parseCsvToInventory(csvText);
      console.log('Parsed CSV inventory:', csvInventory.length, 'items');
      
      // Save to enhanced storage system
      const saveSuccess = saveToMultipleStorageKeys(csvText, csvInventory);
      
      if (!saveSuccess) {
        console.warn('Data saving had issues, but proceeding with loaded inventory');
      }
      
      // Notify parent component about inventory load
      onInventoryLoad(csvInventory);
      
      const storageInfo = StorageService.getStorageInfo();
      
      toast({
        title: "CSV Uploaded Successfully",
        description: `Loaded ${csvInventory.length} items from ${file.name}. Using ${storageInfo.type} storage.`,
      });
      
      // Reset file input
      event.target.value = '';
      
      console.log('=== CSV UPLOAD COMPLETED SUCCESSFULLY ===');
    } catch (error) {
      console.error('Error during CSV upload:', error);
      toast({
        title: "CSV Upload Error",
        description: error instanceof Error ? error.message : "Failed to upload CSV file. Please try again.",
        variant: "destructive",
      });
    } finally {
      setCsvUploading(false);
    }
  };

  return (
    <div className="flex items-center gap-2">
      <Input
        type="file"
        accept=".csv"
        onChange={handleCsvUpload}
        disabled={csvUploading}
        className="hidden"
        id="csv-upload"
      />
      <Button 
        variant="outline" 
        size="sm" 
        onClick={() => document.getElementById('csv-upload')?.click()}
        disabled={csvUploading}
      >
        <FileSpreadsheet className="w-4 h-4 mr-2" />
        {csvUploading ? 'Uploading...' : 'Upload CSV'}
      </Button>
    </div>
  );
}
