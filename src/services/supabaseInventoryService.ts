
import { supabase } from '@/integrations/supabase/client';
import { JewelryItem } from '@/types/jewelry';

export class SupabaseInventoryService {
  static async fetchInventory(): Promise<JewelryItem[]> {
    console.log('Fetching inventory from Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('inventory')
        .select('*')
        .order('ITEMID', { ascending: true });

      if (error) {
        console.error('Supabase query error:', error);
        throw new Error(`Failed to fetch inventory: ${error.message}`);
      }

      if (!data) {
        console.log('No inventory data found in Supabase');
        return [];
      }

      console.log(`Retrieved ${data.length} items from Supabase inventory table`);
      
      // Transform Supabase data to match our JewelryItem interface
      const inventory: JewelryItem[] = data
        .filter(row => row.ITEMID) // Only include rows with valid ITEMID
        .map((row, index) => ({
          sNo: index + 1,
          id: row.ITEMID.trim(),
          name: this.generateItemName(row.ITEMID, row.CATEGORY),
          category: row.CATEGORY || 'DCK',
          rentPrice: parseInt(row.RENTALPRICE?.toString() || '0') || 0,
          available: true,
          description: row.DESCRIPTION || ''
        }));

      console.log(`Transformed ${inventory.length} valid items`);
      return inventory;
    } catch (error) {
      console.error('Error fetching inventory from Supabase:', error);
      throw error;
    }
  }

  private static generateItemName(itemId: string, category: string): string {
    if (!itemId) return 'Unknown Item';
    
    if (itemId.toUpperCase().includes('SLIP')) {
      return `${category || 'Jewelry'} Set ${itemId}`;
    }
    return `${category || 'Jewelry'} Item ${itemId}`;
  }

  static async syncToSupabase(inventory: JewelryItem[]): Promise<boolean> {
    console.log('Syncing inventory to Supabase...');
    
    try {
      // Clear existing data
      const { error: deleteError } = await supabase
        .from('inventory')
        .delete()
        .neq('ITEMID', ''); // Delete all rows

      if (deleteError) {
        console.error('Error clearing existing inventory:', deleteError);
      }

      // Insert new data
      const supabaseData = inventory.map(item => ({
        ITEMID: item.id,
        CATEGORY: item.category,
        RENTALPRICE: item.rentPrice,
        DESCRIPTION: item.description
      }));

      const { error: insertError } = await supabase
        .from('inventory')
        .insert(supabaseData);

      if (insertError) {
        console.error('Error inserting inventory:', insertError);
        throw new Error(`Failed to sync inventory: ${insertError.message}`);
      }

      console.log(`Successfully synced ${inventory.length} items to Supabase`);
      return true;
    } catch (error) {
      console.error('Error syncing inventory to Supabase:', error);
      return false;
    }
  }
}
