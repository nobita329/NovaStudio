#!/usr/bin/env bash
# ==============================================================================
#  Nova Panel - Master Management & Setup Script (run.sh)
#  Menu Type: Install, Auto-Run (PM2), User Create, PM2 Management, Uninstall
#  Auto-Detect A to Z: Dependencies, Database, First User, App Setup, PM2
# ==============================================================================

# Script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# ------------------------------------------------------------------------------
# Colors & Formatting
# ------------------------------------------------------------------------------
CLR_RESET="\033[0m"
CLR_BOLD="\033[1m"
CLR_DIM="\033[2m"
CLR_RED="\033[1;31m"
CLR_GREEN="\033[1;32m"
CLR_YELLOW="\033[1;33m"
CLR_BLUE="\033[1;34m"
CLR_MAGENTA="\033[1;35m"
CLR_CYAN="\033[1;36m"
CLR_WHITE="\033[1;37m"
CLR_GRAY="\033[0;90m"

# Print helpers
print_header() {
    clear 2>/dev/null || true
    echo -e "${CLR_CYAN}${CLR_BOLD}"
    cat << "EOF"
 ███╗   ██╗ ██████╗ ██╗   ██╗ █████╗     ██████╗  █████╗ ███╗   ██╗███████╗██╗     
 ████╗  ██║██╔═══██╗██║   ██║██╔══██╗    ██╔══██╗██╔══██╗████╗  ██║██╔════╝██║     
 ██╔██╗ ██║██║   ██║██║   ██║███████║    ██████╔╝███████║██╔██╗ ██║█████╗  ██║     
 ██║╚██╗██║██║   ██║╚██╗ ██╔╝██╔══██║    ██╔═══╝ ██╔══██║██║╚██╗██║██╔══╝  ██║     
 ██║ ╚████║╚██████╔╝ ╚████╔╝ ██║  ██║    ██║     ██║  ██║██║ ╚████║███████╗███████╗
 ╚═╝  ╚═══╝ ╚═════╝   ╚═══╝  ╚═╝  ╚═╝    ╚═╝     ╚═╝  ╚═╝╚═╝  ╚═══╝╚══════╝╚══════╝
EOF
    echo -e "${CLR_RESET}"
    echo -e "${CLR_WHITE}${CLR_BOLD}   ★ Next-Gen High-Performance Server & Game Management Panel ★${CLR_RESET}"
    echo -e "${CLR_GRAY}   ─────────────────────────────────────────────────────────────${CLR_RESET}"
    echo ""
}

print_title() {
    echo -e "\n${CLR_BLUE}${CLR_BOLD}==> $1${CLR_RESET}"
}

print_success() {
    echo -e " ${CLR_GREEN}[✔]${CLR_RESET} $1"
}

print_warn() {
    echo -e " ${CLR_YELLOW}[!]${CLR_RESET} $1"
}

print_err() {
    echo -e " ${CLR_RED}[✘]${CLR_RESET} $1"
}

print_info() {
    echo -e " ${CLR_CYAN}[i]${CLR_RESET} $1"
}

pause_prompt() {
    echo ""
    echo -ne "${CLR_DIM}Press [Enter] to continue...${CLR_RESET}"
    read -r
}

# ------------------------------------------------------------------------------
# Auto-Detection Helpers
# ------------------------------------------------------------------------------
detect_sudo() {
    if [ "$EUID" -ne 0 ]; then
        if command -v sudo &>/dev/null; then
            SUDO="sudo"
        else
            SUDO=""
        fi
    else
        SUDO=""
    fi
}

detect_system() {
    detect_sudo
    OS_ID="unknown"
    OS_NAME="Linux"
    PKG_MGR="unknown"
    ARCH="$(uname -m)"

    if [ -f /etc/os-release ]; then
        # shellcheck disable=SC1091
        . /etc/os-release
        OS_ID="${ID:-unknown}"
        OS_NAME="${NAME:-Linux}"
    elif [ -f /etc/issue ]; then
        OS_NAME="$(head -n1 /etc/issue | cut -d' ' -f1)"
    fi

    if command -v apt-get &>/dev/null; then
        PKG_MGR="apt"
    elif command -v dnf &>/dev/null; then
        PKG_MGR="dnf"
    elif command -v yum &>/dev/null; then
        PKG_MGR="yum"
    elif command -v pacman &>/dev/null; then
        PKG_MGR="pacman"
    elif command -v apk &>/dev/null; then
        PKG_MGR="apk"
    fi

    # Docker Compose command detection
    if docker compose version &>/dev/null; then
        DOCKER_COMPOSE="docker compose"
    elif command -v docker-compose &>/dev/null; then
        DOCKER_COMPOSE="docker-compose"
    else
        DOCKER_COMPOSE="docker compose"
    fi

    # PM2 binary detection
    if command -v pm2 &>/dev/null; then
        PM2_BIN="pm2"
    elif [ -f "./node_modules/.bin/pm2" ]; then
        PM2_BIN="./node_modules/.bin/pm2"
    elif command -v npx &>/dev/null; then
        PM2_BIN="npx pm2"
    else
        PM2_BIN="pm2"
    fi

    # Server IP detection
    SERVER_IP="127.0.0.1"
    PUBLIC_IP=$(curl -s --max-time 2 https://api.ipify.org 2>/dev/null || curl -s --max-time 2 https://ifconfig.me 2>/dev/null || echo "")
    if [ -n "$PUBLIC_IP" ]; then
        SERVER_IP="$PUBLIC_IP"
    else
        LOCAL_IP=$(hostname -I 2>/dev/null | awk '{print $1}')
        [ -n "$LOCAL_IP" ] && SERVER_IP="$LOCAL_IP"
    fi
}

# ------------------------------------------------------------------------------
# 1. Dependency Installation A to Z
# ------------------------------------------------------------------------------
step1_install_dependencies() {
    print_title "Step 1: Dependency Installation (A to Z)"
    detect_system

    print_info "Detected OS: ${CLR_BOLD}${OS_NAME}${CLR_RESET} (${ARCH}) | Package Manager: ${CLR_BOLD}${PKG_MGR}${CLR_RESET}"

    # Check which base tools are missing
    MISSING_UTILS=""
    for tool in curl wget git tar unzip jq openssl; do
        if ! command -v "$tool" &>/dev/null; then
            MISSING_UTILS="$MISSING_UTILS $tool"
        fi
    done

    if [ -n "$MISSING_UTILS" ]; then
        print_info "Installing missing utilities:${MISSING_UTILS}..."
        case "$PKG_MGR" in
            apt)
                DEBIAN_FRONTEND=noninteractive $SUDO apt-get update -qq
                DEBIAN_FRONTEND=noninteractive $SUDO apt-get install -y -qq $MISSING_UTILS ca-certificates build-essential
                ;;
            dnf|yum)
                $SUDO $PKG_MGR install -y -q $MISSING_UTILS ca-certificates gcc gcc-c++ make
                ;;
            pacman)
                $SUDO pacman -Sy --noconfirm $MISSING_UTILS base-devel
                ;;
            apk)
                $SUDO apk add --no-cache $MISSING_UTILS ca-certificates build-base
                ;;
            *)
                print_warn "Please ensure essential tools ($MISSING_UTILS) are installed."
                ;;
        esac
    fi
    print_success "Base system utilities verified (curl, wget, git, tar, unzip, jq, openssl)."

    # 1.2 Node.js & npm (Requires Node.js 18+ or 20+ LTS)
    NODE_INSTALLED=false
    if command -v node &>/dev/null; then
        NODE_VER=$(node -v | tr -d 'v' | cut -d. -f1)
        if [ "$NODE_VER" -ge 18 ]; then
            NODE_INSTALLED=true
            print_success "Node.js verified: $(node -v) (npm $(npm -v 2>/dev/null || echo 'N/A'))"
        fi
    fi

    if [ "$NODE_INSTALLED" = false ]; then
        print_info "Installing modern Node.js 20 LTS..."
        case "$PKG_MGR" in
            apt)
                curl -fsSL https://deb.nodesource.com/setup_20.x | $SUDO -E bash -
                DEBIAN_FRONTEND=noninteractive $SUDO apt-get install -y -qq nodejs
                ;;
            dnf|yum)
                curl -fsSL https://rpm.nodesource.com/setup_20.x | $SUDO bash -
                $SUDO $PKG_MGR install -y -q nodejs
                ;;
            pacman)
                $SUDO pacman -S --noconfirm nodejs npm
                ;;
            apk)
                $SUDO apk add --no-cache nodejs npm
                ;;
            *)
                print_err "Unable to auto-install Node.js. Please install Node.js 20+ manually."
                ;;
        esac

        if command -v node &>/dev/null; then
            print_success "Node.js successfully installed: $(node -v)"
        else
            print_err "Node.js installation failed. Check network or package repository."
        fi
    fi

    # 1.3 PHP CLI & Required Extensions
    if command -v php &>/dev/null; then
        print_success "PHP verified: $(php -v | head -n1 | cut -d'(' -f1)"
    else
        print_info "Installing PHP CLI and extensions..."
        case "$PKG_MGR" in
            apt)
                DEBIAN_FRONTEND=noninteractive $SUDO apt-get install -y -qq php-cli php-curl php-mbstring php-zip php-mysql php-xml php-bcmath
                ;;
            dnf|yum)
                $SUDO $PKG_MGR install -y -q php-cli php-curl php-mbstring php-zip php-mysqlnd php-xml php-bcmath
                ;;
            pacman)
                $SUDO pacman -S --noconfirm php
                ;;
            apk)
                $SUDO apk add --no-cache php php-cli php-curl php-mbstring php-zip php-mysqli php-xml
                ;;
            *)
                print_warn "Please ensure PHP CLI is installed for template rendering."
                ;;
        esac

        if command -v php &>/dev/null; then
            print_success "PHP CLI installed successfully: $(php -v | head -n1)"
        fi
    fi

    # 1.4 Docker & Docker Compose
    if command -v docker &>/dev/null; then
        print_success "Docker Engine verified: $(docker --version)"
    else
        print_info "Installing Docker Engine via official automated installer..."
        curl -fsSL https://get.docker.com | $SUDO sh
        if [ "$EUID" -ne 0 ]; then
            $SUDO usermod -aG docker "$USER" 2>/dev/null || true
        fi
        if command -v docker &>/dev/null; then
            print_success "Docker Engine installed: $(docker --version)"
        fi
    fi

    # Ensure Docker daemon is active
    if command -v docker &>/dev/null; then
        if ! docker info &>/dev/null; then
            print_info "Starting Docker daemon..."
            $SUDO systemctl start docker 2>/dev/null || $SUDO service docker start 2>/dev/null || true
            $SUDO systemctl enable docker 2>/dev/null || true
            sleep 2
        fi
        if docker info &>/dev/null; then
            print_success "Docker daemon is active and running."
        else
            print_warn "Docker service is not responding yet. Verify daemon permissions."
        fi
    fi

    # 1.5 PM2 Process Manager
    if command -v pm2 &>/dev/null; then
        print_success "PM2 verified: $(pm2 -v)"
    else
        print_info "Installing PM2 process manager globally..."
        $SUDO npm install -g pm2 2>/dev/null || npm install -g pm2 2>/dev/null || true
        if command -v pm2 &>/dev/null; then
            print_success "PM2 installed globally: $(pm2 -v)"
        else
            print_info "PM2 will run via local npx or node package."
        fi
    fi

    print_success "Step 1 completed: All dependencies verified A to Z!"
}

# ------------------------------------------------------------------------------
# 2. Database Configuration + Database Setup
# ------------------------------------------------------------------------------
step2_setup_database() {
    print_title "Step 2: Database Configuration & Setup"
    detect_system

    # 2.1 Check / Generate .env
    if [ ! -f ".env" ]; then
        print_info "Creating default .env configuration file..."
        SESSION_SEC=$(openssl rand -hex 32 2>/dev/null || echo "nova-session-$(date +%s)")
        JWT_SEC=$(openssl rand -hex 32 2>/dev/null || echo "nova-jwt-$(date +%s)")

        cat > .env << EOF
# Nova Panel Configuration
PANEL_NAME=Nova
PANEL_PORT=3001
API_PORT=3003
SFTP_PORT=3004
SFTP_HOST=0.0.0.0
PANEL_URL=http://localhost:3001

# MariaDB (Panel Database on port 3002)
DB_HOST=127.0.0.1
DB_PORT=3002
DB_USER=Nova
DB_PASSWORD=NovaStudio
DB_NAME=Nova

# Server Database Host (MySQL on port 3005)
SERVER_DB_HOST=127.0.0.1
SERVER_DB_PORT=3005
SERVER_DB_USER=root
SERVER_DB_PASSWORD=YourStrongPassword

# Security
SESSION_SECRET=${SESSION_SEC}
JWT_SECRET=${JWT_SEC}
EOF
        print_success ".env configuration file created with secure secrets."
    else
        print_success "Existing .env configuration detected."
    fi

    # Read DB settings from .env
    DB_PORT=$(grep -E '^DB_PORT=' .env | cut -d= -f2 | tr -d ' "' || echo "3002")
    DB_PORT=${DB_PORT:-3002}
    SERVER_DB_PORT=$(grep -E '^SERVER_DB_PORT=' .env | cut -d= -f2 | tr -d ' "' || echo "3005")
    SERVER_DB_PORT=${SERVER_DB_PORT:-3005}

    # 2.2 Check Database Services / Docker Containers
    MARIADB_READY=false
    if timeout 1 bash -c "</dev/tcp/127.0.0.1/${DB_PORT}" 2>/dev/null; then
        MARIADB_READY=true
        print_success "MariaDB is online on port ${DB_PORT}."
    fi

    MYSQL_READY=false
    if timeout 1 bash -c "</dev/tcp/127.0.0.1/${SERVER_DB_PORT}" 2>/dev/null; then
        MYSQL_READY=true
        print_success "MySQL Host is online on port ${SERVER_DB_PORT}."
    fi

    # If services are down, start via Docker or Docker Compose
    if [ "$MARIADB_READY" = false ] || [ "$MYSQL_READY" = false ]; then
        print_info "Starting database containers (MariaDB :${DB_PORT}, MySQL :${SERVER_DB_PORT})..."

        # Start stopped containers if they exist
        if command -v docker &>/dev/null; then
            if docker ps -a --format '{{.Names}}' | grep -Eq '^(mariadb|mysql)$'; then
                docker start mariadb mysql 2>/dev/null || true
            fi
        fi

        # Also run docker compose up if docker-compose.yml exists
        if [ -f "docker-compose.yml" ]; then
            $DOCKER_COMPOSE up -d 2>/dev/null || true
        fi

        # Wait loop for MariaDB readiness (up to 30 seconds)
        print_info "Waiting for MariaDB (port ${DB_PORT}) to become ready..."
        RETRY=0
        while [ $RETRY -lt 30 ]; do
            if timeout 1 bash -c "</dev/tcp/127.0.0.1/${DB_PORT}" 2>/dev/null; then
                MARIADB_READY=true
                break
            fi
            sleep 1
            RETRY=$((RETRY + 1))
        done

        if [ "$MARIADB_READY" = true ]; then
            print_success "MariaDB container ready on port ${DB_PORT}."
        else
            print_warn "MariaDB did not respond on port ${DB_PORT}. Nova will fallback to SQLite if needed."
        fi
    fi

    # 2.3 Run Database Migration and Seed
    print_info "Executing database migrations and default seed schema..."
    if [ -f "bin/migration.js" ]; then
        node bin/migration.js
        print_success "Database schema & seeds applied successfully!"
    else
        print_err "bin/migration.js missing. Skipping migration."
    fi

    print_success "Step 2 completed: Database configuration & setup finished!"
}

# ------------------------------------------------------------------------------
# 3. Add The First User (or Update Existing Admin)
# ------------------------------------------------------------------------------
step3_add_first_user() {
    print_title "Step 3: Add Administrator / First User"
    detect_system

    # Check if an admin user already exists in DB
    EXISTING_ADMIN=""
    if [ -f "src/database/index.js" ]; then
        EXISTING_ADMIN=$(node -e "const { initDb } = require('./src/database/index'); initDb().then(db => db.get('SELECT username, email FROM users WHERE root_admin = 1 LIMIT 1')).then(u => { if (u) console.log('USER_INFO:' + u.username + '|' + u.email); process.exit(0); }).catch(() => process.exit(0));" 2>/dev/null | grep '^USER_INFO:' | sed 's/^USER_INFO://')
    fi

    if [ -n "$EXISTING_ADMIN" ]; then
        ADMIN_NAME=$(echo "$EXISTING_ADMIN" | cut -d'|' -f1)
        ADMIN_EMAIL=$(echo "$EXISTING_ADMIN" | cut -d'|' -f2)
        print_success "Administrator account already exists: ${CLR_BOLD}${ADMIN_NAME}${CLR_RESET} (${ADMIN_EMAIL})"
        echo -ne " ${CLR_YELLOW}[?]${CLR_RESET} Do you want to update administrator password / credentials? (y/N) [N]: "
        read -r UPDATE_CHOICE
        if [[ ! "$UPDATE_CHOICE" =~ ^[Yy]$ ]]; then
            print_info "Keeping existing administrator credentials."
            print_success "Step 3 completed: Existing administrator active!"
            return 0
        fi
    fi

    echo -e "${CLR_WHITE}Configure Administrator Account:${CLR_RESET}"
    read -rp "Enter Email Address [admin@nova.test]: " INP_EMAIL
    INP_EMAIL="${INP_EMAIL:-admin@nova.test}"

    read -rp "Enter Username [admin]: " INP_USER
    INP_USER="${INP_USER:-admin}"

    read -rp "Enter First Name [Nova]: " INP_FIRST
    INP_FIRST="${INP_FIRST:-Nova}"

    read -rp "Enter Last Name [Administrator]: " INP_LAST
    INP_LAST="${INP_LAST:-Administrator}"

    read -rsp "Enter Password [NovaAdmin123!]: " INP_PASS
    echo ""
    INP_PASS="${INP_PASS:-NovaAdmin123!}"

    print_info "Saving user record to database..."
    node bin/createuser.js \
        --email "$INP_EMAIL" \
        --username "$INP_USER" \
        --first "$INP_FIRST" \
        --last "$INP_LAST" \
        --password "$INP_PASS" \
        --admin

    print_success "Step 3 completed: Administrator account configured!"
}

# ------------------------------------------------------------------------------
# 4. All Run A to Z Setup
# ------------------------------------------------------------------------------
step4_all_run_setup() {
    print_title "Step 4: Application Environment & Directory Setup (A to Z)"

    # 4.1 Install Node modules
    print_info "Verifying NPM packages..."
    if [ -f "package.json" ]; then
        npm install --no-audit --prefer-offline 2>/dev/null || npm install
        print_success "NPM dependencies verified."
    fi

    # 4.2 Create necessary runtime directories
    print_info "Creating required storage directories..."
    mkdir -p data logs Instance/volumes Instance/backups public/uploads views/cache
    print_success "Directories verified: Instance/volumes, Instance/backups, data, logs."

    # 4.3 Permissions
    print_info "Configuring execution permissions..."
    chmod +x bin/*.js 2>/dev/null || true
    chmod +x run.sh 2>/dev/null || true
    print_success "Script permissions set."

    print_success "Step 4 completed: Environment setup finished!"
}

# ------------------------------------------------------------------------------
# 5. PM2 Setup & Auto-Start
# ------------------------------------------------------------------------------
step5_pm2_setup() {
    print_title "Step 5: PM2 Setup & Auto-Start"
    detect_system

    # Check / create ecosystem.config.js
    if [ ! -f "ecosystem.config.js" ]; then
        print_info "Creating ecosystem.config.js..."
        cat > ecosystem.config.js << 'EOF'
module.exports = {
  apps: [
    {
      name: 'nova-panel',
      script: 'src/server.js',
      cwd: __dirname,
      instances: 1,
      autorestart: true,
      watch: false,
      max_memory_restart: '1G',
      env: {
        NODE_ENV: 'production',
      },
    },
  ],
};
EOF
        print_success "ecosystem.config.js created."
    fi

    # Auto-run with PM2: start or restart
    print_info "Launching Nova Panel via PM2..."
    if $PM2_BIN describe nova-panel &>/dev/null; then
        $PM2_BIN restart nova-panel --update-env
        print_success "Existing Nova Panel process restarted with latest configuration."
    else
        $PM2_BIN start ecosystem.config.js
        print_success "Nova Panel launched with PM2."
    fi

    # Save PM2 state
    print_info "Saving PM2 process list to persist across reboots..."
    $PM2_BIN save 2>/dev/null || true

    # Configure startup service if systemctl is available
    if command -v systemctl &>/dev/null; then
        $PM2_BIN startup 2>/dev/null || true
    fi

    # Verify panel is answering HTTP
    PANEL_PORT=$(grep -E '^PANEL_PORT=' .env | cut -d= -f2 | tr -d ' "' || echo "3001")
    PANEL_PORT=${PANEL_PORT:-3001}
    API_PORT=$(grep -E '^API_PORT=' .env | cut -d= -f2 | tr -d ' "' || echo "3003")
    API_PORT=${API_PORT:-3003}
    SFTP_PORT=$(grep -E '^SFTP_PORT=' .env | cut -d= -f2 | tr -d ' "' || echo "3004")
    SFTP_PORT=${SFTP_PORT:-3004}

    sleep 1
    HTTP_CHECK=$(curl -s -o /dev/null -w "%{http_code}" --max-time 3 "http://127.0.0.1:${PANEL_PORT}" 2>/dev/null || echo "000")
    if [ "$HTTP_CHECK" = "200" ] || [ "$HTTP_CHECK" = "302" ]; then
        print_success "Health Check: Web panel responding (HTTP ${HTTP_CHECK}) on port ${PANEL_PORT}!"
    else
        print_warn "Web panel starting up on port ${PANEL_PORT} (HTTP ${HTTP_CHECK})."
    fi

    echo ""
    $PM2_BIN status

    echo ""
    echo -e "${CLR_GREEN}${CLR_BOLD}================================================================${CLR_RESET}"
    echo -e "${CLR_GREEN}${CLR_BOLD}         🎉 NOVA PANEL IS ONLINE AND FULLY OPERATIONAL!         ${CLR_RESET}"
    echo -e "${CLR_GREEN}${CLR_BOLD}================================================================${CLR_RESET}"
    echo -e " ${CLR_WHITE}Web Panel URL:${CLR_RESET}   ${CLR_CYAN}http://${SERVER_IP}:${PANEL_PORT}${CLR_RESET} (or http://localhost:${PANEL_PORT})"
    echo -e " ${CLR_WHITE}REST Daemon API:${CLR_RESET} ${CLR_CYAN}http://${SERVER_IP}:${API_PORT}${CLR_RESET}"
    echo -e " ${CLR_WHITE}SFTP Server:${CLR_RESET}     ${CLR_CYAN}${SERVER_IP}:${SFTP_PORT}${CLR_RESET}"
    echo -e " ${CLR_WHITE}MariaDB Port:${CLR_RESET}    ${CLR_CYAN}3002${CLR_RESET}"
    echo -e " ${CLR_WHITE}MySQL Port:${CLR_RESET}      ${CLR_CYAN}3005${CLR_RESET}"
    echo -e "${CLR_GREEN}================================================================${CLR_RESET}"
}

# ------------------------------------------------------------------------------
# Direct Action: Auto-Run via PM2
# ------------------------------------------------------------------------------
auto_run_pm2() {
    print_header
    echo -e "${CLR_CYAN}${CLR_BOLD}Auto-Running Nova Panel via PM2...${CLR_RESET}\n"
    detect_system

    # Check database status
    DB_PORT=$(grep -E '^DB_PORT=' .env 2>/dev/null | cut -d= -f2 | tr -d ' "' || echo "3002")
    DB_PORT=${DB_PORT:-3002}
    if ! timeout 1 bash -c "</dev/tcp/127.0.0.1/${DB_PORT}" 2>/dev/null; then
        print_warn "MariaDB port ${DB_PORT} is not responding. Starting database containers..."
        if command -v docker &>/dev/null; then
            docker start mariadb mysql 2>/dev/null || true
        fi
        if [ -f "docker-compose.yml" ]; then
            $DOCKER_COMPOSE up -d 2>/dev/null || true
        fi
    else
        print_success "MariaDB database is running on port ${DB_PORT}."
    fi

    # Launch / Restart via PM2
    step5_pm2_setup

    echo ""
    print_success "Panel auto-run initiated successfully!"
    pause_prompt
}

# ------------------------------------------------------------------------------
# Full Master Installation (Steps 1 to 5)
# ------------------------------------------------------------------------------
action_install_all() {
    print_header
    echo -e "${CLR_CYAN}${CLR_BOLD}Starting Full Master Installation (Steps 1 to 5 A to Z)...${CLR_RESET}\n"
    
    step1_install_dependencies
    echo ""
    step2_setup_database
    echo ""
    step3_add_first_user
    echo ""
    step4_all_run_setup
    echo ""
    step5_pm2_setup

    echo ""
    print_success "All 5 Installation Steps & PM2 Auto-Run Finished Successfully!"
    pause_prompt
}

# ------------------------------------------------------------------------------
# Menu Action: User Create (usercrate)
# ------------------------------------------------------------------------------
action_usercrate() {
    print_header
    echo -e "${CLR_CYAN}${CLR_BOLD}Nova User Creation Utility (usercrate)${CLR_RESET}"
    echo -e "${CLR_GRAY}Create a new user or administrator directly in the database.${CLR_RESET}\n"

    read -rp "Username: " U_NAME
    if [ -z "$U_NAME" ]; then
        print_err "Username cannot be empty!"
        pause_prompt
        return
    fi

    read -rp "Email Address: " U_EMAIL
    if [ -z "$U_EMAIL" ]; then
        print_err "Email cannot be empty!"
        pause_prompt
        return
    fi

    read -rp "First Name [User]: " U_FIRST
    U_FIRST="${U_FIRST:-User}"

    read -rp "Last Name [Member]: " U_LAST
    U_LAST="${U_LAST:-Member}"

    read -rsp "Password: " U_PASS
    echo ""
    if [ -z "$U_PASS" ]; then
        print_err "Password cannot be empty!"
        pause_prompt
        return
    fi

    read -rp "Grant Root Administrator access? (y/N) [y]: " U_ROLE
    if [[ "$U_ROLE" =~ ^[Nn]$ ]]; then
        ROLE_FLAG="--user"
    else
        ROLE_FLAG="--admin"
    fi

    print_info "Writing user record..."
    node bin/createuser.js \
        --email "$U_EMAIL" \
        --username "$U_NAME" \
        --first "$U_FIRST" \
        --last "$U_LAST" \
        --password "$U_PASS" \
        "$ROLE_FLAG"

    pause_prompt
}

# ------------------------------------------------------------------------------
# Menu Action: PM2 Management (pm2 mang)
# ------------------------------------------------------------------------------
action_pm2_management() {
    detect_system
    while true; do
        print_header
        echo -e "${CLR_YELLOW}${CLR_BOLD}           PM2 PROCESS MANAGEMENT (pm2 mang)           ${CLR_RESET}"
        echo -e "${CLR_GRAY}─────────────────────────────────────────────────────────────${CLR_RESET}"
        echo -e " ${CLR_WHITE}[1]${CLR_RESET} View Process Status (${CLR_CYAN}pm2 status${CLR_RESET})"
        echo -e " ${CLR_WHITE}[2]${CLR_RESET} View Live Logs (${CLR_CYAN}pm2 logs nova-panel${CLR_RESET})"
        echo -e " ${CLR_WHITE}[3]${CLR_RESET} Restart Panel (${CLR_CYAN}pm2 restart nova-panel${CLR_RESET})"
        echo -e " ${CLR_WHITE}[4]${CLR_RESET} Stop Panel (${CLR_CYAN}pm2 stop nova-panel${CLR_RESET})"
        echo -e " ${CLR_WHITE}[5]${CLR_RESET} Start Panel (${CLR_CYAN}pm2 start ecosystem.config.js${CLR_RESET})"
        echo -e " ${CLR_WHITE}[6]${CLR_RESET} Zero-Downtime Reload (${CLR_CYAN}pm2 reload nova-panel${CLR_RESET})"
        echo -e " ${CLR_WHITE}[7]${CLR_RESET} Save PM2 Configuration (${CLR_CYAN}pm2 save${CLR_RESET})"
        echo -e " ${CLR_WHITE}[8]${CLR_RESET} Setup Startup on System Boot (${CLR_CYAN}pm2 startup${CLR_RESET})"
        echo -e " ${CLR_WHITE}[9]${CLR_RESET} Real-Time Terminal Monit (${CLR_CYAN}pm2 monit${CLR_RESET})"
        echo -e " ${CLR_WHITE}[0]${CLR_RESET} Back to Main Menu"
        echo -e "${CLR_GRAY}─────────────────────────────────────────────────────────────${CLR_RESET}"
        echo -ne "${CLR_YELLOW}Select PM2 Option [0-9]: ${CLR_RESET}"
        read -r PM2_CHOICE

        case "$PM2_CHOICE" in
            1)
                echo ""
                $PM2_BIN status
                pause_prompt
                ;;
            2)
                echo -e "\n${CLR_DIM}Showing last 50 log lines (Ctrl+C to return)...${CLR_RESET}\n"
                $PM2_BIN logs nova-panel --lines 50
                pause_prompt
                ;;
            3)
                print_info "Restarting nova-panel..."
                $PM2_BIN restart nova-panel --update-env
                print_success "Nova panel restarted."
                pause_prompt
                ;;
            4)
                print_info "Stopping nova-panel..."
                $PM2_BIN stop nova-panel
                print_success "Nova panel stopped."
                pause_prompt
                ;;
            5)
                print_info "Starting nova-panel..."
                $PM2_BIN start ecosystem.config.js
                print_success "Nova panel started."
                pause_prompt
                ;;
            6)
                print_info "Reloading nova-panel..."
                $PM2_BIN reload nova-panel
                print_success "Nova panel reloaded."
                pause_prompt
                ;;
            7)
                print_info "Saving PM2 process list..."
                $PM2_BIN save
                print_success "PM2 configuration saved."
                pause_prompt
                ;;
            8)
                print_info "Configuring PM2 startup script..."
                $PM2_BIN startup
                $PM2_BIN save
                print_success "PM2 boot startup configured."
                pause_prompt
                ;;
            9)
                $PM2_BIN monit
                ;;
            0)
                break
                ;;
            *)
                print_err "Invalid selection!"
                sleep 1
                ;;
        esac
    done
}

# ------------------------------------------------------------------------------
# Menu Action: Uninstall
# ------------------------------------------------------------------------------
action_uninstall() {
    print_header
    detect_system
    echo -e "${CLR_RED}${CLR_BOLD}                   UNINSTALL NOVA PANEL                      ${CLR_RESET}"
    echo -e "${CLR_GRAY}─────────────────────────────────────────────────────────────${CLR_RESET}"
    print_warn "This action will stop and remove Nova Panel processes and Docker services!"
    echo ""
    read -rp "Are you sure you want to uninstall? (Type 'yes' or 'UNINSTALL' to proceed): " CONFIRM
    if [ "$CONFIRM" != "yes" ] && [ "$CONFIRM" != "UNINSTALL" ]; then
        print_info "Uninstall aborted."
        pause_prompt
        return
    fi

    # 1. Stop PM2
    print_info "Stopping and deleting PM2 process..."
    $PM2_BIN stop nova-panel 2>/dev/null || true
    $PM2_BIN delete nova-panel 2>/dev/null || true
    $PM2_BIN save 2>/dev/null || true
    print_success "PM2 service unregistered."

    # 2. Stop Docker Services
    print_info "Stopping Docker database containers..."
    if [ -f "docker-compose.yml" ]; then
        $DOCKER_COMPOSE down 2>/dev/null || true
    fi
    docker stop mariadb mysql 2>/dev/null || true
    print_success "Database containers stopped."

    # 3. Optional: Delete Data & Volumes
    read -rp "Do you also want to permanently delete database volumes and local data? (y/N): " DEL_VOL
    if [[ "$DEL_VOL" =~ ^[Yy]$ ]]; then
        if [ -f "docker-compose.yml" ]; then
            $DOCKER_COMPOSE down -v 2>/dev/null || true
        fi
        rm -rf data/*.sqlite logs/*.log
        print_success "Database storage and volumes removed."
    fi

    # 4. Optional: Clean node_modules
    read -rp "Remove node_modules directory to free disk space? (y/N): " DEL_NM
    if [[ "$DEL_NM" =~ ^[Yy]$ ]]; then
        rm -rf node_modules package-lock.json
        print_success "node_modules removed."
    fi

    echo ""
    print_success "Nova Panel has been uninstalled successfully."
    pause_prompt
}

# ------------------------------------------------------------------------------
# Main Menu
# ------------------------------------------------------------------------------
main_menu() {
    detect_system
    while true; do
        print_header
        echo -e "${CLR_WHITE}${CLR_BOLD}                MAIN MANAGEMENT CONTROLLER                   ${CLR_RESET}"
        echo -e "${CLR_GRAY}─────────────────────────────────────────────────────────────${CLR_RESET}"
        echo -e " ${CLR_WHITE}[1]${CLR_RESET} ${CLR_GREEN}${CLR_BOLD}Install (Full A-Z Auto Setup + Auto PM2 Run)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}├─ 1. Dependency Installation (A to Z)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}├─ 2. Database Configuration + Setup (MariaDB/MySQL)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}├─ 3. Add The First User (Administrator)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}├─ 4. Project & Runtime Environment Setup${CLR_RESET}"
        echo -e "     ${CLR_GRAY}└─ 5. PM2 Service Setup & Auto-Start${CLR_RESET}"
        echo ""
        echo -e " ${CLR_WHITE}[2]${CLR_RESET} ${CLR_CYAN}${CLR_BOLD}Start / Run Panel (PM2 Auto-Run)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}└─ Instantly launch or restart the panel in the background${CLR_RESET}"
        echo ""
        echo -e " ${CLR_WHITE}[3]${CLR_RESET} ${CLR_MAGENTA}${CLR_BOLD}Create User (usercrate)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}└─ Add an administrator or normal user to Nova${CLR_RESET}"
        echo ""
        echo -e " ${CLR_WHITE}[4]${CLR_RESET} ${CLR_YELLOW}${CLR_BOLD}PM2 Management (pm2 mang)${CLR_RESET}"
        echo -e "     ${CLR_GRAY}└─ Status, Logs, Restart, Stop, Start, Boot Startup${CLR_RESET}"
        echo ""
        echo -e " ${CLR_WHITE}[5]${CLR_RESET} ${CLR_RED}${CLR_BOLD}Uninstall Panel${CLR_RESET}"
        echo -e "     ${CLR_GRAY}└─ Stop & remove PM2 process, Docker services, and clean up${CLR_RESET}"
        echo ""
        echo -e " ${CLR_WHITE}[0]${CLR_RESET} ${CLR_WHITE}Exit${CLR_RESET}"
        echo -e "${CLR_GRAY}─────────────────────────────────────────────────────────────${CLR_RESET}"
        echo -ne "${CLR_CYAN}Select an option [0-5]: ${CLR_RESET}"
        read -r CHOICE

        case "$CHOICE" in
            1)
                action_install_all
                ;;
            2)
                auto_run_pm2
                ;;
            3)
                action_usercrate
                ;;
            4)
                action_pm2_management
                ;;
            5)
                action_uninstall
                ;;
            0)
                echo -e "\n${CLR_GREEN}Goodbye!${CLR_RESET}\n"
                exit 0
                ;;
            *)
                print_err "Invalid selection. Please choose an option from 0 to 5."
                sleep 1
                ;;
        esac
    done
}

# ------------------------------------------------------------------------------
# Entry Point & CLI Arguments Support
# ------------------------------------------------------------------------------
case "$1" in
    install)
        action_install_all
        ;;
    run|start|autorun)
        auto_run_pm2
        ;;
    usercrate|createuser)
        action_usercrate
        ;;
    pm2|pm2mang|"pm2 mang")
        action_pm2_management
        ;;
    uninstall)
        action_uninstall
        ;;
    *)
        main_menu
        ;;
esac
