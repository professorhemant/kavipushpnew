
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Eye, Download } from 'lucide-react';
import { toast } from '@/hooks/use-toast';
import jsPDF from 'jspdf';

interface Invoice {
  id: string;
  invoiceNumber: string;
  customerName: string;
  type: 'booking' | 'pickup' | 'return';
  amount: number;
  date: string;
  status: 'paid' | 'pending' | 'overdue';
  securityAmount?: number;
  bookingAmount?: number;
  bookingData?: any;
}

interface InvoiceDetailsDialogProps {
  invoice: Invoice;
}

export function InvoiceDetailsDialog({ invoice }: InvoiceDetailsDialogProps) {
  const calculateBookingAmount = (rentAmount: number) => {
    if (rentAmount <= 3500) return 1000;
    if (rentAmount <= 4500) return 1500;
    if (rentAmount <= 6500) return 2000;
    if (rentAmount >= 10000) return 5000;
    // For amounts between 6500-10000, we'll use a proportional calculation
    return Math.round((rentAmount - 6500) / (10000 - 6500) * (5000 - 2000) + 2000);
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

  const getStatusColor = (status: Invoice['status']) => {
    switch (status) {
      case 'paid': return 'bg-green-100 text-green-800';
      case 'pending': return 'bg-yellow-100 text-yellow-800';
      case 'overdue': return 'bg-red-100 text-red-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  };

  const getTypeLabel = (type: Invoice['type']) => {
    switch (type) {
      case 'booking': return 'Booking Invoice';
      case 'pickup': return 'Pickup Invoice';
      case 'return': return 'Final Invoice';
      default: return 'Invoice';
    }
  };

  const handleDownload = () => {
    try {
      const doc = new jsPDF();
      
      // Set default font to helvetica for better compatibility
      doc.setFont('helvetica');
      
      // Company header
      doc.setFontSize(24);
      doc.setFont('helvetica', 'bold');
      doc.text('Kavipushp Jewels', 105, 30, { align: 'center' });
      
      doc.setFontSize(12);
      doc.setFont('helvetica', 'normal');
      doc.text('Premium Bridal Jewelry Rentals', 105, 40, { align: 'center' });
      
      // Invoice details
      doc.setFontSize(14);
      doc.setFont('helvetica', 'bold');
      doc.text(getTypeLabel(invoice.type).toUpperCase(), 20, 60);
      
      doc.setFontSize(10);
      doc.setFont('helvetica', 'normal');
      doc.text(`Invoice No: ${invoice.invoiceNumber}`, 20, 75);
      doc.text(`Type: ${getTypeLabel(invoice.type)}`, 20, 85);
      doc.text(`Date: ${new Date(invoice.date).toLocaleDateString()}`, 20, 95);
      doc.text(`Status: ${invoice.status.toUpperCase()}`, 20, 105);
      
      // Customer details
      doc.setFont('helvetica', 'bold');
      doc.text('Customer Details:', 120, 75);
      doc.setFont('helvetica', 'normal');
      doc.text(`Name: ${invoice.customerName}`, 120, 85);
      
      // Amount breakdown
      let yPos = 130;
      doc.setFont('helvetica', 'bold');
      doc.text('Amount Details:', 20, yPos);
      doc.setFont('helvetica', 'normal');
      yPos += 10;
      
      // Set consistent x-position for labels and values
      const labelX = 120;
      const valueX = 170;
      
      doc.text('Rent:', labelX, yPos);
      doc.text(`Rs.${invoice.amount.toLocaleString()}`, valueX, yPos);
      yPos += 8;
      
      const bookingAmount = invoice.bookingAmount || calculateBookingAmount(invoice.amount);
      doc.text('Booking Amount:', labelX, yPos);
      doc.text(`Rs.${bookingAmount.toLocaleString()}`, valueX, yPos);
      yPos += 8;
      
      const securityAmount = invoice.securityAmount || 0;
      doc.text('Security:', labelX, yPos);
      doc.text(`Rs.${securityAmount.toLocaleString()}`, valueX, yPos);
      yPos += 8;
      
      doc.setFont('helvetica', 'bold');
      const totalBalance = calculateTotalBalance(invoice.amount, bookingAmount, securityAmount);
      const balanceLabel = getBalanceLabel(totalBalance);
      doc.text(`${balanceLabel}:`, labelX, yPos);
      doc.text(`Rs.${Math.abs(totalBalance).toLocaleString()}`, valueX, yPos);
      
      // Signature section
      yPos += 20;
      doc.setFont('helvetica', 'normal');
      doc.text('Customer Signature: ____________________', 20, yPos);
      doc.text('Authorized Executive: ____________________', 120, yPos);
      
      // Save the PDF
      doc.save(`${invoice.invoiceNumber}.pdf`);
      
      toast({
        title: "Download Complete",
        description: `${invoice.invoiceNumber}.pdf downloaded successfully.`,
      });
    } catch (error) {
      console.error('Error generating PDF:', error);
      toast({
        title: "Download Failed",
        description: "Failed to generate PDF. Please try again.",
        variant: "destructive",
      });
    }
  };

  const bookingAmount = invoice.bookingAmount || calculateBookingAmount(invoice.amount);
  const securityAmount = invoice.securityAmount || 0;
  const totalBalance = calculateTotalBalance(invoice.amount, bookingAmount, securityAmount);
  const balanceLabel = getBalanceLabel(totalBalance);

  return (
    <Dialog>
      <DialogTrigger asChild>
        <Button size="sm" variant="outline">
          <Eye className="w-4 h-4 mr-1" />
          View
        </Button>
      </DialogTrigger>
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Invoice Details</DialogTitle>
        </DialogHeader>
        <div className="space-y-6">
          {/* Company Header */}
          <div className="text-center border-b pb-4">
            <h1 className="text-2xl font-bold gold-gradient bg-clip-text text-transparent">
              Kavipushp Jewels
            </h1>
            <p className="text-sm text-muted-foreground">Premium Bridal Jewelry Rentals</p>
            <p className="text-lg font-semibold mt-2">{getTypeLabel(invoice.type)}</p>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <h3 className="font-semibold mb-2">Invoice Information</h3>
              <p><strong>Invoice No:</strong> {invoice.invoiceNumber}</p>
              <p><strong>Type:</strong> {getTypeLabel(invoice.type)}</p>
              <p><strong>Date:</strong> {new Date(invoice.date).toLocaleDateString()}</p>
              <div className="mt-2">
                <Badge className={getStatusColor(invoice.status)}>
                  {invoice.status.toUpperCase()}
                </Badge>
              </div>
            </div>
            <div>
              <h3 className="font-semibold mb-2">Customer Details</h3>
              <p><strong>Name:</strong> {invoice.customerName}</p>
            </div>
          </div>

          <div className="bg-primary/10 p-4 rounded-lg">
            <h3 className="font-semibold mb-3">Amount Breakdown</h3>
            <div className="space-y-2">
              <div className="flex justify-between items-center">
                <span className="min-w-[120px]">Rent:</span>
                <span className="font-medium">₹{invoice.amount.toLocaleString()}</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="min-w-[120px]">Booking Amount:</span>
                <span className="font-medium">₹{bookingAmount.toLocaleString()}</span>
              </div>
              <div className="flex justify-between items-center">
                <span className="min-w-[120px]">Security:</span>
                <span className="font-medium">₹{securityAmount.toLocaleString()}</span>
              </div>
              <div className="border-t pt-2 flex justify-between items-center">
                <span className="font-semibold min-w-[120px]">{balanceLabel}:</span>
                <span className={`text-xl font-bold ${totalBalance < 0 ? 'text-green-600' : 'text-primary'}`}>
                  ₹{Math.abs(totalBalance).toLocaleString()}
                </span>
              </div>
            </div>
          </div>

          {/* Signature Section */}
          <div className="flex justify-between items-center pt-4 border-t">
            <div className="text-center">
              <div className="border-b border-gray-400 w-32 mb-2"></div>
              <p className="text-sm font-medium">Customer Signature</p>
            </div>
            <div className="text-center">
              <div className="border-b border-gray-400 w-32 mb-2"></div>
              <p className="text-sm font-medium">Authorized Executive</p>
            </div>
          </div>

          <div className="flex gap-2">
            <Button onClick={handleDownload} className="flex-1">
              <Download className="w-4 h-4 mr-2" />
              Download PDF
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  );
}
