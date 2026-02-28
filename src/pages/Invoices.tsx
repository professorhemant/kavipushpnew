
import { useState, useEffect } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { FileText, Trash2 } from 'lucide-react';
import { InvoiceGenerator } from '@/components/InvoiceGenerator';
import { InvoiceDetailsDialog } from '@/components/InvoiceDetailsDialog';
import { Badge } from '@/components/ui/badge';
import { EditInvoiceDialog } from '@/components/EditInvoiceDialog';
import { Booking } from '@/services/supabaseBookingService';
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
} from "@/components/ui/alert-dialog";
import { useToast } from "@/hooks/use-toast";

interface Invoice {
  id: string;
  invoiceNumber: string;
  customerName: string;
  type: 'booking' | 'pickup' | 'return';
  amount: number;
  date: string;
  status: 'paid' | 'pending' | 'overdue';
  bookingData?: any;
  securityAmount: number;
  bookingAmount?: number;
}

interface AmountSettings {
  bookingAmountType: 'fixed' | 'percentage' | 'tiered';
  fixedBookingAmount: number;
  bookingPercentage: number;
  includeGstInBooking: boolean;
  securityAmountType: 'fixed' | 'percentage' | 'tiered';
  fixedSecurityAmount: number;
  securityPercentage: number;
  includeGstInSecurity: boolean;
  gstRate: number;
  tier1Max: number;
  tier1Amount: number;
  tier2Max: number;
  tier2Amount: number;
  tier3Max: number;
  tier3Amount: number;
  tier4Amount: number;
  securityTier1Max: number;
  securityTier1Amount: number;
  securityTier2Max: number;
  securityTier2Amount: number;
  securityTier3Max: number;
  securityTier3Amount: number;
  securityTier4Amount: number;
}

export default function Invoices() {
  const [invoices, setInvoices] = useState<Invoice[]>([]);
  const [bookings, setBookings] = useState<Booking[]>([]);
  const [amountSettings, setAmountSettings] = useState<AmountSettings>({
    bookingAmountType: 'tiered',
    fixedBookingAmount: 1000,
    bookingPercentage: 20,
    includeGstInBooking: false,
    securityAmountType: 'tiered',
    fixedSecurityAmount: 5000,
    securityPercentage: 50,
    includeGstInSecurity: false,
    gstRate: 3,
    tier1Max: 3500,
    tier1Amount: 1000,
    tier2Max: 4500,
    tier2Amount: 1500,
    tier3Max: 6500,
    tier3Amount: 2000,
    tier4Amount: 5000,
    securityTier1Max: 3500,
    securityTier1Amount: 2000,
    securityTier2Max: 4500,
    securityTier2Amount: 2500,
    securityTier3Max: 6500,
    securityTier3Amount: 3000,
    securityTier4Amount: 4000
  });

  const { toast } = useToast();

  // Function to reload all data
  const reloadData = () => {
    loadAmountSettings();
    loadBookings();
    loadInvoices();
  };

  // Load amount settings from localStorage
  const loadAmountSettings = () => {
    try {
      const savedSettings = localStorage.getItem('amountSettings');
      if (savedSettings) {
        const parsedSettings = JSON.parse(savedSettings);
        setAmountSettings(parsedSettings);
        console.log('Loaded amount settings from localStorage');
      }
    } catch (error) {
      console.error('Failed to load amount settings from localStorage:', error);
    }
  };

  // Load bookings from localStorage
  const loadBookings = () => {
    try {
      const savedBookings = localStorage.getItem('bookingsData');
      if (savedBookings) {
        const parsedBookings = JSON.parse(savedBookings);
        setBookings(parsedBookings);
        console.log(`Loaded ${parsedBookings.length} bookings for invoice generation`);
      } else {
        console.log('No bookings found in localStorage');
      }
    } catch (error) {
      console.error('Failed to load bookings from localStorage:', error);
    }
  };

  // Load invoices from localStorage
  const loadInvoices = () => {
    try {
      const savedInvoices = localStorage.getItem('invoicesData');
      if (savedInvoices) {
        const parsedInvoices = JSON.parse(savedInvoices);
        setInvoices(parsedInvoices);
        console.log(`Loaded ${parsedInvoices.length} invoices from localStorage`);
      } else {
        console.log('No invoices found in localStorage');
      }
    } catch (error) {
      console.error('Failed to load invoices from localStorage:', error);
    }
  };

  // Initial data load
  useEffect(() => {
    reloadData();
  }, []);

  // Listen for storage changes to refresh data when bookings are updated
  useEffect(() => {
    const handleStorageChange = (e: StorageEvent) => {
      if (e.key === 'bookingsData') {
        console.log('Bookings data changed, reloading...');
        loadBookings();
      }
    };

    window.addEventListener('storage', handleStorageChange);
    return () => window.removeEventListener('storage', handleStorageChange);
  }, []);

  // Save invoices to localStorage whenever invoices change
  useEffect(() => {
    try {
      localStorage.setItem('invoicesData', JSON.stringify(invoices));
      console.log(`Saved ${invoices.length} invoices to localStorage`);
    } catch (error) {
      console.error('Failed to save invoices to localStorage:', error);
    }
  }, [invoices]);

  const calculateBookingAmount = (rentAmount: number) => {
    const settings = amountSettings;
    let baseAmount = 0;

    switch (settings.bookingAmountType) {
      case 'fixed':
        baseAmount = settings.fixedBookingAmount;
        break;
      case 'percentage':
        baseAmount = (rentAmount * settings.bookingPercentage) / 100;
        break;
      case 'tiered':
        if (rentAmount <= settings.tier1Max) {
          baseAmount = settings.tier1Amount;
        } else if (rentAmount <= settings.tier2Max) {
          baseAmount = settings.tier2Amount;
        } else if (rentAmount <= settings.tier3Max) {
          baseAmount = settings.tier3Amount;
        } else if (rentAmount >= 10000) {
          baseAmount = settings.tier4Amount;
        } else {
          // For amounts between tier3Max and 10000, use proportional calculation
          baseAmount = Math.round((rentAmount - settings.tier3Max) / (10000 - settings.tier3Max) * (settings.tier4Amount - settings.tier3Amount) + settings.tier3Amount);
        }
        break;
    }

    // Add GST if enabled
    if (settings.includeGstInBooking) {
      baseAmount = baseAmount + (baseAmount * settings.gstRate / 100);
    }

    return Math.round(baseAmount);
  };

  const calculateSecurityAmount = (rentAmount: number, invoiceType: 'booking' | 'pickup' | 'return') => {
    if (invoiceType === 'booking') return 0;
    if (invoiceType === 'return') return 0;

    const settings = amountSettings;
    let baseAmount = 0;

    switch (settings.securityAmountType) {
      case 'fixed':
        baseAmount = settings.fixedSecurityAmount;
        break;
      case 'percentage':
        baseAmount = (rentAmount * settings.securityPercentage) / 100;
        break;
      case 'tiered':
        if (rentAmount <= settings.securityTier1Max) {
          baseAmount = settings.securityTier1Amount;
        } else if (rentAmount <= settings.securityTier2Max) {
          baseAmount = settings.securityTier2Amount;
        } else if (rentAmount <= settings.securityTier3Max) {
          baseAmount = settings.securityTier3Amount;
        } else if (rentAmount >= 10000) {
          baseAmount = settings.securityTier4Amount;
        } else {
          // For amounts between tier3Max and 10000, use proportional calculation
          baseAmount = Math.round((rentAmount - settings.securityTier3Max) / (10000 - settings.securityTier3Max) * (settings.securityTier4Amount - settings.securityTier3Amount) + settings.securityTier3Amount);
        }
        break;
    }

    // Add GST if enabled
    if (settings.includeGstInSecurity) {
      baseAmount = baseAmount + (baseAmount * settings.gstRate / 100);
    }

    return Math.round(baseAmount);
  };

  const calculateTotalBalance = (rentAmount: number, bookingAmount: number, securityAmount: number) => {
    // Total balance = rent - booking amount - security
    return rentAmount - bookingAmount - securityAmount;
  };

  const getBalanceLabel = (balance: number) => {
    if (balance < 0) {
      return "Refund";
    } else if (balance > 0) {
      return "Balance to be paid";
    } else {
      return "Total Balance";
    }
  };

  const addInvoice = (invoiceData: any) => {
    const bookingAmount = calculateBookingAmount(invoiceData.totalAmount);
    const newInvoice: Invoice = {
      id: Date.now().toString(),
      invoiceNumber: invoiceData.invoiceNumber,
      customerName: invoiceData.customerName,
      type: invoiceData.invoiceType || 'booking',
      amount: invoiceData.totalAmount,
      date: invoiceData.date,
      status: 'pending',
      securityAmount: invoiceData.securityAmount || 0,
      bookingAmount: invoiceData.bookingAmount || bookingAmount,
      bookingData: invoiceData
    };
    setInvoices([...invoices, newInvoice]);
  };

  const updateInvoice = (updatedInvoice: Invoice) => {
    setInvoices(invoices.map(invoice => 
      invoice.id === updatedInvoice.id ? updatedInvoice : invoice
    ));
  };

  const deleteInvoice = (invoiceId: string) => {
    const invoiceToDelete = invoices.find(invoice => invoice.id === invoiceId);
    setInvoices(invoices.filter(invoice => invoice.id !== invoiceId));
    
    toast({
      title: "Invoice Deleted",
      description: `Invoice ${invoiceToDelete?.invoiceNumber} has been deleted successfully.`,
    });
  };

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <div>
          <h1 className="text-3xl font-bold text-primary">Invoice Management</h1>
          <p className="text-muted-foreground">Track and manage all rental invoices</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={reloadData}>
            <FileText className="w-4 h-4 mr-2" />
            Refresh Data
          </Button>
          <InvoiceGenerator 
            bookings={bookings}
            onInvoiceGenerated={addInvoice}
            trigger={
              <Button className="gold-gradient text-white">
                <FileText className="w-4 h-4 mr-2" />
                Generate Invoice
              </Button>
            }
          />
        </div>
      </div>

      <Card className="luxury-shadow">
        <CardHeader>
          <CardTitle className="flex items-center gap-2">
            <FileText className="w-5 h-5" />
            All Invoices ({invoices.length})
          </CardTitle>
        </CardHeader>
        <CardContent>
          {invoices.length === 0 ? (
            <div className="text-center py-12">
              <FileText className="w-16 h-16 text-muted-foreground mx-auto mb-4 opacity-50" />
              <h3 className="text-lg font-semibold mb-2">No Invoices Yet</h3>
              <p className="text-muted-foreground">
                Generated invoices will appear here
              </p>
              {bookings.length > 0 && (
                <p className="text-sm text-muted-foreground mt-2">
                  You have {bookings.length} booking(s) available for invoice generation
                </p>
              )}
            </div>
          ) : (
            <div className="space-y-4">
              {invoices.map((invoice) => {
                const bookingAmount = invoice.bookingAmount || calculateBookingAmount(invoice.amount);
                const securityAmount = invoice.securityAmount || 0;
                const totalBalance = calculateTotalBalance(invoice.amount, bookingAmount, securityAmount);
                const balanceLabel = getBalanceLabel(totalBalance);
                const getTypeDisplayName = (type: string) => {
                  return type === 'return' ? 'Final' : type.charAt(0).toUpperCase() + type.slice(1);
                };
                return (
                  <Card key={invoice.id} className="border">
                    <CardContent className="p-4">
                      <div className="flex justify-between items-center">
                        <div className="flex-1">
                          <div className="flex items-center gap-2 mb-1">
                            <h3 className="font-semibold">{invoice.invoiceNumber}</h3>
                            <Badge variant="outline" className="text-xs">
                              {getTypeDisplayName(invoice.type)}
                            </Badge>
                          </div>
                          <p className="text-sm text-muted-foreground mb-2">
                            {invoice.customerName} | {new Date(invoice.date).toLocaleDateString()}
                          </p>
                          <div className="grid grid-cols-2 gap-x-4 gap-y-1 text-sm max-w-md">
                            <div className="flex justify-between">
                              <span>Rent:</span>
                              <span className="font-medium">₹{invoice.amount.toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between">
                              <span>Booking Amount:</span>
                              <span className="font-medium">₹{bookingAmount.toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between">
                              <span>Security:</span>
                              <span className="font-medium">₹{securityAmount.toLocaleString()}</span>
                            </div>
                            <div className="flex justify-between font-bold">
                              <span>{balanceLabel}:</span>
                              <span className={totalBalance < 0 ? 'text-green-600' : 'text-primary'}>
                                ₹{Math.abs(totalBalance).toLocaleString()}
                              </span>
                            </div>
                          </div>
                        </div>
                        <div className="flex gap-2">
                          <EditInvoiceDialog 
                            invoice={invoice} 
                            onInvoiceUpdated={updateInvoice}
                          />
                          <InvoiceDetailsDialog invoice={invoice} />
                          <AlertDialog>
                            <AlertDialogTrigger asChild>
                              <Button variant="outline" size="sm" className="text-red-600 hover:bg-red-50">
                                <Trash2 className="w-4 h-4" />
                              </Button>
                            </AlertDialogTrigger>
                            <AlertDialogContent>
                              <AlertDialogHeader>
                                <AlertDialogTitle>Delete Invoice</AlertDialogTitle>
                                <AlertDialogDescription>
                                  Are you sure you want to delete invoice {invoice.invoiceNumber}? This action cannot be undone.
                                </AlertDialogDescription>
                              </AlertDialogHeader>
                              <AlertDialogFooter>
                                <AlertDialogCancel>Cancel</AlertDialogCancel>
                                <AlertDialogAction 
                                  onClick={() => deleteInvoice(invoice.id)}
                                  className="bg-red-600 hover:bg-red-700"
                                >
                                  Delete
                                </AlertDialogAction>
                              </AlertDialogFooter>
                            </AlertDialogContent>
                          </AlertDialog>
                        </div>
                      </div>
                    </CardContent>
                  </Card>
                );
              })}
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}
