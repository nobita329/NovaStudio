// Nova Interactive Console & Real-Time Terminal Client
(function () {
  'use strict';

  /**
   * Convert ANSI escape codes to styled HTML with strict entity escaping
   */
  function ansiToHtml(text) {
    if (!text) return '';

    // 1. Escape HTML special characters
    let escaped = text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');

    const colors = {
      '30': '#475569',
      '31': '#f87171',
      '32': '#4ade80',
      '33': '#fbbf24',
      '34': '#60a5fa',
      '35': '#c084fc',
      '36': '#38bdf8',
      '37': '#f1f5f9',
      '90': '#94a3b8',
      '91': '#fca5a5',
      '92': '#86efac',
      '93': '#fde047',
      '94': '#93c5fd',
      '95': '#d8b4fe',
      '96': '#67e8f9',
      '97': '#ffffff'
    };

    let openSpans = 0;

    // 2. Replace ANSI SGR sequences \x1b[...m
    escaped = escaped.replace(/\x1b\[([0-9;]*)m/g, (match, seq) => {
      if (!seq || seq === '0') {
        let closing = '';
        while (openSpans > 0) {
          closing += '</span>';
          openSpans--;
        }
        return closing;
      }

      const codes = seq.split(';');
      let styles = [];

      for (const code of codes) {
        if (code === '0' || code === '') {
          let closing = '';
          while (openSpans > 0) {
            closing += '</span>';
            openSpans--;
          }
          return closing;
        } else if (code === '1') {
          styles.push('font-weight: 600');
        } else if (code === '2') {
          styles.push('opacity: 0.7');
        } else if (code === '3') {
          styles.push('font-style: italic');
        } else if (code === '4') {
          styles.push('text-decoration: underline');
        } else if (colors[code]) {
          styles.push(`color: ${colors[code]}`);
        }
      }

      if (styles.length > 0) {
        openSpans++;
        return `<span style="${styles.join('; ')}">`;
      }
      return '';
    });

    while (openSpans > 0) {
      escaped += '</span>';
      openSpans--;
    }

    // 3. Strip remaining control sequences
    escaped = escaped.replace(/\x1b\[[0-9;?]*[A-Za-z]/g, '');

    return escaped;
  }

  function formatBytes(bytes, decimals = 2) {
    if (!bytes || bytes <= 0) return '0 B';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['B', 'KiB', 'MiB', 'GiB', 'TiB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
  }

  function formatUptime(seconds) {
    if (!seconds || seconds <= 0) return '0s';
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    if (d > 0) return `${d}d ${h}h`;
    if (h > 0) return `${h}h ${m}m`;
    if (m > 0) return `${m}m ${s}s`;
    return `${s}s`;
  }

  /**
   * Ultra-smooth HTML5 Canvas Chart with glowing curve and area gradient
   */
  class SmoothSparkline {
    constructor(canvas, options = {}) {
      this.canvas = canvas;
      this.ctx = canvas ? canvas.getContext('2d') : null;
      this.color = options.color || '#38bdf8';
      this.fillColor = options.fillColor || 'rgba(56, 189, 248, 0.18)';
      this.secondaryColor = options.secondaryColor || null;
      this.maxPoints = options.maxPoints || 26;
      this.data = new Array(this.maxPoints).fill(0);
      this.secondaryData = this.secondaryColor ? new Array(this.maxPoints).fill(0) : null;
      this.unit = options.unit || '';
      this.maxY = options.maxY || 10;
      this.width = 0;
      this.height = 0;

      if (this.canvas) {
        this.setupResize();
      }
    }

    setupResize() {
      const resize = () => {
        if (!this.canvas) return;
        const rect = this.canvas.getBoundingClientRect();
        const dpr = window.devicePixelRatio || 1;
        this.width = rect.width;
        this.height = rect.height;
        this.canvas.width = rect.width * dpr;
        this.canvas.height = rect.height * dpr;
        if (this.ctx) {
          this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        this.render();
      };
      window.addEventListener('resize', resize);
      setTimeout(resize, 80);
    }

    push(val, secondaryVal = null, customMax = null) {
      if (!this.canvas || !this.ctx) return;
      this.data.shift();
      this.data.push(val);
      if (this.secondaryData && secondaryVal !== null) {
        this.secondaryData.shift();
        this.secondaryData.push(secondaryVal);
      }
      if (customMax !== null) {
        this.maxY = customMax;
      }
      this.render();
    }

    render() {
      if (!this.ctx || !this.width || !this.height) return;
      const ctx = this.ctx;
      const w = this.width;
      const h = this.height;

      ctx.clearRect(0, 0, w, h);

      // Determine ceiling
      let max = Math.max(...this.data, 0.5);
      if (this.secondaryData) max = Math.max(max, ...this.secondaryData);
      if (this.maxY && this.maxY > max) {
        max = Math.max(max, this.maxY);
      }

      // Draw subtle horizontal grid lines & Y-axis labels
      ctx.lineWidth = 1;
      ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
      ctx.fillStyle = '#64748b';
      ctx.font = '9px JetBrains Mono, monospace';
      ctx.textAlign = 'right';

      const gridSteps = 3;
      for (let i = 0; i <= gridSteps; i++) {
        const y = (h - 18) * (i / gridSteps) + 6;
        ctx.beginPath();
        ctx.moveTo(0, y);
        ctx.lineTo(w - 38, y);
        ctx.stroke();

        const labelVal = Math.round(max * (1 - i / gridSteps));
        ctx.fillText(labelVal + this.unit, w - 2, y + 3);
      }

      // Render secondary wave (e.g. Outbound network)
      if (this.secondaryData && this.secondaryColor) {
        this.drawSpline(this.secondaryData, max, this.secondaryColor, null);
      }

      // Render primary wave (with glowing area gradient)
      this.drawSpline(this.data, max, this.color, this.fillColor);
    }

    drawSpline(points, max, strokeColor, fillColor) {
      const ctx = this.ctx;
      const w = this.width - 42;
      const h = this.height - 12;
      const step = w / (points.length - 1);

      const coords = points.map((p, i) => {
        const clamped = Math.max(0, p);
        const y = h - (clamped / max) * (h - 10) + 6;
        return { x: i * step, y: Math.max(4, Math.min(h + 6, y)) };
      });

      // Area gradient fill
      if (fillColor) {
        ctx.save();
        const fillPath = new Path2D();
        fillPath.moveTo(coords[0].x, h + 6);
        fillPath.lineTo(coords[0].x, coords[0].y);

        for (let i = 0; i < coords.length - 1; i++) {
          const p0 = coords[i === 0 ? 0 : i - 1];
          const p1 = coords[i];
          const p2 = coords[i + 1];
          const p3 = coords[i + 2] || p2;

          const cp1x = p1.x + (p2.x - p0.x) / 6;
          const cp1y = p1.y + (p2.y - p0.y) / 6;
          const cp2x = p2.x - (p3.x - p1.x) / 6;
          const cp2y = p2.y - (p3.y - p1.y) / 6;

          fillPath.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, p2.x, p2.y);
        }

        fillPath.lineTo(coords[coords.length - 1].x, h + 6);
        fillPath.closePath();

        const gradient = ctx.createLinearGradient(0, 0, 0, h + 6);
        gradient.addColorStop(0, fillColor);
        gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
        ctx.fillStyle = gradient;
        ctx.fill(fillPath);
        ctx.restore();
      }

      // Stroke curve
      ctx.beginPath();
      ctx.moveTo(coords[0].x, coords[0].y);

      for (let i = 0; i < coords.length - 1; i++) {
        const p0 = coords[i === 0 ? 0 : i - 1];
        const p1 = coords[i];
        const p2 = coords[i + 1];
        const p3 = coords[i + 2] || p2;

        const cp1x = p1.x + (p2.x - p0.x) / 6;
        const cp1y = p1.y + (p2.y - p0.y) / 6;
        const cp2x = p2.x - (p3.x - p1.x) / 6;
        const cp2y = p2.y - (p3.y - p1.y) / 6;

        ctx.bezierCurveTo(cp1x, cp1y, cp2x, cp2y, p2.x, p2.y);
      }

      ctx.strokeStyle = strokeColor;
      ctx.lineWidth = 2.2;
      ctx.lineJoin = 'round';
      ctx.lineCap = 'round';
      ctx.stroke();
    }
  }

  class NovaConsole {
    constructor(serverId, initialStatus = 'offline') {
      this.serverId = serverId;
      this.currentStatus = String(initialStatus || 'offline').toLowerCase();
      this.isStopped = this.currentStatus === 'offline';

      this.termContainer = document.getElementById('nova-term-container');
      this.output = document.getElementById('terminal-output');
      this.input = document.getElementById('terminal-input');

      // Metric Card elements
      this.cpuVal = document.getElementById('metric-cpu');
      this.memVal = document.getElementById('metric-mem');
      this.netInVal = document.getElementById('metric-net-in');
      this.netOutVal = document.getElementById('metric-net-out');
      this.uptimeVal = document.getElementById('stat-uptime');
      this.statusBadge = document.getElementById('server-status-badge');

      // Chart header values
      this.chartCpuVal = document.getElementById('chart-cpu-val');
      this.chartMemVal = document.getElementById('chart-mem-val');
      this.chartNetVal = document.getElementById('chart-net-val');

      // Font size & UI controls
      this.fontSize = parseInt(localStorage.getItem('nova_term_fontsize') || '11', 10);
      this.fontSizeLabel = document.getElementById('font-size-label');

      this.history = [];
      this.historyIndex = -1;
      this.ws = null;
      this.reconnectTimer = null;
      this.isConnecting = false;
      this.maxNodes = 2000;
      this.prevStatus = null;

      // Initialize Sparkline Charts & Terminal View
      this.initCharts();
      this.initTerminalView();
      this.initControls();

      // Check initial state: if offline, start completely empty with offline message
      if (this.isStopped) {
        this.showOfflineState();
      } else {
        this.showActiveState();
        this.initWebSocket();
      }
    }

    initCharts() {
      const cpuCanvas = document.getElementById('chart-cpu');
      const memCanvas = document.getElementById('chart-mem');
      const netCanvas = document.getElementById('chart-net');

      this.cpuChart = new SmoothSparkline(cpuCanvas, {
        color: '#38bdf8',
        fillColor: 'rgba(56, 189, 248, 0.2)',
        unit: '%',
        maxY: 10
      });

      this.memChart = new SmoothSparkline(memCanvas, {
        color: '#38bdf8',
        fillColor: 'rgba(56, 189, 248, 0.2)',
        unit: 'M',
        maxY: 512
      });

      this.netChart = new SmoothSparkline(netCanvas, {
        color: '#f43f5e',
        fillColor: 'rgba(244, 63, 94, 0.15)',
        secondaryColor: '#f59e0b',
        unit: 'B',
        maxY: 1024
      });
    }

    initTerminalView() {
      if (this.output) {
        this.output.style.fontSize = this.fontSize + 'px';
      }
      if (this.fontSizeLabel) {
        this.fontSizeLabel.textContent = this.fontSize + 'px';
      }
    }

    getOfflinePlaceholderHtml() {
      return `
        <div class="terminal-offline-placeholder">
          <div class="offline-icon-circle">
            <svg width="26" height="26" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 4.243a9 9 0 01-12.728 0m0 0l2.829-2.829m-2.829 2.829L3 21m2.828-15.536a9 9 0 0112.728 0M3 3l18 18"></path></svg>
          </div>
          <div class="offline-title">Server is offline</div>
          <div class="offline-subtitle">Start the server to view console output.</div>
        </div>
      `;
    }

    showOfflineState() {
      this.isStopped = true;
      this.currentStatus = 'offline';

      // 1. Clear terminal and render offline placeholder
      if (this.output) {
        this.output.classList.add('is-offline');
        this.output.innerHTML = this.getOfflinePlaceholderHtml();
      }

      // 2. Disable input bar
      if (this.input) {
        this.input.value = '';
        this.input.disabled = true;
        this.input.placeholder = 'Server is offline — start the server to send commands';
        const inputBar = this.input.closest('.terminal-input-bar');
        if (inputBar) inputBar.classList.add('is-disabled');
      }

      // 3. Reset metrics and sparklines to 0
      this.resetMetrics();

      // 4. Update status badges
      this.updateStatusBadge('offline');

      // 5. Disconnect WebSocket session & cancel reconnect timer
      this.disconnectWebSocket();
    }

    showActiveState() {
      this.isStopped = false;

      // 1. Remove offline styling and remove placeholder
      if (this.output) {
        this.output.classList.remove('is-offline');
        const placeholder = this.output.querySelector('.terminal-offline-placeholder');
        if (placeholder) {
          this.output.innerHTML = '';
        }
      }

      // 2. Enable input bar
      if (this.input) {
        this.input.disabled = false;
        this.input.placeholder = 'Type a command...';
        const inputBar = this.input.closest('.terminal-input-bar');
        if (inputBar) inputBar.classList.remove('is-disabled');
      }
    }

    clearTerminal() {
      if (this.output) {
        this.output.innerHTML = '';
      }
    }

    resetMetrics() {
      if (this.cpuVal) this.cpuVal.textContent = '0.00%';
      if (this.chartCpuVal) this.chartCpuVal.textContent = '0.00%';
      if (this.memVal) this.memVal.textContent = '0.0 MiB';
      if (this.chartMemVal) this.chartMemVal.textContent = '0.0 MiB';
      if (this.netInVal) this.netInVal.textContent = '0.00 KiB';
      if (this.netOutVal) this.netOutVal.textContent = '0.00 KiB';
      if (this.chartNetVal) this.chartNetVal.textContent = '0 B/s';
      if (this.uptimeVal) this.uptimeVal.textContent = '0s';
    }

    disconnectWebSocket() {
      if (this.reconnectTimer) {
        clearTimeout(this.reconnectTimer);
        this.reconnectTimer = null;
      }
      if (this.ws) {
        try {
          this.ws.onopen = null;
          this.ws.onmessage = null;
          this.ws.onclose = null;
          this.ws.onerror = null;
          this.ws.close();
        } catch (e) {}
        this.ws = null;
      }
      this.isConnecting = false;
    }

    initWebSocket() {
      // Prevent duplicate WebSocket connections
      this.disconnectWebSocket();

      this.isConnecting = true;
      const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
      const wsUrl = `${protocol}//${window.location.host}/ws/server/${this.serverId}`;

      try {
        this.ws = new WebSocket(wsUrl);
      } catch (err) {
        this.isConnecting = false;
        console.error('[Nova Terminal] WS Init error:', err);
        return;
      }

      this.ws.onopen = () => {
        this.isConnecting = false;
        if (this.reconnectTimer) {
          clearTimeout(this.reconnectTimer);
          this.reconnectTimer = null;
        }
      };

      this.ws.onmessage = (event) => {
        try {
          const msg = JSON.parse(event.data);
          if (msg.event === 'log') {
            if (!this.isStopped) {
              this.showActiveState();
              this.appendLog(msg.data);
            }
          } else if (msg.event === 'stats') {
            this.updateStats(msg.data);
          } else if (msg.event === 'status') {
            const newStatus = String(msg.status).toLowerCase();
            if (newStatus === 'offline') {
              this.showOfflineState();
            } else {
              this.currentStatus = newStatus;
              this.updateStatusBadge(newStatus);
              if (newStatus === 'online' || newStatus === 'running') {
                this.showActiveState();
              }
            }
          }
        } catch (err) {
          if (!this.isStopped) {
            this.appendLog(event.data);
          }
        }
      };

      this.ws.onclose = () => {
        this.isConnecting = false;
        this.ws = null;

        // Only reconnect if server is NOT stopped / offline
        if (!this.isStopped && (this.currentStatus === 'online' || this.currentStatus === 'starting')) {
          if (!this.reconnectTimer) {
            this.reconnectTimer = setTimeout(() => {
              this.reconnectTimer = null;
              if (!this.isStopped) {
                this.initWebSocket();
              }
            }, 2500);
          }
        }
      };

      this.ws.onerror = (err) => {
        this.isConnecting = false;
        console.error('[Nova Terminal] WS Error:', err);
      };
    }

    appendLog(text) {
      if (!this.output || !text || this.isStopped) return;

      const wasAtBottom =
        this.output.scrollHeight - this.output.clientHeight - this.output.scrollTop < 80;

      const formatted = ansiToHtml(text);
      this.output.insertAdjacentHTML('beforeend', formatted);

      // DOM buffer capping
      if (this.output.childNodes.length > this.maxNodes) {
        const removeCount = Math.min(300, this.output.childNodes.length - this.maxNodes + 100);
        for (let i = 0; i < removeCount && this.output.firstChild; i++) {
          this.output.removeChild(this.output.firstChild);
        }
      }

      if (wasAtBottom) {
        this.output.scrollTop = this.output.scrollHeight;
      }
    }

    updateStats(stats) {
      if (!stats) return;

      const s = String(stats.status || '').toLowerCase();
      if (s === 'offline') {
        if (!this.isStopped) {
          this.showOfflineState();
        }
        return;
      }

      // 1. CPU
      const cpu = stats.cpu || 0;
      if (this.cpuVal) this.cpuVal.textContent = `${cpu.toFixed(2)}%`;
      if (this.chartCpuVal) this.chartCpuVal.textContent = `${cpu.toFixed(2)}%`;
      if (this.cpuChart) this.cpuChart.push(cpu);

      // 2. Memory
      const memMb = stats.memory || 0;
      if (this.memVal) this.memVal.textContent = `${memMb.toFixed(1)} MiB`;
      if (this.chartMemVal) this.chartMemVal.textContent = `${memMb.toFixed(1)} MiB`;
      if (this.memChart) this.memChart.push(memMb, null, stats.memoryLimit || null);

      // 3. Network totals & speeds
      const rxBytes = stats.networkRx || 0;
      const txBytes = stats.networkTx || 0;
      const speedRx = stats.networkSpeedRx || 0;
      const speedTx = stats.networkSpeedTx || 0;

      if (this.netInVal) this.netInVal.textContent = formatBytes(rxBytes);
      if (this.netOutVal) this.netOutVal.textContent = formatBytes(txBytes);
      if (this.chartNetVal) {
        this.chartNetVal.textContent = `${formatBytes(speedRx)}/s`;
      }
      if (this.netChart) {
        this.netChart.push(speedRx, speedTx);
      }

      // 4. Uptime
      if (this.uptimeVal) {
        this.uptimeVal.textContent = formatUptime(stats.uptime || 0);
      }

      // 5. Status
      this.currentStatus = s;
      this.updateStatusBadge(s);
    }

    updateStatusBadge(status) {
      if (!status) return;
      const s = String(status).toLowerCase();

      // Audio feedback
      const panelSoundsEnabled = localStorage.getItem('panelSounds') !== 'false';
      if (this.prevStatus && this.prevStatus !== s && panelSoundsEnabled) {
        if (s === 'online' || s === 'running') {
          try {
            const runningSound = new Audio('/arix/online.mp3');
            runningSound.volume = 1;
            runningSound.play().catch(() => {});
          } catch (e) {}
        } else if (s === 'offline') {
          try {
            const offlineSound = new Audio('/arix/offline.mp3');
            offlineSound.volume = 0.2;
            offlineSound.play().catch(() => {});
          } catch (e) {}
        }
      }
      this.prevStatus = s;

      const classes = {
        online: 'badge-online',
        running: 'badge-online',
        active: 'badge-online',
        offline: 'badge-offline',
        starting: 'badge-starting',
        stopping: 'badge-stopping',
      };
      const cls = classes[s] || 'badge-offline';
      const label = s.charAt(0).toUpperCase() + s.slice(1);

      const badges = document.querySelectorAll('.server-status-badge, #server-status-badge, .arix-status-indicator');
      badges.forEach((badge) => {
        badge.innerHTML = `<span class="status-badge ${cls}"><span class="badge-dot"></span>${label}</span>`;
      });
    }

    updateStatus(status) {
      this.updateStatusBadge(status);
    }

    sendCommand(cmd) {
      if (!cmd || !cmd.trim()) return;
      const cleanCmd = cmd.trim();

      if (this.isStopped) {
        if (window.Nova && window.Nova.toast) {
          window.Nova.toast('Cannot send command: Server is offline', 'error');
        }
        return;
      }

      if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
        this.appendLog('\x1b[31m[Nova Terminal] Cannot send command: Disconnected from server.\x1b[0m\r\n');
        return;
      }

      this.ws.send(JSON.stringify({ event: 'command', command: cleanCmd }));
      this.history.push(cleanCmd);
      this.historyIndex = this.history.length;
      if (this.input) this.input.value = '';
    }

    sendPower(signal) {
      if (signal === 'start') {
        this.currentStatus = 'starting';
        this.isStopped = false;
        this.updateStatusBadge('starting');

        // 1. Clear terminal & old session logs completely
        this.clearTerminal();
        this.showActiveState();

        // 2. Fresh session header
        this.appendLog('\x1b[36m[Nova Daemon] Initializing fresh terminal session...\x1b[0m\r\n');
        this.appendLog('\x1b[36m[Nova Daemon] Sending START command to server container...\x1b[0m\r\n');

        // 3. Connect fresh WebSocket
        this.initWebSocket();

        // 4. Send start action via POST API & WS
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          this.ws.send(JSON.stringify({ event: 'power', signal: 'start' }));
        }

        fetch(`/server/${this.serverId}/power`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'start' })
        }).then(r => r.json()).then(data => {
          if (!data.success && data.error) {
            this.appendLog(`\x1b[31m[Nova Daemon] Start failed: ${data.error}\x1b[0m\r\n`);
            this.showOfflineState();
          }
        }).catch(err => {
          console.error('Start error:', err);
        });

      } else if (signal === 'restart') {
        this.currentStatus = 'starting';
        this.isStopped = false;
        this.updateStatusBadge('starting');

        // 1. Clear terminal output completely
        this.clearTerminal();
        this.showActiveState();

        // 2. Fresh session header
        this.appendLog('\x1b[36m[Nova Daemon] Restarting server instance...\x1b[0m\r\n');
        this.appendLog('\x1b[36m[Nova Daemon] Initializing fresh terminal session...\x1b[0m\r\n');

        // 3. Reconnect fresh WebSocket
        this.initWebSocket();

        // 4. Send restart action via POST API & WS
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          this.ws.send(JSON.stringify({ event: 'power', signal: 'restart' }));
        }

        fetch(`/server/${this.serverId}/power`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'restart' })
        }).catch(err => {
          console.error('Restart error:', err);
        });

      } else if (signal === 'stop') {
        this.currentStatus = 'stopping';
        this.updateStatusBadge('stopping');
        this.appendLog('\x1b[33m[Nova Daemon] Sending STOP command to server...\x1b[0m\r\n');

        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          this.ws.send(JSON.stringify({ event: 'power', signal: 'stop' }));
        }

        fetch(`/server/${this.serverId}/power`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'stop' })
        }).then(() => {
          this.showOfflineState();
        }).catch(() => {
          this.showOfflineState();
        });

      } else if (signal === 'kill') {
        this.updateStatusBadge('offline');

        // On Kill: immediately clear terminal, disconnect WS, show clean offline state
        this.showOfflineState();

        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
          this.ws.send(JSON.stringify({ event: 'power', signal: 'kill' }));
        }

        fetch(`/server/${this.serverId}/power`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ action: 'kill' })
        }).catch(() => {});
      }

      if (window.Nova && window.Nova.toast) {
        window.Nova.toast(`Sent power action: ${signal.toUpperCase()}`, 'info');
      }
    }

    setFontSize(size) {
      this.fontSize = Math.min(22, Math.max(9, size));
      localStorage.setItem('nova_term_fontsize', this.fontSize);
      if (this.output) {
        this.output.style.fontSize = this.fontSize + 'px';
      }
      if (this.fontSizeLabel) {
        this.fontSizeLabel.textContent = this.fontSize + 'px';
      }
    }

    initControls() {
      // Input enter & history navigation
      if (this.input) {
        this.input.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            this.sendCommand(this.input.value);
          } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (this.historyIndex > 0) {
              this.historyIndex--;
              this.input.value = this.history[this.historyIndex];
            }
          } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (this.historyIndex < this.history.length - 1) {
              this.historyIndex++;
              this.input.value = this.history[this.historyIndex];
            } else {
              this.historyIndex = this.history.length;
              this.input.value = '';
            }
          }
        });
      }

      // Font size buttons
      const fontDownBtn = document.getElementById('btn-font-down');
      if (fontDownBtn) {
        fontDownBtn.addEventListener('click', () => {
          this.setFontSize(this.fontSize - 1);
        });
      }

      const fontUpBtn = document.getElementById('btn-font-up');
      if (fontUpBtn) {
        fontUpBtn.addEventListener('click', () => {
          this.setFontSize(this.fontSize + 1);
        });
      }

      // Auto Fit toggle
      const autoFitBtn = document.getElementById('btn-autofit');
      if (autoFitBtn && this.termContainer) {
        autoFitBtn.addEventListener('click', () => {
          this.termContainer.classList.toggle('autofit');
          if (this.output) {
            this.output.scrollTop = this.output.scrollHeight;
          }
        });
      }

      // Clear button
      const clearBtn = document.getElementById('btn-clear-console');
      if (clearBtn && this.output) {
        clearBtn.addEventListener('click', () => {
          if (this.isStopped) {
            this.showOfflineState();
          } else {
            this.clearTerminal();
          }
        });
      }

      // Power buttons
      document.querySelectorAll('[data-power]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
          const signal = e.currentTarget.getAttribute('data-power');
          this.sendPower(signal);
        });
      });
    }
  }

  window.arixCopy = function (text) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
      const panelSoundsEnabled = localStorage.getItem('panelSounds') !== 'false';
      if (panelSoundsEnabled) {
        try {
          const copySound = new Audio('/arix/copy.mp3');
          copySound.volume = 0.25;
          copySound.play().catch(() => {});
        } catch (e) {}
      }
      if (window.Nova && window.Nova.toast) {
        window.Nova.toast('Copied to clipboard: ' + text, 'success');
      }
    }).catch((err) => {
      console.error('Clipboard copy error:', err);
    });
  };

  window.NovaConsole = NovaConsole;
})();
