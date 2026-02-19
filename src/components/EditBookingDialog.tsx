
import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarIcon, Edit } from 'lucide-react';
import { format, addDays } from 'date-fns';
import { cn } from '@/lib/utils';
import { toast } from '@/hooks/use-toast';
import { Booking } from '@/services/supabaseBookingService';

interface EditBookingDialogProps {
  booking: Booking;
  onBookingUpdated: (updatedBooking: Booking) => Promise<void>;
}

export function EditBookingDialog({ booking, onBookingUpdated }: EditBookingDialogProps) {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState({
    customer_name: booking.customer_name,
    contact_number: booking.contact_number,
    address: booking.address || '',
    id_proof_type: booking.id_proof_type || '',
    id_proof_number: booking.id_proof_number || '',
    function_date: new Date(booking.function_date),
    pickup_date: new Date(booking.pickup_date),
    return_date: new Date(booking.return_date),
    total_amount: booking.total_amount,
  });

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    const updatedBooking: Booking = {
      ...booking,
      customer_name: formData.customer_name,
      contact_number: formData.contact_number,
      address: formData.address,
      id_proof_type: formData.id_proof_type,
      id_proof_number: formData.id_proof_number,
      function_date: formData.function_date.toISOString(),
      pickup_date: formData.pickup_date.toISOString(),
      return_date: formData.return_date.toISOString(),
      total_amount: formData.total_amount,
    };

    await onBookingUpdated(updatedBooking);
    setOpen(false);
    
    toast({
      title: "Booking Updated",
      description: "Booking has been updated successfully.",
    });
  };

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      <DialogTrigger asChild>
        <Button size="sm" variant="outline">
          <Edit className="w-4 h-4 mr-1" />
          Edit
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Edit Booking - {booking.id}</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="customer_name">Customer Name *</Label>
              <Input
                id="customer_name"
                value={formData.customer_name}
                onChange={(e) => setFormData({ ...formData, customer_name: e.target.value })}
                required
              />
            </div>
            
            <div>
              <Label htmlFor="contact_number">Contact Number *</Label>
              <Input
                id="contact_number"
                value={formData.contact_number}
                onChange={(e) => setFormData({ ...formData, contact_number: e.target.value })}
                required
              />
            </div>
          </div>

          <div>
            <Label htmlFor="address">Address</Label>
            <Input
              id="address"
              value={formData.address}
              onChange={(e) => setFormData({ ...formData, address: e.target.value })}
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="id_proof_type">ID Proof Type</Label>
              <Input
                id="id_proof_type"
                value={formData.id_proof_type}
                onChange={(e) => setFormData({ ...formData, id_proof_type: e.target.value })}
                placeholder="e.g., Aadhar, PAN, Passport"
              />
            </div>
            
            <div>
              <Label htmlFor="id_proof_number">ID Proof Number</Label>
              <Input
                id="id_proof_number"
                value={formData.id_proof_number}
                onChange={(e) => setFormData({ ...formData, id_proof_number: e.target.value })}
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label>Function Date</Label>
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !formData.function_date && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {formData.function_date ? format(formData.function_date, "PPP") : "Select date"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0">
                  <Calendar
                    mode="single"
                    selected={formData.function_date}
                    onSelect={(date) => date && setFormData({ ...formData, function_date: date, return_date: addDays(date, 1) })}
                  />
                </PopoverContent>
              </Popover>
            </div>

            <div>
              <Label>Pickup Date</Label>
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !formData.pickup_date && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {formData.pickup_date ? format(formData.pickup_date, "PPP") : "Select date"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0">
                  <Calendar
                    mode="single"
                    selected={formData.pickup_date}
                    onSelect={(date) => date && setFormData({ ...formData, pickup_date: date })}
                  />
                </PopoverContent>
              </Popover>
            </div>

            <div>
              <Label>Return Date (Auto: Function + 1 day)</Label>
              <Popover>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !formData.return_date && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {formData.return_date ? format(formData.return_date, "PPP") : "Select date"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0">
                  <Calendar
                    mode="single"
                    selected={formData.return_date}
                    onSelect={(date) => date && setFormData({ ...formData, return_date: date })}
                  />
                </PopoverContent>
              </Popover>
            </div>
          </div>

          <div>
            <Label htmlFor="total_amount">Total Amount (₹)</Label>
            <Input
              id="total_amount"
              type="number"
              value={formData.total_amount}
              onChange={(e) => setFormData({ ...formData, total_amount: Number(e.target.value) })}
              min="0"
            />
          </div>

          <div className="flex gap-2 justify-end">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" className="gold-gradient text-white">
              Update Booking
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
