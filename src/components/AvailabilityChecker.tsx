
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, Calendar, Package } from 'lucide-react';
import { format } from 'date-fns';

interface Booking {
  id: string;
  customerName: string;
  contactNumber: string;
  pickupDate: string;
  returnDate: string;
  status: 'confirmed' | 'picked-up' | 'returned' | 'cancelled';
  jewelryItems: string[];
  selectedItemDetails?: any[];
}

export function AvailabilityChecker() {
  const [itemId, setItemId] = useState('');
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [searchResults, setSearchResults] = useState<any[]>([]);
  const [isSearched, setIsSearched] = useState(false);

  useEffect(() => {
    const loadBookings = () => {
      try {
        const savedBookings = localStorage.getItem('bookingsData');
        if (savedBookings) {
          const parsedBookings = JSON.parse(savedBookings);
          setBookings(parsedBookings);
        }
      } catch (error) {
        console.error('Failed to load bookings:', error);
      }
    };
    
    loadBookings();
  }, []);

  const checkAvailability = () => {
    if (!itemId.trim()) return;

    console.log('Checking availability for item:', itemId);
    
    const relevantBookings = bookings.filter(booking => {
      // Skip cancelled or returned bookings
      if (booking.status === 'cancelled' || booking.status === 'returned') return false;
      
      // Check if this booking contains the item
      const hasItem = booking.selectedItemDetails?.some(detail => 
        detail.id === itemId.trim()
      ) || booking.jewelryItems.some(item => 
        item.toLowerCase().includes(itemId.trim().toLowerCase())
      );
      
      return hasItem;
    });

    console.log('Found bookings for item:', relevantBookings);
    
    const results = relevantBookings.map(booking => ({
      bookingId: booking.id,
      customerName: booking.customerName,
      contactNumber: booking.contactNumber,
      pickupDate: booking.pickupDate,
      returnDate: booking.returnDate,
      status: booking.status
    }));

    setSearchResults(results);
    setIsSearched(true);
  };

  const handleKeyPress = (e: React.KeyboardEvent) => {
    if (e.key === 'Enter') {
      checkAvailability();
    }
  };

  return (
    <Card className="luxury-shadow">
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Package className="w-5 h-5" />
          Availability of Sets
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-4">
        <div className="flex gap-2">
          <Input
            placeholder="Enter item ID (e.g., SLIP001)"
            value={itemId}
            onChange={(e) => setItemId(e.target.value)}
            onKeyPress={handleKeyPress}
            className="flex-1"
          />
          <Button onClick={checkAvailability} className="px-4">
            <Search className="w-4 h-4" />
          </Button>
        </div>

        {isSearched && (
          <div className="mt-4">
            <h3 className="font-semibold mb-3">
              Availability Status for: {itemId}
            </h3>
            
            {searchResults.length === 0 ? (
              <div className="text-center py-6 text-green-600 bg-green-50 rounded-lg">
                <Calendar className="w-8 h-8 mx-auto mb-2 opacity-70" />
                <p className="font-medium">Available</p>
                <p className="text-sm text-muted-foreground">This item is currently available for booking</p>
              </div>
            ) : (
              <div className="space-y-3">
                <div className="text-center py-2 text-orange-600 bg-orange-50 rounded-lg">
                  <p className="font-medium">Currently Booked</p>
                  <p className="text-sm text-muted-foreground">This item has active bookings</p>
                </div>
                
                {searchResults.map((result, index) => (
                  <div key={index} className="border rounded-lg p-3 bg-gray-50">
                    <div className="flex justify-between items-start mb-2">
                      <div>
                        <p className="font-medium">{result.customerName}</p>
                        <p className="text-sm text-muted-foreground">
                          Booking: {result.bookingId}
                        </p>
                        <p className="text-sm text-muted-foreground">
                          Contact: {result.contactNumber}
                        </p>
                      </div>
                      <span className={`px-2 py-1 rounded text-xs font-medium ${
                        result.status === 'confirmed' ? 'bg-blue-100 text-blue-800' :
                        result.status === 'picked-up' ? 'bg-green-100 text-green-800' :
                        'bg-gray-100 text-gray-800'
                      }`}>
                        {result.status.toUpperCase()}
                      </span>
                    </div>
                    
                    <div className="grid grid-cols-2 gap-2 text-sm">
                      <div>
                        <span className="font-medium text-orange-600">Pickup:</span>
                        <p>{format(new Date(result.pickupDate), "PPP")}</p>
                      </div>
                      <div>
                        <span className="font-medium text-red-600">Return:</span>
                        <p>{format(new Date(result.returnDate), "PPP")}</p>
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </div>
        )}
      </CardContent>
    </Card>
  );
}
