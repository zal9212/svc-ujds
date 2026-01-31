if (-NOT ([Security.Principal.WindowsPrincipal][Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator"))  
{  
  Write-Warning "Please run this script as Administrator!"  
  Break  
} 

Write-Host "Configuring Windows Firewall for Web Server..."

# Allow HTTP
New-NetFirewallRule -DisplayName "Allow HTTP (svc-ujds)" -Direction Inbound -LocalPort 80 -Protocol TCP -Action Allow

# Allow HTTPS
New-NetFirewallRule -DisplayName "Allow HTTPS (svc-ujds)" -Direction Inbound -LocalPort 443 -Protocol TCP -Action Allow

Write-Host "Firewall rules updated."
