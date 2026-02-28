
import { supabase } from '@/integrations/supabase/client';

export interface Customer {
  id?: string;
  name: string;
  contact_number: string;
  email?: string;
  address: string;
  id_proof_type?: string;
  id_proof_number?: string;
  function_date?: string;
  booking_date?: string;
  pickup_date?: string;
  return_date?: string;
  created_at?: string;
  updated_at?: string;
}

export class SupabaseCustomerService {
  static async fetchCustomers(): Promise<Customer[]> {
    console.log('Fetching customers from Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('customers')
        .select('*')
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Supabase customer query error:', error);
        throw new Error(`Failed to fetch customers: ${error.message}`);
      }

      console.log(`Retrieved ${data?.length || 0} customers from Supabase`);
      return data || [];
    } catch (error) {
      console.error('Error fetching customers from Supabase:', error);
      throw error;
    }
  }

  static async createCustomer(customer: Omit<Customer, 'id' | 'created_at' | 'updated_at'>): Promise<Customer> {
    console.log('Creating customer in Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('customers')
        .insert([customer])
        .select()
        .single();

      if (error) {
        console.error('Error creating customer:', error);
        throw new Error(`Failed to create customer: ${error.message}`);
      }

      console.log('Successfully created customer:', data.id);
      return data;
    } catch (error) {
      console.error('Error creating customer in Supabase:', error);
      throw error;
    }
  }

  static async updateCustomer(id: string, customer: Partial<Customer>): Promise<Customer> {
    console.log('Updating customer in Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('customers')
        .update({ ...customer, updated_at: new Date().toISOString() })
        .eq('id', id)
        .select()
        .single();

      if (error) {
        console.error('Error updating customer:', error);
        throw new Error(`Failed to update customer: ${error.message}`);
      }

      console.log('Successfully updated customer:', id);
      return data;
    } catch (error) {
      console.error('Error updating customer in Supabase:', error);
      throw error;
    }
  }

  static async deleteCustomer(id: string): Promise<void> {
    console.log('Deleting customer from Supabase...');
    
    try {
      const { error } = await supabase
        .from('customers')
        .delete()
        .eq('id', id);

      if (error) {
        console.error('Error deleting customer:', error);
        throw new Error(`Failed to delete customer: ${error.message}`);
      }

      console.log('Successfully deleted customer:', id);
    } catch (error) {
      console.error('Error deleting customer from Supabase:', error);
      throw error;
    }
  }
}
