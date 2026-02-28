import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CalendarIcon, Plus, AlertTriangle, User } from 'lucide-react';
import { format, addDays } from 'date-fns';
import { cn } from '@/lib/utils';
import { toast } from '@/hooks/use-toast';
import { Card, CardContent } from '@/components/ui/card';
import { JewelryItem } from '@/components/JewelryInventory';

interface Customer {
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

interface NewBookingDialogProps {
  onBookingCreated: (booking: any) => void;
  inventory?: JewelryItem[];
  isItemBooked?: (itemId: string, startDate: string, endDate: string) => boolean;
}

export function NewBookingDialog({ onBookingCreated, inventory = [], isItemBooked }: NewBookingDialogProps) {
  const [open, setOpen] = useState(false);
  const [customers, setCustomers] = useState<Customer[]>([]);
  const [selectedCustomerId, setSelectedCustomerId] = useState<string>('');
  const [slipCode, setSlipCode] = useState('');
  const [selectedItem, setSelectedItem] = useState<any>(null);
  const [availabilityStatus, setAvailabilityStatus] = useState<'available' | 'unavailable' | 'checking' | null>(null);
  
  // Separate popover states for each date picker
  const [functionDateOpen, setFunctionDateOpen] = useState(false);
  const [pickupDateOpen, setPickupDateOpen] = useState(false);
  const [returnDateOpen, setReturnDateOpen] = useState(false);
  
  const [formData, setFormData] = useState({
    customerName: '',
    contactNumber: '',
    address: '',
    email: '',
    idProofType: '',
    idProofNumber: '',
    functionDate: null as Date | null,
    pickupDate: null as Date | null,
    returnDate: null as Date | null,
    bookingDate: null as Date | null,
  });

  // Load customers from localStorage when dialog opens
  const loadCustomers = () => {
    try {
      const savedCustomers = localStorage.getItem('customersData');
      if (savedCustomers) {
        const parsedCustomers = JSON.parse(savedCustomers);
        const customersWithDates = parsedCustomers.map((customer: any) => ({
          ...customer,
          idProofType: customer.idProofType || '',
          idProofNumber: customer.idProofNumber || '',
          functionDate: customer.functionDate ? new Date(customer.functionDate) : null,
          bookingDate: customer.bookingDate ? new Date(customer.bookingDate) : null,
          pickupDate: customer.pickupDate ? new Date(customer.pickupDate) : null,
          returnDate: customer.returnDate ? new Date(customer.returnDate) : null,
        }));
        setCustomers(customersWithDates);
        console.log(`Loaded ${customersWithDates.length} customers for booking selection`);
      }
    } catch (error) {
      console.error('Failed to load customers:', error);
    }
  };

  // Handle customer selection
  const handleCustomerSelect = (customerId: string) => {
    setSelectedCustomerId(customerId);
    
    if (customerId === 'new') {
      // Reset form for new customer
      setFormData({
        customerName: '',
        contactNumber: '',
        address: '',
        email: '',
        idProofType: '',
        idProofNumber: '',
        functionDate: null,
        pickupDate: null,
        returnDate: null,
        bookingDate: null,
      });
    } else {
      // Fill form with selected customer data
      const selectedCustomer = customers.find(c => c.id === customerId);
      if (selectedCustomer) {
        setFormData({
          customerName: selectedCustomer.name,
          contactNumber: selectedCustomer.contactNumber,
          address: selectedCustomer.address,
          email: selectedCustomer.email || '',
          idProofType: selectedCustomer.idProofType || '',
          idProofNumber: selectedCustomer.idProofNumber || '',
          functionDate: selectedCustomer.functionDate,
          pickupDate: selectedCustomer.pickupDate,
          returnDate: selectedCustomer.returnDate,
          bookingDate: selectedCustomer.bookingDate, // Fetch booking date from customer data
        });
        
        toast({
          title: "Customer Data Loaded",
          description: `Information for ${selectedCustomer.name} has been loaded with booking date: ${selectedCustomer.bookingDate ? selectedCustomer.bookingDate.toLocaleDateString() : 'Not set'}.`,
        });
      }
    }
  };

  const checkItemAvailability = (item: any, pickupDate: Date | null, returnDate: Date | null) => {
    if (!pickupDate || !returnDate || !isItemBooked) {
      return 'available';
    }

    const pickupDateStr = pickupDate.toISOString().split('T')[0];
    const returnDateStr = returnDate.toISOString().split('T')[0];
    
    const isBooked = isItemBooked(item.id, pickupDateStr, returnDateStr);
    return isBooked ? 'unavailable' : 'available';
  };

  const handleSlipCodeChange = (value: string) => {
    setSlipCode(value.toUpperCase());
    setSelectedItem(null);
    setAvailabilityStatus(null);
    
    if (value.trim()) {
      const searchCode = value.toUpperCase().trim();
      console.log('Searching for item with code:', searchCode);
      
      // Search only in local CSV inventory
      const item = inventory.find(item => {
        return item.id.toUpperCase() === searchCode;
      });
      
      console.log('Found item in inventory:', item);
      
      if (item) {
        setSelectedItem(item);
        
        // Check availability for the selected dates
        if (formData.pickupDate && formData.returnDate) {
          const availability = checkItemAvailability(item, formData.pickupDate, formData.returnDate);
          setAvailabilityStatus(availability);
          
          if (availability === 'unavailable') {
            toast({
              title: "Item Not Available",
              description: `${item.name} (${item.id}) is already booked for the selected dates.`,
              variant: "destructive",
            });
          } else {
            toast({
              title: "Item Available",
              description: `${item.name} - ₹${item.rentPrice} is available for booking.`,
            });
          }
        } else {
          toast({
            title: "Item Found",
            description: `${item.name} - ₹${item.rentPrice}. Please select dates to check availability.`,
          });
        }
      } else {
        toast({
          title: "Item Not Found",
          description: `No item found with code "${searchCode}".`,
          variant: "destructive",
        });
      }
    }
  };

  const handleDateChange = (field: 'functionDate' | 'pickupDate' | 'returnDate', date: Date | undefined) => {
    const updatedFormData = { ...formData, [field]: date || null };
    
    if (field === 'functionDate' && date) {
      updatedFormData.returnDate = addDays(date, 1);
    }
    
    setFormData(updatedFormData);
    
    // Recheck availability if item is selected and both dates are set
    if (selectedItem && updatedFormData.pickupDate && updatedFormData.returnDate) {
      const availability = checkItemAvailability(selectedItem, updatedFormData.pickupDate, updatedFormData.returnDate);
      setAvailabilityStatus(availability);
      
      if (availability === 'unavailable') {
        toast({
          title: "Item Not Available",
          description: `${selectedItem.name} is already booked for the selected dates.`,
          variant: "destructive",
        });
      }
    }
  };

  const handleFunctionDateSelect = (date: Date | undefined) => {
    handleDateChange('functionDate', date);
    setFunctionDateOpen(false);
  };

  const handlePickupDateSelect = (date: Date | undefined) => {
    handleDateChange('pickupDate', date);
    setPickupDateOpen(false);
  };

  const handleReturnDateSelect = (date: Date | undefined) => {
    handleDateChange('returnDate', date);
    setReturnDateOpen(false);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!formData.customerName || !formData.contactNumber || !formData.address || 
        !formData.functionDate || !selectedItem || !formData.idProofType || !formData.idProofNumber) {
      toast({
        title: "Missing Information",
        description: "Please fill in all required fields including ID proof details and select a jewelry item.",
        variant: "destructive",
      });
      return;
    }

    // Check availability one more time before booking
    if (availabilityStatus === 'unavailable') {
      toast({
        title: "Item Not Available",
        description: "The selected item is not available for the chosen dates.",
        variant: "destructive",
      });
      return;
    }

    const newBooking = {
      id: `BK${Date.now().toString().slice(-6)}`,
      customerName: formData.customerName,
      contactNumber: formData.contactNumber,
      address: formData.address,
      email: formData.email,
      idProofType: formData.idProofType,
      idProofNumber: formData.idProofNumber,
      functionDate: formData.functionDate?.toISOString().split('T')[0],
      pickupDate: formData.pickupDate?.toISOString().split('T')[0] || formData.functionDate?.toISOString().split('T')[0],
      returnDate: formData.returnDate?.toISOString().split('T')[0] || addDays(formData.functionDate!, 1).toISOString().split('T')[0],
      bookingDate: formData.bookingDate?.toISOString().split('T')[0] || new Date().toISOString().split('T')[0], // Use customer's booking date or current date
      status: 'confirmed' as const,
      totalAmount: selectedItem.rentPrice,
      jewelryItems: [`${selectedItem.name} (${selectedItem.id})`],
      selectedItemDetails: [selectedItem],
    };

    onBookingCreated(newBooking);
    
    toast({
      title: "Booking Created",
      description: "New booking has been created successfully with booking date information.",
    });

    // Reset form
    setOpen(false);
    setFormData({
      customerName: '',
      contactNumber: '',
      address: '',
      email: '',
      idProofType: '',
      idProofNumber: '',
      functionDate: null,
      pickupDate: null,
      returnDate: null,
      bookingDate: null,
    });
    setSlipCode('');
    setSelectedItem(null);
    setAvailabilityStatus(null);
    setSelectedCustomerId('');
  };

  return (
    <Dialog open={open} onOpenChange={(isOpen) => {
      setOpen(isOpen);
      if (isOpen) {
        loadCustomers();
      }
    }}>
      <DialogTrigger asChild>
        <Button className="gold-gradient text-white">
          <Plus className="w-4 h-4 mr-2" />
          New Booking
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Create New Booking</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-6">
          {/* Customer Selection */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold flex items-center gap-2">
              <User className="w-5 h-5" />
              Select Customer
            </h3>
            
            <div>
              <Label htmlFor="customerSelect">Choose Existing Customer or Create New</Label>
              <Select value={selectedCustomerId} onValueChange={handleCustomerSelect}>
                <SelectTrigger>
                  <SelectValue placeholder="Select a customer or choose 'New Customer'" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="new">+ Create New Customer</SelectItem>
                  {customers.map((customer) => (
                    <SelectItem key={customer.id} value={customer.id}>
                      {customer.name} - {customer.contactNumber}
                      {customer.idProofType && ` (${customer.idProofType})`}
                    </SelectItem>
                  ))}
                </SelectContent>
              </Select>
            </div>

            {selectedCustomerId && selectedCustomerId !== 'new' && (
              <div className="p-3 bg-green-50 border border-green-200 rounded-lg">
                <p className="text-sm text-green-800 font-medium">
                  ✓ Customer data loaded successfully with booking date information. You can modify the details below if needed.
                </p>
              </div>
            )}
          </div>

          {/* Customer Information */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">Customer Information</h3>
            
            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="customerName">Customer Name *</Label>
                <Input
                  id="customerName"
                  value={formData.customerName}
                  onChange={(e) => setFormData({ ...formData, customerName: e.target.value })}
                  required
                />
              </div>
              <div>
                <Label htmlFor="contactNumber">Contact Number *</Label>
                <Input
                  id="contactNumber"
                  value={formData.contactNumber}
                  onChange={(e) => setFormData({ ...formData, contactNumber: e.target.value })}
                  required
                />
              </div>
            </div>
            
            <div>
              <Label htmlFor="address">Address *</Label>
              <Textarea
                id="address"
                value={formData.address}
                onChange={(e) => setFormData({ ...formData, address: e.target.value })}
                required
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

            <div className="grid grid-cols-2 gap-4">
              <div>
                <Label htmlFor="idProofType">ID Proof Type *</Label>
                <Input
                  id="idProofType"
                  value={formData.idProofType}
                  onChange={(e) => setFormData({ ...formData, idProofType: e.target.value })}
                  placeholder="e.g., Aadhaar, PAN, Passport"
                  required
                />
              </div>
              <div>
                <Label htmlFor="idProofNumber">ID Proof Number *</Label>
                <Input
                  id="idProofNumber"
                  value={formData.idProofNumber}
                  onChange={(e) => setFormData({ ...formData, idProofNumber: e.target.value })}
                  placeholder="Enter ID proof number"
                  required
                />
              </div>
            </div>
          </div>

          {/* Event Schedule */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">Event Schedule</h3>
            <div className="grid grid-cols-3 gap-4">
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
                      {formData.functionDate ? format(formData.functionDate, "PPP") : "Select date"}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={formData.functionDate || undefined}
                      onSelect={handleFunctionDateSelect}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
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
                      {formData.pickupDate ? format(formData.pickupDate, "PPP") : "Select date"}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={formData.pickupDate || undefined}
                      onSelect={handlePickupDateSelect}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>
              <div>
                <Label>Return Date (Auto: Function + 1 day)</Label>
                <Popover open={returnDateOpen} onOpenChange={setReturnDateOpen}>
                  <PopoverTrigger asChild>
                    <Button
                      variant="outline"
                      className={cn(
                        "w-full justify-start text-left font-normal",
                        !formData.returnDate && "text-muted-foreground"
                      )}
                    >
                      <CalendarIcon className="mr-2 h-4 w-4" />
                      {formData.returnDate ? format(formData.returnDate, "PPP") : "Auto-calculated"}
                    </Button>
                  </PopoverTrigger>
                  <PopoverContent className="w-auto p-0" align="start">
                    <Calendar
                      mode="single"
                      selected={formData.returnDate || undefined}
                      onSelect={handleReturnDateSelect}
                      initialFocus
                    />
                  </PopoverContent>
                </Popover>
              </div>
            </div>
          </div>

          {/* Jewelry Selection */}
          <div className="space-y-4">
            <h3 className="text-lg font-semibold">Select Jewelry Item *</h3>
            
            <div>
              <Label htmlFor="slipCode">Enter SLIP Code (e.g., SLIP001, SLIP002)</Label>
              <Input
                id="slipCode"
                placeholder="Enter SLIP code..."
                value={slipCode}
                onChange={(e) => handleSlipCodeChange(e.target.value)}
                className="uppercase"
              />
              <p className="text-sm text-muted-foreground mt-1">
                Available items in inventory: {inventory.length}
              </p>
            </div>

            {/* Selected Item Details */}
            {selectedItem && (
              <Card className={`border-2 ${
                availabilityStatus === 'unavailable' ? 'border-red-500 bg-red-50' : 
                availabilityStatus === 'available' ? 'border-green-500 bg-green-50' : 
                'border-primary bg-primary/5'
              }`}>
                <CardContent className="p-4">
                  <div className="space-y-3">
                    <div className="flex justify-between items-start">
                      <div>
                        <h4 className="font-semibold text-lg">{selectedItem.name}</h4>
                        <p className="text-sm font-mono text-primary">{selectedItem.id}</p>
                      </div>
                      {availabilityStatus && (
                        <div className={`flex items-center gap-2 px-3 py-1 rounded ${
                          availabilityStatus === 'available' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }`}>
                          {availabilityStatus === 'unavailable' && <AlertTriangle className="w-4 h-4" />}
                          <span className="font-medium">
                            {availabilityStatus === 'available' ? 'Available' : 'Unavailable'}
                          </span>
                        </div>
                      )}
                    </div>
                    
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <span className="text-sm font-medium">Category:</span>
                        <p className="text-sm">{selectedItem.category}</p>
                      </div>
                      <div>
                        <span className="text-sm font-medium">Rent:</span>
                        <p className="text-sm font-bold text-primary">₹{selectedItem.rentPrice.toLocaleString()}</p>
                      </div>
                    </div>
                    
                    <div>
                      <span className="text-sm font-medium">Description:</span>
                      <p className="text-sm text-gray-600">{selectedItem.description}</p>
                    </div>

                    {availabilityStatus === 'unavailable' && (
                      <div className="bg-red-100 border border-red-200 rounded p-3">
                        <p className="text-sm text-red-800 font-medium">
                          ⚠️ This item is already booked for the selected dates. Please choose different dates or another item.
                        </p>
                      </div>
                    )}
                  </div>
                </CardContent>
              </Card>
            )}

            {slipCode && !selectedItem && (
              <div className="text-center py-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <p className="text-sm text-yellow-800">
                  No item found with code "{slipCode}". Please check the SLIP code.
                </p>
              </div>
            )}
          </div>

          {/* Total Rent Summary */}
          {selectedItem && availabilityStatus === 'available' && (
            <div className="bg-primary/10 p-4 rounded-lg">
              <div className="flex justify-between items-center">
                <span className="font-semibold">Total Rent:</span>
                <span className="text-xl font-bold text-primary">₹{selectedItem.rentPrice.toLocaleString()}</span>
              </div>
            </div>
          )}

          <div className="flex gap-2 pt-4">
            <Button 
              type="submit" 
              className="flex-1"
              disabled={availabilityStatus === 'unavailable'}
            >
              Create Booking
            </Button>
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>Cancel</Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
