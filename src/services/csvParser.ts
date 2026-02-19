
export interface CsvRow {
  [key: string]: string;
}

export class CsvParser {
  static parse(csvText: string): CsvRow[] {
    const lines = csvText.split('\n').filter(line => line.trim());
    
    if (lines.length === 0) {
      throw new Error('CSV file is empty');
    }

    // Parse headers from first line
    const headers = this.parseRow(lines[0]);
    console.log('CSV Headers:', headers);

    // Parse data rows
    const data: CsvRow[] = [];
    
    for (let i = 1; i < lines.length; i++) {
      const row = this.parseRow(lines[i]);
      
      if (row.length === 0 || row.every(cell => !cell.trim())) {
        continue; // Skip empty rows
      }

      const rowData: CsvRow = {};
      headers.forEach((header, index) => {
        rowData[header] = row[index] || '';
      });
      
      data.push(rowData);
    }

    console.log(`Parsed ${data.length} rows from CSV`);
    return data;
  }

  private static parseRow(line: string): string[] {
    const result: string[] = [];
    let current = '';
    let inQuotes = false;
    
    for (let i = 0; i < line.length; i++) {
      const char = line[i];
      
      if (char === '"') {
        inQuotes = !inQuotes;
      } else if (char === ',' && !inQuotes) {
        result.push(current.trim());
        current = '';
      } else {
        current += char;
      }
    }
    
    result.push(current.trim());
    return result;
  }
}
