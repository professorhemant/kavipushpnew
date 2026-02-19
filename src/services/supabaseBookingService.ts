
import { supabase } from '@/integrations/supabase/client';

export interface Booking {
  id: string; // Changed from optional to required since database records always have an id
  customer_name: string;
  contact_number: string;
  address?: string;
  id_proof_type?: string;
  id_proof_number?: string;
  function_date: string;
  pickup_date: string;
  return_date: string;
  status: 'confirmed' | 'picked-up' | 'returned' | 'cancelled';
  total_amount: number;
  jewelry_items: any[];
  selected_item_details?: any[];
  created_at?: string;
  updated_at?: string;
}

export class SupabaseBookingService {
  static async fetchBookings(): Promise<Booking[]> {
    console.log('Fetching bookings from Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('bookings')
        .select('*')
        .order('created_at', { ascending: false });

      if (error) {
        console.error('Supabase booking query error:', error);
        throw new Error(`Failed to fetch bookings: ${error.message}`);
      }

      console.log(`Retrieved ${data?.length || 0} bookings from Supabase`);
      
      // Cast and ensure proper types
      return (data || []).map(booking => ({
        ...booking,
        id: booking.id, // Ensure id is always present
        status: booking.status as 'confirmed' | 'picked-up' | 'returned' | 'cancelled',
        jewelry_items: Array.isArray(booking.jewelry_items) ? booking.jewelry_items : [],
        selected_item_details: Array.isArray(booking.selected_item_details) ? booking.selected_item_details : []
      }));
    } catch (error) {
      console.error('Error fetching bookings from Supabase:', error);
      throw error;
    }
  }

  static async createBooking(booking: Omit<Booking, 'id' | 'created_at' | 'updated_at'>): Promise<Booking> {
    console.log('Creating booking in Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('bookings')
        .insert([booking])
        .select()
        .single();

      if (error) {
        console.error('Error creating booking:', error);
        throw new Error(`Failed to create booking: ${error.message}`);
      }

      console.log('Successfully created booking:', data.id);
      return {
        ...data,
        id: data.id, // Ensure id is present
        status: data.status as 'confirmed' | 'picked-up' | 'returned' | 'cancelled',
        jewelry_items: Array.isArray(data.jewelry_items) ? data.jewelry_items : [],
        selected_item_details: Array.isArray(data.selected_item_details) ? data.selected_item_details : []
      };
    } catch (error) {
      console.error('Error creating booking in Supabase:', error);
      throw error;
    }
  }

  static async updateBooking(id: string, booking: Partial<Booking>): Promise<Booking> {
    console.log('Updating booking in Supabase...');
    
    try {
      const { data, error } = await supabase
        .from('bookings')
        .update({ ...booking, updated_at: new Date().toISOString() })
        .eq('id', id)
        .select()
        .single();

      if (error) {
        console.error('Error updating booking:', error);
        throw new Error(`Failed to update booking: ${error.message}`);
      }

      console.log('Successfully updated booking:', id);
      return {
        ...data,
        id: data.id, // Ensure id is present
        status: data.status as 'confirmed' | 'picked-up' | 'returned' | 'cancelled',
        jewelry_items: Array.isArray(data.jewelry_items) ? data.jewelry_items : [],
        selected_item_details: Array.isArray(data.selected_item_details) ? data.selected_item_details : []
      };
    } catch (error) {
      console.error('Error updating booking in Supabase:', error);
      throw error;
    }
  }

  static async deleteBooking(id: string): Promise<void> {
    console.log('Deleting booking from Supabase...');
    
    try {
      const { error } = await supabase
        .from('bookings')
        .delete()
        .eq('id', id);

      if (error) {
        console.error('Error deleting booking:', error);
        throw new Error(`Failed to delete booking: ${error.message}`);
      }

      console.log('Successfully deleted booking:', id);
    } catch (error) {
      console.error('Error deleting booking from Supabase:', error);
      throw error;
    }
  }
}
