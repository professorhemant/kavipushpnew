
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Calendar, Download, Trash2 } from 'lucide-react';
import { NewBookingDialog } from '@/components/NewBookingDialog';
import { EditBookingDialog } from '@/components/EditBookingDialog';
import { BookingDetailsDialog } from '@/components/BookingDetailsDialog';
import { JewelryItem } from '@/types/jewelry';
import { CsvInventoryService } from '@/services/csvInventoryService';
import { SupabaseBookingService, Booking } from '@/services/supabaseBookingService';
import { supabase } from '@/integrations/supabase/client';
import * as XLSX from 'xlsx';
import { toast } from '@/components/ui/use-toast';
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog';

export default function Bookings() {
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [inventory, setInventory] = useState<JewelryItem[]>([]);
  const [isLoading, setIsLoading] = useState(true);

  // Function to check if an item is booked during a specific date range
  const isItemBooked = (itemId: string, startDate: string, endDate: string) => {
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    return bookings.some(booking => {
      // Skip cancelled bookings
      if (booking.status === 'cancelled') return false;
      
      // Check if this booking contains the item
      const hasItem = booking.selected_item_details?.some(detail => 
        detail.id === itemId
      ) || booking.jewelry_items.some(item => 
        typeof item === 'string' ? item.includes(itemId) : item.id === itemId
      );
      
      if (!hasItem) return false;
      
      // Check date overlap
      const bookingStart = new Date(booking.pickup_date);
      const bookingEnd = new Date(booking.return_date);
      
      return (start <= bookingEnd && end >= bookingStart);
    });
  };

  // Function to update inventory availability based on bookings
  const updateInventoryAvailability = (baseInventory: JewelryItem[]) => {
    return baseInventory.map(item => ({
      ...item,
      available: !isItemCurrentlyBooked(item.id)
    }));
  };

  // Function to check if an item is currently booked
  const isItemCurrentlyBooked = (itemId: string) => {
    const today = new Date().toISOString().split('T')[0];
    
    return bookings.some(booking => {
      // Skip cancelled or returned bookings
      if (booking.status === 'cancelled' || booking.status === 'returned') return false;
      
      // Check if this booking contains the item
      const hasItem = booking.selected_item_details?.some(detail => 
        detail.id === itemId
      ) || booking.jewelry_items.some(item => 
        typeof item === 'string' ? item.includes(itemId) : item.id === itemId
      );
      
      if (!hasItem) return false;
      
      // Check if booking is currently active
      const bookingStart = new Date(booking.pickup_date);
      const bookingEnd = new Date(booking.return_date);
      const currentDate = new Date(today);
      
      return currentDate >= bookingStart && currentDate <= bookingEnd;
    });
  };

  // Load bookings from Supabase
  const loadBookings = async () => {
    try {
      setIsLoading(true);
      const supabaseBookings = await SupabaseBookingService.fetchBookings();
      setBookings(supabaseBookings);
      console.log(`Loaded ${supabaseBookings.length} bookings from Supabase`);
      
      toast({
        title: "Bookings Loaded",
        description: `Loaded ${supabaseBookings.length} bookings from database`,
      });
    } catch (error) {
      console.error('Failed to load bookings from Supabase:', error);
      toast({
        title: "Error Loading Bookings",
        description: "Failed to load bookings. Please try again.",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Load bookings on component mount
  useEffect(() => {
    loadBookings();
  }, []);

  // Set up real-time subscription for bookings
  useEffect(() => {
    console.log('Setting up real-time subscription for bookings changes...');
    
    const channel = supabase
      .channel('bookings-changes')
      .on(
        'postgres_changes',
        {
          event: '*',
          schema: 'public',
          table: 'bookings'
        },
        async (payload) => {
          console.log('Real-time booking change detected:', payload);
          
          // Refresh bookings data
          try {
            const updatedBookings = await SupabaseBookingService.fetchBookings();
            setBookings(updatedBookings);
            
            const eventType = payload.eventType;
            const bookingId = (payload.new as any)?.id || (payload.old as any)?.id || 'Unknown';
            
            toast({
              title: "Bookings Updated",
              description: `Booking ${bookingId} was ${eventType.toLowerCase()}d. List refreshed automatically.`,
            });
          } catch (error) {
            console.error('Failed to refresh bookings after real-time change:', error);
          }
        }
      )
      .subscribe();

    return () => {
      console.log('Cleaning up bookings real-time subscription...');
      supabase.removeChannel(channel);
    };
  }, []);

  const handleNewBooking = async (newBooking: any) => {
    try {
      // Create booking in Supabase
      await SupabaseBookingService.createBooking({
        customer_name: newBooking.customerName,
        contact_number: newBooking.contactNumber,
        address: newBooking.address,
        id_proof_type: newBooking.idProofType,
        id_proof_number: newBooking.idProofNumber,
        function_date: newBooking.functionDate,
        pickup_date: newBooking.pickupDate,
        return_date: newBooking.returnDate,
        status: newBooking.status || 'confirmed',
        total_amount: newBooking.totalAmount,
        jewelry_items: newBooking.jewelryItems || [],
        selected_item_details: newBooking.selectedItemDetails || []
      });
      
      // Refresh bookings list
      await loadBookings();
      
      toast({
        title: "Booking Created",
        description: "New booking has been successfully created",
      });
    } catch (error) {
      console.error('Failed to create booking:', error);
      toast({
        title: "Error Creating Booking",
        description: "Failed to create booking. Please try again.",
        variant: "destructive",
      });
    }
  };

  const handleBookingUpdated = async (updatedBooking: Booking) => {
    try {
      if (updatedBooking.id) {
        await SupabaseBookingService.updateBooking(updatedBooking.id, updatedBooking);
        await loadBookings();
        
        toast({
          title: "Booking Updated",
          description: "Booking has been successfully updated",
        });
      }
    } catch (error) {
      console.error('Failed to update booking:', error);
      toast({
        title: "Error Updating Booking",
        description: "Failed to update booking. Please try again.",
        variant: "destructive",
      });
    }
  };

  // Load inventory data
  useEffect(() => {
    const loadInventory = () => {
      try {
        const csvData = localStorage.getItem('csvInventoryData');
        if (csvData) {
          const parsedCsvData = CsvInventoryService.parseCsvToInventory(csvData);
          const updatedInventory = updateInventoryAvailability(parsedCsvData);
          setInventory(updatedInventory);
          console.log(`Loaded ${updatedInventory.length} items from CSV storage with availability updated`);
        }
      } catch (error) {
        console.error('Failed to load inventory from CSV:', error);
      }
    };
    
    loadInventory();
  }, [bookings]);

  const handleDeleteBooking = async (bookingId: string) => {
    try {
      await SupabaseBookingService.deleteBooking(bookingId);
      await loadBookings();
      
      toast({
        title: "Booking Deleted",
        description: "The booking has been successfully deleted.",
      });
    } catch (error) {
      console.error('Failed to delete booking:', error);
      toast({
        title: "Error Deleting Booking",
        description: "Failed to delete booking. Please try again.",
        variant: "destructive",
      });
    }
  };

  const exportToExcel = () => {
    try {
      // Prepare data for export
      const exportData = bookings.map(booking => ({
        'Booking ID': booking.id,
        'Customer Name': booking.customer_name,
        'Contact Number': booking.contact_number,
        'Address': booking.address || '',
        'ID Proof Type': booking.id_proof_type || '',
        'ID Proof Number': booking.id_proof_number || '',
        'Function Date': new Date(booking.function_date).toLocaleDateString(),
        'Pickup Date': new Date(booking.pickup_date).toLocaleDateString(),
        'Return Date': new Date(booking.return_date).toLocaleDateString(),
        'Status': booking.status.toUpperCase(),
        'Total Amount': booking.total_amount,
        'Jewelry Items': Array.isArray(booking.jewelry_items) ? booking.jewelry_items.join(', ') : '',
        'Created At': booking.created_at ? new Date(booking.created_at).toLocaleString() : ''
      }));

      // Create workbook and worksheet
      const wb = XLSX.utils.book_new();
      const ws = XLSX.utils.json_to_sheet(exportData);

      // Set column widths
      const colWidths = [
        { wch: 15 }, { wch: 20 }, { wch: 15 }, { wch: 30 }, { wch: 15 }, { wch: 15 },
        { wch: 15 }, { wch: 15 }, { wch: 15 }, { wch: 12 }, { wch: 15 }, { wch: 50 }, { wch: 20 }
      ];
      ws['!cols'] = colWidths;

      XLSX.utils.book_append_sheet(wb, ws, 'Bookings');

      const now = new Date();
      const dateStr = now.toISOString().split('T')[0];
      const filename = `bookings_export_${dateStr}.xlsx`;

      XLSX.writeFile(wb, filename);

      toast({
        title: "Export Successful",
        description: `Exported ${bookings.length} bookings to ${filename}`,
      });
    } catch (error) {
      console.error('Failed to export bookings:', error);
      toast({
        title: "Export Failed",
        description: "Failed to export booking data. Please try again.",
        variant: "destructive"
      });
    }
  };

  const formatDate = (dateString: string | undefined) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString() + ' ' + new Date(dateString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
  };

  if (isLoading) {
    return (
      <div className="space-y-6">
        <div className="text-center py-12">
          <Calendar className="w-16 h-16 text-muted-foreground mx-auto mb-4 opacity-50 animate-pulse" />
          <h3 className="text-lg font-semibold mb-2">Loading Bookings...</h3>
          <p className="text-muted-foreground">Fetching data from database</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-primary">Bookings Management</h1>
          <p className="text-muted-foreground">Manage jewelry rental bookings and schedules</p>
          <p className="text-sm text-green-600 mt-1">● Live data from Supabase database</p>
        </div>
        <div className="flex gap-2">
          <Button 
            onClick={exportToExcel} 
            variant="outline"
            className="flex items-center gap-2"
          >
            <Download className="w-4 h-4" />
            Export to Excel
          </Button>
          <NewBookingDialog 
            onBookingCreated={handleNewBooking} 
            inventory={inventory}
            isItemBooked={isItemBooked}
          />
        </div>
      </div>

      <Card className="luxury-shadow">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <Calendar className="w-5 h-5" />
            All Bookings ({bookings.length}) - Latest First
          </CardTitle>
        </CardHeader>
        <CardContent>
          {bookings.length === 0 ? (
            <div className="text-center py-12">
              <Calendar className="w-16 h-16 text-muted-foreground mx-auto mb-4 opacity-50" />
              <h3 className="text-lg font-semibold mb-2">No Bookings Yet</h3>
              <p className="text-muted-foreground">
                Click "New Booking" to create your first booking
              </p>
            </div>
          ) : (
            <div className="space-y-4">
              {bookings.map((booking) => (
                <Card key={booking.id} className="border">
                  <CardContent className="p-4">
                    <div className="flex justify-between items-start">
                      <div className="space-y-2">
                        <h3 className="font-semibold text-lg">{booking.customer_name}</h3>
                        <p className="text-sm text-muted-foreground">
                          Booking ID: {booking.id} | Contact: {booking.contact_number}
                        </p>
                        {booking.address && (
                          <p className="text-sm text-muted-foreground">
                            Address: {booking.address}
                          </p>
                        )}
                        <div className="grid grid-cols-3 gap-4 text-sm">
                          <div>
                            <span className="font-medium">Function Date:</span>
                            <p>{new Date(booking.function_date).toLocaleDateString()}</p>
                          </div>
                          <div>
                            <span className="font-medium">Pickup Date:</span>
                            <p>{new Date(booking.pickup_date).toLocaleDateString()}</p>
                          </div>
                          <div>
                            <span className="font-medium">Return Date:</span>
                            <p>{new Date(booking.return_date).toLocaleDateString()}</p>
                          </div>
                        </div>
                        <div>
                          <span className="font-medium text-sm">Items:</span>
                          <p className="text-sm text-muted-foreground">
                            {Array.isArray(booking.jewelry_items) ? booking.jewelry_items.join(', ') : 'No items'}
                          </p>
                        </div>
                        <div>
                          <span className="font-medium text-sm">Status:</span>
                          <span className={`ml-2 px-2 py-1 rounded text-xs font-medium ${
                            booking.status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                            booking.status === 'picked-up' ? 'bg-green-100 text-green-800' :
                            booking.status === 'returned' ? 'bg-gray-100 text-gray-800' :
                            'bg-red-100 text-red-800'
                          }`}>
                            {booking.status.toUpperCase()}
                          </span>
                        </div>
                      </div>
                      <div className="text-right flex flex-col gap-2">
                        <p className="text-lg font-bold text-primary">₹{booking.total_amount.toLocaleString()}</p>
                        <p className="text-sm text-muted-foreground">Total Rent</p>
                        <div className="text-xs text-muted-foreground">
                          Created: {formatDate(booking.created_at)}
                        </div>
                        <div className="flex gap-2">
                          <EditBookingDialog 
                            booking={booking} 
                            onBookingUpdated={handleBookingUpdated} 
                          />
                          <BookingDetailsDialog booking={booking} />
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button 
                                variant="outline" 
                                size="sm"
                                className="text-red-600 hover:text-red-700 hover:bg-red-50"
                              >
                                <Trash2 className="w-4 h-4" />
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>Delete Booking</AlertDialogTitle>
                                <AlertDialogDescription>
                                  Are you sure you want to delete this booking for {booking.customer_name}? 
                                  This action cannot be undone.
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction 
                                  onClick={() => booking.id && handleDeleteBooking(booking.id)}
                                  className="bg-red-600 hover:bg-red-700"
                                >
                                  Delete
                                </AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        </div>  
                      </div>
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
