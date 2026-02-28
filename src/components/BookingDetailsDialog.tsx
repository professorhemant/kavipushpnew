
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Eye } from 'lucide-react';
import { Booking } from '@/services/supabaseBookingService';

interface BookingDetailsDialogProps {
  booking: Booking;
}

export function BookingDetailsDialog({ booking }: BookingDetailsDialogProps) {
  const getStatusColor = (status: Booking['status']) => {
    switch (status) {
      case 'confirmed': return 'bg-blue-100 text-blue-800';
      case 'picked-up': return 'bg-green-100 text-green-800';
      case 'returned': return 'bg-gray-100 text-gray-800';
      case 'cancelled': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  return (
    <Dialog>
      <DialogTrigger asChild>
        <Button size="sm" variant="outline">
          <Eye className="w-4 h-4 mr-1" />
          View
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-3xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Booking Details - {booking.id}</DialogTitle>
        </DialogHeader>
        <div className="space-y-6">
          <div className="grid grid-cols-2 gap-6">
            <div className="space-y-4">
              <div>
                <h3 className="font-semibold mb-2">Customer Information</h3>
                <div className="space-y-1">
                  <p><strong>Name:</strong> {booking.customer_name}</p>
                  <p><strong>Contact:</strong> {booking.contact_number}</p>
                  {booking.address && (
                    <p><strong>Address:</strong> {booking.address}</p>
                  )}
                  {booking.id_proof_type && booking.id_proof_number && (
                    <p><strong>ID Proof:</strong> {booking.id_proof_type} - {booking.id_proof_number}</p>
                  )}
                </div>
                <div className="mt-2">
                  <Badge className={getStatusColor(booking.status)}>
                    {booking.status.replace('-', ' ').toUpperCase()}
                  </Badge>
                </div>
              </div>
            </div>
            <div>
              <h3 className="font-semibold mb-2">Event Schedule</h3>
              <div className="space-y-1">
                <p><strong>Function Date:</strong> {new Date(booking.function_date).toLocaleDateString()}</p>
                <p><strong>Pickup Date:</strong> {new Date(booking.pickup_date).toLocaleDateString()}</p>
                <p><strong>Return Date:</strong> {new Date(booking.return_date).toLocaleDateString()}</p>
              </div>
            </div>
          </div>

          <div>
            <h3 className="font-semibold mb-2">Jewelry Items</h3>
            <div className="bg-gray-50 p-4 rounded-lg">
              {booking.jewelry_items && booking.jewelry_items.length > 0 ? (
                <div className="space-y-2">
                  {booking.jewelry_items.map((item, index) => (
                    <div key={index} className="flex justify-between items-center p-2 bg-white rounded border">
                      <span>{typeof item === 'string' ? item : item.name || item.id}</span>
                    </div>
                  ))}
                </div>
              ) : (
                <p className="text-muted-foreground">No items selected</p>
              )}
            </div>
          </div>

          <div className="bg-primary/10 p-4 rounded-lg">
            <div className="flex justify-between items-center">
              <span className="font-semibold">Total Rent:</span>
              <span className="text-xl font-bold text-primary">₹{booking.total_amount.toLocaleString()}</span>
            </div>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
