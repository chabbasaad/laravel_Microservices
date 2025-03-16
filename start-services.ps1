# Kill any existing Node.js processes
Write-Host "Stopping any running Node.js processes..." -ForegroundColor Yellow
taskkill /F /IM node.exe 2>$null
if ($?) {
    Write-Host "Node processes terminated successfully." -ForegroundColor Green
} else {
    Write-Host "No active Node processes found." -ForegroundColor Gray
}

# Wait a moment for processes to fully terminate
Start-Sleep -Seconds 2

# Start MongoDB if needed
Write-Host "Starting MongoDB..." -ForegroundColor Cyan
Start-Process mongod -NoNewWindow

# Wait for MongoDB to initialize
Write-Host "Waiting for MongoDB to initialize..." -ForegroundColor Cyan
Start-Sleep -Seconds 5

# Define service directories
$baseDir = "C:\Users\HP\Desktop\Pro\micro_web"
$services = @("auth-service", "frontend", "incident-service", "map-service","route-service","notification-service")

# Start each service in its own terminal window
foreach ($service in $services) {
    $dir = "$baseDir\$service"
    Write-Host "Starting service in $service" -ForegroundColor Green
    Start-Process cmd -ArgumentList "/k cd /d $dir && npm run dev"
}