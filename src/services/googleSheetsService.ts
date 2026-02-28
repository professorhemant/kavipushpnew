
// Google Sheets service for jewelry inventory
interface JewelryItemFromSheet {
  'S.NO': number;
  'ITEMID': string;
  'Rental Price': number;
  'Category': string;
  'DESCRIPTION': string;
}

interface JewelryItem {
  sNo: number;
  id: string;
  name: string;
  category: string;
  rentPrice: number;
  available: boolean;
  description: string;
}

class GoogleSheetsService {
  private apiKey: string = '';
  private sheetId: string = '19tHsJdcsydCBvYYRR8G4xZO_3Xyth3TE';
  private range: string = 'Sheet1!A1:E'; // Get all data from A1 to column E

  setApiKey(apiKey: string) {
    this.apiKey = apiKey.trim();
    console.log('API Key configured for Google Sheets service');
  }

  async fetchInventoryData(): Promise<JewelryItem[]> {
    if (!this.apiKey) {
      console.warn('Google Sheets API key not configured');
      return [];
    }

    try {
      const url = `https://sheets.googleapis.com/v4/spreadsheets/${this.sheetId}/values/${this.range}?key=${this.apiKey}`;
      console.log('Fetching from Google Sheets URL:', url);
      
      const response = await fetch(url);
      
      if (!response.ok) {
        const errorText = await response.text();
        let errorData;
        
        try {
          errorData = JSON.parse(errorText);
        } catch {
          errorData = { error: { message: errorText } };
        }

        console.error('Google Sheets API Error:', response.status, errorData);
        
        // Handle specific error cases
        if (response.status === 400 && errorData.error?.message?.includes('not supported for this document')) {
          throw new Error('The Google Sheet is not publicly accessible. Please:\n1. Open your Google Sheet\n2. Click "Share" button\n3. Change access to "Anyone with the link"\n4. Set permission to "Viewer"\n5. Try again');
        }
        
        if (response.status === 403) {
          throw new Error('API key doesn\'t have permission to access Google Sheets API. Please check your API key configuration.');
        }
        
        if (response.status === 404) {
          throw new Error('Google Sheet not found. Please check the Sheet ID is correct.');
        }
        
        throw new Error(`HTTP error! status: ${response.status} - ${JSON.stringify(errorData)}`);
      }

      const data = await response.json();
      console.log('Raw Google Sheets response:', data);
      
      if (!data.values || data.values.length === 0) {
        console.warn('No data found in the sheet');
        return [];
      }

      console.log('Found', data.values.length, 'rows in the sheet');
      console.log('Headers (Row 1):', data.values[0]);
      
      if (data.values.length > 1) {
        console.log('Sample data (Row 2):', data.values[1]);
      }

      const transformedData = this.transformSheetData(data.values);
      console.log(`Successfully transformed ${transformedData.length} jewelry items`);
      return transformedData;
    } catch (error) {
      console.error('Error fetching from Google Sheets:', error);
      throw error;
    }
  }

  private transformSheetData(values: any[][]): JewelryItem[] {
    if (!values || values.length <= 1) {
      console.warn('No data rows found (only headers or empty sheet)');
      return [];
    }

    // Row 1 contains headers: S.NO, ITEMID, Rental Price, Category, DESCRIPTION
    const headers = values[0];
    console.log('Processing headers:', headers);
    
    // Data starts from row 2 (index 1)
    const dataRows = values.slice(1);
    console.log(`Processing ${dataRows.length} data rows`);

    return dataRows
      .map((row, index) => {
        console.log(`Processing row ${index + 2}:`, row);
        
        // Skip empty rows
        if (!row || row.length === 0 || !row[1]) {
          console.log(`Skipping empty row ${index + 2}`);
          return null;
        }

        // Map data based on column positions:
        // A (0) = S.NO
        // B (1) = ITEMID
        // C (2) = Rental Price
        // D (3) = Category
        // E (4) = DESCRIPTION
        
        const sNo = row[0] ? parseInt(row[0].toString()) : (index + 1);
        const itemId = row[1] ? row[1].toString().trim() : '';
        const rentalPrice = row[2] ? parseInt(row[2].toString().replace(/[^\d]/g, '')) || 0 : 0;
        const category = row[3] ? row[3].toString().trim() : '';
        const description = row[4] ? row[4].toString().trim() : '';

        if (!itemId) {
          console.log(`Skipping row ${index + 2} - no ITEMID`);
          return null;
        }

        const item = {
          sNo: sNo,
          id: itemId,
          name: this.generateItemName(itemId, category),
          category: category,
          rentPrice: rentalPrice,
          available: true,
          description: description
        };

        console.log(`Created item:`, item);
        return item;
      })
      .filter((item): item is JewelryItem => item !== null);
  }

  private generateItemName(itemId: string, category: string): string {
    if (!itemId) return 'Unknown Item';
    
    if (itemId.toUpperCase().includes('SLIP')) {
      return `${category || 'Jewelry'} Set ${itemId}`;
    }
    return `${category || 'Jewelry'} Item ${itemId}`;
  }

  async findItemBySlipCode(slipCode: string): Promise<JewelryItem | null> {
    const inventory = await this.fetchInventoryData();
    return inventory.find(item => item.id.toLowerCase() === slipCode.toLowerCase()) || null;
  }
}

export const googleSheetsService = new GoogleSheetsService();
