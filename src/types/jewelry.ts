
export interface JewelryItem {
  sNo: number;
  id: string;
  name: string;
  category: string;
  rentPrice: number;
  securityDeposit?: number;
  available: boolean;
  description: string;
}
