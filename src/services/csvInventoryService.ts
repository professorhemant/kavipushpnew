
import { CsvParser, CsvRow } from './csvParser';
import { JewelryItem } from '@/types/jewelry';

export class CsvInventoryService {
  static parseCsvToInventory(csvText: string): JewelryItem[] {
    const csvData = CsvParser.parse(csvText);
    
    return csvData
      .map((row, index) => {
        console.log(`Processing CSV row ${index + 1}:`, row);
        
        // Map CSV columns to our expected format
        // Support various column name variations including your exact CSV headers
        const sNo = this.getColumnValue(row, ['S.NO', 'SNo', 'Serial', 'S NO', 'SNO']);
        const itemId = this.getColumnValue(row, ['ITEMID', 'Item ID', 'ID', 'ItemId']);
        const rentalPrice = this.getColumnValue(row, ['RENTALPRICE', 'Rental Price', 'Rent Price', 'Price', 'Rental']);
        const category = this.getColumnValue(row, ['CATEGORY', 'Category', 'Type', 'CLASS']);
        const description = this.getColumnValue(row, ['DESCRIPTION', 'Description', 'Desc', 'Details']);

        if (!itemId) {
          console.log(`Skipping row ${index + 1} - no ITEMID found`);
          return null;
        }

        const parsedSNo = sNo ? parseInt(sNo) : (index + 1);
        const parsedPrice = rentalPrice ? parseInt(rentalPrice.toString().replace(/[^\d]/g, '')) || 0 : 0;

        const item: JewelryItem = {
          sNo: parsedSNo,
          id: itemId.trim(),
          name: this.generateItemName(itemId, category),
          category: category || 'DCK',
          rentPrice: parsedPrice,
          available: true,
          description: description || ''
        };

        console.log(`Created item from CSV:`, item);
        return item;
      })
      .filter((item): item is JewelryItem => item !== null);
  }

  private static getColumnValue(row: CsvRow, possibleKeys: string[]): string {
    for (const key of possibleKeys) {
      if (row[key] !== undefined && row[key] !== null) {
        return row[key].toString();
      }
    }
    return '';
  }

  private static generateItemName(itemId: string, category: string): string {
    if (!itemId) return 'Unknown Item';
    
    if (itemId.toUpperCase().includes('SLIP')) {
      return `${category || 'Jewelry'} Set ${itemId}`;
    }
    return `${category || 'Jewelry'} Item ${itemId}`;
  }
}
