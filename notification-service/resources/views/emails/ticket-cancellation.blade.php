<!DOCTYPE html>
<html>
<head>
    <title>Ticket Cancellation Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8425f;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .ticket-details {
            background-color: #f8f9fa;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #f8425f;
        }
        .info-list {
            background-color: #fff8f9;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .info-list li {
            margin-bottom: 10px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            text-align: center;
            font-size: 0.9em;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Ticket Cancellation Confirmation</h1>
    </div>
    
    <p>Dear Customer,</p>
    
    <p>We have successfully processed your ticket cancellation request. Here are the details of your cancelled ticket:</p>
    
    <div class="ticket-details">
        <p><strong>Ticket Number:</strong> {{ $ticket['ticket_number'] }}</p>
        <p><strong>Event:</strong> {{ $ticket['event']['title'] ?? 'N/A' }}</p>
        <p><strong>Original Purchase Date:</strong> {{ date('F j, Y, g:i a', strtotime($ticket['purchase_date'])) }}</p>
        <p><strong>Cancellation Date:</strong> {{ date('F j, Y, g:i a', strtotime($ticket['cancelled_at'])) }}</p>
        <p><strong>Refund Amount:</strong> ${{ number_format($ticket['price'], 2) }}</p>
    </div>

    <div class="info-list">
        <h3>Important Information:</h3>
        <ul>
            <li>Your refund of ${{ number_format($ticket['price'], 2) }} will be processed to your original payment method</li>
            <li>Refunds typically take 5-10 business days to appear on your statement</li>
            <li>This ticket is now void and cannot be used for entry</li>
            <li>A confirmation of the refund will be sent separately once processed</li>
        </ul>
    </div>

    <p>If you have any questions about your refund or need further assistance, our support team is here to help:</p>
    <ul>
        <li>Email: support@ticketing.com</li>
        <li>Phone: 1-800-TICKETS (1-800-842-5387)</li>
        <li>Support Hours: Monday - Friday, 9 AM - 6 PM EST</li>
    </ul>

    <div class="footer">
        <p>Thank you for using our service.<br>Your Ticketing Team</p>
        <small>This is an automated message, please do not reply directly to this email.</small>
    </div>
</body>
</html>
