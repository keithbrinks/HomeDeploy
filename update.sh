#!/bin/bash

set -e

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
NC='\033[0m'

cd /opt/homedeploy

echo -e "${BLUE}Pulling latest changes...${NC}"
git pull origin main

echo -e "${BLUE}Restarting services...${NC}"
sudo systemctl restart php8.2-fpm
sudo systemctl restart homedeploy-queue

echo -e "${GREEN}HomeDeploy updated successfully!${NC}"
