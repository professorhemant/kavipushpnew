import { useState, useEffect } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarIcon } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import { toast } from '@/hooks/use-toast';
import { SupabaseBookingService } from '@/services/supabaseBookingService';

interface Booking {
  id: string;
  customer_name?: string;
  customerName?: string;
  contact_number?: string;
  contactNumber?: string;
  address?: string;
  function_date?: string;
  functionDate?: string;
  pickup_date?: string;
  pickupDate?: string;
  return_date?: string;
  returnDate?: string;
  total_amount?: number;
  totalAmount?: number;
  status: 'confirmed' | 'picked-up' | 'returned' | 'cancelled';
  jewelry_items?: any[];
  jewelryItems?: any[];
  selected_item_details?: any[];
  selectedItemDetails?: any[];
}

interface InvoiceGeneratorProps {
  bookings: Booking[];
  onInvoiceGenerated: (invoiceData: any) => void;
  trigger: React.ReactNode;
}

export function InvoiceGenerator({ bookings: initialBookings, onInvoiceGenerated, trigger }: InvoiceGeneratorProps) {
  const [open, setOpen] = useState(false);
  const [bookings, setBookings] = useState<Booking[]>(initialBookings);
  const [selectedBooking, setSelectedBooking] = useState<string>('');
  const [invoiceType, setInvoiceType] = useState<'booking' | 'pickup' | 'return'>('booking');
  const [invoiceDate, setInvoiceDate] = useState<Date>(new Date());
  const [customInvoiceNumber, setCustomInvoiceNumber] = useState('');
  const [loading, setLoading] = useState(false);

  // Fetch fresh bookings data from Supabase when dialog opens
  useEffect(() => {
    if (open) {
      loadLatestBookings();
    }
  }, [open]);

  // Update bookings when props change
  useEffect(() => {
    setBookings(initialBookings);
  }, [initialBookings]);

  const loadLatestBookings = async () => {
    setLoading(true);
    try {
      console.log('Fetching latest bookings from Supabase for invoice generation...');
      const freshBookings = await SupabaseBookingService.fetchBookings();
      setBookings(freshBookings);
      console.log(`Loaded ${freshBookings.length} fresh bookings from Supabase`);
    } catch (error) {
      console.error('Failed to load latest bookings from Supabase:', error);
      // Fallback to localStorage if Supabase fails
      try {
        const savedBookings = localStorage.getItem('bookingsData');
        if (savedBookings) {
          const parsedBookings = JSON.parse(savedBookings);
          setBookings(parsedBookings);
          console.log(`Fallback: Loaded ${parsedBookings.length} bookings from localStorage`);
        }
      } catch (localError) {
        console.error('Failed to load bookings from localStorage:', localError);
      }
    } finally {
      setLoading(false);
    }
  };

  console.log('Available bookings for invoice generation:', bookings);
  console.log('Current invoice type:', invoiceType);

  // Helper function to get property value (handles both naming conventions)
  const getBookingProperty = (booking: Booking, property: string) => {
    switch (property) {
      case 'customer_name':
        return booking.customer_name || booking.customerName;
      case 'contact_number':
        return booking.contact_number || booking.contactNumber;
      case 'function_date':
        return booking.function_date || booking.functionDate;
      case 'pickup_date':
        return booking.pickup_date || booking.pickupDate;
      case 'return_date':
        return booking.return_date || booking.returnDate;
      case 'total_amount':
        return booking.total_amount || booking.totalAmount;
      case 'jewelry_items':
        return booking.jewelry_items || booking.jewelryItems;
      case 'selected_item_details':
        return booking.selected_item_details || booking.selectedItemDetails;
      default:
        return booking[property as keyof Booking];
    }
  };

  // Filter bookings based on invoice type
  const availableBookings = bookings.filter(booking => {
    if (!booking || !booking.status) {
      console.log('Filtering out booking with missing status:', booking);
      return false;
    }
    
    switch (invoiceType) {
      case 'booking':
        return ['confirmed', 'picked-up', 'returned'].includes(booking.status);
      case 'pickup':
        return booking.status === 'confirmed';
      case 'return':
        return booking.status === 'picked-up';
      default:
        return true;
    }
  });

  console.log('Filtered available bookings:', availableBookings);

  const generateInvoiceNumber = (type: string, customerName: string) => {
    if (customInvoiceNumber.trim()) {
      return customInvoiceNumber.trim();
    }
    
    const prefix = type === 'return' ? 'FIN' : type.toUpperCase().substring(0, 3);
    const date = new Date();
    const dateStr = date.toISOString().split('T')[0].replace(/-/g, '');
    const timeStr = date.getTime().toString().slice(-4);
    const customerInitials = customerName.split(' ').map(n => n[0]).join('').toUpperCase();
    
    return `${prefix}-${dateStr}-${customerInitials}-${timeStr}`;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!selectedBooking) {
      toast({
        title: "Error",
        description: "Please select a booking to generate invoice for.",
        variant: "destructive",
      });
      return;
    }

    const booking = bookings.find(b => b.id === selectedBooking);
    if (!booking) {
      toast({
        title: "Error",
        description: "Selected booking not found.",
        variant: "destructive",
      });
      return;
    }

    const customerName = getBookingProperty(booking, 'customer_name') as string;
    const invoiceNumber = generateInvoiceNumber(invoiceType, customerName);
    
    const invoiceData = {
      invoiceNumber,
      customerName,
      contactNumber: getBookingProperty(booking, 'contact_number'),
      address: booking.address || '',
      totalAmount: getBookingProperty(booking, 'total_amount') || 0,
      invoiceType,
      date: invoiceDate.toISOString(),
      bookingId: booking.id,
      functionDate: getBookingProperty(booking, 'function_date'),
      pickupDate: getBookingProperty(booking, 'pickup_date'),
      returnDate: getBookingProperty(booking, 'return_date'),
      jewelryItems: getBookingProperty(booking, 'jewelry_items') || [],
      selectedItemDetails: getBookingProperty(booking, 'selected_item_details') || [],
      bookingData: booking
    };

    onInvoiceGenerated(invoiceData);
    setOpen(false);
    setSelectedBooking('');
    setCustomInvoiceNumber('');
    
    toast({
      title: "Invoice Generated",
      description: `${invoiceType.charAt(0).toUpperCase() + invoiceType.slice(1)} invoice ${invoiceNumber} has been generated successfully.`,
    });
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        {trigger}
      </DialogTrigger>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Generate Invoice</DialogTitle>
        </DialogHeader>
        {loading && (
          <div className="text-center py-4">
            <p className="text-sm text-muted-foreground">Loading latest bookings...</p>
          </div>
        )}
        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <Label htmlFor="invoiceType">Invoice Type</Label>
            <Select value={invoiceType} onValueChange={(value: 'booking' | 'pickup' | 'return') => {
              setInvoiceType(value);
              setSelectedBooking(''); // Reset selection when type changes
            }}>
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="booking">Booking Invoice</SelectItem>
                <SelectItem value="pickup">Pickup Invoice</SelectItem>
                <SelectItem value="return">Final Invoice</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <Label htmlFor="booking">Select Customer/Booking</Label>
            <Select value={selectedBooking} onValueChange={setSelectedBooking} disabled={loading}>
              <SelectTrigger>
                <SelectValue placeholder={loading ? "Loading bookings..." : "Choose a customer booking..."} />
              </SelectTrigger>
              <SelectContent>
                {availableBookings.length === 0 ? (
                  <SelectItem value="no-bookings" disabled>
                    {loading ? "Loading..." : `No bookings available for ${invoiceType} invoice`}
                  </SelectItem>
                ) : (
                  availableBookings.map((booking) => {
                    const customerName = getBookingProperty(booking, 'customer_name') as string;
                    const contactNumber = getBookingProperty(booking, 'contact_number') as string;
                    const totalAmount = getBookingProperty(booking, 'total_amount') as number;
                    
                    return (
                      <SelectItem key={booking.id} value={booking.id}>
                        {customerName} - {contactNumber} - ₹{totalAmount?.toLocaleString() || '0'}
                        {booking.status === 'confirmed' && ' (Confirmed)'}
                        {booking.status === 'picked-up' && ' (Picked Up)'}
                        {booking.status === 'returned' && ' (Returned)'}
                      </SelectItem>
                    );
                  })
                )}
              </SelectContent>
            </Select>
            {!loading && bookings.length > 0 && availableBookings.length === 0 && (
              <p className="text-sm text-muted-foreground mt-1">
                No bookings with status suitable for {invoiceType} invoice. 
                Total bookings available: {bookings.length}
              </p>
            )}
            {!loading && bookings.length > 0 && (
              <p className="text-sm text-muted-foreground mt-1">
                Showing {availableBookings.length} of {bookings.length} total bookings (Data refreshed from Supabase)
              </p>
            )}
          </div>

          <div>
            <Label>Invoice Date</Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  className={cn(
                    "w-full justify-start text-left font-normal",
                    !invoiceDate && "text-muted-foreground"
                  )}
                >
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {invoiceDate ? format(invoiceDate, "PPP") : "Select date"}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0">
                <Calendar
                  mode="single"
                  selected={invoiceDate}
                  onSelect={(date) => date && setInvoiceDate(date)}
                />
              </PopoverContent>
            </Popover>
          </div>

          <div>
            <Label htmlFor="customInvoiceNumber">Custom Invoice Number (Optional)</Label>
            <Input
              id="customInvoiceNumber"
              value={customInvoiceNumber}
              onChange={(e) => setCustomInvoiceNumber(e.target.value)}
              placeholder="Leave empty for auto-generation"
            />
          </div>

          {selectedBooking && (
            <div className="bg-primary/10 p-4 rounded-lg">
              <h3 className="font-semibold mb-2">Selected Booking Details:</h3>
              {(() => {
                const booking = bookings.find(b => b.id === selectedBooking);
                return booking ? (
                  <div className="text-sm space-y-1">
                    <p><strong>Customer:</strong> {getBookingProperty(booking, 'customer_name')}</p>
                    <p><strong>Contact:</strong> {getBookingProperty(booking, 'contact_number')}</p>
                    <p><strong>Total Amount:</strong> ₹{(getBookingProperty(booking, 'total_amount') as number)?.toLocaleString() || '0'}</p>
                    <p><strong>Function Date:</strong> {new Date(getBookingProperty(booking, 'function_date') as string).toLocaleDateString()}</p>
                    <p><strong>Status:</strong> {booking.status.toUpperCase()}</p>
                  </div>
                ) : null;
              })()}
            </div>
          )}

          <div className="flex gap-2 justify-end">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" className="gold-gradient text-white" disabled={loading}>
              Generate Invoice
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
