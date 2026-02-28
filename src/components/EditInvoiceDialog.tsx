
import { useState } from 'react';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Edit } from 'lucide-react';
import { toast } from '@/hooks/use-toast';

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

interface EditInvoiceDialogProps {
  invoice: Invoice;
  onInvoiceUpdated: (updatedInvoice: Invoice) => void;
}

export function EditInvoiceDialog({ invoice, onInvoiceUpdated }: EditInvoiceDialogProps) {
  const [open, setOpen] = useState(false);
  const [formData, setFormData] = useState({
    invoiceNumber: invoice.invoiceNumber,
    customerName: invoice.customerName,
    type: invoice.type,
    amount: invoice.amount,
    status: invoice.status,
    securityAmount: invoice.securityAmount || 0,
    bookingAmount: invoice.bookingAmount || 0,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    
    const updatedInvoice: Invoice = {
      ...invoice,
      invoiceNumber: formData.invoiceNumber,
      customerName: formData.customerName,
      type: formData.type,
      amount: formData.amount,
      status: formData.status,
      securityAmount: formData.securityAmount,
      bookingAmount: formData.bookingAmount,
    };

    onInvoiceUpdated(updatedInvoice);
    setOpen(false);
    
    toast({
      title: "Invoice Updated",
      description: "Invoice has been updated successfully.",
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
      <DialogContent className="max-w-2xl">
        <DialogHeader>
          <DialogTitle>Edit Invoice - {invoice.invoiceNumber}</DialogTitle>
        </DialogHeader>
        <form onSubmit={handleSubmit} className="space-y-4">
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="invoiceNumber">Invoice Number *</Label>
              <Input
                id="invoiceNumber"
                value={formData.invoiceNumber}
                onChange={(e) => setFormData({ ...formData, invoiceNumber: e.target.value })}
                required
              />
            </div>
            
            <div>
              <Label htmlFor="customerName">Customer Name *</Label>
              <Input
                id="customerName"
                value={formData.customerName}
                onChange={(e) => setFormData({ ...formData, customerName: e.target.value })}
                required
              />
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <Label htmlFor="type">Invoice Type</Label>
              <Select value={formData.type} onValueChange={(value: 'booking' | 'pickup' | 'return') => setFormData({ ...formData, type: value })}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="booking">Booking</SelectItem>
                  <SelectItem value="pickup">Pickup</SelectItem>
                  <SelectItem value="return">Return</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div>
              <Label htmlFor="status">Status</Label>
              <Select value={formData.status} onValueChange={(value: 'paid' | 'pending' | 'overdue') => setFormData({ ...formData, status: value })}>
                <SelectTrigger>
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="pending">Pending</SelectItem>
                  <SelectItem value="paid">Paid</SelectItem>
                  <SelectItem value="overdue">Overdue</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <Label htmlFor="amount">Rent Amount (₹)</Label>
              <Input
                id="amount"
                type="number"
                value={formData.amount}
                onChange={(e) => setFormData({ ...formData, amount: Number(e.target.value) })}
                min="0"
              />
            </div>
            
            <div>
              <Label htmlFor="bookingAmount">Booking Amount (₹)</Label>
              <Input
                id="bookingAmount"
                type="number"
                value={formData.bookingAmount}
                onChange={(e) => setFormData({ ...formData, bookingAmount: Number(e.target.value) })}
                min="0"
              />
            </div>
            
            <div>
              <Label htmlFor="securityAmount">Security Amount (₹)</Label>
              <Input
                id="securityAmount"
                type="number"
                value={formData.securityAmount}
                onChange={(e) => setFormData({ ...formData, securityAmount: Number(e.target.value) })}
                min="0"
              />
            </div>
          </div>

          <div className="flex gap-2 justify-end">
            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
              Cancel
            </Button>
            <Button type="submit" className="gold-gradient text-white">
              Update Invoice
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>
  );
}
