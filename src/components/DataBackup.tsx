
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Download, Upload, Shield } from 'lucide-react';
import { StorageService } from '@/services/storageService';
import { toast } from '@/hooks/use-toast';

export const DataBackup = () => {
  const exportAllData = () => {
    try {
      const storageInfo = StorageService.getStorageInfo();
      const allData = {
        customers: StorageService.getItem('customersData'),
        bookings: StorageService.getItem('bookingsData'),
        inventory: StorageService.getItem('csvInventoryData'),
        businessInfo: StorageService.getItem('businessInfo'),
        amountSettings: StorageService.getItem('amountSettings'),
        timestamp: new Date().toISOString(),
        storageType: storageInfo.type
      };

      const dataStr = JSON.stringify(allData, null, 2);
      const dataBlob = new Blob([dataStr], { type: 'application/json' });
      
      const url = URL.createObjectURL(dataBlob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `jewelry-rental-backup-${new Date().toISOString().split('T')[0]}.json`;
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      toast({
        title: "Backup Created",
        description: "All your data has been backed up successfully.",
      });

      console.log('Emergency backup created with all data');
    } catch (error) {
      console.error('Backup failed:', error);
      toast({
        title: "Backup Failed",
        description: "There was an error creating the backup.",
        variant: "destructive",
      });
    }
  };

  const importData = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = (e) => {
      try {
        const backupData = JSON.parse(e.target?.result as string);
        
        // Restore each data type
        if (backupData.customers) {
          StorageService.setItem('customersData', backupData.customers);
        }
        if (backupData.bookings) {
          StorageService.setItem('bookingsData', backupData.bookings);
        }
        if (backupData.inventory) {
          StorageService.setItem('csvInventoryData', backupData.inventory);
        }
        if (backupData.businessInfo) {
          StorageService.setItem('businessInfo', backupData.businessInfo);
        }
        if (backupData.amountSettings) {
          StorageService.setItem('amountSettings', backupData.amountSettings);
        }

        toast({
          title: "Data Restored",
          description: "Your backup has been restored successfully. Please refresh the page.",
        });

        console.log('Data restored from backup file');
        
        // Refresh page after 2 seconds to load restored data
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } catch (error) {
        console.error('Import failed:', error);
        toast({
          title: "Import Failed",
          description: "There was an error importing the backup file.",
          variant: "destructive",
        });
      }
    };
    reader.readAsText(file);
  };

  return (
    <Card className="border-blue-200 bg-blue-50">
      <CardHeader>
        <CardTitle className="flex items-center gap-2 text-blue-800">
          <Shield className="w-5 h-5" />
          Data Protection
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <p className="text-sm text-blue-700">
          Protect your data before Supabase integration! Export your data as a backup file 
          so you can restore it if anything goes wrong.
        </p>
        
        <div className="flex flex-col sm:flex-row gap-3">
          <Button 
            onClick={exportAllData}
            className="bg-blue-600 hover:bg-blue-700 text-white"
          >
            <Download className="w-4 h-4 mr-2" />
            Backup All Data
          </Button>
          
          <div>
            <input
              type="file"
              accept=".json"
              onChange={importData}
              className="hidden"
              id="data-restore"
            />
            <Button
              onClick={() => document.getElementById('data-restore')?.click()}
              variant="outline"
              className="border-blue-600 text-blue-600 hover:bg-blue-50"
            >
              <Upload className="w-4 h-4 mr-2" />
              Restore Backup
            </Button>
          </div>
        </div>
        
        <div className="bg-blue-100 p-3 rounded-md">
          <p className="text-xs text-blue-600">
            <strong>Instructions:</strong><br />
            1. Click "Backup All Data" first<br />
            2. Then try the Supabase integration<br />
            3. If data is lost, use "Restore Backup" to get it back
          </p>
        </div>
      </CardContent>
    </Card>
  );
};
