
import { supabase } from '@/integrations/supabase/client';

export interface Invoice {
  id?: string;
  invoice_number: string;
  customer_name: string;
  type: 'booking' | 'pickup' | 'return';
  amount: number;
  date: string;
  status: 'paid' | 'pending' | 'overdue';
  booking_data?: any;
  security_amount?: number;
  booking_amount?: number;
  created_at?: string;
  updated_at?: string;
}

export class SupabaseInvoiceService {
  static async fetchInvoices(): Promise<Invoice[]> {
    console.log('Fetching invoices from Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('invoices')
        .select('*')
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Supabase invoice query error:', error);
        throw new Error(`Failed to fetch invoices: ${error.message}`);
      }

      console.log(`Retrieved ${data?.length || 0} invoices from Supabase`);
      
      // Cast and ensure proper types
      return (data || []).map(invoice => ({
        ...invoice,
        type: invoice.type as 'booking' | 'pickup' | 'return',
        status: invoice.status as 'paid' | 'pending' | 'overdue'
      }));
    } catch (error) {
      console.error('Error fetching invoices from Supabase:', error);
      throw error;
    }
  }

  static async createInvoice(invoice: Omit<Invoice, 'id' | 'created_at' | 'updated_at'>): Promise<Invoice> {
    console.log('Creating invoice in Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('invoices')
        .insert([invoice])
        .select()
        .single();

      if (error) {
        console.error('Error creating invoice:', error);
        throw new Error(`Failed to create invoice: ${error.message}`);
      }

      console.log('Successfully created invoice:', data.id);
      return {
        ...data,
        type: data.type as 'booking' | 'pickup' | 'return',
        status: data.status as 'paid' | 'pending' | 'overdue'
      };
    } catch (error) {
      console.error('Error creating invoice in Supabase:', error);
      throw error;
    }
  }

  static async updateInvoice(id: string, invoice: Partial<Invoice>): Promise<Invoice> {
    console.log('Updating invoice in Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('invoices')
        .update({ ...invoice, updated_at: new Date().toISOString() })
        .eq('id', id)
        .select()
        .single();

      if (error) {
        console.error('Error updating invoice:', error);
        throw new Error(`Failed to update invoice: ${error.message}`);
      }

      console.log('Successfully updated invoice:', id);
      return {
        ...data,
        type: data.type as 'booking' | 'pickup' | 'return',
        status: data.status as 'paid' | 'pending' | 'overdue'
      };
    } catch (error) {
      console.error('Error updating invoice in Supabase:', error);
      throw error;
    }
  }

  static async deleteInvoice(id: string): Promise<void> {
    console.log('Deleting invoice from Supabase...');
    
    try {
      const { error } = await supabase
        .from('invoices')
        .delete()
        .eq('id', id);

      if (error) {
        console.error('Error deleting invoice:', error);
        throw new Error(`Failed to delete invoice: ${error.message}`);
      }

      console.log('Successfully deleted invoice:', id);
    } catch (error) {
      console.error('Error deleting invoice from Supabase:', error);
      throw error;
    }
  }
}
