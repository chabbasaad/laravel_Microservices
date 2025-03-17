<!DOCTYPE html>
<html>
<head>
    <title>Ticket Purchase Confirmation</title>
</head>
<body>
    <h1>Thank You for Your Purchase!</h1>
    
    <p>Dear Customer,</p>
    
    <p>Your ticket has been successfully purchased. Here are your ticket details:</p>
    
    <div style="background-color: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px;">
        <p><strong>Ticket Number:</strong> {{ $ticket['ticket_number'] }}</p>
        <p><strong>Event:</strong> {{ $ticket['event']['title'] ?? 'N/A' }}</p>
        <p><strong>Date:</strong> {{ $ticket['event']['date'] ?? 'N/A' }}</p>
        <p><strong>Location:</strong> {{ $ticket['event']['location'] ?? 'N/A' }}</p>
        <p><strong>Price:</strong> ${{ number_format($ticket['price'], 2) }}</p>
        <p><strong>Purchase Date:</strong> {{ $ticket['purchase_date'] }}</p>
    </div>

    <p>Important Notes:</p>
    <ul>
        <li>Please keep this ticket information safe</li>
        <li>You may be required to show ID matching the ticket details at the event</li>
        <li>This ticket is non-transferable</li>
    </ul>

    <p>If you have any questions, please don't hesitate to contact our support team.</p>

    <p>Best regards,<br>Your Ticketing Team</p>
</body>
</html>
