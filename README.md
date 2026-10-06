# 🌌 Nova Panel — Next-Gen Game & App Server Management

Nova is a high-performance, modern server management web panel designed to manage Minecraft game servers, Python apps/bots, and Node.js applications directly on Docker containers.

The core engine is built purely on **Node.js** with real-time WebSockets, native Dockerode container virtualization, and built-in SFTP, while the user and administrative interfaces are rendered as native **PHP view templates**.

---

## 🚀 Port Layout & Architecture

| Service | Port | Description |
| :--- | :--- | :--- |
| **Panel Web** | `3001` | Primary web dashboard (Admin & Client views) |
| **Panel Database** | `3002` | MariaDB / MySQL Panel DB (`Nova` / `NovaStudio`) |
| **Dedicated API** | `3003` | Programmatic REST API & mcjars integrations |
| **SFTP Server** | `3004` | Built-in SFTP file server for FileZilla & WinSCP |
| **Server DB Host** | `3005` | MySQL 8.4 database host for user server databases |

---

## 🎮 Supported Server Platforms & Nests

1. **Minecraft (Java Edition & Bedrock Edition)**
   - **mcjars.app Integration**: Select server software (Paper, Purpur, Vanilla, Spigot, Fabric, Forge, BungeeCord, Velocity, Folia) and versions with automated jar downloads and `eula.txt` generation.
   - **Java Eggs**: Java 26, Java 25, Java 21, Java 17, Java 16, Java 11, Java 8 (`ghcr.io/pterodactyl/yolks:java_*`).
2. **Node.js Applications & Bots**
   - **Versions**: Node.js 25, 24, 23, 22, 21, 20, 18, 16 (`ghcr.io/ptero-eggs/yolks:nodejs_*`).
   - Default startup: `sh -c "npm install && npm start"`.
3. **Python Applications & Automation**
   - **Versions**: Python 3.13, 3.12, 3.11, 3.10, 3.9, 3.8, 2.7 (`ghcr.io/ptero-eggs/yolks:python_*`).
   - Default startup: `python3 app.py`.

---

## 🛠️ Quick Start with Master Script (`run.sh`)

You can manage the entire panel lifecycle with the all-in-one interactive menu script:
```bash
chmod +x run.sh
./run.sh
```

### Script Menu Features:
1. **`Install`**: Automated A-to-Z setup:
   - Dependency Installation (Auto-detects OS & installs Node.js, PHP, Docker, PM2, Git, etc.)
   - Database Configuration + Setup (Auto-generates `.env`, starts MariaDB & MySQL, runs migrations)
   - Add The First User (Creates administrator account)
   - Application Setup (Installs npm dependencies, creates directory structure, sets permissions)
   - PM2 Setup (Configures ecosystem and starts Nova Panel)
2. **`usercrate`**: Dedicated user & administrator creation utility.
3. **`pm2 mang`**: PM2 process management (Status, Logs, Restart, Stop, Start, Reload, Boot Startup).
4. **`uninstall`**: Safe uninstallation & cleanup utility.

---

## 🔧 Manual Setup & Commands

### 1. Database Setup (Docker)
Start the Panel MariaDB and Server MySQL containers using Docker Compose:
```bash
docker compose up -d
```
*(Note: If MariaDB is not running, Nova automatically defaults to local SQLite storage with zero disruption).*

### 2. Database Migration & Schema Seeding
Initialize the database tables, default locations, local node, allocations, nests, and eggs:
```bash
npm run migration
```

### 3. Create Administrator Account
Create a new administrator or user interactively or non-interactively:
```bash
# Interactive:
npm run createuser

# Or with CLI flags:
node bin/createuser.js --username admin --email admin@nova.test --password YourPassword --admin
```

### 4. Build Assets
```bash
npm run build
```

### 5. Start Nova Panel
```bash
# Direct run:
npm start

# Or with PM2 process manager:
pm2 start src/server.js --name nova-panel
```

---

## 📂 Instance Storage Structure

All containers and instances are rooted strictly inside the `Instance/` directory:
- **Server working directories**: `Instance/server/{userId}/{serverId}/` (mounted directly to container `/home/container`)
- **Server archives**: `Instance/Archive/{userId}/{serverId}/`
- **Server backups**: `Instance/Backup/{userId}/{serverId}/`

---

## 🌟 Key Features

- **Real-Time Interactive Terminal**: Live container stdout/stderr log streaming, ANSI color support, and input commands over WebSockets (`ws://localhost:3001/ws/server/{id}`).
- **Resource Monitoring**: Live gauges for CPU %, RAM MB/%, and Network I/O.
- **Full File Manager**: In-browser code editor, file uploads, downloads, folder creation, zip archiving, and archive extraction.
- **Built-in SFTP (Port 3004)**: Direct remote access using account credentials formatted as `username.serverId`.
- **Database Provisioning**: Create and manage MySQL databases per server.
- **Schedules & Automation**: Set up cron schedules to automate commands, restarts, and backups.
- **Brand Customization**: Dynamic Panel Name, Logo upload/URL with live preview, and Favicon upload/URL with live preview in Admin Settings.
- **Universal Database Engine**: Auto-detects MariaDB on port 3002 with seamless SQLite fallback.