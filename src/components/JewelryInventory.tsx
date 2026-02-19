
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Package, RefreshCw, AlertTriangle, Database, HardDrive, Zap, Upload, Download } from 'lucide-react';
import { CsvInventoryService } from '@/services/csvInventoryService';
import { StorageService } from '@/services/storageService';
import { SupabaseInventoryService } from '@/services/supabaseInventoryService';
import { supabase } from '@/integrations/supabase/client';
import { JewelryItem } from '@/types/jewelry';
import { SearchInput } from './inventory/SearchInput';
import { JewelryItemCard } from './inventory/JewelryItemCard';
import { CsvUploadButton } from './inventory/CsvUploadButton';
import { EmptyState } from './inventory/EmptyState';
import { toast } from '@/hooks/use-toast';

interface JewelryInventoryProps {
  onSelectItem?: (item: JewelryItem) => void;
  selectedItems?: JewelryItem[];
  onInventoryLoad?: (inventory: JewelryItem[]) => void;
}

export function JewelryInventory({ onSelectItem, selectedItems = [], onInventoryLoad }: JewelryInventoryProps) {
  const [inventory, setInventory] = useState<JewelryItem[]>([]);
  const [searchTerm, setSearchTerm] = useState('');
  const [isLoading, setIsLoading] = useState(true);
  const [dataSource, setDataSource] = useState<'none' | 'supabase' | 'csv' | 'backup' | 'parsed'>('none');
  const [storageInfo, setStorageInfo] = useState(() => StorageService.getStorageInfo());

  const filteredInventory = inventory.filter(item =>
    item.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    item.category.toLowerCase().includes(searchTerm.toLowerCase()) ||
    item.id.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const isSelected = (item: JewelryItem) => 
    selectedItems.some(selected => selected.id === item.id);

  const handleInventoryLoad = async (csvInventory: JewelryItem[]) => {
    console.log('Loading inventory with', csvInventory.length, 'items');
    setInventory(csvInventory);
    setDataSource('csv');
    updateStorageInfo();
    onInventoryLoad?.(csvInventory);
    
    // Also sync to Supabase
    try {
      const syncSuccess = await SupabaseInventoryService.syncToSupabase(csvInventory);
      if (syncSuccess) {
        setDataSource('supabase');
        toast({
          title: "Data Synced",
          description: `Uploaded ${csvInventory.length} items to Supabase database`,
        });
      }
    } catch (error) {
      console.warn('Failed to sync to Supabase, keeping local data');
    }
    
    setIsLoading(false);
  };

  const updateStorageInfo = () => {
    const info = StorageService.getStorageInfo();
    setStorageInfo(info);
    console.log('Updated storage info:', info);
  };

  const loadInventoryFromSupabase = async () => {
    console.log('=== LOADING FROM SUPABASE ===');
    setIsLoading(true);
    
    try {
      const supabaseInventory = await SupabaseInventoryService.fetchInventory();
      if (supabaseInventory.length > 0) {
        console.log(`Loaded ${supabaseInventory.length} items from Supabase`);
        setInventory(supabaseInventory);
        setDataSource('supabase');
        onInventoryLoad?.(supabaseInventory);
        toast({
          title: "Data Loaded",
          description: `Loaded ${supabaseInventory.length} items from Supabase database`,
        });
        return true;
      }
    } catch (error) {
      console.error('Failed to load from Supabase:', error);
      toast({
        title: "Supabase Error",
        description: "Failed to load data from Supabase. Falling back to local storage.",
        variant: "destructive",
      });
    }
    
    return false;
  };

  const loadInventoryFromStorage = async () => {
    console.log('=== INVENTORY LOAD ATTEMPT ===');
    console.log('Timestamp:', new Date().toISOString());
    setIsLoading(true);
    
    try {
      // First try Supabase
      const supabaseLoaded = await loadInventoryFromSupabase();
      if (supabaseLoaded) {
        setIsLoading(false);
        return;
      }
      
      // Fallback to localStorage
      updateStorageInfo();
      
      let csvData = StorageService.getItem('csvInventoryData');
      let source: 'none' | 'supabase' | 'csv' | 'backup' | 'parsed' = 'none';
      
      if (csvData) {
        console.log('Found primary CSV data, length:', csvData.length);
        source = 'csv';
      } else {
        csvData = StorageService.getItem('csvInventoryData_backup');
        if (csvData) {
          console.log('Found backup CSV data, length:', csvData.length);
          source = 'backup';
        } else {
          const parsedData = StorageService.getItem('parsedInventoryData') || StorageService.getItem('parsedInventoryData_backup');
          if (parsedData) {
            console.log('Found parsed inventory data, length:', parsedData.length);
            try {
              const inventory = JSON.parse(parsedData);
              console.log('Successfully parsed inventory:', inventory.length, 'items');
              setInventory(inventory);
              setDataSource('parsed');
              onInventoryLoad?.(inventory);
              setIsLoading(false);
              return;
            } catch (parseError) {
              console.error('Failed to parse stored inventory:', parseError);
            }
          }
        }
      }
      
      if (csvData) {
        console.log('Processing CSV data...');
        const csvInventory = CsvInventoryService.parseCsvToInventory(csvData);
        console.log('Parsed inventory:', csvInventory.length, 'items');
        setInventory(csvInventory);
        setDataSource(source);
        onInventoryLoad?.(csvInventory);
        console.log(`Successfully loaded ${csvInventory.length} items from ${source} storage`);
      } else {
        console.log('No inventory data found in any storage location');
        setInventory([]);
        setDataSource('none');
      }
    } catch (error) {
      console.error('Failed to load from storage:', error);
      setInventory([]);
      setDataSource('none');
    } finally {
      setIsLoading(false);
      updateStorageInfo();
      console.log('=== INVENTORY LOAD COMPLETE ===');
    }
  };

  const handleRetestStorage = () => {
    console.log('Retesting storage availability...');
    const isAvailable = StorageService.retestAvailability();
    console.log('Storage retest result:', isAvailable);
    
    updateStorageInfo();
    
    if (isAvailable) {
      loadInventoryFromStorage();
    }
  };

  const handleForceLocalStorage = () => {
    console.log('Forcing localStorage activation...');
    const success = StorageService.forceLocalStorage();
    console.log('Force localStorage result:', success);
    
    updateStorageInfo();
    
    if (success) {
      loadInventoryFromStorage();
    }
  };

  const handleSyncToSupabase = async () => {
    if (inventory.length === 0) {
      toast({
        title: "No Data to Sync",
        description: "Please load inventory data first",
        variant: "destructive",
      });
      return;
    }

    try {
      const success = await SupabaseInventoryService.syncToSupabase(inventory);
      if (success) {
        setDataSource('supabase');
        toast({
          title: "Sync Successful",
          description: `Synced ${inventory.length} items to Supabase database`,
        });
      }
    } catch (error) {
      toast({
        title: "Sync Failed",
        description: error instanceof Error ? error.message : "Failed to sync to Supabase",
        variant: "destructive",
      });
    }
  };

  // Load inventory on component mount
  useEffect(() => {
    console.log('JewelryInventory component mounted, loading inventory...');
    loadInventoryFromStorage();
  }, []);

  // Set up real-time subscription for inventory changes
  useEffect(() => {
    console.log('Setting up real-time subscription for inventory changes...');
    console.log('Current data source:', dataSource);
    
    const channel = supabase
      .channel('inventory-changes')
      .on(
        'postgres_changes',
        {
          event: '*', // Listen to all events (INSERT, UPDATE, DELETE)
          schema: 'public',
          table: 'inventory'
        },
        async (payload) => {
          console.log('Real-time inventory change detected:', payload);
          
          // Refresh inventory data from Supabase
          try {
            const updatedInventory = await SupabaseInventoryService.fetchInventory();
            console.log(`Real-time update: Refreshed ${updatedInventory.length} items`);
            setInventory(updatedInventory);
            setDataSource('supabase');
            onInventoryLoad?.(updatedInventory);
            
            // Show toast notification about the change
            const eventType = payload.eventType;
            const itemId = (payload.new as any)?.ITEMID || (payload.old as any)?.ITEMID || 'Unknown';
            
            toast({
              title: "Inventory Updated",
              description: `Item ${itemId} was ${eventType.toLowerCase()}d. Inventory refreshed automatically.`,
            });
          } catch (error) {
            console.error('Failed to refresh inventory after real-time change:', error);
          }
        }
      )
      .subscribe((status) => {
        console.log('Real-time subscription status:', status);
        if (status === 'SUBSCRIBED') {
          console.log('Successfully subscribed to inventory changes');
        } else if (status === 'CHANNEL_ERROR') {
          console.error('Error subscribing to inventory changes');
        }
      });

    // Cleanup subscription on unmount
    return () => {
      console.log('Cleaning up real-time subscription...');
      supabase.removeChannel(channel);
    };
  }, [onInventoryLoad]); // Removed dataSource dependency to always listen

  const getDataSourceInfo = () => {
    switch (dataSource) {
      case 'supabase': return { text: 'Supabase DB', color: 'text-blue-600' };
      case 'csv': return { text: 'Primary CSV', color: 'text-green-600' };
      case 'backup': return { text: 'Backup CSV', color: 'text-yellow-600' };
      case 'parsed': return { text: 'Parsed Backup', color: 'text-orange-600' };
      case 'none': return { text: 'No Data', color: 'text-red-600' };
    }
  };

  const getStorageTypeInfo = () => {
    switch (storageInfo.type) {
      case 'localStorage': return { text: 'Browser Storage', color: 'text-green-600', icon: Database };
      case 'fallback': return { text: 'Memory Storage', color: 'text-yellow-600', icon: HardDrive };
      case 'none': return { text: 'No Storage', color: 'text-red-600', icon: AlertTriangle };
    }
  };

  const storageTypeInfo = getStorageTypeInfo();

  return (
    <Card className="luxury-shadow">
      <CardHeader>
        <CardTitle className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <Package className="w-5 h-5" />
            Jewelry Inventory ({inventory.length} items)
            {isLoading && <span className="text-sm text-muted-foreground">(Loading...)</span>}
            {!isLoading && inventory.length > 0 && (
              <span className={`text-xs ${getDataSourceInfo().color}`}>
                ({getDataSourceInfo().text})
                <span className="ml-1 text-green-500">● Live</span>
              </span>
            )}
            <div className={`flex items-center gap-1 text-xs ${storageTypeInfo.color}`}>
              <storageTypeInfo.icon className="w-3 h-3" />
              {storageTypeInfo.text}
              {storageInfo.fallbackSize > 0 && (
                <span className="text-xs text-muted-foreground">
                  ({storageInfo.fallbackSize} cached)
                </span>
              )}
            </div>
          </div>
          <div className="flex gap-2">
            <CsvUploadButton onInventoryLoad={handleInventoryLoad} />
            {dataSource !== 'supabase' && inventory.length > 0 && (
              <Button 
                variant="outline" 
                size="sm" 
                onClick={handleSyncToSupabase}
                disabled={isLoading}
              >
                <Upload className="w-4 h-4 mr-2" />
                Sync to Supabase
              </Button>
            )}
            <Button 
              variant="outline" 
              size="sm" 
              onClick={loadInventoryFromStorage}
              disabled={isLoading}
            >
              <RefreshCw className="w-4 h-4 mr-2" />
              {isLoading ? 'Loading...' : 'Reload'}
            </Button>
            {storageInfo.type !== 'localStorage' && dataSource !== 'supabase' && (
              <>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={handleRetestStorage}
                  disabled={isLoading}
                >
                  <Database className="w-4 h-4 mr-2" />
                  Retest Storage
                </Button>
                <Button 
                  variant="outline" 
                  size="sm" 
                  onClick={handleForceLocalStorage}
                  disabled={isLoading}
                >
                  <Zap className="w-4 h-4 mr-2" />
                  Force Storage
                </Button>
              </>
            )}
          </div>
        </CardTitle>
        <SearchInput 
          searchTerm={searchTerm} 
          onSearchChange={setSearchTerm} 
        />
      </CardHeader>
      <CardContent>
        {isLoading ? (
          <div className="text-center py-8">
            <Package className="w-16 h-16 text-muted-foreground mx-auto mb-4 opacity-50 animate-pulse" />
            <p className="text-muted-foreground">Loading inventory...</p>
          </div>
        ) : inventory.length === 0 ? (
          <div className="space-y-4">
            {storageInfo.type === 'none' && (
              <div className="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                <AlertTriangle className="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" />
                <div className="text-sm">
                  <p className="font-medium text-red-800">No Data Storage Available</p>
                  <p className="text-red-700 mt-1">
                    Unable to save or load data. This may be due to:
                    <br />• Browser storage being disabled or corrupted
                    <br />• Private/incognito browsing mode restrictions
                    <br />• Browser extensions blocking storage access
                    <br />• Storage quota exceeded
                  </p>
                  <div className="flex gap-2 mt-2">
                    <Button 
                      variant="outline" 
                      size="sm" 
                      onClick={handleRetestStorage}
                    >
                      Retry Storage Test
                    </Button>
                    <Button 
                      variant="outline" 
                      size="sm" 
                      onClick={handleForceLocalStorage}
                    >
                      Force Enable Storage
                    </Button>
                  </div>
                </div>
              </div>
            )}
            {storageInfo.type === 'fallback' && (
              <div className="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-start gap-3">
                <HardDrive className="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" />
                <div className="text-sm">
                  <p className="font-medium text-yellow-800">Using Temporary Memory Storage</p>
                  <p className="text-yellow-700 mt-1">
                    Browser storage is not available, using temporary memory instead.
                    <br />⚠️ <strong>Data will be lost when you refresh the page!</strong>
                    <br />Try uploading your CSV file again after refreshing.
                  </p>
                  <div className="flex gap-2 mt-2">
                    <Button 
                      variant="outline" 
                      size="sm" 
                      onClick={handleRetestStorage}
                    >
                      Retry Storage Test
                    </Button>
                    <Button 
                      variant="outline" 
                      size="sm" 
                      onClick={handleForceLocalStorage}
                    >
                      Force Enable Storage
                    </Button>
                  </div>
                </div>
              </div>
            )}
            <EmptyState 
              onUploadClick={() => document.getElementById('csv-upload')?.click()} 
            />
          </div>
        ) : (
          <>
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
              {filteredInventory.map((item) => (
                <JewelryItemCard
                  key={item.id}
                  item={item}
                  isSelected={isSelected(item)}
                  onSelect={onSelectItem}
                />
              ))}
            </div>
            
            {filteredInventory.length === 0 && (
              <div className="text-center py-8 text-muted-foreground">
                No jewelry items found matching your search.
              </div>
            )}
          </>
        )}
      </CardContent>
    </Card>
  );
}

// Export the JewelryItem type for backward compatibility
export type { JewelryItem };
