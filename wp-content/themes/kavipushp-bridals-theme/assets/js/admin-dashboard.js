/**
 * Kavipushp Admin Dashboard JavaScript
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        initSearchFilter();
        initDateFilters();
        initPrintInvoice();
    });

    /**
     * Search Filter for Customers
     */
    function initSearchFilter() {
        $('#search-customer, #search-customers').on('input', function() {
            var query = $(this).val().toLowerCase();
            var $table = $(this).closest('.kp-card').find('.kp-table tbody');

            $table.find('tr').each(function() {
                var name = $(this).find('td:first').text().toLowerCase();
                if (name.indexOf(query) > -1 || query === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });

            updateTableInfo();
        });
    }

    /**
     * Date Range Filters
     */
    function initDateFilters() {
        $('#date-from, #date-to').on('change', function() {
            var dateFrom = $('#date-from').val();
            var dateTo = $('#date-to').val();
            var $table = $('#bookings-table tbody');

            if (!dateFrom && !dateTo) {
                $table.find('tr').show();
                updateTableInfo();
                return;
            }

            $table.find('tr').each(function() {
                var functionDate = $(this).find('td:eq(2)').text();
                if (functionDate === '-') {
                    $(this).show();
                    return;
                }

                // Parse date (DD/MM/YYYY format)
                var parts = functionDate.split('/');
                var date = new Date(parts[2], parts[1] - 1, parts[0]);

                var show = true;
                if (dateFrom && date < new Date(dateFrom)) {
                    show = false;
                }
                if (dateTo && date > new Date(dateTo)) {
                    show = false;
                }

                $(this).toggle(show);
            });

            updateTableInfo();
        });
    }

    /**
     * Update Table Info Count
     */
    function updateTableInfo() {
        var $table = $('#bookings-table');
        var visible = $table.find('tbody tr:visible').length;
        var total = $table.find('tbody tr').length;

        $('.kp-table-info').text('Showing ' + visible + ' of ' + total + ' bookings');
    }

    /**
     * Print Invoice
     */
    function initPrintInvoice() {
        window.kavipushpPrintInvoice = function(bookingId) {
            // AJAX call to get invoice data
            $.ajax({
                url: kavipushp_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'kavipushp_get_invoice',
                    nonce: kavipushp_admin.nonce,
                    booking_id: bookingId
                },
                success: function(response) {
                    if (response.success) {
                        printInvoiceWindow(response.data);
                    } else {
                        alert('Error loading invoice');
                    }
                },
                error: function() {
                    alert('Error loading invoice');
                }
            });
        };
    }

    /**
     * Open Print Window
     */
    function printInvoiceWindow(data) {
        var printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Invoice #${data.invoice_number}</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 40px; max-width: 800px; margin: 0 auto; }
                    .header { text-align: center; border-bottom: 2px solid #c9a86c; padding-bottom: 20px; margin-bottom: 30px; }
                    .header h1 { color: #1a1f36; margin: 0 0 5px 0; }
                    .header p { color: #666; margin: 0; }
                    .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px; }
                    .info-block h3 { color: #c9a86c; margin: 0 0 10px 0; font-size: 14px; text-transform: uppercase; }
                    .info-block p { margin: 5px 0; color: #333; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
                    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
                    th { background: #f5f5f5; font-weight: 600; }
                    .total-row { font-weight: bold; font-size: 18px; }
                    .total-row td { border-top: 2px solid #c9a86c; }
                    .footer { text-align: center; color: #666; font-size: 12px; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; }
                    @media print { body { padding: 20px; } }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Kavipushp Jewels Rental</h1>
                    <p>Premium Bridal Jewelry Rentals</p>
                </div>

                <h2 style="text-align: center; color: #1a1f36;">INVOICE</h2>
                <p style="text-align: center; color: #666;">Invoice #: ${data.invoice_number} | Date: ${data.date}</p>

                <div class="info-grid">
                    <div class="info-block">
                        <h3>Bill To</h3>
                        <p><strong>${data.customer_name}</strong></p>
                        <p>${data.customer_phone}</p>
                        <p>${data.customer_email}</p>
                    </div>
                    <div class="info-block">
                        <h3>Rental Period</h3>
                        <p><strong>Pickup:</strong> ${data.pickup_date}</p>
                        <p><strong>Return:</strong> ${data.return_date}</p>
                        <p><strong>Duration:</strong> ${data.days} days</p>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Set ID</th>
                            <th>Rate/Day</th>
                            <th>Days</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${data.set_name}</td>
                            <td>${data.set_id}</td>
                            <td>₹${data.rate_per_day}</td>
                            <td>${data.days}</td>
                            <td>₹${data.rental_amount}</td>
                        </tr>
                        <tr>
                            <td colspan="4">Security Deposit (Refundable)</td>
                            <td>₹${data.deposit}</td>
                        </tr>
                        <tr class="total-row">
                            <td colspan="4">Grand Total</td>
                            <td>₹${data.total}</td>
                        </tr>
                    </tbody>
                </table>

                <div class="footer">
                    <p>Thank you for choosing Kavipushp Jewels Rental!</p>
                    <p>Terms & Conditions apply. Security deposit will be refunded upon safe return of jewelry.</p>
                </div>

                <script>window.onload = function() { window.print(); }</script>
            </body>
            </html>
        `);
        printWindow.document.close();
    }

    /**
     * Export Customers to Excel/CSV
     */
    window.kavipushpExportCustomers = function() {
        var table = document.querySelector('.kp-table');
        if (!table) {
            alert('No customer data to export');
            return;
        }

        var csv = [];
        var rows = table.querySelectorAll('tr');

        for (var i = 0; i < rows.length; i++) {
            var row = [];
            var cols = rows[i].querySelectorAll('td, th');

            for (var j = 0; j < cols.length - 1; j++) { // Skip actions column
                var text = cols[j].innerText.replace(/,/g, ' ').replace(/\n/g, ' ').trim();
                row.push('"' + text + '"');
            }
            csv.push(row.join(','));
        }

        downloadCSV(csv.join('\n'), 'kavipushp_customers_' + getDateString() + '.csv');
    };

    /**
     * Export Bookings to Excel/CSV
     */
    window.kavipushpExportBookings = function() {
        var cards = document.querySelectorAll('.kp-booking-card');
        if (!cards || cards.length === 0) {
            alert('No booking data to export');
            return;
        }

        var csv = [];
        // Header
        csv.push('"Customer Name","Phone","Booking ID","Function Date","Pickup Date","Return Date","Items","Status","Total Amount"');

        cards.forEach(function(card) {
            var customerName = card.querySelector('.kp-booking-customer h3')?.innerText || '';
            var bookingInfo = card.querySelector('.kp-booking-id')?.innerText || '';
            var phone = bookingInfo.match(/Contact: ([^\|]+)/)?.[1]?.trim() || '';
            var bookingId = bookingInfo.match(/Booking ID: ([^\|]+)/)?.[1]?.trim() || '';

            var dates = card.querySelectorAll('.kp-date-item .kp-value');
            var functionDate = dates[0]?.innerText || '';
            var pickupDate = dates[1]?.innerText || '';
            var returnDate = dates[2]?.innerText || '';

            var items = card.querySelector('.kp-booking-items')?.innerText?.replace(/\n/g, ', ') || '';
            var status = card.querySelector('.kp-status-badge')?.innerText || '';
            var total = card.querySelector('.kp-booking-total .kp-price')?.innerText || '';

            var row = [
                '"' + customerName + '"',
                '"' + phone + '"',
                '"' + bookingId + '"',
                '"' + functionDate + '"',
                '"' + pickupDate + '"',
                '"' + returnDate + '"',
                '"' + items.replace(/"/g, '""') + '"',
                '"' + status + '"',
                '"' + total + '"'
            ];
            csv.push(row.join(','));
        });

        downloadCSV(csv.join('\n'), 'kavipushp_bookings_' + getDateString() + '.csv');
    };

    /**
     * Export Inventory to Excel/CSV
     */
    window.kavipushpExportInventory = function() {
        var cards = document.querySelectorAll('.kp-inv-card');
        if (!cards || cards.length === 0) {
            alert('No inventory data to export');
            return;
        }

        var csv = [];
        // Header
        csv.push('"Set Name","Category","Set ID","Price Per Day"');

        cards.forEach(function(card) {
            var name = card.querySelector('.kp-inv-name')?.innerText || '';
            var category = card.querySelector('.kp-inv-category')?.innerText || '';
            var setId = card.querySelector('.kp-inv-id')?.innerText?.replace('ID: ', '') || '';
            var price = card.querySelector('.kp-inv-price')?.innerText?.replace('/day', '').trim() || '';

            var row = [
                '"' + name + '"',
                '"' + category + '"',
                '"' + setId + '"',
                '"' + price + '"'
            ];
            csv.push(row.join(','));
        });

        downloadCSV(csv.join('\n'), 'kavipushp_inventory_' + getDateString() + '.csv');
    };

    /**
     * Download CSV file
     */
    function downloadCSV(csv, filename) {
        var blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');

        if (navigator.msSaveBlob) {
            // IE 10+
            navigator.msSaveBlob(blob, filename);
        } else {
            link.href = URL.createObjectURL(blob);
            link.download = filename;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }

    /**
     * Get date string for filename
     */
    function getDateString() {
        var d = new Date();
        return d.getFullYear() + '-' +
               String(d.getMonth() + 1).padStart(2, '0') + '-' +
               String(d.getDate()).padStart(2, '0');
    }

})(jQuery);
