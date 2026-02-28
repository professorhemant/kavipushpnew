import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { CalendarIcon } from 'lucide-react';
import { format } from 'date-fns';
import { cn } from '@/lib/utils';
import { toast } from '@/hooks/use-toast';

export interface Customer {
  id: string;
  name: string;
  address: string;
  contactNumber: string;
  email: string;
  idProofType: string;
  idProofNumber: string;
  functionDate: Date | null;
  bookingDate: Date | null;
  pickupDate: Date | null;
  returnDate: Date | null;
}

interface CustomerFormProps {
  onSubmit: (customer: Omit<Customer, 'id'>) => void;
  initialData?: Partial<Customer>;
}

export function CustomerForm({ onSubmit, initialData }: CustomerFormProps) {
  const [formData, setFormData] = useState({
    name: initialData?.name || '',
    address: initialData?.address || '',
    contactNumber: initialData?.contactNumber || '',
    email: initialData?.email || '',
    idProofType: initialData?.idProofType || '',
    idProofNumber: initialData?.idProofNumber || '',
    functionDate: initialData?.functionDate || null,
    bookingDate: initialData?.bookingDate || null,
    pickupDate: initialData?.pickupDate || null,
    returnDate: initialData?.returnDate || null,
  });

  const [functionDateOpen, setFunctionDateOpen] = useState(false);
  const [bookingDateOpen, setBookingDateOpen] = useState(false);
  const [pickupDateOpen, setPickupDateOpen] = useState(false);

  // Calculate return date (1 day after function date)
  const calculateReturnDate = (functionDate: Date | null): Date | null => {
    if (!functionDate) return null;
    const returnDate = new Date(functionDate);
    returnDate.setDate(returnDate.getDate() + 1);
    return returnDate;
  };

  // Update return date whenever function date changes
  useEffect(() => {
    const newReturnDate = calculateReturnDate(formData.functionDate);
    setFormData(prev => ({ ...prev, returnDate: newReturnDate }));
    console.log('Function date changed:', formData.functionDate);
    console.log('New return date:', newReturnDate);
  }, [formData.functionDate]);

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.name || !formData.contactNumber || !formData.functionDate) {
      toast({
        title: "Missing Information",
        description: "Please fill in all required fields.",
        variant: "destructive",
      });
      return;
    }

    onSubmit(formData);

    toast({
      title: "Customer Added",
      description: "Customer information has been saved successfully.",
    });
  };

  return (
    <Card className="luxury-shadow">
      <CardHeader>
        <CardTitle className="text-xl font-bold text-primary">Customer Information</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="name">Full Name *</Label>
              <Input
                id="name"
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                placeholder="Enter customer name"
                required
              />
            </div>
            
            <div>
              <Label htmlFor="contactNumber">Contact Number *</Label>
              <Input
                id="contactNumber"
                value={formData.contactNumber}
                onChange={(e) => setFormData({ ...formData, contactNumber: e.target.value })}
                placeholder="Enter contact number"
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
              placeholder="Enter complete address"
            />
          </div>

          <div>
            <Label htmlFor="email">Email</Label>
            <Input
              id="email"
              type="email"
              value={formData.email}
              onChange={(e) => setFormData({ ...formData, email: e.target.value })}
              placeholder="Enter email address"
            />
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="idProofType">ID Proof Type</Label>
              <Input
                id="idProofType"
                value={formData.idProofType}
                onChange={(e) => setFormData({ ...formData, idProofType: e.target.value })}
                placeholder="e.g., Aadhaar, PAN, Passport"
              />
            </div>
            
            <div>
              <Label htmlFor="idProofNumber">ID Proof Number</Label>
              <Input
                id="idProofNumber"
                value={formData.idProofNumber}
                onChange={(e) => setFormData({ ...formData, idProofNumber: e.target.value })}
                placeholder="Enter ID proof number"
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label>Function Date *</Label>
              <Popover open={functionDateOpen} onOpenChange={setFunctionDateOpen}>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !formData.functionDate && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {formData.functionDate ? format(formData.functionDate, "PPP") : "Select function date"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                  <Calendar
                    mode="single"
                    selected={formData.functionDate || undefined}
                    onSelect={(date) => {
                      setFormData({ ...formData, functionDate: date || null });
                      setFunctionDateOpen(false);
                    }}
                    className="pointer-events-auto"
                  />
                </PopoverContent>
              </Popover>
            </div>

            <div>
              <Label>Booking Date</Label>
              <Popover open={bookingDateOpen} onOpenChange={setBookingDateOpen}>
                <PopoverTrigger asChild>
                  <Button
                    variant="outline"
                    className={cn(
                      "w-full justify-start text-left font-normal",
                      !formData.bookingDate && "text-muted-foreground"
                    )}
                  >
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {formData.bookingDate ? format(formData.bookingDate, "PPP") : "Select booking date"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                  <Calendar
                    mode="single"
                    selected={formData.bookingDate || undefined}
                    onSelect={(date) => {
                      console.log('Selected booking date:', date);
                      setFormData({ ...formData, bookingDate: date || null });
                      setBookingDateOpen(false);
                    }}
                    className="pointer-events-auto"
                  />
                </PopoverContent>
              </Popover>
            </div>
          </div>

          <div>
            <Label>Pickup Date</Label>
            <Popover open={pickupDateOpen} onOpenChange={setPickupDateOpen}>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  className={cn(
                    "w-full justify-start text-left font-normal",
                    !formData.pickupDate && "text-muted-foreground"
                  )}
                >
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {formData.pickupDate ? format(formData.pickupDate, "PPP") : "Select pickup date"}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="start">
                <Calendar
                  mode="single"
                  selected={formData.pickupDate || undefined}
                  onSelect={(date) => {
                    console.log('Selected pickup date:', date);
                    setFormData({ ...formData, pickupDate: date || null });
                    setPickupDateOpen(false);
                  }}
                  className="pointer-events-auto"
                />
              </PopoverContent>
            </Popover>
          </div>

          {formData.functionDate && formData.returnDate && (
            <div className="p-3 bg-primary/10 rounded-lg">
              <p className="text-sm text-primary font-medium">
                Return Date: {format(formData.returnDate, "PPP")}
              </p>
              <p className="text-xs text-muted-foreground">
                Automatically calculated as 1 day after function date
              </p>
            </div>
          )}

          <Button type="submit" className="w-full gold-gradient text-white font-semibold">
            Save Customer Information
          </Button>
        </form>
      </CardContent>
    </Card>
  );
}
