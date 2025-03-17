# Kill any existing PHP processes
Write-Host "Stopping any running PHP processes..." -ForegroundColor Yellow
taskkill /F /IM php.exe 2>$null
if ($?) {
    Write-Host "PHP processes terminated successfully." -ForegroundColor Green
} else {
    Write-Host "No active PHP processes found." -ForegroundColor Gray
}

# Wait a moment for processes to fully terminate
Start-Sleep -Seconds 2

# Define base directory and services
$baseDir = "C:\Users\HP\Desktop\ticketing"
$services = @{
    "api-gateway" = 8000  # Default Laravel port
    "auth-service" = 8001
    "user-service" = 8002
    "event-service" = 8003
    "ticket-service" = 8004
    "notification-service" = 8005
    # Add more services as needed with their respective ports
    # "service-name" = port_number
}

# Start services
foreach ($service in $services.GetEnumerator()) {
    $serviceName = $service.Key
    $port = $service.Value
    $dir = "$baseDir\$serviceName"
    
    Write-Host "Starting Laravel service: $serviceName on port $port" -ForegroundColor Green
    
    if ($port -eq 8000) {
        # Default port for api-gateway
        Start-Process cmd -ArgumentList "/k cd /d $dir && php artisan serve"
    } else {
        # Custom port for other services
        Start-Process cmd -ArgumentList "/k cd /d $dir && php artisan serve --port=$port"
    }
    
    # Give a small delay between starting services to avoid resource conflicts
    Start-Sleep -Seconds 2
}

Write-Host "All Laravel services are now running!" -ForegroundColor Cyan